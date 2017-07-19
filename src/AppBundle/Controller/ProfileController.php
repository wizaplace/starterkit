<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use AppBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\Order\OrderService;
use Wizaplace\User\User as WizaplaceUser;
use Wizaplace\User\UserService;

class ProfileController extends Controller
{
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

        // CSRF token validation
        if (! $this->isCsrfTokenValid('profile_update_token', $submittedToken)) {
            $message = $this->translator->trans('recaptcha_error_message');
            $this->addFlash('warning', $message);

            return $this->redirect($referer);
        }

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

        $user = new WizaplaceUser($data);

        // update user's profile
        $userService = $this->get(UserService::class);
        $userService->updateUser($user);

        // update user's addresses
        if (! empty($data['addresses'])) {
            $userService->updateUserAdresses($user);
        }

        // TODO: validate and update user's password
        if (! empty($data['password'])) {
            $oldPassword = $data['password']['old'];
            $newPassword = $data['password']['new'];
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    // This method sole purpose is the return type hint.
    protected function getUser(): User
    {
        return parent::getUser();
    }
}
