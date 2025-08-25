<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW v_karyawan_base AS
            SELECT 
                tk.ID,
                tk.N_NIK,
                tk.V_NAMA_KARYAWAN,
                tk.V_SHORT_UNIT,
                tk.V_SHORT_POSISI,
                tk.C_KODE_POSISI,
                tk.C_KODE_UNIT,
                tk.V_SHORT_DIVISI,
                tk.C_KODE_DIVISI,
                tk.V_BAND_POSISI,
                tk.C_PERSONNEL_AREA,
                tk.C_PERSONNEL_SUB_AREA,
                tk.V_KOTA_GEDUNG,
                md.DPD,
                md.DPW,
                COALESCE(u.name, tk.V_NAMA_KARYAWAN) AS NAMA_USER,
                u.created_at AS TGL_TERDAFTAR,
                COALESCE(i.IURAN_WAJIB, 0) AS IURAN_WAJIB,
                COALESCE(i.IURAN_SUKARELA, 0) AS IURAN_SUKARELA,
                CAST(CASE WHEN u.id IS NOT NULL THEN 'Terdaftar' ELSE 'Belum Terdaftar' END AS CHAR) AS 'Status_Pendaftaran'
            FROM 
                t_karyawan tk
            LEFT JOIN 
                users u ON tk.N_NIK = u.nik
            LEFT JOIN 
                t_iuran i ON tk.N_NIK = i.N_NIK
            LEFT JOIN 
                mapping_dpd md ON md.PSA_Kodlok = IF(
                    tk.C_KODE_UNIT IS NOT NULL AND tk.C_KODE_UNIT != '' AND LOCATE('-', tk.C_KODE_UNIT) > 0,
                    CONCAT(
                        tk.C_PERSONNEL_SUB_AREA, 
                        '_', 
                        SUBSTRING_INDEX(tk.C_KODE_UNIT, '-', 1), 
                        '-',
CASE
    -- 1) 7 char setelah '-'  ⇒ ambil 3 terakhir (contoh: 'COP-2034a40' -> 'a40')
    WHEN LENGTH(SUBSTRING_INDEX(tk.C_KODE_UNIT, '-', -1)) = 7
    THEN RIGHT(tk.C_KODE_UNIT, 3)

    -- 2) Spesifik lama: hanya untuk prefix ITTP dan 6 char, 2 char terakhir huruf ⇒ ambil 1 terakhir
    --    (contoh: 'ITTP-9374Pa' -> 'a')
    WHEN UPPER(SUBSTRING_INDEX(tk.C_KODE_UNIT, '-', 1)) = 'ITTP'
         AND LENGTH(SUBSTRING_INDEX(tk.C_KODE_UNIT, '-', -1)) = 6
         AND SUBSTRING(tk.C_KODE_UNIT, -2, 1) REGEXP '[A-Za-z]'
         AND SUBSTRING(tk.C_KODE_UNIT, -1, 1) REGEXP '[A-Za-z]'
    THEN RIGHT(tk.C_KODE_UNIT, 1)

    -- 3) Umum: 6 char setelah '-'  ⇒ ambil 2 terakhir
    --    (contoh: 'JVC-5294lb' -> 'lb', 'COP-4745a5' -> 'a5', 'JVC-3713b2' -> 'b2')
    WHEN LENGTH(SUBSTRING_INDEX(tk.C_KODE_UNIT, '-', -1)) = 6
    THEN RIGHT(tk.C_KODE_UNIT, 2)

    -- 4) Fallback ⇒ ambil 3 terakhir
    ELSE RIGHT(tk.C_KODE_UNIT, 3)
END



                    ),
                    CONCAT(tk.C_PERSONNEL_SUB_AREA, '_')
                )
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS v_karyawan_base");
    }
};