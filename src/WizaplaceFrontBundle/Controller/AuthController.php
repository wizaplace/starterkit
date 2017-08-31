<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthController extends Controller
{
    /**
     * @var string field name to be used for the email at login
     */
    public const EMAIL_FIELD_NAME = 'email';

    /**
     * @var string field name to be used for the password at login
     */
    public const PASSWORD_FIELD_NAME = 'password';

    /**
     * @var string field name to be used for the url we wish to be redirected to after login/logout
     */
    public const REDIRECT_URL_FIELD_NAME = 'redirect_url';

    /**
     * @var string field name to be used for the CSRF token at login/logout
     */
    public const CSRF_FIELD_NAME = 'csrf_token';

    /**
     * @var string CSRF token to be used for login
     */
    public const CSRF_LOGIN_ID = 'login_token';

    /**
     * @var string CSRF token to be used for logout
     */
    public const CSRF_LOGOUT_ID = 'logout_token';

    public function loginAction(Request $request): Response
    {
        if ($this->getUser()) {
            $redirectUrl = $request->get(static::REDIRECT_URL_FIELD_NAME, null) ?? $this->generateUrl('home');

            // we are already logged in, redirecting
            return $this->redirect($redirectUrl);
        }

        // logging in requires an existing session
        $this->get('session')->start();

        return $this->render('@WizaplaceFront/auth/login.html.twig');
    }
}
