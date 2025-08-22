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
        Schema::table('t_sekar_pengurus', function (Blueprint $table) {
            $table->string('BIDANG', 255)->nullable()->after('ID_ROLES');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('t_sekar_pengurus', function (Blueprint $table) {
            $table->dropColumn('BIDANG');
        });
    }
};