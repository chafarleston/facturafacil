<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SerieController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CustomerApiController;
use App\Http\Controllers\DecolectaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SunatPadronController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\UbigeoController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\Restaurant\RestaurantController;
use App\Http\Controllers\Restaurant\FloorController;
use App\Http\Controllers\Restaurant\TableController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::post('/theme', [ThemeController::class, 'change'])->name('theme.change')->middleware('auth');

// Rutas públicas
Route::get('/ubigeo/departamentos', [UbigeoController::class, 'getDepartamentos']);
Route::get('/ubigeo/provincias', [UbigeoController::class, 'getProvincias']);
Route::get('/ubigeo/distritos', [UbigeoController::class, 'getDistritos']);
Route::get('/ubigeo/by-codigo', [UbigeoController::class, 'getByUbigeo']);
Route::get('/decolecta/search', [DecolectaController::class, 'search'])->name('decolecta.search');

Route::get('/test-json', function() {
    return response()->json(['test' => 'ok', 'time' => now()]);
});

Route::post('/test-post', function() {
    return response()->json(['success' => true, 'message' => 'POST works!']);
});

Route::middleware('auth')->group(function () {
    // Test route for restaurant
    Route::post('/test-restaurant-open/{id}', function($id) {
        return response()->json([
            'success' => true, 
            'message' => 'Route works!',
            'table_id' => $id
        ]);
    })->name('test.restaurant.open');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-only resources
    Route::middleware(['admin'])->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::post('/companies/{company}/certificate', [CompanyController::class, 'updateCertificate'])->name('companies.certificate');
        Route::post('/companies/{company}/set-main', [CompanyController::class, 'setMain'])->name('companies.setMain');
        Route::resource('customers', CustomerController::class)->parameters(['customers' => 'customer']);
        Route::get('/products/import', [ProductController::class, 'importForm'])->name('products.import.form');
        Route::post('/products/import', [ProductController::class, 'importStore'])->name('products.import.store');
        Route::get('/products/import/template', [ProductController::class, 'downloadTemplate'])->name('products.import.template');
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('purchases', PurchaseController::class);
        Route::resource('cashregisters', CashRegisterController::class);
        Route::get('/cashregisters/{cashregister}/pdf', [CashRegisterController::class, 'pdf'])->name('cashregisters.pdf');
        Route::get('/cashregisters/{cashregister}/ticket', [CashRegisterController::class, 'ticketPdf'])->name('cashregisters.ticket');
        Route::post('/cashregisters/{cashregister}/print-caja', [CashRegisterController::class, 'printCaja'])->name('cashregisters.printCaja');
        Route::post('/cashregister/open', [CashRegisterController::class, 'open'])->name('cashregisters.open');
        Route::post('/cashregister/close', [CashRegisterController::class, 'close'])->name('cashregisters.close');
        Route::resource('series', SerieController::class);
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::resource('roles', \App\Http\Controllers\RoleController::class);
        Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
        Route::post('/companies/download-padron', [SunatPadronController::class, 'downloadPadron'])->name('sunat.padron.download');
        Route::get('/printers/detect', [\App\Http\Controllers\Admin\PrinterController::class, 'detect'])->name('printers.detect');
        Route::post('/printers/detect', [\App\Http\Controllers\Admin\PrinterController::class, 'detect'])->name('printers.detect.post');
        Route::get('/printers', [\App\Http\Controllers\Admin\PrinterController::class, 'index'])->name('printers.index');
        Route::get('/printers/queue', [\App\Http\Controllers\Admin\PrinterController::class, 'queue'])->name('printers.queue');
        Route::post('/printers/queue/{printJob}/retry', [\App\Http\Controllers\Admin\PrinterController::class, 'retry'])->name('printers.queue.retry');
        Route::delete('/printers/queue/{printJob}', [\App\Http\Controllers\Admin\PrinterController::class, 'destroy'])->name('printers.queue.destroy');
        Route::put('/printers/{printer}', [\App\Http\Controllers\Admin\PrinterController::class, 'update'])->name('printers.update');
    });
    
    Route::get('/invoices/{invoice}/send', [InvoiceController::class, 'sendToSunat'])->name('invoices.send');
    Route::get('/invoices/nv', [InvoiceController::class, 'nvIndex'])->name('invoices.nv');
    Route::get('/invoices/{invoice}/print/nv/a4', [InvoiceController::class, 'printNvA4'])->name('invoices.print_nv_a4');
    Route::get('/invoices/{invoice}/print/nv/ticket', [InvoiceController::class, 'printNvTicket'])->name('invoices.print_nv_ticket');
    Route::get('/sunat-products/search', [\App\Http\Controllers\SunatProductSearchController::class, 'search'])->name('sunat-products.search');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/ticket', [InvoiceController::class, 'generateTicketPdf'])->name('invoices.ticket');
    Route::get('/invoices/{invoice}/xml', [InvoiceController::class, 'downloadXml'])->name('invoices.downloadXml');
    Route::get('/invoices/{invoice}/cdr', [InvoiceController::class, 'downloadCdr'])->name('invoices.downloadCdr');
    Route::get('/invoices/{invoice}/credit-note', [InvoiceController::class, 'creditNoteForm'])->name('invoices.creditNoteForm');
    Route::post('/invoices/{invoice}/credit-note', [InvoiceController::class, 'sendCreditNote'])->name('invoices.sendCreditNote');
    Route::resource('invoices', InvoiceController::class);
    
Route::get('/customers/search', [CustomerApiController::class, 'search'])->name('customers.search');
    Route::post('/customers/quick-store', [CustomerApiController::class, 'quickStore'])->name('customers.quickStore');
    
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/success/{id}', [PosController::class, 'success'])->name('pos.success');
    Route::post('/pos/sunat/{id}', [PosController::class, 'sendToSunat'])->name('pos.sunat');
    Route::get('/pos/print/{id}/{format}', [PosController::class, 'printInvoice'])->name('pos.print');
    Route::post('/pos/open-drawer', [PosController::class, 'openDrawer'])->name('pos.openDrawer');

    // Restaurant Routes
    Route::get('/restaurant', [RestaurantController::class, 'index'])->name('restaurant.index');
    Route::get('/restaurant/mode', [RestaurantController::class, 'modeIndex'])->name('restaurant.mode');
    Route::get('/restaurant/kitchen', [RestaurantController::class, 'kitchenIndex'])->name('restaurant.kitchen');
    Route::get('/restaurant/kitchen/cocina', function () { return redirect('/restaurant/kitchen?kds=cocina'); })->name('restaurant.kitchen.cocina');
    Route::get('/restaurant/kitchen/cocina2', function () { return redirect('/restaurant/kitchen?kds=cocina2'); })->name('restaurant.kitchen.cocina2');
    Route::get('/restaurant/kitchen/bar', function () { return redirect('/restaurant/kitchen?kds=bar'); })->name('restaurant.kitchen.bar');
    Route::get('/restaurant/kitchen-orders', [RestaurantController::class, 'getKitchenOrders'])->name('restaurant.kitchenOrders');
    Route::get('/restaurant/kitchen-stream', [RestaurantController::class, 'kitchenStream'])->name('restaurant.kitchenStream');
    Route::get('/restaurant/stream', [RestaurantController::class, 'restaurantStream'])->name('restaurant.stream');
    Route::post('/restaurant/kitchen/{orderId}/ready', [RestaurantController::class, 'markKitchenReady'])->name('restaurant.kitchenReady');
    Route::post('/restaurant/kitchen/{orderId}/deliver', [RestaurantController::class, 'deliverKitchenOrder'])->name('restaurant.kitchenDeliver');
    Route::post('/restaurant/tables/{tableId}/open', [RestaurantController::class, 'openTable'])->name('restaurant.tables.open');
    Route::get('/restaurant/orders/{orderId}', [RestaurantController::class, 'getOrder'])->name('restaurant.orders.show');
    Route::post('/restaurant/orders/{orderId}/items', [RestaurantController::class, 'addItem'])->name('restaurant.orders.items');
    Route::put('/restaurant/orders/items/{itemId}', [RestaurantController::class, 'updateItem'])->name('restaurant.orders.items.update');
    Route::delete('/restaurant/orders/items/{itemId}', [RestaurantController::class, 'removeItem'])->name('restaurant.orders.items.destroy');
    Route::post('/restaurant/orders/{orderId}/send-to-kitchen', [RestaurantController::class, 'sendToKitchen'])->name('restaurant.orders.sendToKitchen');
    Route::post('/restaurant/orders/{orderId}/notes', [RestaurantController::class, 'saveOrderNotes'])->name('restaurant.orders.notes');
    Route::get('/restaurant/orders/{orderId}/print-kitchen', [RestaurantController::class, 'printKitchenTicket'])->name('restaurant.orders.printKitchen');
    Route::get('/restaurant/orders/{orderId}/print-prebill', [RestaurantController::class, 'printPrebill'])->name('restaurant.orders.printPrebill');
    Route::post('/restaurant/orders/{orderId}/print-prebill/{printerKey}', [RestaurantController::class, 'printPrebillTo'])->name('restaurant.orders.printPrebillTo');
    Route::post('/restaurant/orders/{orderId}/close', [RestaurantController::class, 'closeOrder'])->name('restaurant.orders.close');
    Route::post('/restaurant/orders/{orderId}/cancel', [RestaurantController::class, 'cancelOrder'])->name('restaurant.orders.cancel');
    Route::post('/restaurant/orders/{orderId}/charge', [RestaurantController::class, 'chargeOrder'])->name('restaurant.orders.charge');
    Route::post('/restaurant/orders/{orderId}/move-table', [RestaurantController::class, 'moveTable'])->name('restaurant.orders.moveTable');
    Route::post('/restaurant/toggle-mode', [RestaurantController::class, 'toggleMode'])->name('restaurant.toggleMode');
    Route::get('/restaurant/print-status', [\App\Http\Controllers\Admin\PrinterController::class, 'status'])->name('restaurant.printStatus');
    Route::get('/restaurant/active-orders', [RestaurantController::class, 'getActiveOrders'])->name('restaurant.activeOrders');

    // Floor Routes
    Route::get('/restaurant/floors', [FloorController::class, 'index'])->name('restaurant.floors.index');
    Route::get('/restaurant/floors/create', [FloorController::class, 'create'])->name('restaurant.floors.create');
    Route::post('/restaurant/floors', [FloorController::class, 'store'])->name('restaurant.floors.store');
    Route::get('/restaurant/floors/{floor}/edit', [FloorController::class, 'edit'])->name('restaurant.floors.edit');
    Route::put('/restaurant/floors/{floor}', [FloorController::class, 'update'])->name('restaurant.floors.update');
    Route::delete('/restaurant/floors/{floor}', [FloorController::class, 'destroy'])->name('restaurant.floors.destroy');

    // Table Routes
    Route::get('/restaurant/tables/create', [TableController::class, 'create'])->name('restaurant.tables.create');
    Route::post('/restaurant/tables', [TableController::class, 'store'])->name('restaurant.tables.store');
    Route::get('/restaurant/tables/{restaurantTable}/edit', [TableController::class, 'edit'])->name('restaurant.tables.edit');
    Route::put('/restaurant/tables/{restaurantTable}', [TableController::class, 'update'])->name('restaurant.tables.update');
    Route::delete('/restaurant/tables/{restaurantTable}', [TableController::class, 'destroy'])->name('restaurant.tables.destroy');
});

require __DIR__.'/auth.php';

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout.get');
