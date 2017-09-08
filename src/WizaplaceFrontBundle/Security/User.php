<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Wizaplace\SDK\Authentication\ApiKey;
use Wizaplace\SDK\User\User as WizaplaceUser;
use Wizaplace\SDK\User\UserService;

class User implements UserInterface, \Serializable
{
    /** @var WizaplaceUser */
    private $wizaplaceUser;

    /** @var ApiKey */
    private $apiKey;

    /** @var null|UserService */
    private $userService;

    /** @var bool */
    private $userIsFresh;

    public function __construct(ApiKey $apiKey, WizaplaceUser $user, ?UserService $userService = null)
    {
        $this->wizaplaceUser = $user;
        $this->userIsFresh = true;
        $this->apiKey = $apiKey;
        $this->userService = $userService;
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->wizaplaceUser->getEmail();
    }

    public function eraseCredentials(): void
    {
    }

    public function getWizaplaceUser(): WizaplaceUser
    {
        if (!$this->userIsFresh) {
            $this->refreshWizaplaceUser();
        }

        return $this->wizaplaceUser;
    }

    public function getApiKey(): ApiKey
    {
        return $this->apiKey;
    }

    public function serialize()
    {
        return \serialize([
            'apiKey' => $this->apiKey,
            'wUser' => $this->wizaplaceUser,
        ]);
    }

    public function unserialize($serialized)
    {
        $data = \unserialize($serialized);
        $this->apiKey = $data['apiKey'];
        $this->wizaplaceUser = $data['wUser'];
        $this->userIsFresh = false;
    }

    private function refreshWizaplaceUser(): void
    {
        if (is_null($this->userService)) {
            return;
        }
        $this->wizaplaceUser = $this->userService->getProfileFromId($this->wizaplaceUser->getId());
        $this->userIsFresh = true;
    }
}
