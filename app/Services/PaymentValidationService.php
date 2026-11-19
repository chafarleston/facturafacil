<?php

namespace App\Services;

class PaymentValidationService
{
    public const VIRTUAL_METHODS = ['YAPE', 'PLIN', 'TARJETA', 'TRANSFERENCIA'];

    public static function assertVirtualPaymentsDoNotExceedPending(array $payments, float $total): void
    {
        $paid = 0.0;

        foreach ($payments as $payment) {
            $method = strtoupper(trim((string) ($payment['method'] ?? '')));
            $amount = round((float) ($payment['amount'] ?? 0), 2);
            $pending = max(0, round($total - $paid, 2));

            if (in_array($method, self::VIRTUAL_METHODS, true) && $amount > $pending + 0.001) {
                throw new \InvalidArgumentException(
                    'El pago virtual (Yape, Plin, Tarjeta y Transferencia) no puede superar el saldo pendiente de S/ '
                    . number_format($pending, 2) . '. Solo el efectivo permite vuelto.'
                );
            }

            $paid += $amount;
        }
    }
}
