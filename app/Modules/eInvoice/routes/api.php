<?php

use Illuminate\Support\Facades\Route;
use App\Modules\eInvoice\Controllers\LoginTaxpayerController;

Route::prefix('einvoice')->group(function () {
    Route::get('/login-taxpayer', [LoginTaxpayerController::class, 'index']);
});
