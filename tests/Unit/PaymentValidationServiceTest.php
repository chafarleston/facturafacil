<?php

namespace Tests\Unit;

use App\Services\PaymentValidationService;
use PHPUnit\Framework\TestCase;

class PaymentValidationServiceTest extends TestCase
{
    public function test_pago_virtual_que_supera_el_saldo_pendiente_es_rechazado(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PaymentValidationService::assertVirtualPaymentsDoNotExceedPending([
            ['method' => 'EFECTIVO', 'amount' => 45],
            ['method' => 'YAPE', 'amount' => 10],
        ], 50);
    }

    public function test_virtual_primero_y_efectivo_para_vuelto_es_permitido(): void
    {
        PaymentValidationService::assertVirtualPaymentsDoNotExceedPending([
            ['method' => 'YAPE', 'amount' => 40],
            ['method' => 'EFECTIVO', 'amount' => 20],
        ], 50);

        $this->assertTrue(true);
    }

    public function test_dos_pagos_virtuales_que_superan_el_total_son_rechazados(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PaymentValidationService::assertVirtualPaymentsDoNotExceedPending([
            ['method' => 'PLIN', 'amount' => 25],
            ['method' => 'YAPE', 'amount' => 30],
        ], 50);
    }

    public function test_virtual_exacto_y_efectivo_adicional_para_vuelto_es_permitido(): void
    {
        PaymentValidationService::assertVirtualPaymentsDoNotExceedPending([
            ['method' => 'TARJETA', 'amount' => 50],
            ['method' => 'EFECTIVO', 'amount' => 10],
        ], 50);

        $this->assertTrue(true);
    }
}
