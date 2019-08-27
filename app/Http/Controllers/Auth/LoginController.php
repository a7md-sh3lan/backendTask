<?php

namespace App\Http\Controllers\Auth;


use App\User; 
use App\Status; 
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;
use PeterPetrus\Auth\PassportToken;

class LoginController extends Controller
{
    public $successStatus = 200;
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    public function login(){ 
        if(Auth::attempt(['phone_number' => request('phone_number'), 'password' => request('password')])){ 
            $user = Auth::user(); 
            $success['auth_token'] =  $user->createToken('backendTask')->accessToken; 
            return response()->json(['success' => $success], $this->successStatus); 
        } 
        else{ 
            return response()->json(['error'=>'Unauthorised'], 401); 
        } 
    }


    // Adjust Status for user

    public function status(Request $request){
        $phone = $request->phone;
        $status = $request->status;
        $token = PassportToken::dirtyDecode($request->token);
        $user_id = $token['user_id'];
        $user = User::find($user_id);

        $validator = Validator::make($request->all(), [ 
            'token' => ['required', 'string'],
            'phone' => ['required'],
            'status' => ['required', 'string'],
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        if($user && $user->phone_number == $phone){
            $newStatus = Status::create([
                'status' => $status,
                'user_id' => $user_id,
            ]);
            return response()->json(['done'=>"OK"], 200); 
        }
        return response()->json(['false'=>"unauthorized request"], 401);
    }
}
