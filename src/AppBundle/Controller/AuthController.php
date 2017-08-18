<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\ApiClient;
use Wizaplace\Authentication\BadCredentials;
use Wizaplace\User\UserAlreadyExists;
use Wizaplace\User\UserService;
use WizaplaceFrontBundle\Security\User;

class AuthController extends Controller
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function loginAction(): Response
    {
        return $this->render('login/login.html.twig');
    }

    public function registerAction(Request $request): Response
    {
        // redirection url
        $requestedUrl = $request->get('redirect_url');
        $referer = $request->headers->get('referer') ?? $this->get('router')->generate('home');

        // recaptcha validation
        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        $recaptcha = new ReCaptcha($this->getParameter('recaptcha.secret'));
        $recaptchaValidation = $recaptcha->verify($recaptchaResponse);

        if (!$recaptchaValidation->isSuccess()) {
            $message = $this->translator->trans('recaptcha_error_message');
            $this->addFlash('warning', $message);

            return $this->redirect($referer);
        }

        // form validation
        $email = $request->get('email');
        $password = $request->get('password');
        $terms = $request->get('terms');

        if ($email === null || $password === null || $terms === null) {
            $message = $this->translator->trans('fields_required_error_message');
            $this->addFlash('danger', $message);

            return $this->redirect($referer);
        }

        // user registration and authentication
        $userService = $this->get(UserService::class);

        try {
            $userService->register($email, $password);

            // Authenticate the user
            $apiKey = $this->get(ApiClient::class)->authenticate($email, $password);
            $user = new User($apiKey, $userService->getProfileFromId($apiKey->getId()));
            $token = new UsernamePasswordToken($user, null, 'register', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->start(); // Ensure the session exists

            $message = $this->translator->trans('account_creation_success_message');
            $this->addFlash('success', $message);
        } catch (BadCredentials $e) { // Cela ne devrait jamais arriver puisqu'on vient de créer l'utilisateur
            $accountCreationErrorMessage = $this->translator->trans('account_creation_error_message');
            $this->addFlash('danger', $accountCreationErrorMessage);
        } catch (UserAlreadyExists $e) {
            $emailInUseErrorMessage = $this->translator->trans('email_already_in_use');
            $this->addFlash('danger', $emailInUseErrorMessage);
        }

        return $this->redirect($requestedUrl);
    }

    public function resetPasswordAction(Request $request): Response
    {
        // redirection url
        $referer = $request->headers->get('referer');

        // CSRF token validation
        $submittedToken = $request->get('csrf_token');

        if (! $this->isCsrfTokenValid('password_token', $submittedToken)) {
            $message = $this->translator->trans('recaptcha_error_message');
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
        $this->get(UserService::class)->recoverPassword($email);

        $message = $this->translator->trans('password_reset_confirmation_message');
        $this->addFlash('success', $message);

        return $this->redirect($referer);
    }
}
