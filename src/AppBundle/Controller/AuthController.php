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

    /**
     * TODO: CSRF token
     */
    public function loginAction(Request $request): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $userService = $this->get(UserService::class);

        try {
            $apiKey = $userService->authenticate($email, $password);
            $this->get('session')->set(self::API_KEY, $apiKey);
        } catch (BadCredentials $e) {
            $this->get('session')->getFlashBag()->add('danger', 'Les identifiants ne sont pas valides');
        }

        $referer =  $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function registerAction(Request $request): Response
    {
        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        $recaptcha = new ReCaptcha($this->getParameter('recaptcha.secret'));
        $resp = $recaptcha->verify($recaptchaResponse);
        if (!$resp->isSuccess()) {
            $this->get('session')->getFlashBag()->add('danger', 'Souci de Recaptcha, veuillez soumettre le formulaire à nouveau');
        } else {
            $userService = $this->get(UserService::class);
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            try {
                $userService->register($email, $password);
                $apiKey = $userService->authenticate($email, $password);
                $this->get('session')->set(self::API_KEY, $apiKey);
            } catch (BadCredentials $e) {//Ca ne devrait jamais arrivé puisqu'on vient de créer l'utilisateur
                $this->get('session')->getFlashBag()->add('danger', 'Souci de connection après création du compte');
            } catch (UserAlreadyExists $e) {
                $this->get('session')->getFlashBag()->add('warning', 'Cette adresse email est déjà enregistrée. Essayez de vous connecter.');
            }
        }

        return $this->redirectToRoute('home');
    }

    public function logoutAction(Request $request):Response
    {
        $token = $request->query->get('token');
        if ($this->isCsrfTokenValid('logout', $token)) {
            $this->get('session')->remove(self::API_KEY);
        } else {
            $this->get('session')->getFlashBag()->add('danger', 'Souci de token CSRF, Veuillez tenter de renvoyer le formulaire');
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function resetPasswordAction(Request $request): Response
    {
        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('resetPassword', $token)) {
            die("invalid token");
        }

        $email = $request->request->get('email');
        $this->get(UserService::class)->recoverPassword($email);


        $referer= $request->headers->get('referer');

        return $this->redirect($referer);
    }

}
