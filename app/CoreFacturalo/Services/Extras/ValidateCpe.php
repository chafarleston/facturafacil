<?php

namespace App\CoreFacturalo\Services\Extras;

use App\Models\Tenant\Company;
use Carbon\Carbon;
use DiDom\Document as DiDom;
use Exception;
use GoogleCloudVision\GoogleCloudVision;
use GoogleCloudVision\Request\AnnotateImageRequest;
use GuzzleHttp\Client;

class ValidateCpe
{
    const URL_CONSULT = 'http://www.sunat.gob.pe/ol-ti-itconsvalicpe/ConsValiCpe.htm';
    const URL_CAPTCHA = 'http://www.sunat.gob.pe/ol-ti-itconsvalicpe/captcha?accion=image';
    protected $company;
    protected $client;
    protected $document_type_code = [
        '01' => '03',
        '03' => '06'
    ];

    public function __construct()
    {
        $this->company = Company::first();
        $this->client = new Client(['cookies' => true]);
    }

    public function search($document_type_code, $series, $number, $date_of_issue, $total = null)
    {
        try {
            $captcha = trim($this->getCaptchaImage());
            $response = $this->client->request('POST', self::URL_CONSULT, [
                'form_params' => [
                    'accion' => 'CapturaCriterioValidez',
                    'num_ruc' => $this->company->number,
                    'tipocomprobante' => $this->document_type_code[$document_type_code],
                    'num_serie' => $series,
                    'num_comprob' => $number,
                    'fec_emision' => Carbon::parse($date_of_issue)->format('d/m/Y'),
                    'cantidad' => $total,
                    'codigo' => $captcha
                ]
            ]);

            $html = $response->getBody()->getContents();
            $xp = new DiDom($html);
            $sub_headings = $xp->find('td.bgn');
            if (count($sub_headings) > 0) {
                return  [
                    'success' => true,
                    'message' => $sub_headings[0]->text()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "No se obtuvo resultado de la consulta:{$series}-{$number}"
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getCaptchaImage()
    {
        $response = $this->client->request('GET', self::URL_CAPTCHA);
        $image = base64_encode($response->getBody()->getContents());

        $request = new AnnotateImageRequest();
        $request->setImage($image);
        $request->setFeature("TEXT_DETECTION");
        $gcvRequest = new GoogleCloudVision([$request],  env('GOOGLE_CLOUD_VISION_API_KEY'));
        $response = $gcvRequest->annotate();

        if ($response->responses) {
            return str_replace(' ', '', $response->responses[0]->textAnnotations[0]->description);
        } else {
            return 'ERROR';
        }
    }
}
