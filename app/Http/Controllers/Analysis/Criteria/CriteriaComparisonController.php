<?php

namespace App\Http\Controllers\Analysis\Criteria;

use Illuminate\Http\Request;
use App\Models\CriteriaComparison;
use App\Models\Criteria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CriteriaComparisonController extends Controller
{
    public function index(){
        //ambil data dari tabel
        $criteriaComparisons2 = DB::table('criteria_comparisons')
            ->join('criterias as criterias1','criterias1.id_kriteria','=','criteria_comparisons.id_kriteria')
            ->join('criterias as criterias2','criterias2.id_kriteria','=','criteria_comparisons.id_kriteria2')
            ->select('criteria_comparisons.*', 'criterias1.nama_kriteria as nama_kriteria1', 'criterias2.nama_kriteria as nama_kriteria2')
            ->orderBy('criteria_comparisons.id_perbandinganKriteria')
            ->get();
        
        //buat response JSON
        if(count($criteriaComparisons2) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data Perbandingan Kriteria',
                'data' => $criteriaComparisons2,
            ], 200);
        }
        
        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function store(Request $request, $id){
        //set validasi
        $validator = Validator::make($request->all(), [
            'nilai' => 'required|numeric',
        ]);

        //response validasi error 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //cari kriteria berdasar ID
        $criteriaComparison = CriteriaComparison::find($id);
        $inversCriteriaComparison = CriteriaComparison::where([
            ['id_kriteria','=',$criteriaComparison->id_kriteria2],
            ['id_kriteria2','=',$criteriaComparison->id_kriteria]]);

        if($criteriaComparison->id_kriteria < $criteriaComparison->id_kriteria2){
            $criteriaComparison->update(['nilai' => $request->nilai]);
            $inversCriteriaComparison->update(['nilai' => 1 / $request->nilai]);
        }else{
            $criteriaComparison->update(['nilai' => 1 / $request->nilai]);
            $inversCriteriaComparison->update(['nilai' => $request->nilai]);
        }

        return response()->json([
            'success' => True,
            'message' => 'Perbandingan Kriteria Berhasil Diubah',
        ], 201);
    }

    public function createComparisons(){
        $countCriteria = Criteria::all()->count();

        for($i=1; $i<=$countCriteria; $i++){
            for($j=1; $j<=$countCriteria; $j++){
                $countComparisons = CriteriaComparison::all()->count()+1;
                if($i == $j) {
                    CriteriaComparison::create([
                        'id_perbandinganKriteria' => $countComparisons,
                        'id_kriteria' => $i, 
                        'id_kriteria2' => $j,
                        'nilai' => 1
                    ]);
                }
                else{
                    CriteriaComparison::create([
                        'id_perbandinganKriteria' => $countComparisons,
                        'id_kriteria' => $i, 
                        'id_kriteria2' => $j,
                        'nilai' => 0
                    ]);
                }
            }
        }

        return response()->json([
            'success' => True,
            'message' => 'Tabel Perbandingan Kriteria Berhasil Diperbarui',
        ], 201);
    }

    public function resetComparison(){
        $criteria = Criteria::all()->count();;

        if($criteria > 0){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('criteria_comparisons')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->createComparisons();

            return response()->json([
                'success' => True,
                'message' => 'Tabel Perbandingan Kriteria Berhasil Diperbarui',
            ], 201);
        }else{
            return response()->json([
                'success' => False,
                'message' => 'Masukkan Data Kriteria Terlebih Dahulu!',
            ], 201);
        }
    }

    public function resetKriteria(){
        $criteria = Criteria::all()->count();;

        if($criteria > 0){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('criterias')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json([
                'success' => True,
                'message' => 'Data Kriteria Berhasil Dikosongkan',
            ], 201);
        }
        else {
            return response()->json([
                'success' => False,
                'message' => 'Data Kriteria Sudah Kosong!',
            ], 201);
        }
    }
}
