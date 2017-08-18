<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;
use Wizaplace\ApiClient;
use Wizaplace\Authentication\BadCredentials;
use Wizaplace\User\UserService;

class FormAuthenticator implements SimpleFormAuthenticatorInterface
{
    /** @var ApiClient */
    private $apiClient;

    /** @var UserService */
    private $userService;

    public function __construct(ApiClient $apiClient, UserService $userService)
    {
        $this->apiClient = $apiClient;
        $this->userService = $userService;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey): TokenInterface
    {
        try {
            $apiKey = $this->apiClient->authenticate($token->getUsername(), $token->getCredentials());
        } catch (BadCredentials $e) {
            throw new BadCredentialsException($e->getMessage(), $e->getCode(), $e);
        }

        $user = new User($apiKey, $this->userService->getProfileFromId($apiKey->getId()));
        $token = new UsernamePasswordToken($user, $token->getCredentials(), $providerKey, $user->getRoles());

        return $token;
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
