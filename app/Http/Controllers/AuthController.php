<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IAuthRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\User;
use App\Models;
use App\Http\Traits\JsonResponseTrait;
use App\Models\Car;
use App\Models\Role;
use App\Services\AuthService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;

class AuthController extends Controller
{
    use JsonResponseTrait;
    private $cIAuthRepository;

    public function __construct(IAuthRepository $pIAuthRepository)
    {
        $this->cIAuthRepository = $pIAuthRepository;
    }

    public function cpSignIn(Request $request) //, AuthService $AuthService)
    {
        // return response(['message' => $AuthService->doSomethingUseful()], 200);
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'rememberMe' => 'boolean'
        ]);
        if ($validator->fails()) {
            return $this->cpResponse(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }
        try{
            $mLoggedIn = $this->cIAuthRepository->cpSignIn($request->only([
                'client_id','client_secret','email','password','rememberMe'
            ]));
            if(is_null($mLoggedIn)){
                return $this->cpFailureResponse(500,"Unable to signin.");
            }
            return $this->cpResponseWithResults($mLoggedIn,"Signed-in successfully.");
        }catch(QueryException $pEx){
            return $this->cpFailureResponse($pEx->getCode(),"Unable to signin.");
        }
    }

    public function cpSignUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'country' => 'required|string|exists:countries,country_code',
            'mobile' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
                'confirmed'
            ],
            'password_confirmation' => 'same:password'
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->failed()], 422);
        }
        try{
            $mSignedUp = $this->cIAuthRepository->cpSignUp($request->only(['first_name','last_name','email','country','mobile','password']));
            if($mSignedUp){
                return $this->cpSuccessResponse("User registered successfully");
            }
            return $this->cpFailureResponse(500, "User registration failed.");
        }catch(QueryException $pEx){
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(),$pEx);
        }
    }

    public function cpSignOut(Request $request)
    {
        return response(['message' => 'You have been successfully logged out.'], 200);
        // $request->user()->token()->revoke();
        // return response(['message'=>'You have been successfully logged out.'],200);
    }

    public function cpAdminDashboard(Request $request)
    {
        return response(['message' => 'cpAdminDashboard'], 200);
    }

    public function cpModeratorDashboard(Request $request)
    {
        return response(['message' => 'cpModeratorDashboard'], 200);
    }

    public function cpPublicAccess(Request $request)
    {
        $mCar = Car::create($request->input());
        // $mCar = Car::where('name', 'camry')->first();
        // $mCar->name = "camry";
        // $mCar->setTranslation('brand', 'en', 'Hello world!')
        //     ->setTranslation('brand', 'nl', 'Naam in het Nederlands')
        //     ->save();
        // app()->setlocale('nl');
        return response(['message' => $mCar], 200);
    }

    public function cpAdminScopeClient(Request $request)
    {
        return response(['message' => 'cpAdminScopeClient'], 200);
    }

    public function cpUserScopeClient(Request $request)
    {
        return response(['message' => 'cpUserScopeClient'], 200);
    }

    public function cpAdminPass(Request $request)
    {
        return response(['message' => 'cpAdminPass'], 200);
    }

    public function cpUserPass(Request $request)
    {
        return response(['message' => 'cpUserPass'], 200);
    }
}
