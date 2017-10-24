<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserDataMonologProcessor
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function processRecord(array $record): array
    {
        $token = $this->tokenStorage->getToken();
        if ($token && ($token->getUser() instanceof User)) {
            $record['context']['user'] = [
                'username' => $token->getUsername(),
            ];
        }

        return $record;
    }
}
