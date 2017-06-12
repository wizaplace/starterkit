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
use Wizaplace\User\BadCredentials;
use Wizaplace\User\UserAlreadyExists;
use Wizaplace\User\UserService;

class AuthController extends Controller
{
    const API_KEY = '_apiKey';

    public function loginAction(Request $request): Response
    {
        // redirection url
        $requestedUrl = $request->get('redirect_url');

        // CSRF token validation
        $submittedToken = $request->get('csrf_token');

        if (! $this->isCsrfTokenValid('login_token', $submittedToken)) {
            $this->get('session')->getFlashBag()->add('warning', "L'action n'a pas pu être effectuée car elle a expirée, merci de réessayer.");

            return $this->redirect($requestedUrl);
        }

        // user authentication
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $userService = $this->get(UserService::class);

        try {
            $apiKey = $userService->authenticate($email, $password);
            $this->get('session')->set(self::API_KEY, $apiKey);
        } catch (BadCredentials $e) {
            $this->get('session')->getFlashBag()->add('danger', 'Identifiants invalides, merci de réessayer.');
        }

        return $this->redirect($requestedUrl);
    }

    public function registerAction(Request $request): Response
    {
        $flashBag = $this->get('session')->getFlashBag();

        // redirection url
        $requestedUrl = $request->get('redirect_url');
        $referer = $request->headers->get('referer');

        // recaptcha validation
        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        $recaptcha = new ReCaptcha($this->getParameter('recaptcha.secret'));
        $recaptchaValidation = $recaptcha->verify($recaptchaResponse);

        if (! $recaptchaValidation->isSuccess()) {
            $flashBag->add('danger', 'Erreur de Recaptcha, merci de réessayer.');

            return $this->redirect($referer);
        }

        // form validation
        $email = $request->get('email');
        $password = $request->get('password');
        $terms = $request->get('terms');

        if ($email === null || $password === null || $terms === null) {
            $flashBag->add('danger', 'Tous les champs doivent être renseignés, merci de réessayer.');

            return $this->redirect($referer);
        }

        // user registration and authentication
        $userService = $this->get(UserService::class);

        try {
            $userService->register($email, $password);
            $apiKey = $userService->authenticate($email, $password);
            $this->get('session')->set(self::API_KEY, $apiKey);
        } catch (BadCredentials $e) { // Cela ne devrait jamais arriver puisqu'on vient de créer l'utilisateur
            $flashBag->add('danger', 'Erreur de connection après la création du compte.');
        } catch (UserAlreadyExists $e) {
            $flashBag->add('warning', 'Cette adresse email est déjà utilisée, merci de réessayer.');
        }

        // add a success message
        if ($this->get('session')->get(self::API_KEY)) {
            $flashBag->add('success', 'Votre compte a bien été créé.');
        }

        return $this->redirect($requestedUrl);
    }

    public function logoutAction(Request $request):Response
    {
        // redirection url
        $referer = $request->headers->get('referer');

        // CSRF token validation
        $submittedToken = $request->get('csrf_token');

        if (! $this->isCsrfTokenValid('logout_token', $submittedToken)) {
            $this->get('session')->getFlashBag()->add('warning', "L'action n'a pas pu être effectuée car elle a expirée, merci de réessayer.");

            return $this->redirect($referer);
        }

        // logout user
        $this->get('session')->remove(self::API_KEY);

        return $this->redirectToRoute('home');
    }

    public function resetPasswordAction(Request $request): Response
    {
        // redirection url
        $referer = $request->headers->get('referer');

        // CSRF token validation
        $submittedToken = $request->get('csrf_token');

        if (! $this->isCsrfTokenValid('password_token', $submittedToken)) {
            $this->get('session')->getFlashBag()->add('warning', "L'action n'a pas pu être effectuée car elle a expirée, merci de réessayer.");

            return $this->redirect($referer);
        }

        // send password recovery email
        $email = $request->request->get('email');
        $this->get(UserService::class)->recoverPassword($email);

        return $this->redirect($referer);
    }
}
