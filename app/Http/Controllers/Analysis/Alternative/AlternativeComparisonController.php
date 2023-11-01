<?php

namespace App\Http\Controllers\Analysis\Alternative;

use Illuminate\Http\Request;
use App\Models\AlternativeComparison;
use App\Models\Criteria;
use App\Models\Alternative;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AlternativeComparisonController extends Controller
{
    public function index(){
        //ambil data dari tabel
        //$alternativeComparisons = AlternativeComparison::all();

        $alternativeComparisons = DB::table('alternative_comparisons')
            ->join('alternatives as alternatives1','alternatives1.id_alternatif','=','alternative_comparisons.id_alternatif')
            ->join('alternatives as alternatives2','alternatives2.id_alternatif','=','alternative_comparisons.id_alternatif2')
            ->select('alternative_comparisons.*', 'alternatives1.nama_alternatif as nama_alternatif1', 'alternatives2.nama_alternatif as nama_alternatif2')
            ->orderBy('alternative_comparisons.id_perbandinganAlternatif')
            ->get();
        
        //buat response JSON
        if(count($alternativeComparisons) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data Perbandingan Alternatif',
                'data' => $alternativeComparisons  
            ], 200);
        }
        
        return response([
            'message' => 'Empty',
            'data' => null,
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

        $alternativeComparison = AlternativeComparison::find($id);
        $inversAlternativeComparison = AlternativeComparison::where([
            ['id_kriteria','=',$alternativeComparison->id_kriteria],
            ['id_alternatif','=',$alternativeComparison->id_alternatif2],
            ['id_alternatif2','=',$alternativeComparison->id_alternatif]]);

        // if ($x_alternatif == $y_alternatif) {
        //     AlternativeComparison::updateOrCreate(
        //         ['id_kriteria' => $id_kriteria, 'id_alternatif' => $x_alternatif, 'id_alternatif2' => $y_alternatif],
        //         ['nilai' => 1]
        //     );
        // }else{
        //     AlternativeComparison::updateOrCreate(
        //         ['id_kriteria' => $id_kriteria, 'id_alternatif' => $x_alternatif, 'id_alternatif2' => $y_alternatif],
        //         ['nilai' => $request->nilai]
        //     );
        //     AlternativeComparison::updateOrCreate(
        //         ['id_kriteria' => $id_kriteria, 'id_alternatif' => $y_alternatif, 'id_alternatif2' => $x_alternatif],
        //         ['nilai' => 1 / $request->nilai]
        //     );
        // }

        if($alternativeComparison->id_alternatif < $alternativeComparison->id_alternatif2){
            $alternativeComparison->update(['nilai' => $request->nilai]);
            $inversAlternativeComparison->update(['nilai' => 1 / $request->nilai]);
        }else{
            $alternativeComparison->update(['nilai' => 1 / $request->nilai]);
            $inversAlternativeComparison->update(['nilai' => $request->nilai]);
        }

        return response()->json([
            'success' => True,
            'message' => 'Perbandingan Alternatif Berhasil Disimpan',
        ], 201);
    }

    public function createComparisonsAl(){
        set_time_limit(0);

        // $countCriteria = Criteria::all()->count();
        // $countAlternative = Alternative::all()->count();

        // for($a=1; $a<=$countCriteria; $a++){
        //     for($i=1; $i<=$countAlternative; $i++){
        //        for($j=1; $j<=$countAlternative; $j++){
        //            $countComparisonsAlt = AlternativeComparison::count() + 1;
        //            if($i == $j) {
        //                AlternativeComparison::create([
        //                    'id_perbandinganAlternatif' => $countComparisonsAlt,
        //                    'id_kriteria' => $a, 
        //                    'id_alternatif' => $i,
        //                    'id_alternatif2' => $j,
        //                    'nilai' => 1
        //                ]);
        //            }
        //            else{
        //                AlternativeComparison::create([
        //                    'id_perbandinganAlternatif' => $countComparisonsAlt,
        //                    'id_kriteria' => $a, 
        //                    'id_alternatif' => $i,
        //                    'id_alternatif2' => $j,
        //                    'nilai' => 0
        //                ]);
        //            }
        //        } 
        //     }
        // }

        $criteria = Criteria::all();
        $alternatives = Alternative::all();

        foreach ($criteria as $criterion) {
            foreach ($alternatives as $alternative) {
                foreach ($alternatives as $comparisonAlternative) {
                    $countComparisonsAlt = AlternativeComparison::count() + 1;
                    if ($alternative->id_alternatif == $comparisonAlternative->id_alternatif) {
                        AlternativeComparison::create([
                            'id_perbandinganAlternatif' => $countComparisonsAlt,
                            'id_kriteria' => $criterion->id_kriteria,
                            'id_alternatif' => $alternative->id_alternatif,
                            'id_alternatif2' => $comparisonAlternative->id_alternatif,
                            'nilai' => 1
                        ]);
                    } else {
                        AlternativeComparison::create([
                            'id_perbandinganAlternatif' => $countComparisonsAlt,
                            'id_kriteria' => $criterion->id_kriteria,
                            'id_alternatif' => $alternative->id_alternatif,
                            'id_alternatif2' => $comparisonAlternative->id_alternatif,
                            'nilai' => 0
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'success' => True,
            'message' => 'Tabel Perbandingan Criteria Berhasil Diperbarui',
        ], 201);
    }

    public function resetComparison(){
        set_time_limit(0);

        $alternative = Alternative::count();

        if($alternative > 0){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('alternative_comparisons')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->createComparisonsAl();

            return response()->json([
                'success' => True,
                'message' => 'Tabel Perbandingan Alternatif Berhasil Diperbarui',
            ], 201);
        }else{
            return response()->json([
                'success' => False,
                'message' => 'Masukkan Data Alternatif Terlebih Dahulu!',
            ], 201);
        }
    }

    public function resetAlternatif(){
        $alternatif = Alternative::all()->count();;

        if($alternatif > 0){
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('alternatives')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json([
                'success' => True,
                'message' => 'Data Alternatif Berhasil Dikosongkan',
            ], 201);
        }
        else {
            return response()->json([
                'success' => False,
                'message' => 'Data Alternatif Sudah Kosong!',
            ], 201);
        }
    }
}
