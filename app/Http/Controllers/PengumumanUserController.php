<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PengumumanUserController extends Controller
{
    public function index()
    {
        //ambil data dari tabel
        $count = Pengumuman::all()->count();

        $pengumumans = DB::table('pengumumans')
            ->join('users','users.id','=','pengumumans.id_pengguna')
            ->select('pengumumans.*', 'users.name')
            ->get();

        for($i=0; $i<$count; $i++){
            $date_updated = $pengumumans[$i]->updated_at;
            $pengumumans[$i]->updated_at = Carbon::parse($date_updated)->format('d F Y');
        }
        
        //buat response JSON
        if(count($pengumumans) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data Pengumuman',
                'data' => $pengumumans  
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }
    
    public function show($id)
    {
        //cari kriteria berdasarkan ID
        $pengumuman = Pengumuman::findOrfail($id);

        //buat response JSON
        if(!is_null($pengumuman)){
            return response()->json([
                'success' => true,
                'message' => 'Detail Data Pengumuman',
                'data' => $pengumuman 
            ], 200);
        }
    }
}
