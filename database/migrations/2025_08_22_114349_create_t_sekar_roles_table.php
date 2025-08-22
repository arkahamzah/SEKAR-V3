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
        Schema::create('t_sekar_roles', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('NAME', 150)->nullable();
            $table->string('DESC', 255)->nullable();
            $table->char('IS_AKTIF', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_sekar_roles');
    }
};