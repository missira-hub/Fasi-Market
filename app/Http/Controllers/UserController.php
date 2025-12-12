<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function updateAvatar(Request $request)
    {
        // Implement later — just keep it empty for now
        return response()->json(['message' => 'Avatar updated']);
    }
}
