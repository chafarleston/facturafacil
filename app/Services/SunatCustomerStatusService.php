<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class SunatCustomerStatusService
{
    public function __construct(private SunatPadronService $padron)
    {
    }

    public static function isActiveState(?string $estado): bool
    {
        return strtoupper(trim((string) $estado)) === 'ACTIVO';
    }

    public function syncAll(): array
    {
        $this->ensurePadronAvailable();

        $summary = [
            'checked' => 0,
            'active' => 0,
            'blocked' => 0,
            'not_found' => 0,
            'companies' => [],
        ];

        Customer::where('documento_tipo', '6')
            ->select(['id', 'company_id', 'documento_numero'])
            ->chunkById(500, function ($customers) use (&$summary) {
                $rows = $this->padron->findManyByRucs($customers->pluck('documento_numero')->all());

                foreach ($customers as $customer) {
                    $ruc = SunatPadronService::normalizeRuc((string) $customer->documento_numero);
                    $row = $ruc ? ($rows[$ruc] ?? null) : null;
                    $result = $this->applyStatus($customer, $row);

                    $summary['checked']++;
                    $summary['companies'][$customer->company_id] = true;

                    if ($result['active']) {
                        $summary['active']++;
                    } else {
                        $summary['blocked']++;
                    }

                    if ($result['not_found']) {
                        $summary['not_found']++;
                    }
                }
            });

        foreach (array_keys($summary['companies']) as $companyId) {
            Cache::forget('restaurant_customers_' . $companyId);
        }

        unset($summary['companies']);

        $summary['checked_at'] = now()->toDateTimeString();

        return $summary;
    }

    public function syncCustomer(Customer $customer): array
    {
        if ((string) $customer->documento_tipo !== '6') {
            $customer->forceFill([
                'sunat_estado' => null,
                'sunat_condicion' => null,
                'sunat_bloqueado' => false,
                'sunat_checked_at' => null,
            ])->save();

            Cache::forget('restaurant_customers_' . $customer->company_id);

            return [
                'active' => true,
                'blocked' => false,
                'not_found' => false,
                'estado' => null,
            ];
        }

        $this->ensurePadronAvailable();

        $row = $this->padron->findByRuc((string) $customer->documento_numero);
        $result = $this->applyStatus($customer, $row);

        Cache::forget('restaurant_customers_' . $customer->company_id);

        return $result;
    }

    public function customerValidationError(string $documentoTipo, string $documentoNumero): ?string
    {
        if ($documentoTipo !== '6') {
            return null;
        }

        $ruc = SunatPadronService::normalizeRuc($documentoNumero);

        if (!$ruc) {
            return 'El RUC debe tener 11 dígitos.';
        }

        if (!SunatPadronService::driverAvailable() || !$this->padron->exists()) {
            return 'No se puede validar el RUC porque el padrón SUNAT no está disponible.';
        }

        $row = $this->padron->findByRuc($ruc);

        if (!$row) {
            return 'El RUC no se encuentra en el padrón SUNAT. Verifique el número.';
        }

        $estado = trim((string) ($row['estado'] ?? ''));

        if (!self::isActiveState($estado)) {
            return 'El RUC no está ACTIVO en SUNAT (estado: ' . ($estado ?: 'SIN ESTADO') . ').';
        }

        return null;
    }

    public function billingValidationError(string $documentType, ?Customer $customer): ?string
    {
        if (!in_array($documentType, ['01', '03'], true)) {
            return null;
        }

        if (!$customer) {
            return $documentType === '01'
                ? 'Las facturas requieren un cliente con RUC.'
                : null;
        }

        if ($customer->estado !== 'ACTIVO') {
            return 'El cliente está inactivo y no puede usarse para emitir comprobantes.';
        }

        if ((string) $customer->documento_tipo !== '6') {
            return $documentType === '01'
                ? 'Las facturas requieren un cliente con RUC.'
                : null;
        }

        if ($customer->isSunatBlocked()) {
            $estado = $customer->sunat_estado ?: 'SIN VERIFICAR';

            return 'El RUC del cliente está bloqueado para facturación (estado SUNAT: ' . $estado . ').';
        }

        return $this->customerValidationError('6', (string) $customer->documento_numero);
    }

    private function ensurePadronAvailable(): void
    {
        if (!SunatPadronService::driverAvailable() || !$this->padron->exists()) {
            throw new RuntimeException('El padrón SUNAT no está disponible en SQLite. Actualice el padrón antes de continuar.');
        }
    }

    private function applyStatus(Customer $customer, ?array $row): array
    {
        $notFound = $row === null;
        $estado = $notFound ? 'NO ENCONTRADO' : trim((string) ($row['estado'] ?? ''));

        if ($estado === '') {
            $estado = 'SIN ESTADO';
        }

        $active = !$notFound && self::isActiveState($estado);

        $customer->forceFill([
            'sunat_estado' => $estado,
            'sunat_condicion' => $notFound ? null : ($row['condicion'] ?? null),
            'sunat_bloqueado' => !$active,
            'sunat_checked_at' => now(),
        ])->save();

        return [
            'active' => $active,
            'blocked' => !$active,
            'not_found' => $notFound,
            'estado' => $estado,
        ];
    }
}
