<?php

namespace App\Modules\SPA\eInvoice\Config;

class LHDN
{
    public static function getApiBaseUrl()
    {
        return env('LHDN_API_BASE_URL', 'https://api.myinvois.hasil.gov.my');
    }

    public static function getIdSrvBaseUrl()
    {
        return env('LHDN_ID_SRV_BASE_URL', 'https://api.myinvois.hasil.gov.my');
    }
}