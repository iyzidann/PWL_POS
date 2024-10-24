<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    public function update(Request $request, string $user_id) {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|unique:m_user,username,' . $user_id . ',user_id',
            'nama' => 'required|string|max:100',
            'password' => 'nullable|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'msgField' => $validator->errors()
            ]);
        }

        UserModel::find($user_id)->update([
            'username' => $request->username,
            'nama' => $request->nama,
            'password' => $request->password ? bcrypt($request->password) : UserModel::find($user_id)->password
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Akun berhasil diupdate',
            'data' => array(
                'user_id' => $user_id,
                'username' => $request->username,
                'nama' => $request->nama
            )
        ]);
    }

    public function updateAvatar(Request $request, string $user_id) {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'msgField' => $validator->errors()
            ]);
        }

        $user = UserModel::find($user_id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data user tidak ditemukan'
            ]);
        } else {
            if ($user->avatar !== null) {
                $oldFile = 'profil_pictures/' . $user->avatar;

                if (Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $fileExtension = $request->file('avatar')->getClientOriginalExtension();
            $fileName = 'profil_' . $user->user_id . '.' . $fileExtension;
            $request->file('avatar')->storeAs('public/profil_pictures', $fileName);

            UserModel::find($user_id)->update([
                'avatar' => $fileName
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Foto profil berhasil diupdate',
                'data' => array(
                    'avatar' => $fileName
                )
            ]);
        }
    }
}
