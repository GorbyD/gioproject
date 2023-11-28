<?php

namespace App\Contracts;

interface AuthInferface
{
    public function user(): ?UserInterface;

    public function logOut() : void;

    public function attemptLogin(array $credentials) : bool;

    public function checkCredentials(UserInterface $user, array $credentials) : bool;
}