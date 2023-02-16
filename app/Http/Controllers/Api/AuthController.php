<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'anggota'  => User::anggota(),
                'password' => Hash::make($request->password)
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e
            ], 500);
        }

        if($user) {
            return response()->json([
                'status'  => true,
                'message' => 'User berhasil dibuat.',
                'user'    => $user,
            ], 201);
        }

        return response()->json([
            'status'  => false,
            'message' => 'User gagal dibuat.',
        ], 409);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials['email']    = $request->email;
        $credentials['password'] = $request->password;

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                	'status'  => false,
                	'message' => 'Email atau Password anda salah.',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e,
            ], 500);
        }

        return response()->json([
            'success'      => true,
            'message'      => "Berhasil Login.",
            'user'         => JWTAuth::user(),
            'token_type'   => 'bearer',
            'access_token' => $token,
            'expires_in'   => JWTAuth::factory()->getTTL()
        ], 200);
    }

    public function show()
    {
        $user = JWTAuth::user();

        if($user) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan data.',
                'user'    => JWTAuth::user()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mendapatkan data.'
        ], 200);
    }

    public function update(Request $request)
    {
        $user = JWTAuth::user();

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);

        return response()->json([
            'success' => true,
            'message' => "Berhasil update data.",
            'user'    => JWTAuth::user()
        ], 200);
    }

    public function destroy()
    {
        $id = JWTAuth::user()->id;

        JWTAuth::invalidate(JWTAuth::getToken());

        $user = User::find($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghapus data user.'
        ], 200);
    }

    public function logout()
    {
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        if($removeToken) {
            return response()->json([
                'success' => true,
                'message' => 'Logout Berhasil.',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Logout Gagal!',
        ], 403);
    }
}
