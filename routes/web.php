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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::post('/theme', [ThemeController::class, 'change'])->name('theme.change')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-only resources
    Route::middleware(['admin'])->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::post('/companies/{company}/certificate', [CompanyController::class, 'updateCertificate'])->name('companies.certificate');
        Route::post('/companies/{company}/set-main', [CompanyController::class, 'setMain'])->name('companies.setMain');
        Route::resource('customers', CustomerController::class)->parameters(['customers' => 'customer']);
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('purchases', PurchaseController::class);
        Route::resource('cashregisters', CashRegisterController::class);
        Route::get('/cashregisters/{cashregister}/pdf', [CashRegisterController::class, 'pdf'])->name('cashregisters.pdf');
        Route::get('/cashregisters/{cashregister}/ticket', [CashRegisterController::class, 'ticketPdf'])->name('cashregisters.ticket');
        Route::post('/cashregister/open', [CashRegisterController::class, 'open'])->name('cashregisters.open');
        Route::post('/cashregister/close', [CashRegisterController::class, 'close'])->name('cashregisters.close');
        Route::resource('series', SerieController::class);
        Route::resource('users', \App\Http\Controllers\UserController::class);
        // Descargar padrón SUNAT (manual)
        Route::post('/companies/download-padron', [SunatPadronController::class, 'downloadPadron'])->name('sunat.padron.download');
    });
    
    Route::get('/invoices/{invoice}/send', [InvoiceController::class, 'sendToSunat'])->name('invoices.send');
    Route::get('/invoices/nv', [InvoiceController::class, 'nvIndex'])->name('invoices.nv');
    // Nota de Venta printing routes
    Route::get('/invoices/{invoice}/print/nv/a4', [InvoiceController::class, 'printNvA4'])->name('invoices.print_nv_a4');
    Route::get('/invoices/{invoice}/print/nv/ticket', [InvoiceController::class, 'printNvTicket'])->name('invoices.print_nv_ticket');
    // SUNAT catalog search for Nota de Venta (NV) - optional improvement for UX
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
    Route::get('/decolecta/search', [DecolectaController::class, 'search'])->name('decolecta.search')->middleware('auth');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\Auth\LogoutController;
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
