<?php

namespace App\Http\Controllers;

use App\Models\Saran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaranController extends Controller
{
    public function index()
    {
        //ambil data dari tabel
        //$sarans = Saran::all();

        $sarans = Saran::
            join('users','users.id','=','sarans.id_pengguna')
            ->select('sarans.*','users.name',)
            ->get();

    
        //buat response JSON
        if(count($sarans) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data Saran',
                'data' => $sarans  
            ], 200);
        }

        return response([
            'message' => 'Kosong',
            'data' => null
        ], 400);
    }

    public function saranUser()
    {
        //ambil data dari tabel
        //$sarans = Saran::all();

        $sarans = Saran::
            join('users','users.id','=','sarans.id_pengguna')
            ->select('sarans.*','users.name',)
            ->where('id_pengguna',auth()->user()->id)
            ->get();

    
        //buat response JSON
        if(count($sarans) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data Saran',
                'data' => $sarans  
            ], 200);
        }

        return response([
            'message' => 'Kosong',
            'data' => null
        ], 400);
    }
    
    public function show($id)
    {
        //cari kriteria berdasarkan ID
        $saran = Saran::findOrfail($id);

        //buat response JSON
        if(!is_null($saran)){
            return response()->json([
                'success' => true,
                'message' => 'Detail Data Saran',
                'data' => $saran 
            ], 200);
        }
        
        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Saran tidak ditemukan',
        ], 404);
    }
    
    public function store(Request $request, $id_user)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'judul_saran' => 'required',
            'pesan' => 'required',
        ]);
        
        //response validasi error 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //simpan dalam database
        $saran = Saran::create([
            'id_pengguna' => $id_user,
            'judul_saran' => $request->judul_saran,
            'pesan' => $request->pesan,
            'id_admin' => null,
            'status' => 'Belum di Verifikasi'
        ]);

        //berhasil disimpan dalam database
        if($saran) {
            return response()->json([
                'success' => true,
                'message' => 'Saran berhasil disimpan',
                'data'    => $saran  
            ], 201);
        } 

        //gagal menyimpan ke database
        return response()->json([
            'success' => false,
            'message' => 'Saran gagal disimpan',
        ], 409);
    }

    public function update(Request $request, $id)
    {
        ///set validasi
        $validator = Validator::make($request->all(), [
            'judul_saran' => 'required',
            'pesan' => 'required',
        ]);
        
        //response validasi error 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //cari kriteria berdasar ID
        $saran = Saran::find($id);

        if($saran) {
            //update kriteria
            $saran->update([
                'judul_saran' => $request->judul_saran,
                'pesan' => $request->pesan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Saran Berhasil di Edit',
                'data'    => $saran  
            ], 200);

        }

        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Saran tidak ditemukan',
        ], 404);
    }

    public function verifikasi(Request $request, $id)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'id_admin' => 'required',
            'balasan' => 'required',
        ]);
        
        //response validasi error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //cari data saran
        $saran = Saran::find($id);

        if($saran) {
            //update status saran
            $saran->update([
                'id_admin' => $request->id_admin,
                'balasan' => $request->balasan,
                'status' => 'Sudah di Verifikasi'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Saran berhasil diverifikasi',
                'data'    => $saran  
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Saran tidak ditemukan',
        ], 404);
    }
    
    public function destroy($id)
    {
        //cari kriteria berdasar ID
        $saran = Saran::findOrFail($id);

        if($saran) {
            //hapus kriteria
            $saran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Saran Berhasil dihapus',
            ], 200);

        }

        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Saran Not Found',
        ], 404);
    }
}
