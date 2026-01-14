<?php

namespace App\Services;


use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\NotificationAlert;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;
use Illuminate\Support\Facades\Artisan; // Import Artisan

class NotificationAlertService
{
    /**
     * @throws Exception
     */
    public function list()
    {
        try {
            $settings = Settings::group('notification')->all();
            Log::info('NotificationAlertService list() returning settings:', $settings); // Add logging
            return $settings;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(Request $request)
    {
        try {
            $settingsData = [];
            
            // Always try to get these inputs, defaulting to empty string if null
            $settingsData['admin_email'] = $request->input('admin_email') ?? '';
            $settingsData['admin_phone'] = $request->input('admin_phone') ?? '';
            
            // Save events to settings as well
            if ($request->has('events')) {
                 $settingsData['events'] = $request->input('events');
            }

            Log::info('NotificationAlertService update() saving settingsData:', $settingsData); // Log data before saving
            Settings::group('notification')->set($settingsData);
            Artisan::call('optimize:clear'); // Clear cache after saving settings

            $events = $request->input('events');
            
            // If events is a JSON string, decode it for table update
            if (is_string($events)) {
                $events = json_decode($events, true);
            }

            if (is_array($events)) {
                foreach ($events as $event) {
                    if (isset($event['id'])) {
                        $updateData = [];
                        
                        if (isset($event['channels'])) {
                            $channels = $event['channels'];
                            if (isset($channels['email'])) $updateData['email'] = (bool)$channels['email']; // Cast to boolean
                            if (isset($channels['sms'])) $updateData['sms'] = (bool)$channels['sms'];       // Cast to boolean
                            if (isset($channels['whatsapp'])) $updateData['whatsapp'] = (bool)$channels['whatsapp']; // Cast to boolean
                            if (isset($channels['system'])) $updateData['system'] = (bool)$channels['system'];   // Cast to boolean
                        }

                        // Update other fields if present
                        if (isset($event['category'])) $updateData['category'] = $event['category'];
                        if (isset($event['label'])) $updateData['label'] = $event['label'];
                        if (isset($event['description'])) $updateData['description'] = $event['description'];

                        if (!empty($updateData)) {
                            NotificationAlert::updateOrCreate(
                                ['event_key' => $event['id']],
                                $updateData
                            );
                        }
                    }
                }
            }

            return $this->list();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
