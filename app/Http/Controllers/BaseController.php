<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
         protected $vendorId = null;
         protected $driverId = null;

        public function __construct()
        {
            $this->middleware(function ($request, $next) {
                $user = Auth::user();
                $tableName = $user->getTable();

                if ($user) {
                    if($tableName === 'users'){
                        $this->role = $user->role;
                        if ($user->role === 'vendor' && $user->vendor) {
                            $this->vendorId = $user->vendor->id;
                        }
                    }
                    if($tableName === 'drivers')
                    {
                            $this->driverId = $user->id ?? null;
                    }
                }

                return $next($request);
            });
        }
}
