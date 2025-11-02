<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;

    class RoyaltyPackage extends Model
    {
        use HasFactory;

        protected $guarded = [];

        public function benefits() : BelongsToMany
        {
            return $this->belongsToMany(RoyaltyPackageBenefit::class , 'royalty_benefits' , 'royalty_package_id' , 'royalty_package_benefit_id');
        }
    }
