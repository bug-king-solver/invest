<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\AuthenticEmail;
use Validator;
use App\Models\Email;
use Symfony\Component\HttpFoundation\Response;

class EmailsController extends Controller
{
    //
    public function regEmail(Request $request)
    {
        $response = ['success' => false];

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', app(AuthenticEmail::class)],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if ($validator->passes()) {
            Email::firstOrCreate(
                ['email' => $request->input('email')],
                ['name' => $request->get('name', '')]
            );

            $response['success'] = true;
            return response()->json($response);
        } else {
            $response = [
                'success' => false,
                'messages' => $validator->errors()
            ];
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
