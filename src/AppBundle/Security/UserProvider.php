<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wizaplace\ApiClient;
use Wizaplace\User\UserService;

class UserProvider implements UserProviderInterface
{
    /** @var UserService */
    private $userService;

    /** @var ApiClient */
    private $apiClient;

    public function __construct(UserService $userService, ApiClient $apiClient)
    {
        $this->userService = $userService;
        $this->apiClient = $apiClient;
    }

    public function loadUserByUsername($username): UserInterface
    {
        throw new \Exception('Cannot load user by username');
    }

    public function refreshUser(UserInterface $user): User
    {
        if (!($user instanceof User)) {
            throw new UnsupportedUserException();
        }

        // @FIXME: this does not work, as we haven't set the ApiKey at this point...
//        $freshUser = $this->userService->getProfileFromId($user->getWizaplaceUser()->getId());
//
//        return new User($freshUser);

        return $user;
    }

    public function supportsClass($class): bool
    {
        return $class === User::class;
    }
}
