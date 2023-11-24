<?php

namespace App;

use App\Contracts\AuthInferface;
use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class Auth implements AuthInferface
{
    private ?UserInterface $user = null;

    public function __construct(private readonly UserProviderServiceInterface $userProvider)
    {
    }

    public function user(): ?UserInterface
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $userId = $_SESSION['user'] ?? null;

        if (!$userId) {
            return null;
        }

        $user = $this->userProvider->getById($userId);

        if (!$user) {
            return null;
        }
        $this->user = $user;

        return $this->user;
    }

    public function attemptLogin(array $credentials): bool
    {
        $user = $this->userProvider->getByCredentials($credentials);

        if (! $user || ! $this->checkCredentials($user, $credentials)) {
            return false;
        }

        session_regenerate_id();

        $_SESSION['user'] = $user->getId();
        $this->user = $user;
        return true;
    }

    public function checkCredentials(UserInterface $user, array $credentials) : bool
    {
        return password_verify($credentials['password'], $user->getPassword());
    }

    public function logOut(): void
    {
        dd('logout');
        unset($_SESSION['user']);
        $this->user = null;
    }
}