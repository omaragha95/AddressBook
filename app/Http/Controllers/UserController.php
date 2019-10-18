<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\test;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\User;
use App\Session;

class UserController extends Controller
{
    public function index() {
        if (!auth('api')->user() || auth('api')->user()->role_id != 1) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $users = User::all();
        return response()->json([
            'message' => 'success',
            'users' => $users
        ], 200);
    }

    public function register(RegisterRequest $request) {
        if ($request->password != $request->confirmPassword) {
            return response()->json([
                'errors' => [
                    'message' => ['Password must be matched!']
                ]
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'section_id' => auth('api')->user()->section_id,
            'role_id'    => $request->role_id
        ]);

        return response()->json([
            'data' => [
                'message' => 'Created user successfully.',
                'id'      => $user->id
            ]
        ], 201);
    }

    public function login(LoginRequest $request) {
        if (! $token = auth('api')->attempt($request->all())) {
            return response()->json([
                'message' => ['Incorrect email or password.']
            ], 422);
        }
        Session::create([
            'token' => $token,
            'user_id' => auth('api')->user()->id
        ]);
        return response()->json([
            'message' => 'Successfully logged in.',
            'token'   => $token
        ], 200);
    }

    public function logout() {
        if (!auth('api')->user()) {
            return response()->json([
                'errors' => [
                    'message' => [
                        'Unauthorized'
                    ]
                ]
            ], 401);
        }
        Session::where('token', substr(request()->header('Authorization'), 7))->delete();
        auth('api')->logout();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function update($id) {
        if (!auth('api')->user() || auth('api')->user()->id != $id) {
            return response()->json([
                'messagae' => 'Unauthorized'
            ], 401);
        }

        $token = Session::where('token',substr(request()->header('Authorization'), 7))->get();

        if (!count($token)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = User::find($id);
        $user->name = request('name');
        $user->email = request('email');
        $user->password = bcrypt(request('password'));
        $user->save();

        $user->sessions()->delete();

        return response()->json([
            'message' => 'Update user successfylly.'
        ], 200);
    }



    public function delete($id) {
        if (!auth('api')->user() || auth('api')->user()->role_id != 1) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        User::find($id)->delete();
        return response()->json([
            'message' => 'User deleted successfully.'
        ], 200);
    }



}
