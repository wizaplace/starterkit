<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Wizaplace\ApiClient;

class SessionAuthenticator implements SimplePreAuthenticatorInterface
{
    /** @var ApiClient */
    private $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        /** @var ApiKeyToken $token */
        $this->apiClient->setApiKey($token->getCredentials());

        return $token;
    }

    public function supportsToken(TokenInterface $token, $providerKey): bool
    {
        return ($token instanceof ApiKeyToken);
    }

    public function createToken(Request $request, $providerKey)
    {
        return null;
    }
}
