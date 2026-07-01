<?php

namespace Database\Seeders;

use App\Models\Printer;
use Illuminate\Database\Seeder;

class PrinterSeeder extends Seeder
{
    protected array $slots = [
        ['assigned_to' => 'cocina-1', 'name' => 'Cocina 1', 'type' => 'local', 'port' => 9100],
        ['assigned_to' => 'cocina-2', 'name' => 'Cocina 2', 'type' => 'local', 'port' => 9100],
        ['assigned_to' => 'bar-1', 'name' => 'Bar 1', 'type' => 'local', 'port' => 9100],
        ['assigned_to' => 'precuenta', 'name' => 'Precuenta', 'type' => 'local', 'port' => 9100],
        ['assigned_to' => 'precuenta2', 'name' => 'Precuenta 2', 'type' => 'local', 'port' => 9100],
        ['assigned_to' => 'precuenta3', 'name' => 'Precuenta 3', 'type' => 'local', 'port' => 9100],
        ['assigned_to' => 'caja', 'name' => 'Caja', 'type' => 'local', 'port' => 9100],
        ['assigned_to' => 'autopedido', 'name' => 'Auto Pedido', 'type' => 'local', 'port' => 9100],
    ];

    public function run(): void
    {
        foreach ($this->slots as $slot) {
            Printer::firstOrCreate(
                ['assigned_to' => $slot['assigned_to']],
                $slot
            );
        }
    }
}
