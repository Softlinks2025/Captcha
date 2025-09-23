<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('agent_joining_fees', function (Blueprint $table) {
            $table->string('validity')->after('amount');
        });
        
        // Update existing records to maintain data
        \DB::table('agent_joining_fees')->update([
            'validity' => \DB::raw('validity_days')
        ]);
        
        Schema::table('agent_joining_fees', function (Blueprint $table) {
            $table->dropColumn('validity_days');
        });
    }
    
    public function down()
    {
        Schema::table('agent_joining_fees', function (Blueprint $table) {
            $table->integer('validity_days')->after('amount');
        });
        
        \DB::table('agent_joining_fees')->update([
            'validity_days' => \DB::raw('CASE WHEN validity = "lifetime" THEN 36500 ELSE CAST(validity AS UNSIGNED) END')
        ]);
        
        Schema::table('agent_joining_fees', function (Blueprint $table) {
            $table->dropColumn('validity');
        });
    }
};
