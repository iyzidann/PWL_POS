<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index() 
    {
        // praktikum 2.5 no 1
        /* $user = UserModel::create(
            [
                'username' => 'manager55',
                'nama' => 'Manager55',
                'password' => Hash::make('12345'),
                'level_id' => 2
            ],
        );
        $user->username = 'manager56';

        $user->isDirty(); // true
        $user->isDirty('username'); // true
        $user->isDirty('nama'); // false
        $user->isDirty('nama', 'username'); // true

        $user->isClean(); // false
        $user->isClean('username'); // false
        $user->isClean('nama'); // true
        $user->isClean('nama', 'username'); // false

        $user->save();

        $user->isDirty(); // false
        $user->isClean(); // true
        dd($user->isDirty()); */

        // praktikum 2.5 no 3
        $user = UserModel::create(
            [
                'username' => 'manager11',
                'nama' => 'Manager11',
                'password' => Hash::make('12345'),
                'level_id' => 2
            ],
        );
        $user->username = 'manager12';

        $user->save();

        $user->wasChanged(); // true
        $user->wasChanged('username'); // true
        $user->wasChanged('username', 'level_id'); // true
        $user->wasChanged('nama'); // flase
        dd($user->wasChanged(['nama', 'username'])); //true
    }
}
