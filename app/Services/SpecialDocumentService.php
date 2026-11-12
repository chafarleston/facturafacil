<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SpecialDocument;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company as GreenterCompany;
use Greenter\Model\Retention\Retention;
use Greenter\Model\Retention\RetentionDetail;
use Greenter\Model\Retention\Payment;
use Greenter\Model\Retention\Exchange;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Perception\Perception;
use Greenter\Model\Perception\PerceptionDetail;
use Greenter\Sunat\GRE\Api\AuthApi;
use Greenter\Sunat\GRE\Api\CpeApi;
use Greenter\Sunat\GRE\Configuration as GreConfiguration;
use Greenter\Sunat\GRE\Model\CpeDocument;
use Greenter\Sunat\GRE\Model\CpeDocumentArchivo;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Zip\ZipFly;
use Illuminate\Support\Facades\Log;

class SpecialDocumentService
{
    private $see;

    private function setupSee(Company $company, string $serviceType = 'factura')
    {
        $certField = $company->certificado_path ?? $company->certificate;
        if (!$certField) throw new \Exception('No hay certificado configurado');

        // Try PEM first (no password needed, OpenSSL 3.0 compatible)
        $pemPath = storage_path('app/certificates/' . $company->ruc . '_certificate.pem');
        if (file_exists($pemPath)) {
            $pemContent = file_get_contents($pemPath);
            $this->see = new \Greenter\See();
            $this->see->setCertificate($pemContent);
        } else {
            $pfxPath = storage_path('app/certificates/' . $certField);
            if (!file_exists($pfxPath)) $pfxPath = storage_path('app/' . $certField);
            if (!file_exists($pfxPath)) throw new \Exception('Certificado no encontrado');

            $password = $company->certificado_password;
            if (!$password) throw new \Exception('Contraseña del certificado no configurada');

            $pfxContent = file_get_contents($pfxPath);
            $cert = new X509Certificate($pfxContent, $password);
            $pemContent = $cert->export(X509ContentType::PEM);
            $this->see = new \Greenter\See();
            $this->see->setCertificate($pemContent);
        }

        $sunatUser = $company->soap_username ?? $company->ruc;
        $sunatPassword = $company->soap_password ?? $company->certificado_password ?? '';
        $this->see->setClaveSOL($company->ruc, $sunatUser, $sunatPassword);

        $production = $company->soap_type_id == 2;

        if ($serviceType === 'guia') {
            $this->see->setService($production ? SunatEndpoints::GUIA_PRODUCCION : SunatEndpoints::GUIA_BETA);
        } elseif ($serviceType === 'retencion') {
            $this->see->setService($production ? SunatEndpoints::RETENCION_PRODUCCION : SunatEndpoints::RETENCION_BETA);
        } else {
            $this->see->setService($production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
        }
    }

    private function buildGreenterCompany(Company $company): GreenterCompany
    {
        $gc = new GreenterCompany();
        $gc->setRuc($company->ruc);
        $gc->setRazonSocial($company->razon_social);
        $gc->setNombreComercial($company->nombre_comercial ?? $company->razon_social);
        $addr = new Address();
        $addr->setUbigueo($company->ubigeo ?? '150101');
        $addr->setDepartamento($company->departamento ?? 'LIMA');
        $addr->setProvincia($company->provincia ?? 'LIMA');
        $addr->setDistrito($company->distrito ?? 'LIMA');
        $addr->setUrbanizacion('-');
        $addr->setDireccion($company->direccion);
        $addr->setCodLocal('0000');
        $gc->setAddress($addr);
        return $gc;
    }

    private function buildClient($entityData): Client
    {
        $client = new Client();
        $client->setTipoDoc($entityData['tipo_doc'] ?? '6');
        $client->setNumDoc($entityData['num_doc'] ?? '');
        $client->setRznSocial($entityData['razon_social'] ?? '');
        if (!empty($entityData['direccion'])) {
            $addr = new Address();
            $addr->setDireccion($entityData['direccion']);
            $client->setAddress($addr);
        }
        return $client;
    }

    private function buildTransportist(SpecialDocument $doc): ?Transportist
    {
        if (!$doc->transportista_num_doc) {
            return null;
        }

        $transportista = new Transportist();
        $transportista->setTipoDoc($doc->transportista_tipo_doc ?: '6');
        $transportista->setNumDoc($doc->transportista_num_doc);
        $transportista->setRznSocial($doc->transportista_razon_social ?: '');
        $transportista->setNroMtc($doc->transportista_nro_mtc ?: '');

        return $transportista;
    }

    private function buildVehicle(SpecialDocument $doc): ?Vehicle
    {
        if (!$doc->vehiculo_placa) {
            return null;
        }

        $vehiculo = new Vehicle();
        $vehiculo->setPlaca($doc->vehiculo_placa);
        $vehiculo->setNroCirculacion($doc->vehiculo_nro_circulacion ?: null);
        $vehiculo->setCodEmisor($doc->vehiculo_cod_emisor ?: null);
        $vehiculo->setNroAutorizacion($doc->vehiculo_nro_autorizacion ?: null);

        return $vehiculo;
    }

    private function buildDriver(SpecialDocument $doc): ?Driver
    {
        if (!$doc->chofer_num_doc) {
            return null;
        }

        $chofer = new Driver();
        $chofer->setTipo('01');
        $chofer->setTipoDoc($doc->chofer_tipo_doc ?: '1');
        $chofer->setNroDoc($doc->chofer_num_doc);
        $chofer->setNombres($doc->chofer_nombres ?: '');
        $chofer->setApellidos($doc->chofer_apellidos ?: '');
        $chofer->setLicencia($doc->chofer_licencia ?: '');

        return $chofer;
    }

    public function sendRetention(SpecialDocument $doc): array
    {
        $company = Company::getMainCompany();
        if (!$company) return ['success' => false, 'description' => 'No hay empresa principal'];

        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return ['success' => false, 'description' => $e->getMessage()];
        }

        try {
            $entity = $doc->entity;
            $retencion = new Retention();
            $retencion->setSerie($doc->serie);
            $retencion->setCorrelativo(str_pad($doc->numero, 8, '0', STR_PAD_LEFT));
            $retencion->setFechaEmision(new \DateTime($doc->fecha_emision));
            $retencion->setCompany($this->buildGreenterCompany($company));
            $retencion->setProveedor($this->buildClient([
                'tipo_doc' => $entity->tipo_doc,
                'num_doc' => $entity->num_doc,
                'razon_social' => $entity->razon_social,
                'direccion' => $entity->direccion,
            ]));
            $retencion->setRegimen($doc->regimen ?? '01');
            $retencion->setTasa($doc->tasa ?? 3);
            $retencion->setImpRetenido($doc->imp_retenido ?? $doc->total);
            $retencion->setImpPagado($doc->imp_pagado ?? $doc->total);
            $retencion->setObservacion($doc->observacion ?? '');

            $result = $this->see->send($retencion);

            if ($result->isSuccess()) {
                $xmlContent = $this->see->getFactory()->getLastXml();
                $doc->update([
                    'sunat_estado' => 'ACEPTADO',
                    'sunat_code' => '0',
                    'xml_content' => $xmlContent,
                ]);
                return ['success' => true, 'description' => 'Retención enviada correctamente'];
            } else {
                $error = $result->getError();
                $doc->update(['sunat_estado' => 'RECHAZADO', 'sunat_code' => $error->getCode()]);
                return ['success' => false, 'description' => $error->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error('Retention Error: ' . $e->getMessage());
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }

    public function sendDespatch(SpecialDocument $doc): array
    {
        $company = Company::getMainCompany();
        if (!$company) return ['success' => false, 'description' => 'No hay empresa principal'];

        try {
            $this->setupSee($company, 'guia');
        } catch (\Exception $e) {
            return ['success' => false, 'description' => $e->getMessage()];
        }

        try {
            $entity = $doc->entity;
            if (!$entity) {
                return ['success' => false, 'description' => 'La guía no tiene destinatario.'];
            }

            if ((float) ($doc->peso_total ?? 0) <= 0) {
                return ['success' => false, 'description' => 'La guía debe tener un peso total mayor a cero.'];
            }

            $despatch = new Despatch();
            $despatch->setTipoDoc('09');
            $despatch->setSerie($doc->serie);
            $despatch->setCorrelativo(str_pad($doc->numero, 8, '0', STR_PAD_LEFT));
            $despatch->setFechaEmision(new \DateTime($doc->fecha_emision));
            $despatch->setObservacion($doc->observacion);
            $despatch->setCompany($this->buildGreenterCompany($company));
            $despatch->setDestinatario($this->buildClient([
                'tipo_doc' => $entity->tipo_doc,
                'num_doc' => $entity->num_doc,
                'razon_social' => $entity->razon_social,
                'direccion' => $entity->direccion,
            ]));

            $shipment = new Shipment();
            $shipment->setCodTraslado($doc->cod_traslado ?: '01');
            $shipment->setDesTraslado($doc->des_traslado ?: null);
            $shipment->setModTraslado($doc->mod_traslado ?: '01');
            $shipment->setFecTraslado(new \DateTime($doc->fecha_traslado ?: $doc->fecha_emision));
            $shipment->setPesoTotal((float) $doc->peso_total);
            $shipment->setUndPesoTotal($doc->und_peso_total ?: 'KGM');

            if ($doc->num_bultos) {
                $shipment->setNumBultos((int) $doc->num_bultos);
            }

            $shipment->setPartida(new Direction(
                $doc->ubigeo_partida ?: $company->ubigeo,
                $doc->dir_partida ?: $company->direccion
            ));
            $shipment->setLlegada(new Direction(
                $doc->ubigeo_llegada ?: '150101',
                $doc->dir_llegada ?: $entity->direccion
            ));

            if ($transportista = $this->buildTransportist($doc)) {
                $shipment->setTransportista($transportista);
            }
            if ($vehiculo = $this->buildVehicle($doc)) {
                $shipment->setVehiculo($vehiculo);
            }
            if ($chofer = $this->buildDriver($doc)) {
                $shipment->setChoferes([$chofer]);
            }

            $despatch->setEnvio($shipment);

            $items = [];
            foreach ($doc->items as $item) {
                $detail = new DespatchDetail();
                $detail->setCantidad($item->cantidad);
                $detail->setUnidad($item->unidad);
                $detail->setDescripcion($item->descripcion);
                $detail->setCodigo($item->codigo ?? '');
                $items[] = $detail;
            }
            $despatch->setDetails($items);

            if (!$company->gre_client_id || !$company->gre_client_secret || !$company->soap_username || !$company->soap_password) {
                return [
                    'success' => false,
                    'description' => 'Configure las credenciales GRE: usuario SOL, clave SOL, Client ID y Client Secret.',
                ];
            }

            $xmlContent = $this->see->getXmlSigned($despatch);
            if (!$xmlContent) {
                return ['success' => false, 'description' => 'No se pudo generar el XML firmado de la guía.'];
            }

            $fileBase = $company->ruc . '-09-' . $doc->serie . '-' . str_pad($doc->numero, 8, '0', STR_PAD_LEFT);
            $zipContent = (new ZipFly())->compress($fileBase . '.xml', $xmlContent);

            if (!$zipContent) {
                return ['success' => false, 'description' => 'No se pudo generar el ZIP de la guía.'];
            }

            $solUser = trim((string) $company->soap_username);
            $apiUser = str_starts_with($solUser, (string) $company->ruc)
                ? $solUser
                : $company->ruc . $solUser;

            $authApi = new AuthApi(new \GuzzleHttp\Client());
            $tokenResult = $authApi->getToken(
                'password',
                'https://api-cpe.sunat.gob.pe',
                $company->gre_client_id,
                $company->gre_client_secret,
                $apiUser,
                $company->soap_password
            );

            $token = $tokenResult->getAccessToken();
            if (!$token) {
                return ['success' => false, 'description' => 'SUNAT GRE no devolvió un token de acceso.'];
            }

            $config = GreConfiguration::getDefaultConfiguration()->setAccessToken($token);
            $config->setHost('https://api-cpe.sunat.gob.pe/v1');
            $api = new CpeApi(new \GuzzleHttp\Client(), $config);

            $archivo = (new CpeDocumentArchivo())
                ->setNomArchivo($fileBase . '.zip')
                ->setArcGreZip(base64_encode($zipContent))
                ->setHashZip(hash('sha256', $zipContent));

            $response = $api->enviarCpe(
                $fileBase,
                (new CpeDocument())->setArchivo($archivo)
            );

            $ticket = $response->getNumTicket();

            $doc->update([
                'sunat_estado' => 'ENVIADO',
                'sunat_code' => 'GRE',
                'sunat_description' => 'ENVIADO A GRE. Ticket: ' . ($ticket ?: 'sin ticket'),
                'xml_content' => $xmlContent,
                'ticket' => $ticket,
            ]);

            return [
                'success' => true,
                'code' => $ticket,
                'description' => 'Guía enviada a SUNAT GRE. Ticket: ' . ($ticket ?: 'sin ticket'),
            ];
        } catch (\Exception $e) {
            Log::error('Despatch GRE Error: ' . $e->getMessage());
            $doc->update([
                'sunat_estado' => 'RECHAZADO',
                'sunat_code' => 'GRE_ERROR',
                'sunat_description' => $e->getMessage(),
            ]);
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }

    public function sendPerception(SpecialDocument $doc): array
    {
        $company = Company::getMainCompany();
        if (!$company) return ['success' => false, 'description' => 'No hay empresa principal'];

        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return ['success' => false, 'description' => $e->getMessage()];
        }

        try {
            $entity = $doc->entity;
            $percepcion = new Perception();
            $percepcion->setSerie($doc->serie);
            $percepcion->setCorrelativo(str_pad($doc->numero, 8, '0', STR_PAD_LEFT));
            $percepcion->setFechaEmision(new \DateTime($doc->fecha_emision));
            $percepcion->setCompany($this->buildGreenterCompany($company));
            $percepcion->setProveedor($this->buildClient([
                'tipo_doc' => $entity->tipo_doc,
                'num_doc' => $entity->num_doc,
                'razon_social' => $entity->razon_social,
                'direccion' => $entity->direccion,
            ]));
            $percepcion->setRegimen('01');
            $percepcion->setTasa(2);
            $percepcion->setImpPercepcion($doc->total);
            $percepcion->setImpTotalPagar($doc->total);

            $result = $this->see->send($percepcion);

            if ($result->isSuccess()) {
                $xmlContent = $this->see->getFactory()->getLastXml();
                $doc->update([
                    'sunat_estado' => 'ACEPTADO',
                    'sunat_code' => '0',
                    'xml_content' => $xmlContent,
                ]);
                return ['success' => true, 'description' => 'Percepción enviada correctamente'];
            } else {
                $error = $result->getError();
                $doc->update(['sunat_estado' => 'RECHAZADO', 'sunat_code' => $error->getCode()]);
                return ['success' => false, 'description' => $error->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error('Perception Error: ' . $e->getMessage());
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }
}
