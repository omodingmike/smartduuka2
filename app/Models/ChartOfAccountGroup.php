<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

    class ChartOfAccountGroup extends Model
    {
        use HasFactory , HasRecursiveRelationships;

        protected $guarded    = [];
        public    $timestamps = false;
        protected $appends    = [ 'nature' ];

        public function ledgers() : ChartOfAccountGroup | Builder | HasMany
        {
            return $this->hasMany(Ledger::class , 'parent_id' , 'id');
        }

        public function childrenRecursive() : HasMany
        {
            return $this->children()->with([ 'childrenRecursive' , 'ledgers.currency' ]);
        }

        public function getNatureAttribute()
        {
            return 'group';
        }
    }
