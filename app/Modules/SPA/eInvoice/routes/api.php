<?php

use Illuminate\Support\Facades\Route;
use app\modules\spa\einvoice\controllers\LoginTaxpayerController;

Route::prefix('spa/einvoice')->group(function () {
    Route::get('/login-taxpayer', [LoginTaxpayerController::class, 'index']);
});
