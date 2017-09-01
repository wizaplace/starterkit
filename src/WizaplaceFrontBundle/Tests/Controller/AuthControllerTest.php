<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use WizaplaceFrontBundle\Controller\AuthController;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class AuthControllerTest extends BundleTestCase
{
    public function testLogInAndOut()
    {
        $client = static::createClient();

        // load the login form
        $client->request('GET', '/login');
        $this->assertResponseCodeEquals(Response::HTTP_OK, $client);

        // submit the login form
        $client->request('POST', '/login', [
            AuthController::EMAIL_FIELD_NAME => 'user@wizaplace.com',
            AuthController::PASSWORD_FIELD_NAME => 'password',
            AuthController::REDIRECT_URL_FIELD_NAME => '/',
            AuthController::CSRF_FIELD_NAME => $this->generateCsrfToken(AuthController::CSRF_LOGIN_ID, $client),
        ]);

        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $client);
        $this->assertSame('http://localhost/', $client->getResponse()->headers->get('Location'));

        // load the login form again, to check that we get redirected (as we are already logged in)
        $client->request('GET', '/login');
        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $client);
        $this->assertSame('/', $client->getResponse()->headers->get('Location'));

        // logout
        $client->request('GET', '/logout', [
            AuthController::CSRF_FIELD_NAME => $this->generateCsrfToken(AuthController::CSRF_LOGOUT_ID, $client),
        ]);
        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $client);
        $this->assertSame('http://localhost/', $client->getResponse()->headers->get('Location'));

        // load the login form again, to check that we do not get redirected (as we are logged out)
        $client->request('GET', '/login');
        $this->assertResponseCodeEquals(Response::HTTP_OK, $client);
    }
}
