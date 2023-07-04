<?php

namespace App\Http\Controllers\Api\Auth;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Models\Smsir;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    public function registerByEmail(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function loginByEmail(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Bad creds'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function authEmail(Request $request) {

        $fields = $request->validate([
            'email' => 'required|string|unique:users,email',
        ]);

        // Check status for resend after [set time: default 2minutes] minutes
        $check = PasswordReset::checkEmail($request->email);
        if ($check) { // Check time for sending OTP for current mobile
            // Generate OTP code for login
            $otp = PasswordReset::makeOtpToken($request, 'email');

            $emailClass = new Email();
            $parameters = [
                'otp' => $otp->token
            ];
            // Send Email to verify
            $response = $emailClass->Send($request->email, $parameters);
            return $response;
            if($response === 200)
            {
                $body = "$otp->token \n کد تایید شما جهت ورود";
                TextMessageController::send($request->mobile, $body);
                return response(['message' => $body], 201);
            }else{
                PasswordReset::where('mobile', $request->mobile)->delete();
            }

        }else{ // check time for resend OTP again
            return response(['message' => 'به منظور ارسال مجدد کد به مدت 2 دقیقه منتظر بمانید.'], 401);
        }

    }

    public function authMobile(Request $request) {


        $fields = $request->validate([
            'mobile' => 'required|string|digits:11',
        ]);

        // Check status for resend after [set time: default 2minutes] minutes
        $check = PasswordReset::check($request->mobile);
        if ($check) { // Check time for sending OTP for current mobile
                // Generate OTP code for login
                $otp = PasswordReset::makeOtpToken($request, 'mobile');

            $smsClass = new Smsir();
            $parameters = array([
                'name' => 'OTP',
                'value' => "$otp->token",
            ],[
                'name' => 'WEBSITE',
                'value' => 'siteiran.com',
            ]);
            // Send SMS to verify
            $response = $smsClass->Send($request->mobile, '414932', $parameters);
            if($response === 200)
            {
                $body = "$otp->token \n کد تایید شما جهت ورود";
                TextMessageController::send($request->mobile, $body);
                return response(['message' => $body], 201);
            }else{
                PasswordReset::where('mobile', $request->mobile)->delete();
            }

        }else{ // check time for resend OTP again
            return response(['message' => 'به منظور ارسال مجدد کد به مدت 2 دقیقه منتظر بمانید.'], 401);
        }

    }

    public function verifyMobile(Request $request)
    {
        request()->validate([
            'otpCode' => 'required',
        ]);
        $found = PasswordReset::where('token', $request->otpCode)->first();
            if ($found) { // Check token is valid

                $check = PasswordReset::check($found->mobile);
                if ($check) { // Check time expire, Discontinue
                    return response(['message' => 'زمان استفاده از کد تایید منقضی گردیده است'], 401);
                }
                    $userdata = User::where('mobile', $found->mobile)->first();
                if ($userdata === null) { // if user is not exists, Register
                    $user = User::create([
                        'mobile' => $found->mobile,
                    ]);
                    $token = $user->createToken('myapptoken')->plainTextToken;
                    $response = [
                        'user' => $user,
                        'token' => $token
                    ];
                    PasswordReset::where('mobile', $request->mobile)->delete();
                } else { // if user is exists, Login
                    $token = $userdata->createToken('myapptoken')->plainTextToken;
                    $response = [
                        'user' => $userdata,
                        'token' => $token
                    ];
                    PasswordReset::where('mobile', $request->mobile)->delete();
                }
                return response($response, 201);

            } else { // if OTP is wrong, show error
                return response(['message' => 'کد وارد شده صحیح نمی باشد'], 401);
            }
    }

    public function logout(Request $request) {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }


    public function getUserInfo(Request $request)
    {
        $token = $request->header('Authorization'); // دریافت توکن از هدر درخواست
    
        $user = Auth::guard('sanctum')->user();
    
        if ($user) {
            return response()->json($user);
        } else {
            return response()->json(['message' => 'توکن معتبر نیست'], 401);
        }
    }
    
    
    

}
