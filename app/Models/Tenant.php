<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Stancl\Tenancy\Contracts\TenantWithDatabase;
    use Stancl\Tenancy\Database\Concerns\HasDatabase;
    use Stancl\Tenancy\Database\Concerns\HasDomains;
    use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
    use Stancl\Tenancy\Database\Models\TenantPivot;

    class Tenant extends BaseTenant implements TenantWithDatabase
    {
        use HasDatabase , HasDomains;

        protected $fillable = [
            'id' ,
            'business_id' ,
            'print_agent_token' ,
            'data' ,'pin_pepper','frontend_url'
        ];

        public static function getCustomColumns() : array
        {
            return [
                'id' ,
                'business_id' ,
                'print_agent_token' ,
            ];
        }
        public function users() : BelongsToMany
        {
            return $this->belongsToMany(CentralUser::class, 'tenant_users', 'tenant_id', 'global_user_id', 'id', 'global_id')
                        ->using(TenantPivot::class);
        }
    }
