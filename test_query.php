<?php
try {
    $orders = App\Models\RestaurantOrder::whereNotIn('status', ['COMPLETED', 'CANCELLED'])
        ->with(['table.floor', 'items'])
        ->orderBy('created_at', 'desc')
        ->get();
    echo 'OK: ' . $orders->count() . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo 'Line: ' . $e->getLine() . PHP_EOL;
}
