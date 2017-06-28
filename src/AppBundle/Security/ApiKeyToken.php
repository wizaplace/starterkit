<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Wizaplace\Authentication\ApiKey;

class ApiKeyToken extends AbstractToken implements TokenInterface
{
    /** @var ApiKey */
    private $apiKey;

    public function __construct(ApiKey $apiKey, User $user, array $roles = [])
    {
        parent::__construct($roles);
        $this->apiKey = $apiKey;
        parent::setUser($user);
        $this->setAuthenticated(true);
    }

    public function getCredentials(): ApiKey
    {
        return $this->apiKey;
    }

    public function serialize()
    {
        return serialize([
            'parent' => parent::serialize(),
            'apiKey' => \serialize($this->apiKey),
        ]);
    }

    public function unserialize($serialized)
    {
        $data = \unserialize($serialized);
        parent::unserialize($data['parent']);
        $this->apiKey = \unserialize($data['apiKey']);
    }
}
