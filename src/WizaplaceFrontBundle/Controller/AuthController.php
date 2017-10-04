<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Controller;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Uri;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\SDK\User\UserService;

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

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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

    public function initiateResetPasswordAction(Request $request): Response
    {
        // redirection url
        $referer = $request->headers->get('referer') ?? $this->generateUrl('home');

        // CSRF token validation
        $submittedToken = $request->get('csrf_token');

        if (!$this->isCsrfTokenValid('password_token', $submittedToken)) {
            $message = $this->translator->trans('csrf_error_message');
            $this->addFlash('warning', $message);

            return $this->redirect($referer);
        }

        // form validation
        $email = $request->get('email');

        if ($email === null) {
            $message = $this->translator->trans('email_field_required_error_message');
            $this->addFlash('danger', $message);

            return $this->redirect($referer);
        }

        // send password recovery email
        $recoveryUrl = new Uri(str_replace('token_placeholder', '', $this->generateUrl('reset_password_form', ['token' => 'token_placeholder'], UrlGeneratorInterface::ABSOLUTE_URL)));
        $this->get(UserService::class)->recoverPassword($email, $recoveryUrl);

        $message = $this->translator->trans('password_reset_confirmation_message');
        $this->addFlash('success', $message);

        return $this->redirect($referer);
    }

    public function resetPasswordFormAction(string $token)
    {
        return $this->render('@WizaplaceFront/auth/reset-password.html.twig', [
            'token' => $token,
        ]);
    }

    public function submitResetPasswordAction(Request $request)
    {
        $token = $request->request->get('token');
        $newPassword = $request->request->get('newPassword');

        if (empty($token)) {
            throw new BadRequestHttpException("missing token for password reset");
        }

        if (empty($newPassword)) {
            $this->addFlash('warning', $this->translator->trans('error_new_password_required'));
        }

        try {
            $this->get(UserService::class)->changePasswordWithRecoveryToken($token, $newPassword);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                $this->addFlash('error', $this->translator->trans('invalid_password_reset_token'));

                return $this->redirectToRoute('reset_password_form', ['token' => $token]);
            }

            throw $e;
        }
        $this->addFlash('success', $this->translator->trans('password_changed'));

        return $this->redirectToRoute('login_form');
    }
}
