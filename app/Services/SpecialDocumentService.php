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
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Perception\Perception;
use Greenter\Model\Perception\PerceptionDetail;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Log;

class SpecialDocumentService
{
    private $see;

    private function setupSee(Company $company)
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
        $sunatPassword = $company->soap_password ?? $password;
        $this->see->setClaveSOL($company->ruc, $sunatUser, $sunatPassword);

        if ($company->soap_type_id == 2) {
            $this->see->setService(SunatEndpoints::FE_HOMOLOGACION);
        } else {
            $this->see->setService(SunatEndpoints::FE_BETA);
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
            $this->setupSee($company);
        } catch (\Exception $e) {
            return ['success' => false, 'description' => $e->getMessage()];
        }

        try {
            $entity = $doc->entity;
            $despatch = new Despatch();
            $despatch->setSerie($doc->serie);
            $despatch->setCorrelativo(str_pad($doc->numero, 8, '0', STR_PAD_LEFT));
            $despatch->setFechaEmision(new \DateTime($doc->fecha_emision));
            $despatch->setCompany($this->buildGreenterCompany($company));
            $despatch->setDestinatario($this->buildClient([
                'tipo_doc' => $entity->tipo_doc,
                'num_doc' => $entity->num_doc,
                'razon_social' => $entity->razon_social,
                'direccion' => $entity->direccion,
            ]));

            $shipment = new Shipment();
            $shipment->setCodTraslado('01');
            $shipment->setModTraslado('01');
            $shipment->setFecTraslado(new \DateTime($doc->fecha_emision));
            $shipment->setPesoBruto(1000.00);
            $shipment->setUndPesoBruto('KGM');
            $shipment->setPartida(new Direction('LIMA', 'LIMA', 'LIMA', '150101', 'Av. Siempre Viva 123'));
            $shipment->setLlegada(new Direction('LIMA', 'LIMA', 'LIMA', '150101', 'Av. Los Olivos 456'));
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

            $result = $this->see->send($despatch);

            if ($result->isSuccess()) {
                $xmlContent = $this->see->getFactory()->getLastXml();
                $doc->update([
                    'sunat_estado' => 'ACEPTADO',
                    'sunat_code' => '0',
                    'xml_content' => $xmlContent,
                ]);
                return ['success' => true, 'description' => 'Guía de remisión enviada correctamente'];
            } else {
                $error = $result->getError();
                $doc->update(['sunat_estado' => 'RECHAZADO', 'sunat_code' => $error->getCode()]);
                return ['success' => false, 'description' => $error->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error('Despatch Error: ' . $e->getMessage());
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
