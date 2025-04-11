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

    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_no' => 'required|digits:10'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            $driver = Drivers::where('phone_no', $request->phone_no)->first();
    
            if (!$driver) {
                return response()->json([
                    'status' => false,
                    'message' => 'Driver not found',
                ], 404);
            }
    
            // Delete existing tokens
            $driver->tokens->each(function ($token) {
                $token->delete();
            });
    
            // Generate and update OTP
            $otp = rand(100000, 999999);
            $driver->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(5)
            ]);
    
            // Send OTP
            $this->sendWhatsAppOtp($request->phone_no, $otp);
    
            return response()->json([
                'status' => true,
                'message' => 'OTP sent to WhatsApp',
                'data' => [
                    'phone_no' => $request->phone_no,
                ]
            ], 200);
    
        } catch (\Exception $e) {
            Log::error('Error sending OTP: '.$e->getMessage());
    
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_no' => 'required|digits:10',
                'otp' => 'required|digits:6'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            $driver = Drivers::where('phone_no', $request->phone_no)
                ->where('otp', $request->otp)
                ->where('otp_expires_at', '>=', Carbon::now())
                ->first();
    
            if (!$driver) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP'
                ], 401);
            }
    
            // Clear OTP fields
            $driver->update([
                'otp' => null,
                'otp_expires_at' => null
            ]);
    
            // Delete old tokens
            $driver->tokens->each(function ($token) {
                $token->delete();
            });
    
            // Create new token
            $token = $driver->createToken('driver_token')->plainTextToken;
    
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'driver' => $driver,
                    'token' => $token
                ]
            ], 200);
    
        } catch (\Exception $e) {
            Log::error('Error verifying OTP: ' . $e->getMessage());
    
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error logging out: ' . $e->getMessage());
    
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while logging out. Please try again later.'
            ], 500);
        }
    }

    private function sendWhatsAppOtp($phone_no, $otp)
    {
        try {
            // Prepare the OTP message
            $message = "Please use this OTP $otp to login Savvy Water application";
    
            // Send the request to the SMS API
            $response = Http::withoutVerifying()->asForm()->post('http://redirect.ds3.in/submitsms.jsp', [
                'user' => 'SITSol',
                'key' => 'b6b34d1d4dXX',
                'mobile' => $phone_no,
                'message' => $message,
                'senderid' => 'DALERT',
                'accusage' => '10',
            ]);
    
            // Log the response body and the phone number for debugging
            Log::info("OTP sent to $phone_no | Response: " . $response->body());
    
            // Check if the request was successful (e.g., status code 200)
            if ($response->successful()) {
                return response()->json([
                    'status' => true,
                    'message' => 'OTP sent successfully',
                ], 200);
            }
    
            // If the response was not successful, log the error and return a failure message
            Log::error("Failed to send OTP to $phone_no | Response: " . $response->body());
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP. Please try again later.',
            ], 500);
    
        } catch (\Exception $e) {
            // Log any exception that occurs during the request
            Log::error("Error sending OTP to $phone_no: " . $e->getMessage());
    
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while sending OTP. Please try again later.',
            ], 500);
        }
    }

    protected function guard()
    {
        return Auth::guard(); // defaults to 'web'
    }
}
