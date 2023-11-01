<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

use App\Models\Criteria;
use App\Models\Alternative;
use App\Models\Saran;

class UserController extends Controller{

    public function index()
    {
        //ambil data dari tabel
        $users = User::all();

        //buat response JSON
        if(count($users) > 0){
            return response()->json([
                'success' => true,
                'message' => 'List Data User',
                'data' => $users  
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
        $user = User::findOrfail($id);

        //buat response JSON
        if(!is_null($user)){
            return response()->json([
                'success' => true,
                'message' => 'Detail Data User',
                'data' => $user 
            ], 200);
        }
        
        //data kriteria tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'User Not Found',
        ], 404);
    }

    public function update(Request $request, $id)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'email'  => 'required|email:rfc,dns',
            'alamat'  => 'required',
            'no_telpon'  => 'required|max:14',
            'foto' => 'file'
        ]);
        
        //response validasi error 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        //validator ndak return array jadi harus divalidated dulu
        $validated = $validator->validated(); 

        //cari user berdasar ID
        $user = User::find($id);

         if($request->file('foto')){
            if($user->foto != null){
                Storage::delete($user->foto);
            }
            $validated['foto'] = $request->file('foto')->store('foto-profil');
            $path = $validated['foto'];

            $user->foto = $path;
        }

        if($user) {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->alamat = $request->alamat;
            $user->no_telpon = $request->no_telpon;

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Data Pengguna Berhasil di Edit',
                'data'    => $user  
            ], 200);

        }

        //data user tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Data Pengguna tidak ditemukan',
        ], 404);
    }

    public function gantiPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'passwordBaru'  => 'required|min:8|confirmed',
            'passwordLama' => 'required|min:8',
        ]);
        
        //response validasi error 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $value = Hash::check($request->passwordLama, auth()->user()->password);

        if($value) {
            //jika true
            User::whereId(auth()->user()->id)->update([
                'password' => bcrypt($request->passwordBaru),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengubah kata sandi',
            ], 200);
        }

        //password lama tidak match
        return response()->json([
            'success' => false,
            'message' => 'Kata sandi lama tidak tepat',
        ], 404);
    }

    public function addAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'email'  => 'required|email:rfc,dns|unique:users',
            'alamat'  => 'required',
            'no_telpon'  => 'required|max:14'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'no_telpon' => $request->no_telpon,
            'is_admin' => 1,
            'password' => bcrypt($request->email),
        ]);
       
        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan Admin',
            'user'    => $user  
        ]);
    }

    public function getDashboard(){
        $countUser = DB::table('users')
            ->where('is_admin',0)
            ->count();

        $countCriteria = Criteria::all()->count();
        $countAlternative = Alternative::all()->count();

        $countSaran = DB::table('sarans')
            ->where('status','Belum di Verifikasi')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan jumlah user',
            'countUser' => $countUser,
            'countCriteria' => $countCriteria,
            'countAlternative' => $countAlternative,
            'countSaran' => $countSaran,
        ]);
    }
}