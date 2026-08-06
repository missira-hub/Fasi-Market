<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\ContactMessageReceived;

class ContactController extends Controller
{
   public function send(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'    => 'required|string|max:255',
        'email'   => 'required|email|max:255',
        'message' => 'required|string|max:5000',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors'  => $validator->errors()
        ], 422);
    }

    $data = $validator->validated();

    try {
        // ✅ Send to your specified Gmail address
        Mail::to('abbaboukarmissira@gmail.com')
            ->send(new ContactMessageReceived($data));

        return response()->json([
            'message' => 'Your message has been sent successfully!'
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Contact email failed: ' . $e->getMessage());
        return response()->json([
            'message' => 'Sorry, we could not deliver your message. Please try again later.'
        ], 500);
    }
}
}