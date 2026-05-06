<?php

namespace App\CoreFacturalo;

use App\CoreFacturalo\Documents\InvoiceBuilder;
use App\CoreFacturalo\Documents\NoteBuilder;
use App\CoreFacturalo\Documents\SummaryBuilder;
use App\CoreFacturalo\Documents\VoidedBuilder;
use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
use App\CoreFacturalo\Helpers\Xml\XmlFormat;
use App\CoreFacturalo\Helpers\Xml\XmlHash;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\WS\Client\WsClient;
use App\CoreFacturalo\WS\Services\BillSender;
use App\CoreFacturalo\WS\Services\ExtService;
use App\CoreFacturalo\WS\Services\SummarySender;
use App\CoreFacturalo\WS\Services\SunatEndpoints;
use App\CoreFacturalo\WS\Signed\XmlSigned;
use App\CoreFacturalo\WS\Validator\XmlErrorCodeProvider;
use Exception;
use Mpdf\Mpdf;

class Facturalo
{
    use StorageDocument;

    protected $signer;
    protected $wsClient;
    protected $inputs;
    protected $company;
    protected $document;
    protected $type;
    protected $xmlSigned;
    protected $pathCertificate;
    protected $soapUsername;
    protected $soapPassword;
    protected $endpoint;

    public function __construct($company)
    {
        $this->signer = new XmlSigned();
        $this->wsClient = new WsClient();
        $this->company = $company;
        $this->setDataSoapType();
    }

    public function setInputs($inputs)
    {
        $this->inputs = $inputs;
        $this->type = $inputs['type'];
    }

    public function setDocument($document)
    {
        $this->document = $document;
    }

public function createXmlAndSign()
    {
        $xmlUnsigned = $this->createXml();
        
        // En entorno beta (pruebas), no requiere firma digital
        if ($this->company->soap_type_id === '01') {
            // Guardar XML sin firma para pruebas
            $this->xmlSigned = $xmlUnsigned;
            if ($this->document) {
                $this->uploadFile($xmlUnsigned, 'signed');
            }
            return;
        }
        
        // Producción: requiere firma digital
        $xmlSigned = $this->signXml($xmlUnsigned);
        $this->updateDocumentByXml($xmlSigned);
    }

    public function save()
    {
        // Only save if we don't already have a document
        if ($this->document) {
            return $this->document;
        }
        
        switch ($this->type) {
            case 'debit':
            case 'credit':
                $builder = new NoteBuilder();
                break;
            case 'summary':
                $builder = new SummaryBuilder();
                break;
            case 'voided':
                $builder = new VoidedBuilder();
                break;
            default:
                $builder = new InvoiceBuilder();
                break;
        }

        $this->document = $builder->save($this->inputs);
        return $this->document;
    }

    public function createXml()
    {
        $template = new Template();
        $xmlUnsigned = XmlFormat::format($template->xml($this->type, $this->company, $this->document));
        $this->uploadFile($xmlUnsigned, 'unsigned');
        return $xmlUnsigned;
    }

    public function signXml($content)
    {
        $this->signer->setCertificateFromFile($this->pathCertificate);
        $xmlSigned = $this->signer->signXml($content);
        $this->xmlSigned = $xmlSigned;
        $this->uploadFile($xmlSigned, 'signed');
        return $xmlSigned;
    }

    public function loadAndSendXml()
    {
        $content = $this->getStorage($this->document->filename, 'signed', $this->company->number);
        return $this->sendXml($content);
    }

    public function sendXml($content)
    {
        $sender = in_array($this->type, ['summary', 'voided'])?new SummarySender():new BillSender();
        $sender->setClient($this->wsClient);
        $sender->setCodeProvider(new XmlErrorCodeProvider());

        $res = $sender->send($this->document->filename, $content);

        if(!$res->isSuccess()) {
            throw new Exception("Code: {$res->getError()->getCode()}; Description: {$res->getError()->getMessage()}");
        } else {
            if(!in_array($this->type, ['summary', 'voided'])) {
                $cdrResponse = $res->getCdrResponse();
                $this->uploadFile($res->getCdrZip(), 'cdr');
                return [
                    'code' => $cdrResponse->getCode(),
                    'description' => $cdrResponse->getDescription(),
                    'notes' => $cdrResponse->getNotes()
                ];
            } else {
                $this->updateDocumentByTicket($res->getTicket());
                return true;
            }
        }
    }

    public function statusSummary($ticket)
    {
        $extService = new ExtService();
        $extService->setClient($this->wsClient);
        $extService->setCodeProvider(new XmlErrorCodeProvider());
        $res = $extService->getStatus($ticket);
        if(!$res->isSuccess()) {
            throw new Exception("Code: {$res->getError()->getCode()}; Description: {$res->getError()->getMessage()}");
        } else {
            $cdrResponse = $res->getCdrResponse();
            $this->uploadFile($res->getCdrZip(), 'cdr');
            return [
                'code' => $cdrResponse->getCode(),
                'description' => $cdrResponse->getDescription(),
                'notes' => $cdrResponse->getNotes()
            ];
        }
    }

    public function createPdf()
    {
        $template = new Template();
        $html = $template->pdf($this->type, $this->company, $this->document);

        $pdf = new Mpdf();
        $pdf->WriteHTML($html);
        $this->uploadFile($pdf->output('', 'S'), 'pdf');
    }

    public function uploadFile($file_content, $file_type)
    {
        $this->uploadStorage($this->document->filename, $file_content, $file_type, $this->company->number);
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getXmlSigned()
    {
        return $this->xmlSigned;
    }

    private function setDataSoapType()
    {
        if($this->company->soap_type_id === '01') {
            // Ambiente de Pruebas (Beta)
            $this->soapUsername = $this->company->soap_username ?? '20000000000MODDATOS';
            $this->soapPassword = $this->company->soap_password ?? 'moddatos';
            $this->pathCertificate = null; // Para beta no requiere certificado
            $this->endpoint = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
        } else {
            // Ambiente de Producción
            $this->soapUsername = $this->company->soap_username;
            $this->soapPassword = $this->company->soap_password;
            $this->pathCertificate = storage_path('app'.DIRECTORY_SEPARATOR.'certificates'.DIRECTORY_SEPARATOR.$this->company->certificate);
            $this->endpoint = SunatEndpoints::FE_PRODUCCION;
        }
        
        if (!$this->soapUsername || !$this->soapPassword) {
            throw new \Exception('Faltan credenciales SOAP. Configure usuario y clave SOL en la empresa.');
        }
        
        $this->wsClient->setCredentials($this->soapUsername, $this->soapPassword);
        $this->wsClient->setService($this->endpoint);
    }

    private function updateDocumentByXml($xmlContent)
    {
        if(!in_array($this->type, ['summary', 'voided'])) {
            $hash = $this->getHash($xmlContent);
            $this->document->update([
                'hash' => $hash,
                'qr' => $this->getQr($hash)
            ]);
        }
    }

    private function updateDocumentByTicket($ticket)
    {
        if(in_array($this->type, ['summary', 'voided'])) {
            $this->document->update([
                'ticket' => $ticket
            ]);
        }
    }

    private function getHash($content)
    {
        $helper = new XmlHash();
        return $helper->getHashSign($content);
    }

    private function getQr($hash)
    {
        $customer = $this->document->customer;
        $text = join('|', [
            $this->company->number,
            $this->document->document_type_id,
            $this->document->series,
            $this->document->number,
            $this->document->total_igv,
            $this->document->total,
            $this->document->date_of_issue->format('Y-m-d'),
            $customer->identity_document_type_id,
            $customer->number,
            $hash
        ]);

        $qrCode = new QrCodeGenerate();
        $qr = $qrCode->displayPNGBase64($text);
        return $qr;
    }
}
