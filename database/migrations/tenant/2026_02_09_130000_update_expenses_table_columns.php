<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'category')) {
                $table->renameColumn('category', 'expense_category_id');
            }
            
            if (Schema::hasColumn('expenses', 'referenceNo')) {
                $table->renameColumn('referenceNo', 'reference_no');
            }
            
            if (Schema::hasColumn('expenses', 'isRecurring')) {
                $table->renameColumn('isRecurring', 'is_recurring');
            }

            if (Schema::hasColumn('expenses', 'paymentMethod')) {
                $table->dropColumn('paymentMethod');
            }
            
            if (Schema::hasColumn('expenses', 'payment_method_id')) {
                 $table->dropForeign(['payment_method_id']);
                 $table->dropColumn('payment_method_id');
            }

            if (!Schema::hasColumn('expenses', 'base_amount')) {
                $table->decimal('base_amount', 20, 2)->nullable()->after('amount');
            }
            
            if (!Schema::hasColumn('expenses', 'extra_charge')) {
                $table->decimal('extra_charge', 20, 2)->nullable()->after('base_amount');
            }

            $table->decimal('amount', 20, 2)->change();
            $table->decimal('paid', 20, 2)->change();
            
            // Let's try to drop it first.
             try {
                 $table->dropForeign(['expense_category_id']);
             } catch (\Exception $e) {
                 // Ignore if it doesn't exist
             }
             
             $table->foreign('expense_category_id')->references('id')->on('expense_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']); 

            $table->renameColumn('expense_category_id', 'category');
            $table->renameColumn('reference_no', 'referenceNo');
            $table->renameColumn('is_recurring', 'isRecurring');
            
            $table->integer('paymentMethod')->nullable();
            
            $table->dropColumn('base_amount');
            $table->dropColumn('extra_charge');
        });
    }
};
