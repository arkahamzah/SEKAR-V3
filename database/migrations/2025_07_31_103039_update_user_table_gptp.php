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
        Schema::table('users', function (Blueprint $table) {
            // Add GPTP preorder columns
            $table->enum('membership_status', ['pending', 'active', 'inactive'])
                  ->default('active')
                  ->after('password');
                  
            $table->datetime('membership_active_date')
                  ->nullable()
                  ->after('membership_status')
                  ->comment('Date when membership becomes active (for GPTP preorder)');
                  
            $table->boolean('is_gptp_preorder')
                  ->default(false)
                  ->after('membership_active_date')
                  ->comment('Flag to identify GPTP preorder members');
                  
            $table->text('preorder_notes')
                  ->nullable()
                  ->after('is_gptp_preorder')
                  ->comment('Additional notes for preorder members');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'membership_status',
                'membership_active_date', 
                'is_gptp_preorder',
                'preorder_notes'
            ]);
        });
    }
};