<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Pro51ApiService
{
    private string $baseUrl;
    private string $token;

    public function __construct(private Company $company)
    {
        $this->baseUrl = rtrim($company->pro51_url ?? '', '/');
        $this->token = $company->pro51_token;
    }

    private function apiPost(string $endpoint, array $data = []): array
    {
        try {
            $response = Http::timeout(30)
                ->withToken($this->token)
                ->withOptions(['verify' => false])
                ->post("{$this->baseUrl}/api/{$endpoint}", $data);

            return $response->json() ?? ['success' => false, 'message' => 'Respuesta vacía'];
        } catch (\Exception $e) {
            Log::error('pro51 API error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function apiGet(string $endpoint): array
    {
        try {
            $response = Http::timeout(15)
                ->withToken($this->token)
                ->withOptions(['verify' => false])
                ->get("{$this->baseUrl}/api/{$endpoint}");

            return $response->json() ?? ['success' => false, 'message' => 'Respuesta vacía'];
        } catch (\Exception $e) {
            Log::error('pro51 API error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function testConnection(): array
    {
        try {
            $response = $this->apiGet('company');
            if (isset($response['company'])) {
                return ['success' => true, 'message' => 'Conexión exitosa'];
            }
            $msg = $response['message'] ?? 'Token inválido o sin acceso';
            if (isset($response['success']) && $response['success'] === false) {
                return ['success' => false, 'message' => $msg];
            }
            if (!empty($response)) {
                return ['success' => true, 'message' => 'Conexión exitosa'];
            }
            return ['success' => false, 'message' => $msg];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function syncProduct(array $productData): array
    {
        return $this->apiPost('item', $productData);
    }

    public function createDocument(array $documentData): array
    {
        return $this->apiPost('documents', $documentData);
    }

    public function sendDocument(string $externalId): array
    {
        return $this->apiPost('documents/send', ['external_id' => $externalId]);
    }

    public function checkDocumentStatus(string $externalId): array
    {
        return $this->apiPost('documents/status', ['external_id' => $externalId]);
    }

    public function searchRuc(string $ruc): array
    {
        return $this->apiGet("services/ruc/{$ruc}");
    }

    public function searchDni(string $dni): array
    {
        return $this->apiGet("services/dni/{$dni}");
    }

    public function getCompanyData(): array
    {
        return $this->apiGet('company');
    }

    public function getSeries(): array
    {
        $response = $this->apiGet('document/series');
        if (isset($response[0]) || isset($response['data'])) {
            $series = $response['data'] ?? $response;
            return ['success' => true, 'data' => $series];
        }
        if (isset($response['success']) && !$response['success']) {
            return $response;
        }
        return ['success' => true, 'data' => $response];
    }

    public static function getIgvTypeCode(string $tipoAfectacion): string
    {
        return match ($tipoAfectacion) {
            'GRA' => '10',
            'EXO' => '20',
            'INA' => '30',
            'EXE' => '40',
            default => '10',
        };
    }

    public static function getPaymentMethodCode(?string $metodoPago): string
    {
        return match (strtoupper($metodoPago ?? '')) {
            'EFECTIVO', 'CASH', 'CONTADO' => '01',
            'TARJETA', 'TARJETA CREDITO', 'TARJETA DEBITO' => '02',
            'YAPE' => '03',
            'PLIN' => '04',
            'TRANSFERENCIA', 'DEPOSITO' => '05',
            default => '01',
        };
    }
}
