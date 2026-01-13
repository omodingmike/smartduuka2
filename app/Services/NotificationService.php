<?php

namespace App\Services;

use App\Http\Requests\NotificationChannelRequest;
use App\Http\Requests\NotificationRequest;
use App\Libraries\AppLibrary;
use Dipokhalder\EnvEditor\EnvEditor;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;

class NotificationService
{
    public $envService;

    private const EVENTS = [
        'new_order' => [
            'category' => 'Sales',
            'label' => 'New Sale / Order',
            'description' => 'New order received from POS or Online Store.',
        ],
        'sale_voided' => [
            'category' => 'Sales',
            'label' => 'Sale Voided / Deleted',
            'description' => 'A completed sale is voided by a cashier.',
        ],
        'refund_processed' => [
            'category' => 'Sales',
            'label' => 'Refund Processed',
            'description' => 'A sale refund or return is approved.',
        ],
        'low_stock' => [
            'category' => 'Inventory',
            'label' => 'Low Stock Alert',
            'description' => 'Product inventory drops below reorder level.',
        ],
        'purchase_received' => [
            'category' => 'Inventory',
            'label' => 'Purchase Order Received',
            'description' => 'New stock arrived from a supplier.',
        ],
        'transfer_received' => [
            'category' => 'Inventory',
            'label' => 'Stock Transfer Received',
            'description' => 'Incoming stock from another branch is verified.',
        ],
        'stock_adjustment' => [
            'category' => 'Inventory',
            'label' => 'Stock Adjustment (Damage/Loss)',
            'description' => 'Manual adjustment of stock levels due to damage or theft.',
        ],
        'expense_added' => [
            'category' => 'Finance',
            'label' => 'Expense Created',
            'description' => 'A new expense record is added by staff.',
        ],
        'credit_limit' => [
            'category' => 'Finance',
            'label' => 'Credit Limit Warning',
            'description' => 'Customer attempts to buy on credit exceeding their limit.',
        ],
        'credit_payment' => [
            'category' => 'Finance',
            'label' => 'Credit Payment Received',
            'description' => 'Customer pays off their outstanding debt.',
        ],
        'deposit_added' => [
            'category' => 'Finance',
            'label' => 'New Deposit Received',
            'description' => 'Security deposit or advance payment received.',
        ],
        'shift_closed' => [
            'category' => 'Finance',
            'label' => 'Shift / Register Closed',
            'description' => 'End of day report and cash reconciliation.',
        ],
        'sub_expiring' => [
            'category' => 'System',
            'label' => 'Subscription Expiring Soon',
            'description' => 'System license is about to expire (7 days notice).',
        ],
        'sub_failed' => [
            'category' => 'System',
            'label' => 'Subscription Payment Failed',
            'description' => 'Automatic renewal of system subscription failed.',
        ],
        'login_alert' => [
            'category' => 'System',
            'label' => 'Unusual Login Attempt',
            'description' => 'Login detected from a new device or IP.',
        ],
    ];

    public function __construct(EnvEditor $envEditor)
    {
        $this->envService = $envEditor;
    }

    /**
     * @throws Exception
     */
    public function list()
    {
        try {
            $settings = Settings::group('notification')->all();
            
            if (isset($settings['events'])) {
                $storedEvents = json_decode($settings['events'], true);
                if (is_array($storedEvents)) {
                    $enrichedEvents = [];
                    foreach ($storedEvents as $event) {
                        if (isset($event['id']) && isset(self::EVENTS[$event['id']])) {
                            $enrichedEvents[] = array_merge($event, self::EVENTS[$event['id']]);
                        } else {
                            $enrichedEvents[] = $event;
                        }
                    }
                    $settings['events'] = json_encode($enrichedEvents);
                }
            } else {
                // If events are not set, return default structure
                $defaultEvents = [];
                foreach (self::EVENTS as $id => $details) {
                    $defaultEvents[] = array_merge(['id' => $id], $details, [
                        'channels' => [
                            'email' => false,
                            'sms' => false,
                            'whatsapp' => false,
                            'system' => true
                        ]
                    ]);
                }
                $settings['events'] = json_encode($defaultEvents);
            }

            return $settings;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @param NotificationRequest $request
     * @return
     * @throws Exception
     */
    public function update(NotificationRequest $request)
    {
        try {
            AppLibrary::fcmDataBind($request);
            Settings::group('notification')->set($request->validated());
            $this->envService->addData([
                'FCM_SECRET_KEY' => $request->notification_fcm_secret_key
            ]);
            Artisan::call('optimize:clear');
            return $this->list();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @param NotificationChannelRequest $request
     * @return
     * @throws Exception
     */
    public function updateChannels(NotificationChannelRequest $request)
    {
        try {
            $data = $request->validated();
            if (isset($data['admin_email']) && is_null($data['admin_email'])) {
                $data['admin_email'] = '';
            }
            if (isset($data['admin_phone']) && is_null($data['admin_phone'])) {
                $data['admin_phone'] = '';
            }
            Settings::group('notification')->set($data);
            return $this->list();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
