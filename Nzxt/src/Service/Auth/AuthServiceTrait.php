<?php
namespace Nzxt\Service\Auth;

/**
 * Trait AuthServiceTrait
 * @package Nzxt\Service\Auth
 */
trait AuthServiceTrait
{
    /**
     * @var \Nzxt\Service\Auth\AuthService
     */
    protected $authService;

    /**
     * Sets the Auth-Service via Dependency Injection.
     * @param \Nzxt\Service\Auth\AuthService $authService
     * @return void
     */
    public function setAuthService(\Nzxt\Service\Auth\AuthService $authService)
    {
        $this->authService = $authService;
    }
}
