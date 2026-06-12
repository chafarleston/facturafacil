<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$inv = \App\Models\Invoice::find(1);
echo "Documento: {$inv->full_number}\n";
echo "Estado SUNAT: {$inv->sunat_estado}\n";
echo "Descripción: {$inv->sunat_description}\n";
echo "Código: {$inv->sunat_code}\n\n";

$summary = \App\Models\SummaryDocument::first();
echo "Resumen Diario:\n";
echo "  Correlativo: {$summary->correlativo}\n";
echo "  Ticket: {$summary->ticket}\n";
echo "  Estado: {$summary->sunat_estado}\n";
echo "  Fecha: {$summary->sunat_fecha}\n";
