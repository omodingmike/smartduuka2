<?php

    namespace App\Http\Controllers\Frontend;


    use App\Http\Controllers\Controller;
    use App\Models\ThemeSetting;

    class RootController extends Controller
    {
        public function index()
        {
            $themeFavicon = ThemeSetting::where( [ 'key' => 'theme_favicon' ] )->first();
            $favIcon      = $themeFavicon->faviconLogo;
            return view( 'master' , [ 'favicon' => $favIcon ] );
        }
    }
