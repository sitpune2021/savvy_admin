<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Drivers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;


class AuthController extends Controller
{
    use AuthenticatesUsers;

    // public function login(Request $request)
    // {
    //     $this->validateLogin($request);

    //     if (!$this->attemptLogin($request)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     $user = $this->guard()->user();
    //     $token = $user->createToken('api_token')->plainTextToken;

    //     return response()->json([
    //         'access_token' => $token,
    //         'token_type' => 'Bearer',
    //         'user' => $user
    //     ]);
    // }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_no' => 'required|digits:10'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $driver = Drivers::where('phone_no', $request->phone_no)->first();
        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }

        $driver->tokens->each(function ($token) {
            $token->delete();
        });

        $otp = rand(100000, 999999);
        $driver->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5)
        ]);

        $this->sendWhatsAppOtp($request->phone_no, $otp);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to WhatsApp',
            'data' => [
                'phone_no' => $request->phone_no,
            ]
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_no' => 'required|digits:10',
            'otp' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $driver = Drivers::where('phone_no', $request->phone_no)
            ->where('otp', $request->otp)
            ->where('otp_expires_at', '>=', Carbon::now())
            ->first();

        if (!$driver) {
            return response()->json(['error' => 'Invalid or expired OTP'], 401);
        }

        $driver->update([
            'otp' => null,
            'otp_expires_at' => null
        ]);

        $driver->tokens->each(function ($token) {
            $token->delete();
        });
        $token = $driver->createToken('driver_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'driver' => $driver,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    private function sendWhatsAppOtp($phone_no, $otp)
    {
        $message = "Please use this OTP $otp to login Savvy Water application";

        $response = Http::withoutVerifying()->asForm()->post('http://redirect.ds3.in/submitsms.jsp', [
            'user' => 'SITSol',
            'key' => 'b6b34d1d4dXX',
            'mobile' => $phone_no,
            'message' => $message,
            'senderid' => 'DALERT',
            'accusage' => '10',
        ]);

        \Log::info("OTP sent to $phone_no | Response: " . $response->body());
    }

    protected function guard()
    {
        return Auth::guard(); // defaults to 'web'
    }
}
