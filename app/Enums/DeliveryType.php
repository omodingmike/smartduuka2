<?php

    namespace App\Enums;

    enum DeliveryType : int
    {
        case CUSTOMER_DROPS_OFF_AND_COLLECTS = 0;
        case WE_PICK_UP_AND_DELIVER          = 1;
        case WE_PICK_UP_CUSTOMER_COLLECTS    = 2;
        case CUSTOMER_DROPS_OFF_WE_DELIVER   = 3;

        public function label(): string
        {
            return match ($this) {
                self::CUSTOMER_DROPS_OFF_AND_COLLECTS => 'Customer Drops Off & Collects',
                self::WE_PICK_UP_AND_DELIVER          => 'We Pick Up & Deliver',
                self::WE_PICK_UP_CUSTOMER_COLLECTS    => 'We Pick Up, Customer Collects',
                self::CUSTOMER_DROPS_OFF_WE_DELIVER   => 'Customer Drops Off, We Deliver',
            };
        }

        /**
         * Try to get enum instance from a label (for DeliveryType itself, not steps)
         */
        public static function tryFromLabel(string $label): ?self
        {
            foreach (self::cases() as $case) {
                if ($case->label() === $label) {
                    return $case;
                }
            }
            return null;
        }

        public function steps(): array
        {
            $steps = match ($this) {
                self::CUSTOMER_DROPS_OFF_AND_COLLECTS => [
                    'Pending Acceptance',
                    'Accepted',
                    'Awaiting Drop off',
                    'Received',
                    'Cleaning',
                    'Ready to Pickup',
                    'Completed',
                ],
                self::WE_PICK_UP_AND_DELIVER => [
                    'Pending Acceptance',
                    'Accepted',
                    'Awaiting Pickup',
                    'Picked up',
                    'Cleaning',
                    'Ready for Delivery',
                    'Completed',
                    'Cancelled',
                ],
                self::WE_PICK_UP_CUSTOMER_COLLECTS => [
                    'Pending Acceptance',
                    'Accepted',
                    'Awaiting Pickup',
                    'Picked up',
                    'Cleaning',
                    'Ready to Pickup',
                    'Completed',
                    'Cancelled',
                ],
                self::CUSTOMER_DROPS_OFF_WE_DELIVER => [
                    'Pending Acceptance',
                    'Accepted',
                    'Awaiting Drop off',
                    'Received',
                    'Cleaning',
                    'Ready for Delivery',
                    'Completed',
                    'Cancelled',
                ],
            };

            return array_map(
                fn($step) => [
                    'title' => $step,
                    'value' => CleaningOrderStatus::tryFromLabel($step)?->value ?? null
                ],
                $steps
            );
        }

        public static function options(): array
        {
            return array_map(
                fn($case) => ['value' => $case->value, 'label' => $case->label()],
                self::cases()
            );
        }
    }
