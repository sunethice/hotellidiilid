<?php

namespace App\Http\Repository\Contracts;

interface IAuthRepository
{
    public function cpSignIn($pLoginDetails);
    public function cpSignUp($pUserDetails);
    public function cpSignOut();
    public function cpSendResetLink($pEmail);
    public function cpResetPassword();
}
