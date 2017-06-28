<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Wizaplace\User\User as WizaplaceUser;

class User implements UserInterface
{
    /** @var WizaplaceUser */
    private $user;

    public function __construct(WizaplaceUser $user = null)
    {
        $this->user = $user;
    }


    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return '';// TODO: Implement getPassword() method.
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->user->getEmail();
    }

    public function eraseCredentials(): void
    {
    }

    public function getWizaplaceUser(): \Wizaplace\User\User
    {
        return $this->user;
    }
}
