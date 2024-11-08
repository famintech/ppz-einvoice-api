<?php

namespace App\Modules\SPA\eInvoice\Config;

class lhdn
{
    public static function getApiBaseUrl()
    {
        return env('LHDN_API_BASE_URL', 'https://api.myinvois.hasil.gov.my');
    }

    public static function getIdSrvBaseUrl()
    {
        return env('LHDN_ID_SRV_BASE_URL', 'https://api.myinvois.hasil.gov.my');
    }

    public static function getClientId()
    {
        return env('LHDN_CLIENT_ID', '01e5f835-9d2c-454b-9911-24a0804502f3');
    }

    public static function getClientSecret()
    {
        return env('LHDN_CLIENT_SECRET', '11fc8798-e441-4226-a47e-56ab218591ba');
    }
}