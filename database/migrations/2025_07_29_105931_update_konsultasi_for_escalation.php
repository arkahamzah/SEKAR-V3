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
        // Cek apakah tabel t_konsultasi ada
        if (Schema::hasTable('t_konsultasi')) {
            Schema::table('t_konsultasi', function (Blueprint $table) {
                // Pastikan kolom CLOSED_BY ada
                if (!Schema::hasColumn('t_konsultasi', 'CLOSED_BY')) {
                    $table->string('CLOSED_BY', 30)->nullable()->after('STATUS')->comment('NIK admin yang menutup konsultasi');
                }
                
                // Pastikan kolom CLOSED_AT ada
                if (!Schema::hasColumn('t_konsultasi', 'CLOSED_AT')) {
                    $table->datetime('CLOSED_AT')->nullable()->after('CLOSED_BY')->comment('Waktu konsultasi ditutup');
                }
                
                // Update kolom TUJUAN untuk menampung nilai yang lebih panjang termasuk GENERAL
                $table->enum('TUJUAN', ['DPP', 'DPW', 'DPD', 'GENERAL'])->change();
                
                // Update kolom STATUS dengan nilai yang lebih lengkap
                $table->enum('STATUS', ['OPEN', 'IN_PROGRESS', 'CLOSED', 'RESOLVED'])->default('OPEN')->change();
            });
            
            // Buat index untuk performance - dengan pengecekan error
            try {
                Schema::table('t_konsultasi', function (Blueprint $table) {
                    // Index untuk pencarian berdasarkan status
                    if (!$this->indexExists('t_konsultasi', 'idx_konsultasi_status')) {
                        $table->index('STATUS', 'idx_konsultasi_status');
                    }
                    
                    // Index untuk pencarian berdasarkan tujuan
                    if (!$this->indexExists('t_konsultasi', 'idx_konsultasi_tujuan')) {
                        $table->index('TUJUAN', 'idx_konsultasi_tujuan');
                    }
                    
                    // Index untuk pencarian berdasarkan jenis
                    if (!$this->indexExists('t_konsultasi', 'idx_konsultasi_jenis')) {
                        $table->index('JENIS', 'idx_konsultasi_jenis');
                    }
                    
                    // Index untuk pencarian berdasarkan NIK dan status
                    if (!$this->indexExists('t_konsultasi', 'idx_konsultasi_nik_status')) {
                        $table->index(['N_NIK', 'STATUS'], 'idx_konsultasi_nik_status');
                    }
                    
                    // Index untuk pencarian berdasarkan tanggal
                    if (!$this->indexExists('t_konsultasi', 'idx_konsultasi_created')) {
                        $table->index('CREATED_AT', 'idx_konsultasi_created');
                    }
                });
            } catch (\Exception $e) {
                // Index mungkin sudah ada, abaikan error
                \Log::info('Index creation skipped: ' . $e->getMessage());
            }
        }
        
        // Cek apakah tabel t_konsultasi_komentar ada (nama tabel yang benar)
        if (Schema::hasTable('t_konsultasi_komentar')) {
            Schema::table('t_konsultasi_komentar', function (Blueprint $table) {
                // Pastikan kolom PENGIRIM_ROLE menggunakan nilai yang konsisten
                // Tabel sudah memiliki kolom PENGIRIM_ROLE dengan enum('USER','ADMIN')
                // Kita akan menambahkan alias JENIS_KOMENTAR jika diperlukan di Model
                
                // Tambahkan kolom UPDATED_BY dan UPDATED_AT jika belum ada
                if (!Schema::hasColumn('t_konsultasi_komentar', 'UPDATED_BY')) {
                    $table->string('UPDATED_BY', 30)->nullable()->after('CREATED_BY')->comment('NIK yang mengupdate komentar');
                }
                
                if (!Schema::hasColumn('t_konsultasi_komentar', 'UPDATED_AT')) {
                    $table->datetime('UPDATED_AT')->nullable()->after('UPDATED_BY')->comment('Waktu komentar diupdate');
                }
            });
            
            // Buat index untuk performance
            try {
                Schema::table('t_konsultasi_komentar', function (Blueprint $table) {
                    // Index untuk pencarian komentar berdasarkan konsultasi
                    if (!$this->indexExists('t_konsultasi_komentar', 'idx_komentar_konsultasi')) {
                        $table->index('ID_KONSULTASI', 'idx_komentar_konsultasi');
                    }
                    
                    // Index untuk pencarian berdasarkan role pengirim
                    if (!$this->indexExists('t_konsultasi_komentar', 'idx_komentar_role')) {
                        $table->index('PENGIRIM_ROLE', 'idx_komentar_role');
                    }
                    
                    // Index untuk pencarian berdasarkan NIK
                    if (!$this->indexExists('t_konsultasi_komentar', 'idx_komentar_nik')) {
                        $table->index('N_NIK', 'idx_komentar_nik');
                    }
                    
                    // Index untuk pencarian berdasarkan tanggal
                    if (!$this->indexExists('t_konsultasi_komentar', 'idx_komentar_created')) {
                        $table->index('CREATED_AT', 'idx_komentar_created');
                    }
                });
            } catch (\Exception $e) {
                // Index mungkin sudah ada, abaikan error
                \Log::info('Index creation skipped: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus index yang ditambahkan
        if (Schema::hasTable('t_konsultasi')) {
            Schema::table('t_konsultasi', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_konsultasi_status');
                    $table->dropIndex('idx_konsultasi_tujuan');
                    $table->dropIndex('idx_konsultasi_jenis');
                    $table->dropIndex('idx_konsultasi_nik_status');
                    $table->dropIndex('idx_konsultasi_created');
                } catch (\Exception $e) {
                    // Index mungkin tidak ada, abaikan error
                }
                
                // Hapus kolom yang ditambahkan
                if (Schema::hasColumn('t_konsultasi', 'CLOSED_BY')) {
                    $table->dropColumn('CLOSED_BY');
                }
                if (Schema::hasColumn('t_konsultasi', 'CLOSED_AT')) {
                    $table->dropColumn('CLOSED_AT');
                }
                
                // Kembalikan TUJUAN ke nilai original
                $table->enum('TUJUAN', ['DPP', 'DPW', 'DPD'])->change();
                
                // Kembalikan STATUS ke nilai original
                $table->enum('STATUS', ['OPEN', 'IN_PROGRESS', 'CLOSED'])->default('OPEN')->change();
            });
        }
        
        if (Schema::hasTable('t_konsultasi_komentar')) {
            Schema::table('t_konsultasi_komentar', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_komentar_konsultasi');
                    $table->dropIndex('idx_komentar_role');
                    $table->dropIndex('idx_komentar_nik');
                    $table->dropIndex('idx_komentar_created');
                } catch (\Exception $e) {
                    // Index mungkin tidak ada, abaikan error
                }
                
                // Hapus kolom yang ditambahkan
                if (Schema::hasColumn('t_konsultasi_komentar', 'UPDATED_BY')) {
                    $table->dropColumn('UPDATED_BY');
                }
                if (Schema::hasColumn('t_konsultasi_komentar', 'UPDATED_AT')) {
                    $table->dropColumn('UPDATED_AT');
                }
            });
        }
    }
    
    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
        return !empty($indexes);
    }
};