<?php

namespace App\Http\Controllers\Cashflow;

use App\Http\Requests\Cashflow\SettingRequest;
use Exception;
use Smartisan\Settings\Facades\Settings;

class SettingsController extends Controller
{
    public function all()
    {
        return Settings::all();
    }

    public function update(SettingRequest $request)
    {
        try {
            Settings::set($request->validated());
            activityLog('Updated Settings', $request->header('X-App-Id'));
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 422);
        }
    }
}
