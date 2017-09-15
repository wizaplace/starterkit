<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */

declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\User\User;
use Wizaplace\SDK\User\UserAddress;
use Wizaplace\SDK\User\UserTitle;
use WizaplaceFrontBundle\Controller\AuthController;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class ProfileControllerTest extends BundleTestCase
{
    public function testUpdateUser()
    {
        $this->login('user@wizaplace.com', 'password');

        $this->client->request('POST', '/update-user', [
            'return_url' => '/profil',
            'csrf_token' => $this->generateCsrfToken('profile_update_token', $this->client),
            'user' => [
                'email' => 'user@wizaplace.com',
                'firstName' => 'Janet',
                'lastName' => 'Jackson',
                'title' => UserTitle::MRS()->getValue(),
            ],
        ]);

        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $this->client);
        $this->clearRenderedData();
        $this->client->followRedirect();
        $this->assertResponseCodeEquals(Response::HTTP_OK, $this->client);

        $profileData = $this->getRenderedData('@WizaplaceFront/profile/profile.html.twig');
        /** @var User $user */
        $user = $profileData['profile'];
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('user@wizaplace.com', $user->getEmail());
        $this->assertSame('Janet', $user->getFirstname());
        $this->assertSame('Jackson', $user->getLastname());
        $this->assertTrue($user->getTitle()->equals(UserTitle::MRS()));
    }

    public function testUpdateUserWithOneAddress()
    {
        $this->login('user@wizaplace.com', 'password');

        $this->client->request('POST', '/update-user', [
            'return_url' => '/profil',
            'csrf_token' => $this->generateCsrfToken('profile_update_token', $this->client),
            'addresses_are_identical' => true,
            'user' => [
                'email' => 'user@wizaplace.com',
                'firstName' => 'Janet',
                'lastName' => 'Jackson',
                'title' => UserTitle::MRS()->getValue(),
                'addresses' => [
                    'billing' => [
                        'firstName' => 'Janet',
                        'lastName' => 'Jackson',
                        'title' => UserTitle::MRS()->getValue(),
                        'company' => 'Acme Inc',
                        'phone' => '0123456798',
                        'address' => '24 rue de la Gare',
                        'address_2' => '1er étage',
                        'zipcode' => '69009',
                        'city' => 'Lyon',
                        'country' => 'FR',
                    ],
                ],
            ],
        ]);

        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $this->client);
        $this->clearRenderedData();
        $this->client->followRedirect();
        $this->assertResponseCodeEquals(Response::HTTP_OK, $this->client);

        $profileData = $this->getRenderedData('@WizaplaceFront/profile/profile.html.twig');
        /** @var User $user */
        $user = $profileData['profile'];
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('user@wizaplace.com', $user->getEmail());
        $this->assertSame('Janet', $user->getFirstname());
        $this->assertSame('Jackson', $user->getLastname());
        $this->assertTrue($user->getTitle()->equals(UserTitle::MRS()));

        foreach ([$user->getBillingAddress(), $user->getShippingAddress()] as $address) {
            /** @var UserAddress $address */
            $this->assertSame('Janet', $address->getFirstname());
            $this->assertSame('Jackson', $address->getLastname());
            $this->assertSame(UserTitle::MRS()->getValue(), $address->getTitle());
            $this->assertSame('69009', $address->getZipCode());
            $this->assertSame('Lyon', $address->getCity());
            $this->assertSame('FR', $address->getCountry());
            $this->assertSame('0123456798', $address->getPhone());
            $this->assertSame('Acme Inc', $address->getCompany());
            $this->assertSame('24 rue de la Gare', $address->getAddress());
            $this->assertSame('1er étage', $address->getAddressSecondLine());
        }
    }

    private function login(string $email, string $password): void
    {
        // load the login form
        $this->client->request('GET', '/login');
        $this->assertResponseCodeEquals(Response::HTTP_OK, $this->client);

        // submit the login form
        $this->client->request('POST', '/login', [
            AuthController::EMAIL_FIELD_NAME => 'user@wizaplace.com',
            AuthController::PASSWORD_FIELD_NAME => 'password',
            AuthController::REDIRECT_URL_FIELD_NAME => '/',
            AuthController::CSRF_FIELD_NAME => $this->generateCsrfToken(AuthController::CSRF_LOGIN_ID, $this->client),
        ]);

        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $this->client);
    }
}
