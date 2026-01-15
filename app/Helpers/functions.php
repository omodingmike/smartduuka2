<?php

    use App\Enums\Ask;
    use App\Enums\BarcodeType;
    use App\Enums\Constants;
    use App\Enums\OrderStatus;
    use App\Enums\PaymentStatus;
    use App\Enums\Role;
    use App\Enums\SettingsEnum;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Models\ActivityLog;
    use App\Models\ChartOfAccountGroup;
    use App\Models\Currency;
    use App\Models\Ledger;
    use App\Models\Order;
    use App\Models\PaymentAccount;
    use App\Models\PaymentMethod;
    use App\Models\RoyaltyPointsExchageRate;
    use App\Models\User;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Smartisan\Settings\Facades\Settings;

    function project()
    {
        return Settings::group( 'site' )->get( 'project' );
    }

    function numericToAssociativeArrayBuilder($array) : array
    {
        $i                 = 0;
        $parentId          = NULL;
        $parentIncrementId = NULL;
        $buildArray        = [];
        if ( count( $array ) ) {
            foreach ( $array as $arr ) {
                if ( ! $arr[ 'parent' ] ) {
                    $parentId          = $arr[ 'id' ];
                    $parentIncrementId = $i;
                    $buildArray[ $i ]  = $arr;
                    $i++;
                }

                if ( $arr[ 'parent' ] == $parentId ) {
                    $buildArray[ $parentIncrementId ][ 'children' ][] = $arr;
                }
            }
        }
        if ( $buildArray ) {
            foreach ( $buildArray as $key => $build ) {
                if ( $build[ 'url' ] == '#' && ! isset( $build[ 'children' ] ) ) {
                    unset( $buildArray[ $key ] );
                }
            }
        }
        return $buildArray;
    }

    function phoneNumber() : string
    {
        $code  = Settings::group( 'company' )->get( 'company_calling_code' );
        $phone = substr( Settings::group( 'company' )->get( 'company_phone' ) , -9 );
        return "$code$phone";
    }

    function permissionWithAccess(&$permissions , $rolePermissions) : object
    {
        if ( $permissions ) {
            foreach ( $permissions as $permission ) {
                if ( isset( $rolePermissions[ $permission->id ] ) ) {
                    $permission->access = TRUE;
                }
                else {
                    $permission->access = FALSE;
                }
            }
        }
        return $permissions;
    }

    function isDistributor()
    {
        $user = Auth::user();
        return $user->roles->some( fn($role) => $role->id == Role::DISTRIBUTOR );
    }

    function parseDate(string $date) : string
    {
        return Carbon::parse( $date )->format( 'Y-m-d H:i:s' );
    }

    function toCarbonDate(string $date) : Carbon
    {
        return Carbon::parse( $date );
    }

    function ledgerCode() : int | string
    {
        $last_ledger = Ledger::orderBy( 'id' , 'desc' )->first();
        return ! $last_ledger ? Str::padLeft( 1 , 5 , '0' ) : Str::padLeft( ( (int) $last_ledger->code ) + 1 , 5 , '0' );
    }

    function enabledWarehouse() : bool
    {
        $setting = Settings::group( 'module' )->get( 'module_warehouse' );
        if ( $setting == 1 ) {
            return TRUE;
        }
        return FALSE;
    }

    function moduleEnabled(string $key , string $group = 'module') : bool
    {
        return Settings::group( $group )->get( $key ) == Ask::YES;
    }

    function settingEnabled(string $key , string $group = SettingsEnum::APP_SETTINGS->value) : bool
    {
        return Settings::group( $group )->get( $key ) == Ask::YES;
    }

    function isEnabled(string $key , string $group) : bool
    {
        return filter_var( Settings::group( $group )->get( $key ) ?? FALSE , FILTER_VALIDATE_BOOLEAN );
    }

    function settingValue(string $key , string $group = SettingsEnum::APP_SETTINGS->value) : string | null
    {
        return Settings::group( $group )->get( $key );
    }

    function activityLog(string $action) : void
    {
        ActivityLog::create( [
            'user_id'   => Auth::user()->id ,
            'user_type' => User::class ,
            'action'    => $action ,
        ] );
    }

    function orderLabel(Order $order) : string
    {
        if ( $order->status == OrderStatus::PENDING ) {
            return 'Quotation';
        }
        else if ( $order->payment_status == PaymentStatus::UNPAID || $order->payment_status == PaymentStatus::PARTIALLY_PAID ) {
            return 'Invoice';
        }
        else if ( $order->payment_status == PaymentStatus::PAID ) {
            return 'Receipt';
        }
        else return '';
    }

    function orderName(Order $order) : string
    {
        $order_serial_no = $order->order_serial_no;
        $label           = orderLabel( $order );
        return $order->user->name . ' ' . $label . '#' . $order_serial_no;
    }

    function validateAndCorrectChecksum($code , $type) : string
    {
        $code = (string) $code;

        if ( $type === BarcodeType::EAN_13 ) {
            if ( strlen( $code ) !== 13 ) {
                throw new \Exception( 'EAN-13 must be 13 digits long.' );
            }

            $digits   = str_split( $code );
            $checksum = 0;

            // Calculate the checksum for EAN-13
            for ( $i = 0 ; $i < 12 ; $i++ ) {
                $digit    = (int) $digits[ $i ];
                $checksum += ( $i % 2 === 0 ) ? $digit : $digit * 3;
            }

            $calculatedChecksum = ( 10 - ( $checksum % 10 ) ) % 10;

            // Compare the calculated checksum with the 13th digit
            if ( $calculatedChecksum != $digits[ 12 ] ) {
                // If the checksum is invalid, correct it
                return substr( $code , 0 , 12 ) . $calculatedChecksum;
            }

            return $code; // The checksum is already correct
        }

        if ( $type === BarcodeType::UPC_A ) {
            // UPC-A requires 12 digits (11 digits + 1 checksum)
            if ( strlen( $code ) !== 12 ) {
                throw new \Exception( 'UPC-A must be 12 digits long.' );
            }

            $digits   = str_split( $code );
            $checksum = 0;

            // Calculate the checksum for UPC-A
            for ( $i = 0 ; $i < 11 ; $i++ ) {
                $digit    = (int) $digits[ $i ];
                $checksum += ( $i % 2 === 0 ) ? $digit * 3 : $digit;
            }

            $calculatedChecksum = ( 10 - ( $checksum % 10 ) ) % 10;

            // Compare the calculated checksum with the 12th digit
            if ( $calculatedChecksum != $digits[ 11 ] ) {
                // If the checksum is invalid, correct it
                return substr( $code , 0 , 11 ) . $calculatedChecksum;
            }
            return $code; // The checksum is already correct
        }

        throw new \Exception( 'Unsupported barcode type.' );
    }

    function validateAndCorrectEAN13Checksum($ean13)
    {
        if ( strlen( $ean13 ) !== 13 ) {
            throw new \Exception( 'EAN-13 must be 13 digits long.' );
        }

        $digits   = str_split( $ean13 );
        $checksum = 0;

        // Calculate the checksum
        for ( $i = 0 ; $i < 12 ; $i++ ) {
            $digit    = (int) $digits[ $i ];
            $checksum += ( $i % 2 === 0 ) ? $digit : $digit * 3;
        }

        $calculatedChecksum = ( 10 - ( $checksum % 10 ) ) % 10;

        // Compare the calculated checksum with the 13th digit
        if ( $calculatedChecksum != $digits[ 12 ] ) {
            // If the checksum is invalid, correct it
            $correctedEAN13 = substr( $ean13 , 0 , 12 ) . $calculatedChecksum;
            return $correctedEAN13;
        }

        return $ean13; // The checksum is already correct
    }

    function royaltyPointsExchangeRate() : float | int
    {
        $exchange_rate = RoyaltyPointsExchageRate::first();
        $points_value  = Constants::ROYALTY_POINTS_DEFAULT_VALUE;
        if ( $exchange_rate ) {
            $points_value = $exchange_rate->value / $exchange_rate->points;
        }
        return $points_value;
    }

    function clean_amount(string $value) : int
    {
        return (int) preg_replace( '/\D/' , '' , $value );
    }

    function transformGroup($group)
    {
        return [
            'id'       => $group->id ,
            'name'     => $group->name ,
            'ledgers'  => $group->ledger ,
            'children' => $group->childrenRecursive->map( fn($child) => transformGroup( $child ) ) ,
        ];
    }

    function updateCoa() : void
    {
        $default_currency_symbol = Settings::group( 'site' )->get( 'site_default_currency_symbol' );
        $default_currency        = Currency::where( 'symbol' , $default_currency_symbol )->first();

        $code = ledgerCode();

        $current_assets = ChartOfAccountGroup::whereName( 'Current Assets' )->first();
        $revenue        = ChartOfAccountGroup::whereName( 'Revenue' )->first();

        foreach ( PaymentMethod::all() as $payment_method ) {
            if ( $default_currency ) {
                PaymentAccount::firstOrCreate(
                    [ 'name' => $payment_method->name ] ,
                    [ 'currency_id' => $default_currency->id ]
                );
            }
        }

        foreach ( PaymentAccount::all() as $payment_account ) {
            if ( $current_assets ) {
                Ledger::firstOrCreate(
                    [ 'name' => $payment_account->name , 'parent_id' => $current_assets->id ] ,
                    [
                        'currency_id' => $payment_account->currency_id ,
                        'type'        => 'debit' ,
                        'code'        => $code ,
                    ]
                );
            }
        }

        $ledgers = [
            [
                'name'      => 'Stock value' ,
                'parent_id' => $current_assets?->id ,
                'type'      => 'debit' ,
            ] ,
            [
                'name'      => 'Sales' ,
                'parent_id' => $revenue?->id ,
                'type'      => 'credit' ,
            ] ,
            [
                'name'      => 'Sales return' ,
                'parent_id' => $revenue?->id ,
                'type'      => 'debit' ,
            ] ,
            [
                'name'      => 'Cost of Sales' ,
                'parent_id' => $revenue?->id ,
                'type'      => 'debit' ,
            ] ,
        ];

        foreach ( $ledgers as $ledger ) {
            if ( $current_assets && $revenue ) {
                Ledger::firstOrCreate(
                    [ 'name' => $ledger[ 'name' ] , 'parent_id' => $ledger[ 'parent_id' ] ] ,
                    [
                        'currency_id' => $default_currency->id ,
                        'type'        => $ledger[ 'type' ] ,
                        'code'        => $code ,
                    ]
                );
            }
        }
    }

    function phpToDateFnsFormat(string $phpFormat) : string
    {
        $replacements = [
            // Day
            'd' => 'dd' ,     // Day with leading zero
            'j' => 'd' ,      // Day without leading zero
            'D' => 'EEE' ,    // Short weekday name
            'l' => 'EEEE' ,   // Full weekday name
            'S' => 'do' ,     // Day with ordinal suffix (e.g., 1st, 2nd)

            // Month
            'm' => 'MM' ,     // Month with leading zero
            'n' => 'M' ,      // Month without leading zero
            'M' => 'MMM' ,    // Short month name (e.g., Jan)
            'F' => 'MMMM' ,   // Full month name (e.g., January)

            // Year
            'Y' => 'yyyy' ,   // Full year
            'y' => 'yy' ,     // Two-digit year

            // Separator characters like -, /, ., space, etc. are kept
        ];

        // Convert the format string
        return preg_replace_callback( '/[a-zA-Z]/' , function ($matches) use ($replacements) {
            $char = $matches[ 0 ];
            return $replacements[ $char ] ?? $char;
        } , $phpFormat );
    }

    function statusLabel($status) : string | null
    {
        return match ( $status ) {
            Status::ACTIVE   => 'Active' ,
            Status::INACTIVE => 'Inactive' ,
            Status::CANCELED => 'Canceled' ,
            default          => NULL
        };
    }

    function stockStatusLabel($status) : string | null
    {
        return match ( $status ) {
            StockStatus::OUT_OF_STOCK => 'Out of Stock' ,
            StockStatus::LOW_STOCK    => 'Low Stock' ,
            StockStatus::IN_STOCK     => 'In stock' ,
            default                   => NULL
        };
    }



