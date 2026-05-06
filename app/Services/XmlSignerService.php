<?php

namespace App\Services;

use DOMDocument;

class XmlSignerService
{
    private $privateKey;
    private $certificate;
    
    public function __construct($company = null)
    {
        if ($company && $company->certificado_path) {
            $fullPath = storage_path('app/' . $company->certificado_path);
            
            if (file_exists($fullPath) && $company->certificado_password) {
                $this->loadPfx($fullPath, $company->certificado_password);
            }
        }
    }
    
    private function loadPfx($pfxPath, $password)
    {
        $pfxContent = file_get_contents($pfxPath);
        $result = openssl_pkcs12_read($pfxContent, $certs, $password);
        
        if (!$result) {
            throw new \Exception('Cannot read PFX file. Wrong password?');
        }
        
        $this->privateKey = $certs['pkey'];
        
        openssl_x509_export($certs['cert'], $certOut);
        
        $this->certificate = str_replace('-----BEGIN CERTIFICATE-----', '', $certOut);
        $this->certificate = str_replace('-----END CERTIFICATE-----', '', $this->certificate);
        $this->certificate = str_replace(["\r\n", "\r", "\n"], '', $this->certificate);
    }
    
    public function signXml($xmlContent, $ruc)
    {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;
        $doc->loadXML($xmlContent);
        
        $UBLExtension = $doc->getElementsByTagName('UBLExtension')->item(0);
        $ExtensionContent = null;
        
        if ($UBLExtension) {
            $ExtensionContent = $UBLExtension->getElementsByTagName('ExtensionContent')->item(0);
            if ($ExtensionContent) {
                while ($ExtensionContent->firstChild) {
                    $ExtensionContent->removeChild($ExtensionContent->firstChild);
                }
            }
        }
        
        $signatureId = 'signature' . $ruc;
        
        $signatureXml = $this->createSignatureXml($signatureId);
        $sigDoc = new DOMDocument();
        $sigDoc->loadXML($signatureXml);
        
        $signatureNode = $doc->importNode($sigDoc->documentElement, true);
        
        if ($ExtensionContent) {
            $ExtensionContent->appendChild($signatureNode);
        } else {
            $doc->documentElement->appendChild($signatureNode);
        }
        
        $this->signDocument($doc, $signatureId);
        
        return $doc->saveXML();
    }
    
    private function createSignatureXml($signatureId)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<ds:Signature Id="' . $signatureId . '" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
  <ds:SignedInfo>
    <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
    <ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
    <ds:Reference URI="">
      <ds:Transforms>
        <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
        <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
      </ds:Transforms>
      <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
      <ds:DigestValue/>
    </ds:Reference>
  </ds:SignedInfo>
  <ds:SignatureValue/>
  <ds:KeyInfo>
    <ds:X509Data>
      <ds:X509Certificate>' . $this->certificate . '</ds:X509Certificate>
    </ds:X509Data>
  </ds:KeyInfo>
</ds:Signature>';
    }
    
    private function signDocument($doc, $signatureId)
    {
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        
        $signatures = $xpath->query('//ds:Signature[@Id="' . $signatureId . '"]');
        $signatureNode = $signatures->length > 0 ? $signatures->item(0) : $xpath->query('//ds:Signature')->item(0);
        
        $signedInfoNodes = $xpath->query('.//ds:SignedInfo', $signatureNode);
        $signedInfoNode = $signedInfoNodes->item(0);
        
        $referenceNodes = $xpath->query('.//ds:Reference', $signedInfoNode);
        $referenceNode = $referenceNodes->item(0);
        
        $docCopy = new DOMDocument();
        $docCopy->preserveWhiteSpace = false;
        $docCopy->formatOutput = false;
        $docCopy->loadXML($doc->saveXML());
        
        $sigList = $docCopy->getElementsByTagName('Signature');
        while ($sigList->length > 0) {
            $sig = $sigList->item(0);
            if ($sig->parentNode) {
                $sig->parentNode->removeChild($sig);
            }
        }
        
        $canonicalDoc = $docCopy->C14N(false, false);
        
        $digest = base64_encode(sha1($canonicalDoc, true));
        
        $digestValueNodes = $xpath->query('.//ds:DigestValue', $referenceNode);
        if ($digestValueNodes->length > 0) {
            $digestValueNodes->item(0)->nodeValue = $digest;
        }
        
        $signedInfoC14N = $signedInfoNode->C14N(false, false);
        
        $signature = '';
        openssl_sign($signedInfoC14N, $signature, $this->privateKey, OPENSSL_ALGO_SHA1);
        
        $sigValueNodes = $xpath->query('.//ds:SignatureValue', $signatureNode);
        if ($sigValueNodes->length > 0) {
            $sigValueNodes->item(0)->nodeValue = base64_encode($signature);
        }
    }
}