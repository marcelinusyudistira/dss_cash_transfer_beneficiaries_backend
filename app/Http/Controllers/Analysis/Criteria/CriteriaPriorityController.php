<?php

namespace App\Http\Controllers\Analysis\Criteria;;

use Illuminate\Http\Request;
use App\Models\CriteriaComparison;
use App\Models\CriteriaPriority;
use App\Models\Criteria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CriteriaPriorityController extends Controller
{
    public function index(){
        //ambil data dari tabel
        // $criteriaPriority = CriteriaPriority::all();

        $criteriaPriority2 = DB::table('criteria_priorities')
            ->join('criterias','criterias.id_kriteria','=','criteria_priorities.id_kriteria')
            ->select('criteria_priorities.*', 'criterias.*')
            ->get();
        
        //buat response JSON
        if(count($criteriaPriority2) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data Prioritas Kriteria',
                'data' => $criteriaPriority2  
            ], 200);
        }
        
        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function normalisasi(){
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('criteria_priorities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $compares = CriteriaComparison::all();
        $countKriteria = Criteria::all()->count();

        //masukkan data kedalam array
        $matriks = [];
        foreach($compares as $compare){
            $matriks[$compare->id_kriteria][$compare->id_kriteria2] = $compare->nilai;            
        }

        //cari jumlah nilai masing2 kolom matriks
        $jumlahKolom = [];
        for ($j=1; $j <= $countKriteria; $j++) {
            $temp=0;
			for ($i=1; $i <= $countKriteria; $i++) {
				$temp += $matriks[$i][$j];
			}
            $jumlahKolom[$j]=$temp;
		}

        //matriks dinormalisasikan
        $matriksNormalisasi = [];
        for ($b=1; $b <= $countKriteria; $b++) {
			for ($a=1; $a <= $countKriteria; $a++) {
				$matriksNormalisasi[$a][$b] = $matriks[$a][$b] / $jumlahKolom[$b];
			}
		}

        //cari bobot
        $bobotKriteria = [];
        for ($x=1; $x <= $countKriteria; $x++) {
            $temp2=0;
			for ($y=1; $y <= $countKriteria; $y++) {
                $temp2 += $matriksNormalisasi[$x][$y];
			}
            $bobotKriteria[$x] = $temp2/$countKriteria;
		}

        //id kepentingan alternatif
        $id_temp = CriteriaPriority::all()->count()+1;

        //buat data prioritas kriteria
        for($m=1; $m<=$countKriteria; $m++){
            CriteriaPriority::where('id_kriteria',$m)
                ->create([
                    'id_kepentinganKriteria' => $id_temp,
                    'id_kriteria' => $m,
                    'bobot' => $bobotKriteria[$m],
                ]);
                $id_temp +=1; 
        }

        //panggil fungsi mencariCR
        $cr = $this->mencariCR($matriks,$countKriteria,$bobotKriteria);

        return response()->json([
            'success' => True,
            'message' => 'Perhitungan Prioritas Kriteria Selesai',
            'data' => $cr
        ], 201);
    }

    public function mencariCR($matriksKriteria, $jumlahKriteria, $bobotKriteria){
        //cari lamda max => menuju cr
        $hasilKali = [];
        for ($m=1; $m <= $jumlahKriteria; $m++) {
            $temp3=0;
			for ($n=1; $n <= $jumlahKriteria; $n++) {
                $temp3 += ($matriksKriteria[$m][$n]*$bobotKriteria[$n]);
			}
            $hasilKali[$m]=$temp3;
		}

        //bagi hasilKali dengan bobot => menuju cr
        $lamdaMax = 0;
        for ($c=1; $c <= $jumlahKriteria; $c++){
            $temp4 = 0;
            $temp4 = $hasilKali[$c]/$bobotKriteria[$c];
            $lamdaMax += $temp4;
        }
        $lamdaMax = $lamdaMax/$jumlahKriteria;

        //cari ci
        $ci = ($lamdaMax - $jumlahKriteria)/($jumlahKriteria - 1);

        //cari cr
        $nilai_index = DB::table('index_random')
            ->select('value')
            ->where('n',$jumlahKriteria)->first();

        $cr = $ci/$nilai_index->value;

        return $cr;
    }

    //=================NORMALISAI DENGAN PERKALIAN MATRIKS===============================

        // //fungsi untuk mencari kuadrat matriks
    // public function kuadratMatriks($matriks, $jumlahBaris){
    //     $hasilkuadrat= [];
	// 	for ($i=1; $i <= $jumlahBaris; $i++) {
	// 		for ($j=1; $j <= $jumlahBaris; $j++) {
	// 			$temp=0;
	// 			for ($x=1; $x <= $jumlahBaris; $x++) {
	// 				$temp += $matriks[$i][$x]*$matriks[$x][$j];
	// 			}
	// 			$hasilkuadrat[$i][$j]=$temp;
	// 		}
	// 	}

    //     return $hasilkuadrat;
    // }

    // //fungsi mencari nilai eigen
    // public function findEigen($matriks, $jumlahBaris){
    //     $sumbaris=[];
    //     $sumallbaris=0;
    //     for($a=1; $a <= $jumlahBaris; $a++){
    //         $tempbaris=0;
    //         for($b=1; $b <= $jumlahBaris; $b++){
    //             //menambahkan jumlah setiap baris
    //             $tempbaris += $matriks[$a][$b];
    //         }
    //         $sumbaris[$a] = $tempbaris;
    //         //menjumlahkan seluruh baris
    //         $sumallbaris += $tempbaris;
    //     }

    //     //mencari eigen value
    //     $eigen = [];
    //     for($i=1; $i <= $jumlahBaris; $i++){
    //         $eigen[$i] = $sumbaris[$i]/$sumallbaris;
    //     }

    //     return $eigen;
    // }

    // public function normalization(){
    //     $compares = CriteriaComparison::all();
    //     $countKriteria = Criteria::all()->count();

    //     //masukkan data kedalam array
    //     $matriks = [];
    //     foreach($compares as $compare){
    //         $matriks[$compare->id_kriteria][$compare->id_kriteria2] = $compare->nilai;            
    //     }

    //     $hasilkali = $this->kuadratMatriks($matriks,$countKriteria);

    //     $bobot = $this->findEigen($hasilkali,$countKriteria);

    //     //updated nilai eigen untuk jadi bobot
    //     for($a=1; $a<=$countKriteria; $a++){
    //         CriteriaPriority::where('id_kriteria',$a)
    //             ->update(['bobot' => $bobot[$a]]);
    //     }

    //     $cr = $this->cekCR($matriks,$bobot,$countKriteria);

    //     return response()->json([
    //         'success' => True,
    //         'message' => 'Perhitungan Prioritas Criteria Selesai',
    //         'data' => $cr
    //     ], 201);
    // }

    // public function cekCR($matriks,$bobot,$countRow){ 
    //     //variabel nilai_index mereturnkan collection sehingga harus diakses atribut value untuk dapat nilainya
    //     $nilai_index = DB::table('index_random')
    //         ->select('value')
    //         ->where('n',$countRow)->first();

    //     for($a=1;$a<=$countRow;$a++){
    //         $temp=0;
    //         for($b=1;$b<=$countRow;$b++){
    //             $temp += $matriks[$b][$a];
    //         }
    //         $sum[$a]=$temp;
    //     }

    //     $lamdamax=0;
    //     for($c=1;$c<=$countRow;$c++){
    //         $lamdamax += $sum[$c] * $bobot[$c];
    //     }

    //     $ci = ($lamdamax-$countRow)/($countRow-1);
    //     $cr = $ci/$nilai_index->value;

    //     return $cr;
    // }

}
