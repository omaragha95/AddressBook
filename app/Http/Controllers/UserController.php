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
use App\Log;

class UserController extends Controller
{
    public function index() {
        if (!auth('api')->user() || auth('api')->user()->role_id != 1) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $users = User::all();

        $data = [];
        
        foreach($users as $user) {
            $us = [
                'name' => $user->name,
                'email' => $user->email,
                'section' => $user->section->name
            ];
            array_push($data, $us);
        }

        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'getting all users.',
            'table'  => 'users',
            'role'   => auth('api')->user()->role->name
        ]);

        return response()->json([
            'message' => 'success',
            'data' => $data
        ], 200);
    }

    public function create(RegisterRequest $request) {
        if (!auth('api')->user() || auth('api')->user()->role_id != 1) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if ($request->password != $request->confirmPassword) {
            return response()->json([
                'message' => 'Passwords must be matched!'
            ], 422);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'section_id' => $request->section_id
        ];

        if ($request->role_id) {
            $data['role_id'] = $request->role_id;
        }

        $user = User::create($data);

        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'adding new user.',
            'table'  => 'users',
            'role'   => auth('api')->user()->role->name
        ]);

        return response()->json([
            'message' => 'Created user successfully.',
            'id'      => $user->id
        ], 201);
    }

    public function login(LoginRequest $request) {
        $role_id = User::where('email', $request->email)->get()->first();
        if (!$role_id) {
            return response()->json([
                'message' => 'Incorrect email.'
            ], 422);
        }
        if (! $token = auth('api')->attempt($request->all() + ['role_id' => $role_id])) {
            return response()->json([
                'message' => 'Incorrect password.'
            ], 422);
        }
        Session::create([
            'token' => $token,
            'user_id' => auth('api')->user()->id
        ]);
        
        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'loggin in.',
            'table'  => 'users',
            'role'   => auth('api')->user()->role->name
        ]);

        return response()->json([
            'message' => 'Successfully logged in.',
            'token'   => $token
        ], 200);
    }

    public function logout() {
        if (!auth('api')->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        Session::where('token', substr(request()->header('Authorization'), 7))->delete();

        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'loggin out.',
            'table'  => 'users',
            'role'   => auth('api')->user()->role->name
        ]);

        auth('api')->logout();
        


        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    public function update($id) {
        if (!auth('api')->user() || auth('api')->user()->id != $id) {
            return response()->json([
                'messagae' => 'Unauthorized'
            ], 401);
        }

        $token = Session::where('token',substr(request()->header('Authorization'), 7))->get()->first();

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = User::find($id);

        if (request('name')) {
            $user->name = request('name');
        }
        if (request('email')) {
            $user->email = request('email');
        }
        if (request('password')) {
            $user->password = bcrypt(request('password'));
        }
        if (request('role_id')) {
            $user->role_id = request('role_id');
        }
        if (request('section_id')) {
            $user->section_id = request('section_id');
        }
        
        $token = auth('api')->attempt(['email' => request('email'), 'password' => request('password'), 'role_id' => $user->role_id]);
        
        if (!$token) {
            $user->sessions()->delete();
        }

        $user->save();
        
        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'updating user.',
            'table'  => 'users',
            'role'   => auth('api')->user()->role->name
        ]);


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
        if (auth('api')->user()->id == $id) {
            return response()->json([
                'message' => 'You can not delete your self.'
            ], 401);
        }
        $user = User::find($id);
        $user->contacts()->delete();
        $user->sessions()->delete();
        $user->delete();
        Log::create([
            'name' => auth('api')->user()->name,
            'email' => auth('api')->user()->email,
            'action' => 'deleting user.',
            'table'  => 'users',
            'role'   => auth('api')->user()->role->name
        ]);

        return response()->json([
            'message' => 'User deleted successfully.'
        ], 200);
    }

    public function getLog() {
        if (!auth('api')->user() || auth('api')->user()->role_id != 1) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        return response()->json(Log::all());
    }

}
