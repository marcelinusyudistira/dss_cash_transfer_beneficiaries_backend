<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\AlternativePriority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AlternativeController extends Controller
{
    public function index()
    {
        //ambil data dari tabel
        $alternative = Alternative::all();

        //buat response JSON
        if(count($alternative) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data Alternatif',
                'data' => $alternative  
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
        $alternative = Alternative::findOrfail($id);

        //buat response JSON
        if(!is_null($alternative)){
            return response()->json([
                'success' => true,
                'message' => 'Detail Data Alternatif',
                'data' => $alternative 
            ], 200);
        }
        
        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Alternatif Not Found',
        ], 404);
    }
    
    public function store(Request $request)
    {
        //hitung jumlah data pada tabel
        $count= DB::table('alternatives')->count() +1;
        $countPriority = AlternativePriority::all()->count() +1;

        //set validasi
        $validator = Validator::make($request->all(), [
            'nama_alternatif' => 'required',
            'nik_alternatif' => 'required|size:16',
            'alamat_alternatif' => 'required',
            'pekerjaan_alternatif' => 'required',
        ]);
        
        //response validasi error 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //simpan dalam database
        $alternative = Alternative::create([
            'id_alternatif' => $count,
            'kode' => 'A'.$count,
            'nama_alternatif' => $request->nama_alternatif,
            'nik_alternatif' => $request->nik_alternatif,
            'alamat_alternatif' => $request->alamat_alternatif,
            'pekerjaan_alternatif' => $request->pekerjaan_alternatif,
        ]);

        //berhasil disimpan dalam database
        if($alternative) {
            //buat for dengan berdasar jumlah kriteria lalu buat entitas dalam table prioritas alternatif

            return response()->json([
                'success' => true,
                'message' => 'Alternatif Berhasil Disimpan',
                'data'    => $alternative  
            ], 201);
        } 

        //gagal menyimpan ke database
        return response()->json([
            'success' => false,
            'message' => 'Alternatif Failed to Save',
        ], 409);

    }
    
    public function update(Request $request, $id)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'nama_alternatif' => 'required',
            'nik_alternatif' => 'required|size:16',
            'alamat_alternatif' => 'required',
            'pekerjaan_alternatif' => 'required',
        ]);
        
        //response validasi error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //cari kriteria berdasar ID
        $alternative = Alternative::find($id);

        if($alternative) {
            //update kriteria
            $alternative->update([
                'nama_alternatif' => $request->nama_alternatif,
                'nik_alternatif' => $request->nik_alternatif,
                'alamat_alternatif' => $request->alamat_alternatif,
                'pekerjaan_alternatif' => $request->pekerjaan_alternatif,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alternatif Berhasil Diubah',
                'data'    => $alternative  
            ], 200);

        }

        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Alternatif Not Found',
        ], 404);
    }
    
    public function destroy($id)
    {
        //cari kriteria berdasar ID
        $alternative = Alternative::findOrfail($id);

        if($alternative) {
            //hapus kriteria
            $alternative->delete();

            return response()->json([
                'success' => true,
                'message' => 'Alternatif Berhasil Dihapus',
            ], 200);
        }

        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Alternative Not Found',
        ], 404);
    }
}
