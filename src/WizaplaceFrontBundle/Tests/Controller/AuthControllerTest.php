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
        $this->assertSame('http://localhost/', $this->client->getResponse()->headers->get('Location'));

        // load the login form again, to check that we get redirected (as we are already logged in)
        $this->client->request('GET', '/login');
        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $this->client);
        $this->assertSame('/', $this->client->getResponse()->headers->get('Location'));

        // logout
        $this->client->request('GET', '/logout', [
            AuthController::CSRF_FIELD_NAME => $this->generateCsrfToken(AuthController::CSRF_LOGOUT_ID, $this->client),
        ]);
        $this->assertResponseCodeEquals(Response::HTTP_FOUND, $this->client);
        $this->assertSame('http://localhost/', $this->client->getResponse()->headers->get('Location'));

        // load the login form again, to check that we do not get redirected (as we are logged out)
        $this->client->request('GET', '/login');
        $this->assertResponseCodeEquals(Response::HTTP_OK, $this->client);
    }
}
