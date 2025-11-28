<?php

    namespace App\Enums;

    enum DeliveryType : int
    {
        case CUSTOMER_DROPS_OFF_AND_COLLECTS = 1;
        case WE_PICK_UP_AND_DELIVER          = 2;
        case WE_PICK_UP_CUSTOMER_COLLECTS    = 3;
        case CUSTOMER_DROPS_OFF_WE_DELIVER   = 4;

        public function label() : string
        {
            return match ($this) {
                self::CUSTOMER_DROPS_OFF_AND_COLLECTS => 'Customer Drops Off & Collects',
                self::WE_PICK_UP_AND_DELIVER          => 'We Pick Up & Deliver',
                self::WE_PICK_UP_CUSTOMER_COLLECTS    => 'We Pick Up, Customer Collects',
                self::CUSTOMER_DROPS_OFF_WE_DELIVER   => 'Customer Drops Off, We Deliver',
            };
        }

        public static function tryFromLabel(string $label) : ?self
        {
            foreach (self::cases() as $case) {
                if ($case->label() === $label) {
                    return $case;
                }
            }
            return null;
        }

        /**
         * Returns full status flow as structured enum-based arrays.
         */
        public function steps() : array
        {
            return match ($this) {

                self::CUSTOMER_DROPS_OFF_AND_COLLECTS => [
                    [
                        'label' => CleaningOrderStatus::PendingAcceptance->label(),
                        'value' => CleaningOrderStatus::PendingAcceptance->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Accepted->label(),
                        'value' => CleaningOrderStatus::Accepted->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::AwaitingDropOff->label(),
                        'value' => CleaningOrderStatus::AwaitingDropOff->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Received->label(),
                        'value' => CleaningOrderStatus::Received->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Cleaning->label(),
                        'value' => CleaningOrderStatus::Cleaning->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::ReadyForPickup->label(),
                        'value' => CleaningOrderStatus::ReadyForPickup->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Completed->label(),
                        'value' => CleaningOrderStatus::Completed->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Cancelled->label(),
                        'value' => CleaningOrderStatus::Cancelled->value,
                    ],
                ],

                // 2. Customer Drops Off, We Deliver
                self::CUSTOMER_DROPS_OFF_WE_DELIVER => [
                    [
                        'label' => CleaningOrderStatus::PendingAcceptance->label(),
                        'value' => CleaningOrderStatus::PendingAcceptance->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Accepted->label(),
                        'value' => CleaningOrderStatus::Accepted->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::AwaitingDropOff->label(),
                        'value' => CleaningOrderStatus::AwaitingDropOff->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Received->label(),
                        'value' => CleaningOrderStatus::Received->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Cleaning->label(),
                        'value' => CleaningOrderStatus::Cleaning->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::ReadyForDelivery->label(),
                        'value' => CleaningOrderStatus::ReadyForDelivery->value,
                    ],
                    // These statuses do NOT exist in your enum, so skipping:
                    // Out for Delivery, Delivered
                    [
                        'label' => CleaningOrderStatus::Completed->label(),
                        'value' => CleaningOrderStatus::Completed->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Cancelled->label(),
                        'value' => CleaningOrderStatus::Cancelled->value,
                    ],
                ],

                // 3. We Pick Up, Customer Collects
                self::WE_PICK_UP_CUSTOMER_COLLECTS => [
                    [
                        'label' => CleaningOrderStatus::PendingAcceptance->label(),
                        'value' => CleaningOrderStatus::PendingAcceptance->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Accepted->label(),
                        'value' => CleaningOrderStatus::Accepted->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::AwaitingPickup->label(),
                        'value' => CleaningOrderStatus::AwaitingPickup->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Received->label(),
                        'value' => CleaningOrderStatus::Received->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Cleaning->label(),
                        'value' => CleaningOrderStatus::Cleaning->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::ReadyForPickup->label(),
                        'value' => CleaningOrderStatus::ReadyForPickup->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Completed->label(),
                        'value' => CleaningOrderStatus::Completed->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Cancelled->label(),
                        'value' => CleaningOrderStatus::Cancelled->value,
                    ],
                ],

                // 4. We Pick Up & Deliver
                self::WE_PICK_UP_AND_DELIVER => [
                    [
                        'label' => CleaningOrderStatus::PendingAcceptance->label(),
                        'value' => CleaningOrderStatus::PendingAcceptance->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Accepted->label(),
                        'value' => CleaningOrderStatus::Accepted->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::AwaitingPickup->label(),
                        'value' => CleaningOrderStatus::AwaitingPickup->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Received->label(),
                        'value' => CleaningOrderStatus::Received->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Cleaning->label(),
                        'value' => CleaningOrderStatus::Cleaning->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::ReadyForDelivery->label(),
                        'value' => CleaningOrderStatus::ReadyForDelivery->value,
                    ],
                    // Same as before — skipping non-enum statuses
                    [
                        'label' => CleaningOrderStatus::Completed->label(),
                        'value' => CleaningOrderStatus::Completed->value,
                    ],
                    [
                        'label' => CleaningOrderStatus::Cancelled->label(),
                        'value' => CleaningOrderStatus::Cancelled->value,
                    ],
                ],
            };
        }

        public static function options() : array
        {
            return array_map(
                fn($case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ],
                self::cases()
            );
        }
    }
