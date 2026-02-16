<?php

    namespace Database\Seeders;

    use App\Enums\Status;
    use App\Models\Warehouse;
    use Illuminate\Database\Seeder;

    class WarehouseSeeder extends Seeder
    {
        public function run() : void
        {
            Warehouse::firstOrCreate( [ 'name' => 'Shop Storage' ] , [
                'name'     => 'Shop Storage' ,
                'location' => 'Kampala' ,
                'phone'    => '+256759370734' ,
                'email'    => 'shop@shop.com' ,
                'manager'  => 'Manager' ,
                'status'   => Status::ACTIVE()
            ] );
        }
    }
