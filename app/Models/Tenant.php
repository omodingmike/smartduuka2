<?php

    namespace App\Models;

    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Stancl\Tenancy\Contracts\TenantWithDatabase;
    use Stancl\Tenancy\Database\Concerns\HasDatabase;
    use Stancl\Tenancy\Database\Concerns\HasDomains;
    use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
    use Stancl\Tenancy\Database\Models\TenantPivot;

    class Tenant extends BaseTenant implements TenantWithDatabase
    {
        use HasDatabase , HasDomains;

        protected $casts = [ 'status' => Status::class ];

        protected $fillable = [
            'id' ,
            'business_id' ,
            'print_agent_token' ,
            'data' , 'pin_pepper' , 'frontend_url' , 'name' , 'type' , 'location' , 'email' , 'phone' , 'domain' , 'status','company_whatsapp_phone'
        ];

        public static function getCustomColumns() : array
        {
            return [
                'id' ,
                'business_id' ,
                'print_agent_token' , 'name' , 'type' , 'location' ,
                'email' , 'phone' , 'domain' , 'status'
            ];
        }

        public function subscriptions() : HasMany
        {
            return $this->hasMany( TenantSubscription::class , 'tenant_id' , 'id' )->latest();
        }

        public function users() : BelongsToMany
        {
            return $this->belongsToMany( CentralUser::class , 'tenant_users' , 'tenant_id' , 'global_user_id' , 'id' , 'global_id' )
                        ->using( TenantPivot::class );
        }
    }
