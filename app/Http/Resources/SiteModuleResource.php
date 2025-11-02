<?php

    namespace App\Http\Resources;

    use App\Enums\Activity;
    use App\Enums\Ask;
    use App\Enums\Modules;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class SiteModuleResource extends JsonResource
    {
        public $info;

        public function __construct($info)
        {
            parent::__construct( $info );
            $this->info = $info;
        }

        public function toArray(Request $request) : array
        {
            return [
                'module_warehouse'    => $this->info[ 'module_warehouse' ] ?? 0 ,
                'module_wholesale'    => $this->info[ 'module_wholesale' ] ?? Activity::DISABLE ,
                'accounting'          => $this->info[ 'accounting' ] ?? Ask::NO ,
                'production'          => $this->info[ 'production' ] ?? Ask::NO ,
                'a4_receipt'          => $this->info[ 'a4_receipt' ] ?? Ask::NO ,
                'primaryColor'        => $this->info[ 'primaryColor' ] ?? NULL ,
                'primaryLight'        => $this->info[ 'primaryLight' ] ?? NULL ,
                'secondaryColor'      => $this->info[ 'secondaryColor' ] ?? NULL ,
                'secondaryLight'      => $this->info[ 'secondaryLight' ] ?? NULL ,
                Modules::COMMISSION   => $this->info[ Modules::COMMISSION ] ?? NULL ,
                Modules::DISTRIBUTION => $this->info[ Modules::DISTRIBUTION ] ?? NULL ,
            ];
        }
    }
