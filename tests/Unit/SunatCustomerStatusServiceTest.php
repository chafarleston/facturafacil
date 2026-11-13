<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Services\SunatCustomerStatusService;
use PHPUnit\Framework\TestCase;

class SunatCustomerStatusServiceTest extends TestCase
{
    public function test_solo_activo_es_un_estado_valido(): void
    {
        $this->assertTrue(SunatCustomerStatusService::isActiveState('ACTIVO'));
        $this->assertTrue(SunatCustomerStatusService::isActiveState(' activo '));
        $this->assertFalse(SunatCustomerStatusService::isActiveState('SUSPENSION TEMPORAL'));
        $this->assertFalse(SunatCustomerStatusService::isActiveState('BAJA DEFINITIVA'));
        $this->assertFalse(SunatCustomerStatusService::isActiveState(null));
    }

    public function test_un_ruc_no_activo_queda_bloqueado(): void
    {
        $customer = new Customer([
            'documento_tipo' => '6',
            'documento_numero' => '15613082741',
            'estado' => 'ACTIVO',
            'sunat_estado' => 'BAJA DEFINITIVA',
            'sunat_bloqueado' => false,
        ]);

        $this->assertTrue($customer->isSunatBlocked());
        $this->assertFalse($customer->canBeEdited());
    }

    public function test_un_dni_no_se_bloquea_por_estado_sunat(): void
    {
        $customer = new Customer([
            'documento_tipo' => '1',
            'documento_numero' => '12345678',
            'estado' => 'ACTIVO',
        ]);

        $this->assertFalse($customer->isSunatBlocked());
        $this->assertTrue($customer->canBeEdited());
    }
}
