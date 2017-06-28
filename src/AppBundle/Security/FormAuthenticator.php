<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;
use Wizaplace\ApiClient;
use Wizaplace\User\UserService;

class FormAuthenticator implements SimpleFormAuthenticatorInterface
{
    /** @var ApiClient */
    private $apiClient;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserService */
    private $userService;

    public function __construct(ApiClient $apiClient, UserService $userService, TokenStorageInterface $tokenStorage)
    {
        $this->apiClient = $apiClient;
        $this->tokenStorage = $tokenStorage;
        $this->userService = $userService;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey): TokenInterface
    {
        $apiKey = $this->apiClient->authenticate($token->getUsername(), $token->getCredentials());

        $user = new User($this->userService->getProfileFromId($apiKey->getId()));

        $apiKeyToken = new ApiKeyToken($apiKey, $user, $token->getRoles());
        $apiKeyToken->setAuthenticated(true);

        $this->tokenStorage->setToken($apiKeyToken);

        return $apiKeyToken;
    }

    public function supportsToken(TokenInterface $token, $providerKey): bool
    {
        return (($token instanceof UsernamePasswordToken) && $token->getProviderKey() === $providerKey);
    }

    public function createToken(Request $request, $username, $password, $providerKey): TokenInterface
    {
        return new UsernamePasswordToken($username, $password, $providerKey, ['ROLE_USER']);
    }
}
