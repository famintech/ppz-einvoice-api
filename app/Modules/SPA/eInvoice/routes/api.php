<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SPA\eInvoice\Http\Controllers\LoginTaxpayerController;
use App\Modules\SPA\eInvoice\Http\Controllers\GetAllDocumentTypesController;

Route::prefix('spa/einvoice')->group(function () {
    Route::get('/login-taxpayer', LoginTaxpayerController::class);
    Route::get('/document-types', GetAllDocumentTypesController::class);
});
