<?php

use Illuminate\Support\Facades\Route;
use app\Modules\SPA\eInvoice\Controllers\LoginTaxpayerController;

Route::prefix('spa/einvoice')->group(function () {
    Route::get('/login-taxpayer', [LoginTaxpayerController::class, 'index']);
});
