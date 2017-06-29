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
use Wizaplace\Order\OrderService;
use Wizaplace\User\User as WizaplaceUser;
use Wizaplace\User\UserService;

class ProfileController extends Controller
{
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

    public function savAction(): Response
    {
        return $this->render('profile/sav.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
        ]);
    }

    public function updateProfileAction(Request $request)
    {
        $data = $request->request->get('user');
        $sameAddress = $request->request->get('sameAddress');

        // Si l'adresse de facturation et de livraison sont différentes, on peut laisser
        // tel quel, sinon, on remplace les champs de shipping par ceux de billing
        if ($sameAddress) {
            $data['addresses']['shipping'] = $data['addresses']['billing'];

            //Petite manip pour les champs de profil qui ont un Id qui est different
            // dans billing et shipping
            $data['addresses']['shipping'][38] = $data['addresses']['shipping'][37];
            unset($data['addresses']['shipping'][37]);
            $data['addresses']['shipping'][40] = $data['addresses']['shipping'][39];
            unset($data['addresses']['shipping'][39]);
        }
        $user = new WizaplaceUser($data);

        $userService = $this->get(UserService::class);
        $userService->updateUser($user);
        $userService->updateUserAdresses($user);

        $referer =  $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function updateBillingAddressAction(Request $request)
    {
        $userService = $this->get(UserService::class);

        $userId = $request->request->get('user[id]');
        $user = $this->getUser();
        $userData = $request->request->get('user');
    }

    protected function getUser(): User
    {
        // This method is just here for the return type hint.
        return parent::getUser();
    }
}
