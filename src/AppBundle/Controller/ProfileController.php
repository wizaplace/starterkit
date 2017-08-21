<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\ApiClient;
use Wizaplace\Authentication\BadCredentials;
use Wizaplace\Order\OrderService;
use Wizaplace\User\User as WizaplaceUser;
use Wizaplace\User\UserService;
use WizaplaceFrontBundle\Security\User;

class ProfileController extends Controller
{
    private const PASSWORD_MINIMUM_LENGTH = 6;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function viewAction(): Response
    {
        return $this->render('profile/profile.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
        ]);
    }

    public function addressesAction(): Response
    {
        return $this->render('profile/addresses.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
        ]);
    }

    public function ordersAction(): Response
    {
        $orders = $this->get(OrderService::class)->getOrders();

        return $this->render('profile/orders.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
            'orders' => $orders,
        ]);
    }

    public function returnsAction(): Response
    {
        return $this->render('profile/returns.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
        ]);
    }

    public function updateProfileAction(Request $request)
    {
        $data = $request->request->get('user');
        $sameAddress = $request->request->get('sameAddress');
        $referer = $request->headers->get('referer');
        $submittedToken = $request->get('csrf_token');

        $user = new WizaplaceUser($data);
        $userService = $this->get(UserService::class);

        // CSRF token validation
        if (! $this->isCsrfTokenValid('profile_update_token', $submittedToken)) {
            $message = $this->translator->trans('csrf_error_message');
            $this->addFlash('warning', $message);

            return $this->redirect($referer);
        }

        // update user's password
        if (! empty($data['password'])) {
            $newPassword = $data['password']['new'];

            // check new password corresponds to password rules
            if (strlen($newPassword) < self::PASSWORD_MINIMUM_LENGTH) {
                $message = $this->translator->trans('update_new_password_error_message', ['%n%' => self::PASSWORD_MINIMUM_LENGTH]);
                $this->addFlash('danger', $message);

                return $this->redirect($referer);
            }

            // check user's old credentials
            $oldPassword = $data['password']['old'];
            $api = $this->get(ApiClient::class);

            try {
                $api->authenticate($user->getEmail(), $oldPassword);
            } catch (BadCredentials $e) {
                $message = $this->translator->trans('update_old_password_error_message');
                $this->addFlash('danger', $message);

                return $this->redirect($referer);
            }

            $userService->changePassword($user->getId(), $newPassword);

            // add a notification
            $message = $this->translator->trans('update_password_success_message');
            $this->addFlash('success', $message);
        }

        // update user's profile
        $userService->updateUser($user->getId(), $user->getEmail(), $user->getFirstname(), $user->getLastname());

        $message = $this->translator->trans('update_profile_success_message');
        $this->addFlash('success', $message);

        // Override shipping address fields with billing ones if both are the same (else do nothing)
        if ($sameAddress) {
            // actual override
            $data['addresses']['shipping'] = $data['addresses']['billing'];

            // Petite manip pour les champs de profil qui ont un Id qui est different dans billing et shipping
            $data['addresses']['shipping'][38] = $data['addresses']['shipping'][37];
            unset($data['addresses']['shipping'][37]);
            $data['addresses']['shipping'][40] = $data['addresses']['shipping'][39];
            unset($data['addresses']['shipping'][39]);
        }

        // update user's addresses
        if (! empty($data['addresses'])) {
            $userService->updateUserAdresses($user);

            $message = $this->translator->trans('update_addresses_success_message');
            $this->addFlash('success', $message);
        }

        return $this->redirect($referer);
    }

    // This method sole purpose is the return type hint.
    protected function getUser(): User
    {
        return parent::getUser();
    }
}
