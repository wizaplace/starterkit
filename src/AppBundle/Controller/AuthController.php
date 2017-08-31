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
use Wizaplace\Company\CompanyRegistration;
use Wizaplace\Company\CompanyService;
use Wizaplace\User\UserAlreadyExists;
use Wizaplace\User\UserService;
use WizaplaceFrontBundle\Security\User;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

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
        return $this->render('@App/auth/login.html.twig');
    }

    public function registerUserAction(Request $request): Response
    {
        // redirection url
        $requestedUrl = $request->get('redirect_url');
        $referer = $request->headers->get('referer') ?? $this->get('router')->generate('home');

        // recaptcha validation
        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        $recaptcha = new ReCaptcha($this->getParameter('recaptcha.secret'));
        $recaptchaValidation = $recaptcha->verify($recaptchaResponse);

        if (!$recaptchaValidation->isSuccess()) {
            $message = $this->translator->trans('csrf_error_message');
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
        $this->get(UserService::class)->recoverPassword($email);

        $message = $this->translator->trans('password_reset_confirmation_message');
        $this->addFlash('success', $message);

        return $this->redirect($referer);
    }

    public function registerCompanyAction(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // redirect url
            $requestedUrl = $request->get('redirect_url');
            $referer = $request->headers->get('referer') ?? $request->get('return_url');

            // company info
            $name = $request->get('company_name');
            $address = $request->get('company_address');
            $zipcode = $request->get('company_zipcode');
            $city = $request->get('company_city');
            $country = $request->get('company_country');
            $legalStatus = $request->get('company_status');
            $capital = $request->get('company_capital');
            $siret = $request->get('company_siret');
            $rcs = $request->get('company_rcs');
            $vat = $request->get('company_vat');
            $description = $request->get('company_description');

            // company admin info
            $firstName = $request->get('admin_firstname');
            $lastName = $request->get('admin_lastname');
            $email = $request->get('admin_email');
            $phoneNumber = $request->get('admin_phone');
            $password = $request->get('admin_password');
            $url = $request->get('admin_url');

            $terms = $request->get('terms');

            // Symfony => PSR7 adapter
            $psr7Factory = new DiactorosFactory();
            $psr7Request = $psr7Factory->createRequest($request);
            $uploadedFiles = $psr7Request->getUploadedFiles();

            $idCard = $uploadedFiles['admin_document_id_card'];
            $kbis = $uploadedFiles['company_document_kbis'];
            $bic = $uploadedFiles['company_document_bic']; // RIB

            if (! $email || ! $firstName || ! $lastName || ! $name || ! $password || ! $phoneNumber ||
                ! $legalStatus || ! $zipcode || ! $capital || ! $siret || ! $rcs || ! $city || ! $country ||
                ! $address || ! $idCard || ! $kbis || ! $bic || ! $capital || ! $terms) {
                $notification = $this->translator->trans('fields_required_error_message');
                $this->addFlash('danger', $notification);

                return $this->redirect($referer);
            }

            // user and company registration + authentication
            $companyService = $this->get(CompanyService::class);

            try {
                $this->registerAndAuthenticate($email, $password, $firstName, $lastName);

                $registration = new CompanyRegistration($name, $email);
                $registration->setName($name);
                $registration->setEmail($email);
                $registration->setZipcode($zipcode);
                $registration->setAddress($address);
                $registration->setCity($city);
                $registration->setLegalStatus($legalStatus);
                $registration->setPhoneNumber($phoneNumber);
                $registration->setSiretNumber($siret);
                $registration->setDescription($description);
                $registration->addUploadedFile('kbis', $kbis);
                $registration->addUploadedFile('idCard', $idCard);
                $registration->addUploadedFile('bic', $bic);

                $companyService->register($registration);

                $notification = $this->translator->trans('account_creation_success_message');
                $this->addFlash('success', $notification);

                return $this->redirect($requestedUrl);
            } catch (BadCredentials $e) { // Cela ne devrait jamais arriver puisqu'on vient de créer l'utilisateur
                $accountCreationErrorNotification = $this->translator->trans('account_creation_error_message');
                $this->addFlash('danger', $accountCreationErrorNotification);
            } catch (UserAlreadyExists $e) {
                $emailInUseErrorNotification = $this->translator->trans('email_already_in_use');
                $this->addFlash('danger', $emailInUseErrorNotification);
            }

            return $this->redirect($requestedUrl);
        }

        return $this->render('@App/auth/vendor-registration.html.twig');
    }

    private function registerAndAuthenticate(
        string $email,
        string $password,
        string $firstName = '',
        string $lastName = ''
    ): void {
        $userService = $this->get(UserService::class);
        $userService->register($email, $password, $firstName, $lastName);

        $apiKey = $this->get(ApiClient::class)->authenticate($email, $password);
        $user = new User($apiKey, $userService->getProfileFromId($apiKey->getId()));
        $token = new UsernamePasswordToken($user, null, 'register', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->start(); // Ensure the session exists
    }
}
