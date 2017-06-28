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
use Wizaplace\Authentication\BadCredentials;
use Wizaplace\User\UserAlreadyExists;
use Wizaplace\User\UserService;

class AuthController extends Controller
{
    const API_KEY = '_apiKey';

    public function registerAction(Request $request): Response
    {
        // redirection url
        $requestedUrl = $request->get('redirect_url');
        $referer = $request->headers->get('referer');

        // recaptcha validation
        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        $recaptcha = new ReCaptcha($this->getParameter('recaptcha.secret'));
        $recaptchaValidation = $recaptcha->verify($recaptchaResponse);

        if (! $recaptchaValidation->isSuccess()) {
            $this->addFlash('danger', 'Erreur de Recaptcha, merci de réessayer.');

            return $this->redirect($referer);
        }

        // form validation
        $email = $request->get('email');
        $password = $request->get('password');
        $terms = $request->get('terms');

        if ($email === null || $password === null || $terms === null) {
            $this->addFlash('danger', 'Tous les champs doivent être renseignés, merci de réessayer.');

            return $this->redirect($referer);
        }

        // user registration and authentication
        $userService = $this->get(UserService::class);

        try {
            $userService->register($email, $password);
            $userService->authenticate($email, $password);

            $this->addFlash('success', 'Votre compte a bien été créé.');
        } catch (BadCredentials $e) { // Cela ne devrait jamais arriver puisqu'on vient de créer l'utilisateur
            $this->addFlash('danger', 'Erreur de connection après la création du compte.');
        } catch (UserAlreadyExists $e) {
            $this->addFlash('danger', 'Cette adresse email est déjà utilisée, merci de réessayer.');
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
            $this->addFlash('warning', "L'action n'a pas pu être effectuée car elle a expirée, merci de réessayer.");

            return $this->redirect($referer);
        }

        // form validation
        $email = $request->get('email');
        $password = $request->get('password');
        $terms = $request->get('terms');

        if ($email === null) {
            $this->addFlash('danger', 'Vous devez renseigner votre adresse email, merci de réessayer.');

            return $this->redirect($referer);
        }

        // send password recovery email
        $email = $request->request->get('email');
        $this->get(UserService::class)->recoverPassword($email);

        $this->addFlash('success', 'Vous allez recevoir un email afin de pouvoir réinitialiser votre mot de passe.');

        return $this->redirect($referer);
    }
}
