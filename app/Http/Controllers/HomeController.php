<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SertifikatSignature; 
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $ketuaUmumInfo = DB::table('t_sekar_jajaran')
            ->join('m_jajaran', 't_sekar_jajaran.ID_JAJARAN', '=', 'm_jajaran.ID')
            ->where('m_jajaran.NAMA_JAJARAN', 'KETUA UMUM')
            ->where('t_sekar_jajaran.IS_AKTIF', '1')
            ->select('t_sekar_jajaran.V_NAMA_KARYAWAN as nama', 'm_jajaran.NAMA_JAJARAN as jabatan')
            ->first();

        $ketuaUmumSignature = SertifikatSignature::where('jabatan', 'LIKE', '%Ketua Umum%')
                                        ->where('start_date', '<=', Carbon::now())
                                        ->where('end_date', '>=', Carbon::now())
                                        ->first();

        // Kirim data ke view
        return view('home', [
            'ketuaUmum' => $ketuaUmumInfo,
            'ketuaUmumSignature' => $ketuaUmumSignature
        ]);
    }
}