<?php
namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private $firstname;
    private $lastname;
    private $mobile;
    private $password;
    private $accountcreateat;
    private $accountupdatedat;

    public function registration(Request $request)
    {
        try {
            $messages = [
                'unique'    => 'The User is already registerd',
            ];
            // Validate the request data
            $validatedData = $request->validate([
                'firstname' => 'required|string|max:20',
                'lastname' => 'required|string|max:20',
              'mobile' => 'required|numeric|digits:10|unique:registrations,mobile',
                'password' => 'required|string|min:6',
                
            ], $messages);

            // Save user data
            $obj = new Registration();
            $obj->firstname = $validatedData['firstname'];
            $obj->lastname = $validatedData['lastname'];
            $obj->mobile = $validatedData['mobile'];
            $obj->password = Hash::make($validatedData['password']);

            if ($obj->save()) {
                return response()->json(['success' => 'Your registration was successfully completed'], 201);
            } else {
                return response()->json(['error' => 'An error occurred during registration. Please try again.'], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors with a 422 status
            return response()->json(['errors' => $e->errors()], 422);
        }
    }




    public function login(Request $request)
    {

        $validData = $request->validate([
            'mobile' => 'required|digits:10',
            'password' => 'required|string|min:6',
        ]);
    
        if (Auth::attempt($validData)) {
            return response()->json([
                'success' => 'You have successfully logged in',], 200);
        } else {
 
            return response()->json([
                'unsuccess' => 'Invalid mobile number or password',
            ], 401);
        }
    }
    
}
