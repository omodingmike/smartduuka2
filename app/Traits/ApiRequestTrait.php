<?php

namespace App\Traits;

use App\Libraries\AppLibrary;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

trait ApiRequestTrait
{
    public function makeApiRequest()
    {
        try {
            return AppLibrary::licenseApiResponse(Http::post(Config::get('installer.url') . Config::get('installer.productlicence'), Config::get('installer.buildPayload')+['domain' => AppLibrary::domain(Config('app.url'))]));
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}