<?php

    namespace App\Http\Controllers;

    use App\Enums\SettingsEnum;
    use App\Http\Requests\StoreAppSettingsRequest;
    use App\Http\Requests\StoreModuleRequest;
    use App\Http\Resources\SiteModuleResource;
    use Exception;
    use Illuminate\Support\Facades\Log;
    use Laravel\Sanctum\PersonalAccessToken;
    use Smartisan\Settings\Facades\Settings;

    class ModuleController extends Controller
    {
        public function index()
        {
            return new SiteModuleResource($this->list());
        }

        public function list()
        {
            try {
                return Settings::group('module')->all();
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function appSettings()
        {
            try {
                return new SiteModuleResource (Settings::group(SettingsEnum::APP_SETTINGS())->all());
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function update(StoreModuleRequest $request)
        {
            try {
                $data = $request->validated();
                Settings::group('module')->set($data);
                PersonalAccessToken::truncate();
                return new SiteModuleResource($this->list());
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function updateAppSettings(StoreAppSettingsRequest $request)
        {
            try {
                $data = $request->validated();
                Settings::group(SettingsEnum::APP_SETTINGS())->set($data);
                return new SiteModuleResource($this->list());
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }
    }
