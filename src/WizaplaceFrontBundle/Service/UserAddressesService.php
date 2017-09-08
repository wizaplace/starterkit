<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use Wizaplace\SDK\User\UpdateUserAddressCommand;
use Wizaplace\SDK\User\UpdateUserAddressesCommand;
use Wizaplace\SDK\User\User;

class UserAddressesService
{
    public function generateUpdateUserAdressesCommand(User $user)
    {
        $shippingAddress = new UpdateUserAddressCommand();
        $shippingAddress
            ->setFirstName($user->getShippingAddress()->getFirstName())
            ->setLastName($user->getShippingAddress()->getLastName())
            ->setCompany($user->getShippingAddress()->getCompany())
            ->setPhone($user->getShippingAddress()->getPhone())
            ->setAddress($user->getShippingAddress()->getAddress())
            ->setAddressSecondLine($user->getShippingAddress()->getAddressSecondLine())
            ->setZipCode($user->getShippingAddress()->getZipCode())
            ->setCity($user->getShippingAddress()->getCity())
            ->setCountry($user->getShippingAddress()->getCountry());
        $billingAddress = new UpdateUserAddressCommand();
        $billingAddress
            ->setFirstName($user->getBillingAddress()->getFirstName())
            ->setLastName($user->getBillingAddress()->getLastName())
            ->setCompany($user->getBillingAddress()->getCompany())
            ->setPhone($user->getBillingAddress()->getPhone())
            ->setAddress($user->getBillingAddress()->getAddress())
            ->setAddressSecondLine($user->getBillingAddress()->getAddressSecondLine())
            ->setZipCode($user->getBillingAddress()->getZipCode())
            ->setCity($user->getBillingAddress()->getCity())
            ->setCountry($user->getBillingAddress()->getCountry());
        $updateUserAddressesCommand = new UpdateUserAddressesCommand();
        $updateUserAddressesCommand
            ->setUserId($user->getId())
            ->setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
        ;

        return $updateUserAddressesCommand;
    }
}
