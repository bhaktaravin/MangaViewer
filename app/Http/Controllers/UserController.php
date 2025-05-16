<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display the user's dashboard.
     */
    public function dashboard()
    {
        return view('user.dashboard');
    }
}
