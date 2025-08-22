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
        Schema::create('t_setting', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('SETTING_KEY', 100)->unique();
            $table->text('SETTING_VALUE')->nullable();
            $table->string('SETTING_TYPE', 50)->default('text')->nullable();
            $table->string('DESCRIPTION', 255)->nullable();
            $table->string('CREATED_BY', 30)->nullable();
            $table->string('UPDATED_BY', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_setting');
    }
};
