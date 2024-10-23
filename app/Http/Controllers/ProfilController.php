<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Profil Anda',
            'list'  => ['Home', 'Profil']
        ];

        $page = (object) [
            'title' => ''
        ];

        $activeMenu = 'profil';

        // Ambil semua level dari LevelModel
        $level = LevelModel::all();

        // Ambil data pengguna yang sedang login
        $user = auth()->user(); 

        return view('profil.index', [
            'breadcrumb' => $breadcrumb, 
            'page' => $page, 
            'level' => $level, 
            'activeMenu' => $activeMenu,
            'user' => $user // Kirim data pengguna ke view
        ]);
    }

    public function update(Request $request, String $id)
    {
        // Validasi input
        $request->validate([
            'username'  => 'required|string|min:3|unique:m_user,username,'.$id.',user_id',
            'nama'      => 'required|string|max:100',
            'password'  => 'nullable|min:6',
            'level_id'  => 'required|integer',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // validasi gambar
        ]);

        // Cari user berdasarkan ID
        $user = UserModel::find($id);

        // Proses file foto profil (jika ada)
        if ($request->hasFile('foto_profil')) {
            // Hapus file lama jika ada
            if ($user->avatar) {
                Storage::delete('public/foto_profil/' . $user->avatar);
            }

            // Simpan file baru dan ambil nama file
            $file = $request->file('foto_profil');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/foto_profil', $fileName);

            // Simpan nama file ke dalam kolom avatar
            $user->avatar = $fileName;
        }

        // Update data user
        $user->update([
            'username'  => $request->username,
            'nama'      => $request->nama,
            'password'  => $request->password ? bcrypt($request->password) : $user->password,
            'level_id'  => $request->level_id,
        ]);

        // Simpan nama file avatar (jika ada update)
        if (isset($fileName)) {
            $user->avatar = $fileName;
        }

        $user->save();

        return redirect()->back()->with('success', 'Data user berhasil diubah');
    }
}
