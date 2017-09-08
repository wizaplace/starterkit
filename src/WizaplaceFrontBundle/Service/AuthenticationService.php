<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wizaplace\SDK\ApiClient;
use Wizaplace\SDK\Authentication\BadCredentials;
use Wizaplace\SDK\User\UserService;
use WizaplaceFrontBundle\Security\User;

class AuthenticationService
{
    /** @var ApiClient */
    private $apiClient;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SessionInterface */
    private $session;

    /** @var UserService */
    private $userService;

    public function __construct(ApiClient $apiClient, TokenStorageInterface $tokenStorage, SessionInterface $session, UserService $userService)
    {
        $this->apiClient = $apiClient;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->userService = $userService;
    }

    /**
     * Authenticate a user and store their API key where it's needed.
     * Normal log in should not use this method, but instead go through a Symfony firewall.
     * This method exists for special cases, like authenticating right after registering a user.
     *
     * @throws BadCredentials
     */
    public function authenticate(string $email, string $password): void
    {
        $apiKey = $this->apiClient->authenticate($email, $password);
        $user = new User($apiKey, $this->userService->getProfileFromId($apiKey->getId()));
        $token = new UsernamePasswordToken($user, null, 'register', $user->getRoles());
        $this->tokenStorage->setToken($token);
        $this->session->start(); // Ensure the session exists
    }
}
