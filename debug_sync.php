<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$company = App\Models\Company::where('facturacion_mode', 'api_externa')->first();
if (!$company) { echo "No company\n"; exit; }

$api = new App\Services\Pro51ApiService($company);

try {
    echo "Fetching documents from pro51...\n";
    $docsResponse = $api->apiGet('documents/lists');
    echo "Response keys: " . implode(', ', array_keys($docsResponse)) . "\n";
    
    $docsList = $docsResponse['data'] ?? $docsResponse;
    echo "List type: " . gettype($docsList) . "\n";
    
    if (is_array($docsList)) {
        echo "Count: " . count($docsList) . "\n";
        echo "First item: " . json_encode($docsList[0] ?? 'empty', JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } elseif (is_object($docsList)) {
        echo "Object: " . get_class($docsList) . "\n";
    } else {
        echo "Other: " . $docsList . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
