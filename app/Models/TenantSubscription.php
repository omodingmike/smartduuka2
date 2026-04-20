<?php

    namespace App\Models;

    use App\Enums\Plan;
    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Model;

    class TenantSubscription extends Model
    {
        protected $fillable = [
            'duration' ,
            'plan' ,
            'setup' ,
            'amount' , 'status' , 'tenant_id' , 'expires_at',
        ];
        protected $casts    = [ 'status' => Status::class , 'plan' => Plan::class ];
    }
