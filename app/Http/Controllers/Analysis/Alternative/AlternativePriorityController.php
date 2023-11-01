<?php

namespace App\Http\Controllers\Analysis\Alternative;;

use Illuminate\Http\Request;
use App\Models\AlternativeComparison;
use App\Models\AlternativePriority;
use App\Models\Alternative;
use App\Models\Criteria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AlternativePriorityController extends Controller
{
    public function index(){
        //ambil data dari tabel
        $alternativePriority2 = DB::table('alternative_priorities')
            ->join('alternatives','alternatives.id_alternatif','=','alternative_priorities.id_alternatif')
            ->select('alternative_priorities.*', 'alternatives.*')
            ->get();

        $alternativePriority = AlternativePriority::all();
        
        //buat response JSON
        if(count($alternativePriority2) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data Prioritas Alternatif',
                'data' => $alternativePriority2  
            ], 200);
        }
        
        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function hitungPrioritas(){
        set_time_limit(0);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('alternative_priorities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $compares = AlternativeComparison::all();
        $countAlternatif = Alternative::count();
        $countKriteria = Criteria::count();

        $matriksall = [];
        $matriks = [];

        //buat beberapa array untuk menampung matriks pairwase dari alternatif
        for($a=1; $a<=$countKriteria; $a++){
            $matriksall[$a] = null;
        }

        foreach($compares as $key => $compare){
            $matriks[$compare->id_alternatif][$compare->id_alternatif2] = $compare->nilai;
            $matriksall[$compare->id_kriteria] = $matriks;
        }

        //cari jumlah nilai masing2 kolom matriks
        $jumlahKolomAll = [];
        for ($b=1; $b <= $countKriteria; $b++) {
            $jumlahKolomAll[$b] = $this->cariJumlahKolom($matriksall[$b],$countAlternatif);
		}

        //matriks dinormalisasikan
        $matriksNormalisasiAll = [];
        for ($c=1; $c <= $countKriteria; $c++) {
            $matriksNormalisasiAll[$c] = $this->matriksNormalisasi($countAlternatif,$matriksall[$c],$jumlahKolomAll[$c]);
		}

        //cari bobot
        $bobotAlternatifAll = [];
        for ($d=1; $d <= $countKriteria; $d++) {
            $bobotAlternatifAll[$d] = $this->cariBobot($countAlternatif,$matriksNormalisasiAll[$d]);
		}

        //id kepentingan alternatif
        $id_temp = AlternativePriority::count() + 1;

        //buat data prioritas alternatif
        for($i=1; $i<=$countKriteria; $i++){
            for($j=1; $j<=$countAlternatif; $j++){
                AlternativePriority::where([
                    ['id_kriteria',$i],
                    ['id_alternatif',$j]
                ])->create([
                    'id_kepentinganAlternatif' => $id_temp,
                    'id_kriteria' => $i,
                    'id_alternatif' => $j,
                    'bobot' => $bobotAlternatifAll[$i][$j]]);
                $id_temp +=1; 
            }
        }

        $crAll = [];
        for ($e=1; $e <= $countKriteria; $e++){
            $crAll[$e] = $this->mencariCR($matriksall[$e],$countAlternatif,$bobotAlternatifAll[$e]);
        }

        return response()->json([
            'success' => True,
            'message' => 'Perhitungan Prioritas Alternatif Selesai',
            'data' => $crAll,
        ], 201);
    }

    public function cariJumlahKolom($matriks,$jumlahAlternatif){
        //cari jumlah nilai masing2 kolom dalam matriks
        $jumlahKolom = [];
        for ($j=1; $j <= $jumlahAlternatif; $j++) {
            $temp=0;
			for ($i=1; $i <= $jumlahAlternatif; $i++) {
				$temp += $matriks[$i][$j];
			}
            $jumlahKolom[$j]=$temp;
		}
        return $jumlahKolom;
    }

    public function matriksNormalisasi($jumlahAlternatif,$matriks,$jumlahKolom){
        //matriks dinormalisasikan
        $matriksNormalisasi = [];
        for ($b=1; $b <= $jumlahAlternatif; $b++) {
			for ($a=1; $a <= $jumlahAlternatif; $a++) {
				$matriksNormalisasi[$a][$b] = $matriks[$a][$b] / $jumlahKolom[$b];
			}
		}
        return $matriksNormalisasi;
    }

    public function cariBobot($jumlahAlternatif,$matriksNormalisasi){
        //cari pv atau bobot
        $bobotAlternatif = [];
        for ($x=1; $x <= $jumlahAlternatif; $x++) {
            $temp2=0;
			for ($y=1; $y <= $jumlahAlternatif; $y++) {
                $temp2 += $matriksNormalisasi[$x][$y];
			}
            $bobotAlternatif[$x] = $temp2/$jumlahAlternatif;
		}
        return $bobotAlternatif;
    }

    public function mencariCR($matriksAlternatif, $jumlahAlternatif, $bobotAlternatif){
        //cari lamda max => menuju cr
        $hasilKali = [];
        for ($m=1; $m <= $jumlahAlternatif; $m++) {
            $temp3=0;
			for ($n=1; $n <= $jumlahAlternatif; $n++) {
                $temp3 += ($matriksAlternatif[$m][$n]*$bobotAlternatif[$n]);
			}
            $hasilKali[$m]=$temp3;
		}

        //bagi hasilKali dengan bobot => menuju cr
        $lamdaMax = 0;
        for ($c=1; $c <= $jumlahAlternatif; $c++){
            $temp4 = 0;
            $temp4 = $hasilKali[$c]/$bobotAlternatif[$c];
            $lamdaMax += $temp4;
        }
        $lamdaMax = $lamdaMax/$jumlahAlternatif;

        //cari ci
        $ci = ($lamdaMax - $jumlahAlternatif)/($jumlahAlternatif - 1);

        //cari cr
        $nilai_index = DB::table('index_random')
            ->select('value')
            ->where('n',$jumlahAlternatif)->first();

        $cr = $ci/$nilai_index->value;

        return $cr;
    }

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
    //     DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    //     DB::table('alternative_priorities')->truncate();
    //     DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    //     $compares = AlternativeComparison::all();
    //     $countAlternatif = Alternative::all()->count();
    //     $countKriteria = Criteria::all()->count();

    //     $matriksall = [];
    //     $matriks = [];

    //     //buat beberapa array untuk menampung matriks pairwase dari alternatif
    //     for($a=1; $a<=$countKriteria; $a++){
    //         $matriksall[$a] = null;
    //     }

    //     foreach($compares as $key => $compare){
    //         $matriks[$compare->id_alternatif][$compare->id_alternatif2] = $compare->nilai;
    //         $matriksall[$compare->id_kriteria] = $matriks;
    //     }

    //     //melakukan perkalian alternatif
    //     $matrikskaliall = [];
    //     $matrikskali = [];

    //     for($a=1; $a <= $countKriteria; $a++){
    //         //memindahkan matriks per kriteria lalu kuadratkan
    //         $matrikskali = $this->kuadratMatriks($matriksall[$a],$countAlternatif);
    //         //matriks yang sudah dikuadratkan dikumpulkan lagi menjadi satu array
    //         $matrikskaliall[$a] = $matrikskali;
    //     }

    //     //mencari nilai eigen
    //     $eigenall = [];
    //     $eigen = [];

    //     for($a=1; $a <= $countKriteria; $a++){
    //         //memindahkan matriks hasil kuadrat per kriteria lalu cari eigen
    //         $eigen = $this->findEigen($matrikskaliall[$a],$countAlternatif);
    //         //matriks yang eigennya sudah ketemu dikumpulkan lagi menjadi satu array
    //         $eigenall[$a] = $eigen;
    //     }

    //     //id kepentingan alternatif
    //     $id_temp = AlternativePriority::all()->count()+1;

    //     //updated nilai eigen untuk jadi bobot
    //     for($a=1; $a<=$countKriteria; $a++){
    //         for($b=1; $b<=$countAlternatif; $b++){
    //             AlternativePriority::where([
    //                 ['id_kriteria',$a],
    //                 ['id_alternatif',$b]
    //             ])->create([
    //                 'id_kepentinganAlternatif' => $id_temp,
    //                 'id_kriteria' => $a,
    //                 'id_alternatif' => $b,
    //                 'bobot' => $eigenall[$a][$b]]);
    //             $id_temp +=1; 
    //         }
    //     }

    //     $cr = $this->cekCR($matriksall,$eigenall,$countAlternatif,$countKriteria);

    //     return response()->json([
    //         'success' => True,
    //         'message' => 'Perhitungan Prioritas Alternative Selesai',
    //         'data' => $cr,
    //     ], 201);
    // }

    // public function cekCR($matriks,$bobot,$countAlternatif,$countCriteria){ 
    //     //variabel nilai_index mereturnkan collection sehingga harus diakses atribut value untuk dapat nilainya
    //     $nilai_index = DB::table('index_random')
    //         ->select('value')
    //         ->where('n',$countAlternatif)->first();

    //     $crArray=[];

    //     for($i=1;$i<=$countCriteria;$i++){
    //         $matriksArray=[];
    //         $matriksArray=$matriks[$i];

    //         $eigenArray=[];
    //         $eigenArray=$bobot[$i];

    //         $ci = 0; 
    //         $cr = 0;

    //         for($a=1;$a<=$countAlternatif;$a++){
    //             $temp=0;
    //             $sum[$a]=0;
    //             for($b=1;$b<=$countAlternatif;$b++){
    //                 $temp += $matriksArray[$b][$a];
    //             }
    //             $sum[$a]=$temp;
    //         }
    
    //         $lamdamax=0;
    //         for($c=1;$c<=$countAlternatif;$c++){
    //             $lamdamax += $sum[$c] * $eigenArray[$c];
    //         }
    
    //         $ci = ($lamdamax-$countAlternatif)/($countAlternatif-1);
    //         $cr = $ci/$nilai_index->value;

    //         $crArray[$i] = $cr;
    //     }

    //     return $crArray;
    // }
    
}
