<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Attributes\Description;
    use Illuminate\Console\Attributes\Signature;
    use Illuminate\Console\Command;
    use Smartisan\Settings\Facades\Settings;
    use Stancl\Tenancy\Concerns\HasATenantsOption;
    use Stancl\Tenancy\Concerns\TenantAwareCommand;

    #[Signature( 'reset-notification-settings' )]
    #[Description( 'Command description' )]
    class ResetNotificationSettings extends Command
    {
        use TenantAwareCommand , HasATenantsOption;

        public function handle() : void
        {
            $e = Settings::group( 'notification' )->get( 'events' );

            $eventsArray = is_array( $e ) ? $e : json_decode( $e , TRUE );

            if ( is_array( $eventsArray ) ) {
                foreach ( $eventsArray as &$event ) {
                    if ( isset( $event[ 'channels' ] ) ) {
                        foreach ( $event[ 'channels' ] as $channel => $value ) {
                            if ( $channel !== 'system' ) {
                                $event[ 'channels' ][ $channel ] = FALSE;
                            }
                            else {
                                $event[ 'channels' ][ $channel ] = TRUE;
                            }
                        }
                    }
                }
                Settings::group( 'notification' )->set( 'events' , $eventsArray );

                $this->info( 'Notification settings have been reset.' );
            }
        }
    }
