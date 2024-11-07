<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SPA\eInvoice\Controllers\LoginTaxpayerController;

Route::prefix('einvoice')->group(function () {
    Route::get('/login-taxpayer', [LoginTaxpayerController::class, 'index']);
});
