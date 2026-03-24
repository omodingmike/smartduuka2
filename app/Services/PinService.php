<?php

    namespace App\Services;

    use Exception;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Random\RandomException;

    class PinService
    {
        /**
         * @throws RandomException
         */
        public function generateUniquePin() : string
        {
            $maxAttempts = 500;
            $attempts    = 0;
            $isUnique    = FALSE;

            do {
                $attempts++;
                $rawPin    = str_pad( random_int( 0 , 99999 ) , 5 , '0' , STR_PAD_LEFT );
                $hashedPin = hash_hmac( 'sha256' , $rawPin , config( 'app.pin_pepper' ) );

                // 3. Check if this hash is already in use by ANY active user in this tenant's DB
                $exists = DB::table( 'users' )
                            ->where( 'pin' , $hashedPin )
                            ->exists();

                if ( ! $exists ) {
                    $isUnique = TRUE;
                }

                if ( $attempts >= $maxAttempts ) {
                    Log::critical( 'PIN keyspace exhaustion detected. Failed to generate a unique PIN after 500 attempts.' );
                    throw new Exception( 'Unable to generate a unique PIN at this time. Please contact support.' );
                }

            } while ( ! $isUnique );
            return $rawPin;
        }

        public function verifyPin(string $pin_hash , string $rawPin) : bool
        {
            $hashedInput = $this->hashPin( $rawPin );

            return hash_equals( $pin_hash , $hashedInput );
        }

        /**
         * Internal helper to ensure consistent hashing logic.
         */
        public function hashPin(string $rawPin) : string
        {
            return hash_hmac( 'sha256' , $rawPin , config( 'app.pin_pepper' ) );
        }
    }