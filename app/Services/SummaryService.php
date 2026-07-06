<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice as InvoiceModel;
use App\Models\SummaryDocument;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Document as GreenterDocument;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Log;

class SummaryService
{
    private $see;

    private function setupSee(Company $company)
    {
        $certField = $company->certificado_path ?? $company->certificate;
        if (!$certField) {
            throw new \Exception('No hay certificado digital configurado.');
        }

        // Try to use PEM file directly (no password needed, OpenSSL 3.0 compatible)
        $pemPath = storage_path('app/certificates/' . $company->ruc . '_certificate.pem');
        if (file_exists($pemPath)) {
            $pemContent = file_get_contents($pemPath);
            $this->see = new \Greenter\See();
            $this->see->setCertificate($pemContent);
        } else {
            $pfxPath = storage_path('app/certificates/' . $certField);
            if (!file_exists($pfxPath)) {
                $pfxPath = storage_path('app/' . $certField);
            }
            if (!file_exists($pfxPath)) {
                throw new \Exception('El archivo del certificado no existe.');
            }

            $password = $company->certificado_password;
            if (!$password) {
                throw new \Exception('No se ha configurado la contraseña del certificado.');
            }

            $pfxContent = file_get_contents($pfxPath);
            $cert = new X509Certificate($pfxContent, $password);
            $pemContent = $cert->export(X509ContentType::PEM);
            $this->see = new \Greenter\See();
            $this->see->setCertificate($pemContent);
        }

        $sunatUser = $company->soap_username ?? $company->ruc;
        $sunatPassword = $company->soap_password ?? $company->certificado_password ?? '';
        $this->see->setClaveSOL($company->ruc, $sunatUser, $sunatPassword);

        if ($company->soap_type_id == 2) {
            $this->see->setService(SunatEndpoints::FE_PRODUCCION);
        } else {
            $this->see->setService(SunatEndpoints::FE_BETA);
        }
    }

    private function buildGreenterCompany(Company $company)
    {
        $greenterCompany = new \Greenter\Model\Company\Company();
        $greenterCompany->setRuc($company->ruc);
        $greenterCompany->setRazonSocial($company->razon_social);
        $greenterCompany->setNombreComercial($company->nombre_comercial ?? $company->razon_social);
        $address = new Address();
        $address->setUbigueo($company->ubigeo ?? '150101');
        $address->setDepartamento($company->departamento ?? 'LIMA');
        $address->setProvincia($company->provincia ?? 'LIMA');
        $address->setDistrito($company->distrito ?? 'LIMA');
        $address->setUrbanizacion('-');
        $address->setDireccion($company->direccion);
        $address->setCodLocal('0000');
        $greenterCompany->setAddress($address);
        return $greenterCompany;
    }

    private function getNextCorrelativo(Company $company): string
    {
        $lastSummary = SummaryDocument::where('company_id', $company->id)
            ->where('fecha_emision', now()->format('Y-m-d'))
            ->orderBy('id', 'desc')
            ->first();
        $lastNum = 0;
        if ($lastSummary && $lastSummary->correlativo) {
            $parts = explode('-', $lastSummary->correlativo);
            $lastNum = (int)end($parts);
        }
        return str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    }

    public function sendDailySummary(): array
    {
        $company = Company::getMainCompany();
        if (!$company) {
            return ['success' => false, 'description' => 'No hay empresa principal configurada'];
        }

        $invoices = InvoiceModel::where('company_id', $company->id)
            ->where('tipo_documento', '03')
            ->where('sunat_estado', 'PENDIENTE')
            ->whereDate('fecha_emision', now()->format('Y-m-d'))
            ->get();

        if ($invoices->isEmpty()) {
            return ['success' => false, 'description' => 'No hay boletas pendientes para hoy'];
        }

        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return ['success' => false, 'description' => $e->getMessage()];
        }

        try {
            $correlativo = $this->getNextCorrelativo($company);
            $details = [];

            foreach ($invoices as $invoice) {
                $cd = $this->getClientData($invoice);
                $detail = new SummaryDetail();
                $detail->setTipoDoc('03');
                $detail->setSerieNro($invoice->serie . '-' . str_pad($invoice->numero, 8, '0', STR_PAD_LEFT));
                $detail->setClienteTipo($cd['tipo_doc']);
                $detail->setClienteNro($cd['num_doc']);
                $detail->setEstado('1');
                $detail->setTotal($invoice->total);
                $detail->setMtoOperGravadas($invoice->gravado ?? $invoice->subtotal);
                $detail->setMtoOperInafectas(0);
                $detail->setMtoOperExoneradas(0);
                $detail->setMtoOperExportacion(0);
                $detail->setMtoOperGratuitas(0);
                $detail->setMtoIGV($invoice->igv);
                $detail->setPorcentajeIgv($company->getActiveIgvPercent());
                $details[] = $detail;
            }

            $summary = new Summary();
            $summary->setFecGeneracion(new \DateTime(now()->format('Y-m-d')));
            $summary->setFecResumen(new \DateTime());
            $summary->setCorrelativo($correlativo);
            $summary->setMoneda('PEN');
            $summary->setCompany($this->buildGreenterCompany($company));
            $summary->setDetails($details);

            $result = $this->see->send($summary);

            if ($result->isSuccess()) {
                $ticket = $result->getTicket();
                $fullCorrelativo = 'RC-' . now()->format('Ymd') . '-' . $correlativo;
                $total = $invoices->count();

                SummaryDocument::create([
                    'company_id' => $company->id,
                    'fecha_emision' => now()->format('Y-m-d'),
                    'fecha_operacion' => now()->format('Y-m-d'),
                    'correlativo' => $fullCorrelativo,
                    'cantidad_documentos' => $total,
                    'ticket' => $ticket,
                    'sunat_estado' => 'ENVIADO',
                    'sunat_response' => json_encode(['ticket' => $ticket, 'count' => $total]),
                ]);

                foreach ($invoices as $invoice) {
                    $invoice->update([
                        'sunat_estado' => 'ENVIADO',
                        'sunat_code' => $ticket,
                        'sunat_description' => 'ENVIADO POR RESUMEN DIARIO. Ticket: ' . $ticket,
                    ]);
                }

                \Log::info("Daily summary sent: {$fullCorrelativo}, {$total} boletas, ticket: {$ticket}");

                return [
                    'success' => true,
                    'ticket' => $ticket,
                    'description' => "Resumen diario enviado con {$total} boleta(s). Ticket: {$ticket}",
                ];
            } else {
                $error = $result->getError();
                return ['success' => false, 'description' => $error->getMessage() ?? 'Error desconocido'];
            }
        } catch (\Exception $e) {
            \Log::error('Daily Summary Error: ' . $e->getMessage());
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }

    private function getClientData($invoice): array
    {
        $client = $invoice->customer;
        if ($client) {
            return [
                'tipo_doc' => $client->documento_tipo == '6' ? '6' : '1',
                'num_doc' => $client->documento_numero ?? '',
                'razon_social' => $client->nombre ?? 'CLIENTES VARIOS',
            ];
        }
        return [
            'tipo_doc' => '1',
            'num_doc' => '88888888',
            'razon_social' => 'CLIENTES VARIOS',
        ];
    }

    public function sendBoletaToSummary(InvoiceModel $invoice): array
    {
        $company = Company::getMainCompany();
        if (!$company) {
            return ['success' => false, 'description' => 'No hay empresa principal configurada'];
        }

        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return ['success' => false, 'description' => $e->getMessage()];
        }

        try {
            $correlativo = $this->getNextCorrelativo($company);
            $cd = $this->getClientData($invoice);

            $detail = new SummaryDetail();
            $detail->setTipoDoc('03');
            $detail->setSerieNro($invoice->serie . '-' . str_pad($invoice->numero, 8, '0', STR_PAD_LEFT));
            $detail->setClienteTipo($cd['tipo_doc']);
            $detail->setClienteNro($cd['num_doc']);
            $detail->setEstado('1');
            $detail->setTotal($invoice->total);
            $detail->setMtoOperGravadas($invoice->gravado ?? $invoice->subtotal);
            $detail->setMtoOperInafectas(0);
            $detail->setMtoOperExoneradas(0);
            $detail->setMtoOperExportacion(0);
            $detail->setMtoOperGratuitas(0);
            $detail->setMtoIGV($invoice->igv);
            $detail->setPorcentajeIgv($company->getActiveIgvPercent());

            $summary = new Summary();
            $summary->setFecGeneracion(new \DateTime($invoice->fecha_emision));
            $summary->setFecResumen(new \DateTime());
            $summary->setCorrelativo($correlativo);
            $summary->setMoneda('PEN');
            $summary->setCompany($this->buildGreenterCompany($company));
            $summary->setDetails([$detail]);

            $result = $this->see->send($summary);

            if ($result->isSuccess()) {
                $ticket = $result->getTicket();
                $fullCorrelativo = 'RC-' . now()->format('Ymd') . '-' . $correlativo;

                $summaryDoc = SummaryDocument::create([
                    'company_id' => $company->id,
                    'fecha_emision' => now()->format('Y-m-d'),
                    'fecha_operacion' => $invoice->fecha_emision,
                    'correlativo' => $fullCorrelativo,
                    'cantidad_documentos' => 1,
                    'ticket' => $ticket,
                    'sunat_estado' => 'ENVIADO',
                    'sunat_response' => json_encode(['ticket' => $ticket]),
                ]);

                $invoice->update([
                    'sunat_estado' => 'ENVIADO',
                    'sunat_code' => $ticket,
                    'sunat_description' => 'ENVIADO POR RESUMEN DIARIO. Ticket: ' . $ticket,
                ]);

                Log::info('Boleta sent via Summary', [
                    'invoice' => $invoice->full_number,
                    'ticket' => $ticket,
                    'correlativo' => $correlativo,
                ]);

                return [
                    'success' => true,
                    'code' => $ticket,
                    'description' => 'Documento enviado por resumen diario. Ticket: ' . $ticket,
                    'ticket' => $ticket,
                    'summary_id' => $summaryDoc->id,
                ];
            } else {
                $error = $result->getError();
                $invoice->update([
                    'sunat_estado' => 'RECHAZADO',
                    'sunat_code' => $error->getCode() ?? 'ERROR',
                    'sunat_description' => $error->getMessage() ?? 'Error desconocido',
                ]);
                return ['success' => false, 'code' => $error->getCode(), 'description' => $error->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error('Summary Error: ' . $e->getMessage());
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }

    public function checkTicketStatus(string $ticket): array
    {
        $company = Company::getMainCompany();
        if (!$company) {
            return ['success' => false, 'description' => 'No hay empresa principal configurada'];
        }

        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return ['success' => false, 'description' => $e->getMessage()];
        }

        try {
            $result = $this->see->getStatus($ticket);

            if ($result->isSuccess()) {
                $cdrZip = $result->getCdrZip();
                if ($cdrZip) {
                    $cdrFileName = 'R-' . $company->ruc . '-SUMMARY-' . $ticket . '.zip';
                    $cdrPath = 'sunat/' . $cdrFileName;
                    \Storage::put($cdrPath, $cdrZip);
                }

                // Update invoice status to ACEPTADO
                InvoiceModel::where('sunat_code', $ticket)
                    ->update([
                        'sunat_estado' => 'ACEPTADO',
                        'sunat_description' => 'ACEPTADO VÍA RESUMEN DIARIO',
                    ]);

                // Update summary document
                SummaryDocument::where('ticket', $ticket)
                    ->update([
                        'sunat_estado' => 'ACEPTADO',
                        'sunat_fecha' => now(),
                    ]);

                return ['success' => true, 'description' => 'Documento aceptado por SUNAT'];
            } else {
                $error = $result->getError();
                if ($error) {
                    SummaryDocument::where('ticket', $ticket)
                        ->update([
                            'sunat_estado' => 'RECHAZADO',
                            'sunat_response' => json_encode(['error' => $error->getMessage()]),
                        ]);
                    return ['success' => false, 'description' => $error->getMessage()];
                }
                return ['success' => false, 'description' => 'Pendiente de procesar'];
            }
        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage());
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }

    public function voidBoleta(InvoiceModel $invoice): array
    {
        $company = Company::getMainCompany();
        if (!$company) {
            return ['success' => false, 'description' => 'No hay empresa principal configurada'];
        }

        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return ['success' => false, 'description' => $e->getMessage()];
        }

        try {
            $correlativo = $this->getNextCorrelativo($company);
            $cd = $this->getClientData($invoice);

            $detail = new SummaryDetail();
            $detail->setTipoDoc('03');
            $detail->setSerieNro($invoice->serie . '-' . str_pad($invoice->numero, 8, '0', STR_PAD_LEFT));
            $detail->setClienteTipo($cd['tipo_doc']);
            $detail->setClienteNro($cd['num_doc']);
            $detail->setEstado('3');
            $detail->setTotal(0);
            $detail->setMtoOperGravadas(0);
            $detail->setMtoOperInafectas(0);
            $detail->setMtoOperExoneradas(0);
            $detail->setMtoOperExportacion(0);
            $detail->setMtoOperGratuitas(0);
            $detail->setMtoIGV(0);

            $summary = new Summary();
            $summary->setFecGeneracion(new \DateTime($invoice->fecha_emision));
            $summary->setFecResumen(new \DateTime());
            $summary->setCorrelativo($correlativo);
            $summary->setMoneda('PEN');
            $summary->setCompany($this->buildGreenterCompany($company));
            $summary->setDetails([$detail]);

            $result = $this->see->send($summary);

            if ($result->isSuccess()) {
                $ticket = $result->getTicket();

                SummaryDocument::create([
                    'company_id' => $company->id,
                    'fecha_emision' => now()->format('Y-m-d'),
                    'fecha_operacion' => $invoice->fecha_emision,
                    'correlativo' => $correlativo,
                    'cantidad_documentos' => 1,
                    'ticket' => $ticket,
                    'sunat_estado' => 'ENVIADO',
                    'sunat_response' => json_encode(['ticket' => $ticket]),
                ]);

                $invoice->update([
                    'sunat_estado' => 'ANULADO',
                    'sunat_code' => $ticket,
                    'sunat_description' => 'ANULADO VÍA RESUMEN DIARIO. Ticket: ' . $ticket,
                ]);

                Log::info('Boleta voided via Summary', [
                    'invoice' => $invoice->full_number,
                    'ticket' => $ticket,
                ]);

                return [
                    'success' => true,
                    'ticket' => $ticket,
                    'description' => 'Boleta anulada por resumen diario. Ticket: ' . $ticket,
                ];
            } else {
                $error = $result->getError();
                return ['success' => false, 'description' => $error->getMessage() ?? 'Error desconocido'];
            }
        } catch (\Exception $e) {
            Log::error('Void boleta Summary Error: ' . $e->getMessage());
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }

    public function sendNoteToSummary(InvoiceModel $note, InvoiceModel $originalInvoice, string $tipoDoc): array
    {
        $company = Company::getMainCompany();
        if (!$company) {
            return ['success' => false, 'description' => 'No hay empresa principal configurada'];
        }

        try {
            $this->setupSee($company);
        } catch (\Exception $e) {
            return ['success' => false, 'description' => $e->getMessage()];
        }

        try {
            $correlativo = $this->getNextCorrelativo($company);
            $cd = $this->getClientData($note->id ? $note : $originalInvoice);

            // Reference the original boleta
            $refDoc = new GreenterDocument();
            $refDoc->setTipoDoc('03');
            $refDoc->setSerieNro($originalInvoice->serie . '-' . str_pad($originalInvoice->numero, 8, '0', STR_PAD_LEFT));

            $detail = new SummaryDetail();
            $detail->setTipoDoc($tipoDoc); // '07' for credit note, '08' for debit note
            $detail->setSerieNro($note->serie . '-' . str_pad($note->numero, 8, '0', STR_PAD_LEFT));
            $detail->setClienteTipo($cd['tipo_doc']);
            $detail->setClienteNro($cd['num_doc']);
            $detail->setDocReferencia($refDoc);
            $detail->setEstado('1');
            $detail->setTotal($note->total);
            $detail->setMtoOperGravadas($note->gravado ?? $note->subtotal);
            $detail->setMtoOperInafectas(0);
            $detail->setMtoOperExoneradas(0);
            $detail->setMtoOperExportacion(0);
            $detail->setMtoOperGratuitas(0);
            $detail->setMtoIGV($note->igv);
            $detail->setPorcentajeIgv($company->getActiveIgvPercent());

            $summary = new Summary();
            $summary->setFecGeneracion(new \DateTime($note->fecha_emision));
            $summary->setFecResumen(new \DateTime());
            $summary->setCorrelativo($correlativo);
            $summary->setMoneda('PEN');
            $summary->setCompany($this->buildGreenterCompany($company));
            $summary->setDetails([$detail]);

            $result = $this->see->send($summary);

            if ($result->isSuccess()) {
                $ticket = $result->getTicket();

                SummaryDocument::create([
                    'company_id' => $company->id,
                    'fecha_emision' => now()->format('Y-m-d'),
                    'fecha_operacion' => $note->fecha_emision,
                    'correlativo' => $correlativo,
                    'cantidad_documentos' => 1,
                    'ticket' => $ticket,
                    'sunat_estado' => 'ENVIADO',
                    'sunat_response' => json_encode(['ticket' => $ticket]),
                ]);

                $note->update([
                    'sunat_estado' => 'ENVIADO',
                    'sunat_code' => $ticket,
                    'sunat_description' => 'ENVIADO POR RESUMEN DIARIO. Ticket: ' . $ticket,
                ]);

                Log::info('Note sent via Summary', [
                    'note' => $note->full_number,
                    'ticket' => $ticket,
                ]);

                return [
                    'success' => true,
                    'ticket' => $ticket,
                    'description' => 'Documento enviado por resumen diario. Ticket: ' . $ticket,
                ];
            } else {
                $error = $result->getError();
                return ['success' => false, 'description' => $error->getMessage() ?? 'Error desconocido'];
            }
        } catch (\Exception $e) {
            Log::error('Summary Note Error: ' . $e->getMessage());
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }
}
