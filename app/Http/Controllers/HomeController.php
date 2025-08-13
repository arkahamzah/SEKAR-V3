<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // AMBIL DATA KETUA UMUM YANG AKTIF DARI DATABASE
        // Menggabungkan tabel t_sekar_jajaran dan m_jajaran
        $ketuaUmum = DB::table('t_sekar_jajaran')
            ->join('m_jajaran', 't_sekar_jajaran.ID_JAJARAN', '=', 'm_jajaran.ID')
            ->where('m_jajaran.NAMA_JAJARAN', 'KETUA UMUM') // Filter berdasarkan nama jabatan
            ->where('t_sekar_jajaran.IS_AKTIF', '1') // Memastikan pengurus masih aktif
            ->select('t_sekar_jajaran.V_NAMA_KARYAWAN as nama', 'm_jajaran.NAMA_JAJARAN as jabatan') // Pilih kolom yang diperlukan dan berikan alias
            ->first();

        // Kirim data ke view
        return view('home', [
            'ketuaUmum' => $ketuaUmum
        ]);
    }
}