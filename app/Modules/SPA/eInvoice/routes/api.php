<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\LoginTaxpayerController;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\GetAllDocumentTypesController;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\GetDocumentTypeController;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\GetDocumentTypeVersionController;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\GetNotificationsController;
use App\Modules\SPA\eInvoice\Http\Controllers\eInvoicing\ValidateTINTaxpayerController;
use App\Modules\SPA\eInvoice\Http\Controllers\eInvoicing\SubmitDocumentsController;
use App\Modules\SPA\eInvoice\Http\Controllers\Base64Controller;

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



