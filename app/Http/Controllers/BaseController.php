<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
         protected $vendorId = null;

        public function __construct()
        {
            $this->middleware(function ($request, $next) {
                $user = Auth::user();

                if ($user) {
                    $this->role = $user->role;
                    if ($user->role === 'vendor' && $user->vendor) {
                        $this->vendorId = $user->vendor->id;
                    }
                }

                return $next($request);
            });
        }
}
