<?php

namespace App\Http\Controllers\Analysis;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Alternative;
use App\Models\AlternativePriority;
use App\Models\Criteria;
use App\Models\CriteriaPriority;
use App\Models\Recommendation;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    //fungsi perkalian matriks
    public function kaliMatriksPrioritas($matriks1, $matriks2, $jumlahAlternative, $jumlahCriteria){
        $hasilkali= [];
		for ($i=1; $i <= $jumlahAlternative; $i++) {
            $temp=0;
			for ($j=1; $j <= $jumlahCriteria; $j++) {
				$temp += $matriks1[$i][$j]*$matriks2[$j];	
			}
            $hasilkali[$i]=$temp;
		}
        return $hasilkali;
    }
    
    public function calculate(){
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('recommendations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $priorityCriterias = CriteriaPriority::all();
        $priorityAlternatifs = AlternativePriority::all();
        $countCriteria = Criteria::all()->count();
        $countAlternatif = Alternative::all()->count();
        $countRekomendasi = Recommendation::all()->count()+1;

        $matriksAlternatif = [];
        $matriksKriteria = [];

        //memindahkan data menjadi array matriks
        foreach($priorityCriterias as $priorityCriteria){
            $matriksKriteria[$priorityCriteria->id_kriteria] = $priorityCriteria->bobot;            
        }

        foreach($priorityAlternatifs as $priorityAlternatif){
            $matriksAlternatif[$priorityAlternatif->id_alternatif][$priorityAlternatif->id_kriteria] = $priorityAlternatif->bobot;            
        }

        //mengkalikan matriks
        $hasilKali = $this->kaliMatriksPrioritas($matriksAlternatif,$matriksKriteria,$countAlternatif,$countCriteria);

        //membuat data rekomendasi
        for($a=1; $a<=$countAlternatif; $a++){
            Recommendation::create([
                'id_rekomendasi' => $countRekomendasi,
                'id_alternatif' => $a,
                'total' => $hasilKali[$a]
            ]);

            $countRekomendasi+=1;
        }

        return response([
            'success' => True,
            'message' => 'Berhasil mendapatkan data rekomendasi',
            'data' => $hasilKali
        ], 200);
    }

    public function index(){
        $rekomendasi = DB::table('recommendations')
            ->join('alternatives','alternatives.id_alternatif','=','recommendations.id_alternatif')
            ->select('alternatives.*', 'recommendations.*')
            ->orderBy('recommendations.total','desc')
            ->get();

        if(count($rekomendasi) > 0){
            return response()->json([
                'success' => true,
                'message' => 'Berhasil menampilkan rekomendasi',
                'data' => $rekomendasi
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }


}
