<?php

    namespace App\Models;

    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Stancl\Tenancy\Contracts\SyncMaster;
    use Stancl\Tenancy\Database\Concerns\CentralConnection;
    use Stancl\Tenancy\Database\Concerns\ResourceSyncing;
    use Stancl\Tenancy\Database\Models\TenantPivot;

    class CentralUser extends Model implements SyncMaster
    {
        // Note that we force the central connection on this model
        use ResourceSyncing , CentralConnection;

        protected $guarded    = [];
        public    $timestamps = FALSE;
        public    $table      = 'users';

        protected $casts = [ 'status' => Status::class ];

        public function tenants() : BelongsToMany
        {
            return $this->belongsToMany( Tenant::class , 'tenant_users' , 'global_user_id' , 'tenant_id' , 'global_id' )
                        ->using( TenantPivot::class );
        }

        public function getTenantModelName() : string
        {
            return User::class;
        }

        public function getGlobalIdentifierKey()
        {
            return $this->getAttribute( $this->getGlobalIdentifierKeyName() );
        }

        public function getGlobalIdentifierKeyName() : string
        {
            return 'global_id';
        }

        public function getCentralModelName() : string
        {
            return static::class;
        }

        public function getSyncedAttributeNames() : array
        {
            return [
                'name' ,
                'password' ,
                'email' ,
            ];
        }
    }
