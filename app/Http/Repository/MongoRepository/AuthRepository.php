<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IAuthRepository;
use App\Http\Traits\HBApiTrait;
use App\Http\Traits\HBFileStoreTrait;
use App\Mail\PasswordReset;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthRepository extends BaseRepository implements IAuthRepository
{
    use HBApiTrait, HBFileStoreTrait;
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function cpSignIn($pLoginDetails){
        $mUser = User::where('email', $pLoginDetails['email'])->first();
        if ($mUser) {
            $mUserRole = $mUser->role;
            if ($mUserRole) {
                if($mUserRole->role == 'user')
                    $this->scope = 'user';
                else if($mUserRole->role == 'admin')
                    $this->scope = 'admin';
                else    
                    $this->scope = '';  
            }
            if (Hash::check($pLoginDetails['password'], $mUser->password)) {
                $mPostData = [
                    'grant_type' => 'password',
                    'client_id' => $pLoginDetails['client_id'],
                    'client_secret' => $pLoginDetails['client_secret'],
                    'username' => $pLoginDetails['email'],
                    'password' => $pLoginDetails['password'],
                    'scope' => $this->scope
                ];
                $mRequest = HttpRequest::create('/oauth/token','post', $mPostData);
                $mResponse = app()->handle($mRequest);
                // if ($pLoginDetails['rememberMe']) {
                //     $mResponse->token->expires_at = Carbon::now()->addWeeks(1);
                // }
                // $mResponse->token->save();
                return json_decode($mResponse->getContent());
            } else {
                return null;
            }
        }
    }

    public function cpSignUp($pUserDetails){
        $pUserDetails['password'] = Hash::make($pUserDetails['password']);
        $pUserDetails['rememberToken'] = Str::random(10);
        $mUser = User::create($pUserDetails->toArray());
        if(isset($mUser)){
            $mRole = Role::create([
                'role' => 'user',
                'user_id' => $mUser['_id']
            ]);
            if(isset($mRole)){
                return true;
            }
        }
        return false;
    }

    public function cpSignOut(){

    }

    public function cpSendResetLink($pEmail){
        $mUser = $this->model->where('email', $pEmail)->first();
        if($mUser){
            $mResetLink = URL::temporarySignedRoute("reset",now()->addMinutes(2),['user'=>$mUser['id']]);
            $mClientDetails = [
                'first_name' => $mUser['first_name'],
                'reset_link' => $mResetLink
            ];
            Mail::to($pEmail)->send(new PasswordReset($mClientDetails));
            return true;
        }
        return false;
    }

    public function cpResetPassword()
    {
        // $mImageType = $this->model->where('code', $pImageTypeCode)->get();
        // if (!$mImageType) {
        //     return null;
        // }
        // return $mImageType;
    }
}
