<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Propaganistas\LaravelPhone\PhoneNumber;

class RegisterController extends Controller
{

    public $successStatus = 201;
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'required_with:phonefield'],
            'phone_number' => ['required', 'phone:country_field', 'unique:users'],
            'gender' => ['required'],
            'birthdate' => ['required', 'date_format:Y-m-d|before:today'],
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(Request $request)
    {

        $validator = Validator::make($request->all(), [ 
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'phone_number' => ['phone:country_field', 'unique:users'],
            'country_code' => ['required', 'required_with:phone_number'],
            'phone_number' => ['required', 'regex:/^\+[1-9]\d{1,14}$/', 'phone:country_code', 'unique:users'],
            'birthdate' => ['required', 'date', 'date_format:"Y-m-d"', 'before:today'],
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'], 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        

        $input = $request->all(); 
        $input['password'] = bcrypt($input['password']); 
        $user = User::create($input); 
        $avatarName = $user->id.'_avatar'.time().'.'.request()->avatar->getClientOriginalExtension();

        $request->avatar->storeAs('avatars',$avatarName);

        $user->avatar = $avatarName;
        $user->save();
        $user->createToken('backendTask')->accessToken; 
        
        return response()->json(['success'=>$user->only(['id', 'first_name', 'last_name', 'country_code', 'phone_number', 'gender', 'birthdate'])], $this->successStatus);  
        
    }
}
