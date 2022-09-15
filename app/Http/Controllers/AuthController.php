<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:8|max:255',
            'username' => 'required|min:8|max:255|unique:users',
            'email' => 'required|min:8|max:255|unique:users|email',
            'password' => 'required|min:8|max:255',
        ],[
            'name.required' => 'Họ và tên không được bỏ trống', 
            'name.min' => 'Họ và tên quá ngắn',
            'name.max' => 'Họ và tên quá dài',
            'username.unique' => 'Username đã tồn tại',
            'email.unique' => 'Email đã tồn tại'
        ]);

        if($validator->fails()) {
            $arrRes = [
                'errCode' => 1,
                'message' => "Lỗi validate dữ liệu",
                'data' => $validator->errors()
            ];

            return response()->json($arrRes, 402);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password'=> Hash::make($request->password),
                'role_id' => 3,
                'active' => 1
            ]);

            $arrRes = [
                'errCode' => 0,
                'message' => "Đăng ký thành công",
                'data' => []
            ];
        } catch (\Throwable $th) {
            $arrRes = [
                'errCode' => 0,
                'message' => "Lỗi phía server",
                'data' => $th->getMessage()
            ];
        }
        return response()->json($arrRes, 201);
    }


    public function login (Request $request) {
        $validator = Validator::make($request->all(), [
            'email'=> 'email|required|min:8',
            'password' => 'required|min:8'
        ]);


        if($validator->fails()) {
            $arrRes = [
                'errCode'=> 1,
                'message' => 'Lỗi validate dữ liệu',
                'data' => $validator->errors()
            ];

            return response()->json($arrRes, 402);
        }

        try {
            if(!$token = auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
                $arrRes = [
                    'errCode'=> 2,
                    'message' => 'Vui lòng kiểm tra email và mật khẩu',
                    'data' => []
                ];
                return response()->json($arrRes, 201);
            }

            // auth()->login($token);
            $arrRes = [
                'errCode'=> 0,
                'message' => 'Đăng nhập thành công',
                'data' => [
                    "user" => auth()->setToken($token)->user(),
                    'token' => $token
                ]
            ];
            return response()->json($arrRes, 201);
        } catch (\Throwable $th) {
            $arrRes = [
                'errCode'=> 2,
                'message' => 'Lỗi phía server',
                'data' => $th->getMessage()
            ];
            return response()->json($arrRes, 501);
        }

    }
}
