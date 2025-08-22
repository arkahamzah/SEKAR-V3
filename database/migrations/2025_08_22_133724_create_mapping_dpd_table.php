<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mapping_dpd', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('PSA_Kodlok', 255)->nullable()->index('psa_kodlok_index');
            $table->string('DPD', 255)->nullable();
            $table->string('DPW', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mapping_dpd');
    }
};