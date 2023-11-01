<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\CriteriaPriority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CriteriaController extends Controller
{
    public function index()
    {
        //ambil data dari tabel
        $criterias = Criteria::all();

        //buat response JSON
        if(count($criterias) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data Kriteria',
                'data' => $criterias  
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
        $criteria = Criteria::findOrfail($id);

        //buat response JSON
        if(!is_null($criteria)){
            return response()->json([
                'success' => true,
                'message' => 'Detail Data Kriteria',
                'data' => $criteria 
            ], 200);
        }
        
        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Criteria Not Found',
        ], 404);
    }
    
    public function store(Request $request)
    {
        //hitung jumlah data pada tabel
        $count= DB::table('criterias')->count() +1;
        $countPriority = CriteriaPriority::all()->count() +1;

        //set validasi
        $validator = Validator::make($request->all(), [
            'nama_kriteria' => 'required',
            'keterangan' => 'required',
        ]);
        
        //response validasi error 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //simpan dalam database
        $criteria = Criteria::create([
            'id_kriteria' => $count,
            'kode' => 'C'.$count,
            'nama_kriteria' => $request->nama_kriteria,
            'keterangan' => $request->keterangan,
        ]);

        //berhasil disimpan dalam database
        if($criteria) {
            CriteriaPriority::create([
                'id_kepentinganKriteria' => $countPriority,
                'id_kriteria' => $count,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Kriteria berhasil ditambahkan.',
                'data'    => $criteria  
            ], 201);
        } 

        //gagal menyimpan ke database
        return response()->json([
            'success' => false,
            'message' => 'Data Kriteria gagal ditambahkan.',
        ], 409);
    }
    
    public function update(Request $request, $id)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'nama_kriteria' => 'required',
            'keterangan' => 'required',
        ]);
        
        //response validasi error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //cari kriteria berdasar ID
        $criteria = Criteria::find($id);

        if($criteria) {
            //update kriteria
            $criteria->update([
                'nama_kriteria' => $request->nama_kriteria,
                'keterangan' => $request->keterangan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Kriteria berhasil disimpan.',
                'data'    => $criteria  
            ], 200);

        }

        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Criteria Not Found',
        ], 404);
    }
    
    public function destroy($id)
    {
        //cari kriteria berdasar ID
        $criteria = Criteria::findOrfail($id);

        if($criteria) {
            //hapus kriteria
            $criteria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kriteria berhasil dihapus.',
            ], 200);

        }

        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Criteria Not Found',
        ], 404);
    }
}
