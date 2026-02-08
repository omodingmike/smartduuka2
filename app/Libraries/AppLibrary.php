<?php

    namespace App\Libraries;

    use App\Enums\CurrencyPosition;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\User;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\File;
    use InvalidArgumentException;

    class AppLibrary
    {
        public static function date($date , $pattern = NULL) : string
        {
            if ( ! $pattern ) {
                $pattern = config( 'system.date_format' );
            }
            return Carbon::parse( $date )->format( $pattern );
        }

        public static function time($time , $pattern = NULL) : string
        {
            if ( ! $pattern ) {
                $pattern = config( 'system.time_format' );
            }
            return Carbon::parse( $time )->format( $pattern );
        }

        public static function datetime($dateTime , $pattern = NULL) : string
        {
            if ( ! $pattern ) {
                $pattern = config( 'system.time_format' ) . ', ' . config( 'system.date_format' );
            }
            return Carbon::parse( $dateTime )->format( $pattern );
        }

        public static function datetime2(Carbon $dateTime , $pattern = NULL) : string
        {
            if ( ! $pattern ) {
                $pattern = config( 'system.date_format' ) . ' ' . config( 'system.time_format' );
            }
            return $dateTime->format( $pattern );
        }

        public static function increaseDate($dateTime , $days , $pattern = NULL) : string
        {
            if ( ! $pattern ) {
                $pattern = config( 'system.date_format' );
            }
            return Carbon::parse( $dateTime )->addDays( $days )->format( $pattern );
        }

        public static function deliveryTime($dateTime , $pattern = NULL) : string
        {
            if ( ! $pattern ) {
                $pattern = config( 'system.time_format' );
            }
            $explode = explode( '-' , $dateTime );
            if ( count( $explode ) == 2 ) {
                return Carbon::parse( trim( $explode[ 0 ] ) )->format( $pattern ) . ' - ' . Carbon::parse(
                        trim( $explode[ 1 ] )
                    )->format( $pattern );
            }
            return '';
        }

        public static function associativeToNumericArrayBuilder($array) : array
        {
            $i          = 1;
            $buildArray = [];
            if ( count( $array ) ) {
                foreach ( $array as $arr ) {
                    if ( isset( $arr[ 'children' ] ) ) {
                        $children = $arr[ 'children' ];
                        unset( $arr[ 'children' ] );

                        $arr[ 'parent' ]  = 0;
                        $buildArray[ $i ] = $arr;
                        $parentId         = $i;
                        $i++;
                        foreach ( $children as $child ) {
                            $child[ 'parent' ] = $parentId;
                            $buildArray[ $i ]  = $child;
                            $i++;
                        }
                    }
                    else {
                        $arr[ 'parent' ]  = 0;
                        $buildArray[ $i ] = $arr;
                        $i++;
                    }
                }
            }
            return $buildArray;
        }

        public static function recursiveFlattenPermissions($array , &$buildArray = [] , &$i = 1 , $parentId = 0) : array
        {
            foreach ( $array as $arr ) {
                // Capture children if they exist (supporting both 'items' or 'children' keys)
                $children = $arr[ 'items' ] ?? $arr[ 'children' ] ?? [];

                // Clean the current item so it can be inserted into the DB
                unset( $arr[ 'items' ] , $arr[ 'children' ] );

                $currentId       = $i;
                $arr[ 'id' ]     = $currentId; // We manually assign ID to link parents/children
                $arr[ 'parent' ] = $parentId;

                $buildArray[ $currentId ] = $arr;
                $i++;

                // If children exist, recurse deeper
                if ( ! empty( $children ) ) {
                    self::recursiveFlattenPermissions( $children , $buildArray , $i , $currentId );
                }
            }
            return $buildArray;
        }

        public static function buildPermissionTree(array $array) : array
        {
            $tree        = [];
            $indexedData = [];

            // 1. Index every item by its actual database ID
            foreach ( $array as $item ) {
                $item[ 'children' ]           = [];
                $indexedData[ $item[ 'id' ] ] = $item;
            }

            // 2. Loop again to link children to parents
            foreach ( $indexedData as $id => &$item ) {
                $parentId = (int) $item[ 'parent' ];

                if ( $parentId === 0 ) {
                    // No parent, this is a root node
                    $tree[] = &$item;
                }
                else {
                    // If the parent ID exists in our list, attach as child
                    if ( isset( $indexedData[ $parentId ] ) ) {
                        $indexedData[ $parentId ][ 'children' ][] = &$item;
                    }
                    else {
                        // If parent ID is not found, keep it at the root so it's not lost
                        $tree[] = &$item;
                    }
                }
            }

            return array_values( $tree );
        }

        public static function numericToAssociativeArrayBuilder($array) : array
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
                    if ( $build[ 'url' ] == "#" && ! isset( $build[ 'children' ] ) ) {
                        unset( $buildArray[ $key ] );
                    }
                }
            }
            return $buildArray;
        }

        public static function permissionWithAccess(&$permissions , $rolePermissions) : object
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

        public static function menu(&$menus , $permissions) : array
        {
            if ( $menus && $permissions ) {
                foreach ( $menus as $key => $menu ) {
                    if ( isset( $permissions[ $menu[ 'url' ] ] ) && ! $permissions[ $menu[ 'url' ] ][ 'access' ] ) {
                        if ( $menu[ 'url' ] != '#' ) {
                            unset( $menus[ $key ] );
                        }
                    }
                }
            }
            return $menus;
        }

        public static function pluck($array , $value , $key = NULL , $type = 'object') : array
        {
            $returnArray = [];
            if ( $array ) {
                foreach ( $array as $item ) {
                    if ( $key != NULL ) {
                        if ( $type == 'array' ) {
                            $returnArray[ $item[ $key ] ] = strtolower( $value ) == 'obj' ? $item : $item[ $value ];
                        }
                        else {
                            $returnArray[ $item[ $key ] ] = strtolower( $value ) == 'obj' ? $item : $item->$value;
                        }
                    }
                    elseif ( $value == 'obj' ) {
                        $returnArray[] = $item;
                    }
                    elseif ( $type == 'array' ) {
                        $returnArray[] = $item[ $value ];
                    }
                    else {
                        $returnArray[] = $item->$value;
                    }
                }
            }
            return $returnArray;
        }

        public static function username($name)
        {
            if ( $name ) {
                $username = strtolower( str_replace( ' ' , '' , $name ) ) . rand( 1 , 999999 );
                if ( User::where( [ 'username' => $username ] )->first() ) {
                    self::username( $name );
                }
                return $username;
            }
        }

        public static function name($firstName , $lastName) : string
        {
            return $firstName . ' ' . $lastName;
        }

        public static function amountCheck($amount , $attr = 'price') : object
        {
            $response = [
                'status'  => TRUE ,
                'message' => ''
            ];

            if ( ! is_numeric( $amount ) ) {
                $response[ 'status' ]  = FALSE;
                $response[ 'message' ] = "This {$attr} must be integer.";
            }

            if ( $amount <= 0 ) {
                if ( ! $response[ 'status' ] ) {
                    return (object) $response;
                }
                else {
                    $response[ 'status' ]  = FALSE;
                    $response[ 'message' ] = "This {$attr} negative amount not allow.";
                }
            }

            $replaceValue = str_replace( '.' , '' , $amount );
            if ( strlen( $replaceValue ) > 12 ) {
                if ( ! $response[ 'status' ] ) {
                    return (object) $response;
                }
                else {
                    $response[ 'status' ]  = FALSE;
                    $response[ 'message' ] = "This {$attr} length can't be greater than 12 digit.";
                }
            }

            return (object) $response;
        }

        public static function currencyAmountFormat($amount) : string
        {
            if ( config( 'system.currency_position' ) == CurrencyPosition::RIGHT ) {
                return number_format( $amount , config( 'system.currency_decimal_point' ) ) . ' ' . currencySymbol();
            }
            return currencySymbol() . ' ' . number_format( $amount , config( 'system.currency_decimal_point' ) );
        }

        public static function flatAmountFormat($amount) : string
        {
            return number_format( $amount , config( 'system.currency_decimal_point' ) , '.' , '' );
        }

        public static function convertAmountFormat($amount) : float
        {
            return (float) number_format( $amount , config( 'system.currency_decimal_point' ) , '.' , '' );
        }

//        public static function fcmDataBind($request) : void
//        {
//            $cdn         = public_path( "firebase-cdn.txt" );
//            $textContent = public_path( "firebase-content.txt" );
//            $file        = public_path( "firebase-messaging-sw.js" );
//            $content     = 'let config = {
//        apiKey: "' . $request->notification_fcm_api_key . '",
//        authDomain: "' . $request->notification_fcm_auth_domain . '",
//        projectId: "' . $request->notification_fcm_project_id . '",
//        storageBucket: "' . $request->notification_fcm_storage_bucket . '",
//        messagingSenderId: "' . $request->notification_fcm_messaging_sender_id . '",
//        appId: "' . $request->notification_fcm_app_id . '",
//        measurementId: "' . $request->notification_fcm_measurement_id . '",' . "\n" . ' };' . "\n";
//            File::put( $file , File::get( $cdn ) . $content . File::get( $textContent ) );
//        }

        // app/Libraries/AppLibrary.php

        public static function fcmDataBind($request) : void
        {
            $cdn         = public_path( 'firebase-cdn.txt' );
            $textContent = public_path( 'firebase-content.txt' );

            // CHANGE: Use a tenant-specific filename so they don't overwrite each other
            $fileName = tenant( 'id' ) ? 'firebase-messaging-sw-' . tenant( 'id' ) . '.js' : 'firebase-messaging-sw.js';
            $file     = public_path( $fileName );

            $content = 'let config = {
        apiKey: "' . $request->notification_fcm_api_key . '",
        authDomain: "' . $request->notification_fcm_auth_domain . '",
        projectId: "' . $request->notification_fcm_project_id . '",
        storageBucket: "' . $request->notification_fcm_storage_bucket . '",
        messagingSenderId: "' . $request->notification_fcm_messaging_sender_id . '",
        appId: "' . $request->notification_fcm_app_id . '",
        measurementId: "' . $request->notification_fcm_measurement_id . '",' . "\n" . ' };' . "\n";

            File::put( $file , File::get( $cdn ) . $content . File::get( $textContent ) );
        }

        public static function defaultPermission($permissions)
        {
            $defaultPermission = (object) [];
            if ( count( $permissions ) ) {
                foreach ( $permissions as $permission ) {
                    if ( $permission->access ) {
                        $defaultPermission = $permission;
                        break;
                    }
                }
            }
            return $defaultPermission;
        }

        public static function domain($input) : array | string | null
        {
            $input = trim( $input , '/' );
            if ( ! preg_match( '#^http(s)?://#' , $input ) ) {
                $input = 'http://' . $input;
            }
            $urlParts = parse_url( $input );

            $link = '';
            if ( isset( $urlParts[ 'port' ] ) ) {
                $link .= ':' . $urlParts[ 'port' ];
            }

            if ( isset( $urlParts[ 'path' ] ) ) {
                $link .= $urlParts[ 'path' ];
            }

            return preg_replace( '/^www\./' , '' , ( $urlParts[ 'host' ] . $link ) );
        }

        public static function licenseApiResponse($response)
        {
            $header      = explode( ';' , $response->getHeader( 'Content-Type' )[ 0 ] );
            $contentType = $header[ 0 ];
            if ( $contentType == 'application/json' ) {
                $contents = $response->getBody()->getContents();
                $data     = json_decode( $contents );
                if ( json_last_error() == JSON_ERROR_NONE ) {
                    return $data;
                }
                return $contents;
            }

            return [ 'status' => FALSE , 'message' => 'data not found' ];
        }


        public static function deleteDir($dirPath) : void
        {
            if ( ! is_dir( $dirPath ) ) {
                throw new InvalidArgumentException( "$dirPath must be a directory" );
            }
            if ( ! str_ends_with( $dirPath , '/' ) ) {
                $dirPath .= '/';
            }
            $files = glob( $dirPath . '*' , GLOB_MARK );
            foreach ( $files as $file ) {
                if ( is_dir( $file ) ) {
                    self::deleteDir( $file );
                }
                else {
                    unlink( $file );
                }
            }
            rmdir( $dirPath );
        }

        public static function sku($sku)
        {
            $productVariation = ProductVariation::where( [ 'sku' => $sku ] )->first();
            $product          = Product::where( [ 'sku' => $sku ] )->first();
            if ( $productVariation || $product ) {
                self::sku( rand( 1 , 9999999999999 ) );
            }
            return $sku;
        }

        public static function recursive($elements , $parentId = 0) : array
        {
            $branch = [];
            foreach ( $elements as $element ) {
                if ( $element[ 'parent_id' ] == $parentId ) {
                    $children = self::recursive( $elements , $element[ 'id' ] );
                    if ( $children ) {
                        $element[ 'children' ] = $children;
                    }
                    $branch[] = $element;
                }
            }

            return $branch;
        }

        public static function tagString($arrays) : string
        {
            $string = '';
            $i      = 1;
            $count  = count( $arrays );
            if ( count( $arrays ) > 0 ) {
                foreach ( $arrays as $array ) {
                    if ( $i == $count ) {
                        $string .= $array->name;
                    }
                    else {
                        $string .= $array->name . ', ';
                    }
                    $i++;
                }
            }
            return $string;
        }

        public static function taxString($arrays) : string
        {
            $string = '';
            $i      = 1;
            $count  = count( $arrays );
            if ( count( $arrays ) > 0 ) {
                foreach ( $arrays as $array ) {
                    if ( $i == $count ) {
                        $string .= $array?->tax?->name;
                    }
                    else {
                        $string .= $array?->tax?->name . ', ';
                    }
                    $i++;
                }
            }
            return $string;
        }

        public static function lowerWithReplaceToSpace($string) : string
        {
            return strtolower( str_replace( $string , '' , ' ' ) );
        }

        public static function reportCurrencyAmountFormat($amount) : string
        {
            return number_format( $amount , config( 'system.currency_decimal_point' ) , '.' , ',' );
        }

        public static function textShortener($text , $number = 30)
        {
            if ( $text && mb_strlen( $text ) > $number ) {
                return mb_substr( $text , 0 , $number ) . "..";
            }
            return $text;
        }
    }
