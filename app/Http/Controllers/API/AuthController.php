<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Drivers;
use App\Models\Distributor;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Plant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ShippingContact;
use Illuminate\Support\Facades\Hash;
use App\Models\AppVersion;

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
    
            $otp = rand(100000, 999999);
            $driver->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10)
            ]);
    
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
                'error' => $e->getMessage()
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

            $driver->update([
                'otp' => null,
                'otp_expires_at' => null
            ]);

    
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

    public function logout()
    {
        try {
            $user = auth()->user();
            $user->currentAccessToken()->delete();
            return response()->json([
                'status' => true,
                'message' => 'Successfully logged out from current session.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging out : ' . $e->getMessage());
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
            $message = "Please use this OTP $otp to login Saavy Water application";
    
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

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_no' => 'nullable|digits:10',
            'email' => 'nullable|email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:customer,vendor,plant-manager,distributor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

         try {
            $user = null;

            if ($request->role === 'customer') {
                   $users = ShippingContact::where('phone', $request->phone_no)
                    ->with('shippingContactMultiples.shippingAddress')
                    ->get();

                if ($users->count() === 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }

                if ($users->count() > 1) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Multiple users found with the same phone number. Cannot proceed with login.',
                    ], 409); // 409 Conflict
                }

                $user = $users->first();

                // Extract and assign shipping addresses only
                $shippingAddresses = $user->shippingContactMultiples
                    ->pluck('shippingAddress')
                    ->filter()
                    ->values();

                unset($user->shippingContactMultiples); // Hide unwanted relation

                $user->shipping_addresses = $shippingAddresses;


            } elseif ($request->role === 'vendor') {
                $vendor = Vendor::with('user')->where('phone_number', $request->phone_no)->first();

                if (!$vendor || !$vendor->user) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }

                $user = $vendor->user;
                $user->phone = $vendor->phone_number;
                $user->address = $vendor->address;
                $user->vendor_id = $vendor->id;
            }
            elseif ($request->role === 'plant-manager') {
                $plantUser = User::where('role', 'plant-manager')
                    ->where('email', $request->email)
                    ->first();
                $plant = Plant::with('managerRecord')->where('manager_id', $plantUser->id)->first();

                if (!$plantUser || !$plant->managerRecord) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }

                        // Augment user with plant data
                $plantUser->address = $plant->address;
                $plantUser->plant_id = $plant->id;

                $user = $plantUser;
            }elseif ($request->role === 'distributor') {
                $user = Distributor::where('email', $request->email)->first();
                if (!$user) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }
            }

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid phone number or password',
                ], 401);
            }

            $token = $user->createToken($request->role . '_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    $request->role => $user,
                    'token' => $token,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while logging in',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_no' => 'nullable|digits:10',
            'email' => 'nullable|email',
            'role' => 'required|string|in:customer,vendor,plant-manager,distributor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = null;

            if ($request->role === 'customer') {
                $users = ShippingContact::where('phone', $request->phone_no)
                    ->with('shippingAddress:id,shipping_address')
                    ->get();

                if ($users->count() === 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }

                if ($users->count() > 1) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Multiple users found with the same phone number. Cannot proceed with login.',
                    ], 409); // 409 Conflict
                }

                $user = $users->first();

            } elseif ($request->role === 'vendor') {
                $vendor = Vendor::with('user')->where('phone_number', $request->phone_no)->first();

                if (!$vendor || !$vendor->user) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }

                $user = $vendor->user;
                $user->phone = $vendor->phone_number;
                $user->address = $vendor->address;
                $user->vendor_id = $vendor->id;
            }
             elseif ($request->role === 'plant-manager') {
                $plantUser = User::where('role', 'plant-manager')
                    ->where('email', $request->email)
                    ->first();
                $plant = Plant::with('managerRecord')->where('manager_id', $plantUser->id)->first();

                if (!$plantUser || !$plant->managerRecord) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }

                        // Augment user with plant data
                $plantUser->address = $plant->address;
                $plantUser->plant_id = $plant->id;

                $user = $plantUser;
            }
            elseif ($request->role === 'distributor') {
                $user = Distributor::where('email', $request->email)->first();
                if (!$user) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'customer verified successfully',
                'data' => [
                    $request->role => $user,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while verifying the customer',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function resetPassword(Request $request)    
    {
        $validator = Validator::make($request->all(), [
            'phone_no' => 'nullable|digits:10',
             'email' => 'nullable|email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:customer,vendor,plant-manager,distributor',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
             $user = null;

            if ($request->role === 'customer') {
                $users = ShippingContact::where('phone', $request->phone_no)->get();
                if ($users->count() === 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }

                if ($users->count() > 1) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Multiple users found with the same phone number. Cannot proceed with login.',
                    ], 409); // 409 Conflict
                }

                $user = $users->first();
            }else if ($request->role === 'vendor') {
                $vendor = Vendor::with('user')->where('phone_number', $request->phone_no)->first();

                if (!$vendor || !$vendor->user) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Vendor not found',
                    ], 404);
                }

                $user = $vendor->user;
            }else if ($request->role === 'plant-manager') {
                $plantUser = User::where('role', 'plant-manager')
                    ->where('email', $request->email)
                    ->first();
                $plant = Plant::with('managerRecord')->where('manager_id', $plantUser->id)->first();

                if (!$plantUser || !$plant->managerRecord) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }


                $user = $plantUser;
            }
            elseif ($request->role === 'distributor') {
                $user = Distributor::where('email', $request->email)->first();
                if (!$user) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found',
                    ], 404);
                }
            }
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while resetting the password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function guard()
    {
        return Auth::guard(); // defaults to 'web'
    }

    
    public function appVersion(Request $request)
    {
        $request->validate([
            'for' => 'required|string|in:vendor,customer,driver,plant',
            'platform' => 'required|string|in:android,ios',
        ]);
        $for = $request->for;
        $platform = $request->platform;
        $version = AppVersion::where('platform', $platform)->where('for', $for)->get();
        return response()->json([
            'status' => true,
            'data' => $version,
        ]);
    }
}
