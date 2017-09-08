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
    public function generateUpdateUserAdressesCommand(array $data)
    {
        $shippingAddress = new UpdateUserAddressCommand();
        $shippingAddress
            ->setFirstName($data['addresses']['shipping']['firstName'])
            ->setLastName($data['addresses']['shipping']['lastName'])
            ->setCompany($data['addresses']['shipping']['company'])
            ->setPhone($data['addresses']['shipping']['phone'])
            ->setAddress($data['addresses']['shipping']['address'])
            ->setAddressSecondLine($data['addresses']['shipping']['address_2'])
            ->setZipCode($data['addresses']['shipping']['zipcode'])
            ->setCity($data['addresses']['shipping']['city'])
            ->setCountry($data['addresses']['shipping']['country']);
        $billingAddress = new UpdateUserAddressCommand();
        $billingAddress
            ->setFirstName($data['addresses']['billing']['firstName'])
            ->setLastName($data['addresses']['billing']['lastName'])
            ->setCompany($data['addresses']['billing']['company'])
            ->setPhone($data['addresses']['billing']['phone'])
            ->setAddress($data['addresses']['billing']['address'])
            ->setAddressSecondLine($data['addresses']['billing']['address_2'])
            ->setZipCode($data['addresses']['billing']['zipcode'])
            ->setCity($data['addresses']['billing']['city'])
            ->setCountry($data['addresses']['billing']['country']);
        $updateUserAddressesCommand = new UpdateUserAddressesCommand();
        $updateUserAddressesCommand
            ->setUserId($data['id'])
            ->setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
        ;

        return $updateUserAddressesCommand;
    }
}
