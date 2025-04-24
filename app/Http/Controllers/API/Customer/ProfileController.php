<?php

namespace App\Http\Controllers\API\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ProfileController extends Controller
{
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|same:new_password',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()], 422);
        }
        try {
            $user = auth()->user();
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'error' => 'Old password is incorrect'], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json([
                'satus' => false,
                'error' => 'An error occurred while updating the password'], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Account deleted successfully'], 200);
    }
}
