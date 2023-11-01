<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Carbon;

class PengumumanController extends Controller
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
                'message' => 'Detail Data Kriteria',
                'data' => $pengumuman 
            ], 200);
        }
    }

    public function store(Request $request)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'judul_pengumuman' => 'required',
            'isi_pengumuman' => 'required',
            'file' => 'file|max:1024'
        ]);
        
        //response validasi error 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validated = $validator->validated(); //validator ndak return array jadi harus divalidated dulu

        //ubah nama file menjadi slug judul pengumumannya
        if($request->file('file')){
            $judul = SlugService::createSlug(Pengumuman::class, 'slug', $validated['judul_pengumuman']);
            $time = Carbon::now()->format('dmY');
            $nama_file = $judul."_".$time.".pdf";

            $validated['file'] = $request->file('file')->storeAs('post-pengumuman',$nama_file,'public');
        }

        $validated['slug'] = SlugService::createSlug(Pengumuman::class, 'slug', $validated['judul_pengumuman']);
        $validated['id_pengguna'] = Auth::guard('api')->user()->id;

        //simpan dalam database
        $pengumuman = Pengumuman::create($validated);

        //berhasil disimpan dalam database
        if($pengumuman) {
            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil disimpan',
                'data'    => $pengumuman  
            ], 201);
        } 

        //gagal menyimpan ke database
        return response()->json([
            'success' => false,
            'message' => 'Pengumuman gagal disimpan',
        ], 409);

    }
    
    public function update(Request $request, $id)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'judul_pengumuman' => 'required',
            'isi_pengumuman' => 'required',
            'file' => 'file|max:1024'
        ]);
        
        //response validasi error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validated = $validator->validated(); //validator ndak return array jadi harus divalidated dulu

        //cari kriteria berdasar ID
        $pengumuman = Pengumuman::find($id);
        
        if($request->file('file')){
            if($pengumuman->file != null){
                Storage::delete($pengumuman->file);
            }

            $judul = SlugService::createSlug(Pengumuman::class, 'slug', $validated['judul_pengumuman'],['unique' => false]);
            $time = Carbon::now()->format('dmY');
            $nama_file = $judul."_".$time.".pdf";

            $validated['file'] = $request->file('file')->storeAs('post-pengumuman',$nama_file,'public');
        }

        $validated['slug'] = SlugService::createSlug(Pengumuman::class, 'slug', $validated['judul_pengumuman'],['unique' => false]);

        if($pengumuman) {
            $pengumuman->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil di simpan.',
                'data'    => $pengumuman  
            ], 200);
        }

        //data pengumuman tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Pengumuman tidak ditemukan',
        ], 404);
    }
    
    public function destroy($id)
    {
        //cari kriteria berdasar ID
        $pengumuman = Pengumuman::findOrfail($id);

        if($pengumuman) {
            if($pengumuman->file){ 
                Storage::delete($pengumuman->file);
            }

            //hapus kriteria
            $pengumuman->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dihapus.',
            ], 200);

        }

        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Pengumuman Not Found',
        ], 404);
    }

    public function downloadFile($id){
        $pengumuman = DB::table('pengumumans')->where('id_pengumuman',$id)->get();

        return response()->download(public_path('storage/' . $pengumuman[0]->file));

    }
}
