<?php

    namespace App\Helpers;

    use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;


    class TenantAwareUrlGenerator extends DefaultUrlGenerator
    {
        public function getUrl() : string
        {
            $url = tenant_asset( $this->getPathRelativeToRoot() );
            return $this->versionUrl( $url );
        }
    }