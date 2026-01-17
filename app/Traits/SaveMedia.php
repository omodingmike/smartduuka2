<?php

    namespace App\Traits;

    use App\Enums\MediaEnum;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Str;


    trait SaveMedia
    {
        public function saveMedia(Request $request , Model $model , string $collection) : void
        {
            if ( $request->hasFile( MediaEnum::MEDIA_FILE ) ) {
                $media = $model->addMediaFromRequest( MediaEnum::MEDIA_FILE )
                               ->usingFileName( Str::random( 20 ) . '.' . $request->file( MediaEnum::MEDIA_FILE )->getClientOriginalExtension() )
//                               ->withResponsiveImages()
                               ->toMediaCollection( $collection );
                Artisan::call( 'media-library:regenerate' , [
                    '--ids'   => [ $media->id ] ,
                    '--force' => TRUE ,
                ] );
            }
        }
    }