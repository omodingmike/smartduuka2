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
                'module_warehouse'    => (int) data_get($this->info, 'module_warehouse', Ask::NO),
                'module_wholesale'    => (int) data_get($this->info, 'module_wholesale', Activity::DISABLE),
                'accounting'          => (int) data_get($this->info, 'accounting', Ask::NO),
                'production'          => (int) data_get($this->info, 'production', Ask::NO),
                'a4_receipt'          => data_get($this->info, 'a4_receipt', Ask::NO),
                'primaryColor'        => data_get($this->info, 'primaryColor', NULL),
                'primaryLight'        => data_get($this->info, 'primaryLight', NULL),
                'secondaryColor'      => data_get($this->info, 'secondaryColor', NULL),
                'secondaryLight'      => data_get($this->info, 'secondaryLight', NULL),
                Modules::COMMISSION   => (int) data_get($this->info, Modules::COMMISSION, Ask::NO),
                Modules::DISTRIBUTION => (int) data_get($this->info, Modules::DISTRIBUTION, Ask::NO),
            ];
        }
    }
