<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SPA\eInvoice\Http\Controllers\LHDN_Platform\LoginTaxpayerController;
use App\Modules\SPA\eInvoice\Http\Controllers\LHDN_Platform\GetAllDocumentTypesController;
use App\Modules\SPA\eInvoice\Http\Controllers\LHDN_Platform\GetDocumentTypeController;
use App\Modules\SPA\eInvoice\Http\Controllers\LHDN_Platform\GetDocumentTypeVersionController;
use App\Modules\SPA\eInvoice\Http\Controllers\LHDN_Platform\GetNotificationsController;
use App\Modules\SPA\eInvoice\Http\Controllers\LHDN_eInvoicing\ValidateTINTaxpayerController;
use App\Modules\SPA\eInvoice\Http\Controllers\LHDN_eInvoicing\SubmitDocumentsController;
use App\Modules\SPA\eInvoice\Http\Controllers\Utility\Base64Controller;
use App\Modules\SPA\eInvoice\Http\Controllers\Gateway\ProcessInvoiceController;

Route::prefix('spa/einvoice/gateway')->group(function () {
    Route::post('/process-invoice', ProcessInvoiceController::class);
});

Route::prefix('spa/einvoice/lhdn/platform')->group(function () {
    Route::get('/login-taxpayer', LoginTaxpayerController::class);
    Route::get('/document-types', GetAllDocumentTypesController::class);
    Route::get('/document-type/{id}', GetDocumentTypeController::class);
    Route::get('/document-type/{id}/version/{vid}', GetDocumentTypeVersionController::class);
    Route::get('/notifications/taxpayer', GetNotificationsController::class);
});

Route::prefix('spa/einvoice/lhdn/einvoicing')->group(function () {
    Route::get('/validate-tin-taxpayer/{tin}/{idType}/{idValue}', ValidateTINTaxpayerController::class);
    Route::post('/submit-documents', SubmitDocumentsController::class);
});

Route::prefix('spa/einvoice/utils')->group(function () {
    Route::post('/base64/encode', [Base64Controller::class, 'encode']);
    Route::post('/base64/decode', [Base64Controller::class, 'decode']);
});






