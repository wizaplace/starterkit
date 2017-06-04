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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wizaplace\Order\Order;
use Wizaplace\Order\OrderService;
use Wizaplace\User\ApiKey;
use Wizaplace\User\User;
use Wizaplace\User\UserService;

class ProfileController extends Controller
{
    public function viewAction(): Response
    {
        $session = $this->get('session');
        if ($session->has(AuthController::API_KEY)) {
            $apiKey = $session->get(AuthController::API_KEY);
            $profile = $this->get(UserService::class)->getProfileFromId($apiKey->getId(), $apiKey);

            return $this->render('legacy/profile/profile.html.twig', ['profile' => $profile]);
        }
        throw new NotFoundHttpException();
    }

    public function addressesAction(): Response
    {
        $session = $this->get('session');
        if ($session->has(AuthController::API_KEY)) {
            $apiKey = $session->get(AuthController::API_KEY);
            $profile = $this->get(UserService::class)->getProfileFromId($apiKey->getId(), $apiKey);

            return $this->render('legacy/profile/addresses.html.twig', ['profile' => $profile]);
        }
        throw new NotFoundHttpException();
    }

    public function ordersAction(): Response
    {
        $session = $this->get('session');
        if ($session->has(AuthController::API_KEY)) {
            $vendorId = $this->get('kernel')->getVendorId();
            $apiKey = $session->get(AuthController::API_KEY);
            $profile = $this->get(UserService::class)->getProfileFromId($apiKey->getId(), $apiKey);

            $orders = $this->get(OrderService::class)->getOrders($this->getApiKey());
            $orders = array_filter(
                $orders,
                function (Order $order) use ($vendorId) {
                    return $vendorId == $order->getCompanyId();
                }
            );

            return $this->render('legacy/profile/orders.html.twig', ['profile' => $profile, 'orders' => $orders]);
        }
        throw new NotFoundHttpException();
    }

    public function returnsAction(): Response
    {
        $session = $this->get('session');
        if ($session->has(AuthController::API_KEY)) {
            $apiKey = $session->get(AuthController::API_KEY);
            $profile = $this->get(UserService::class)->getProfileFromId($apiKey->getId(), $apiKey);

            return $this->render('legacy/profile/returns.html.twig', ['profile' => $profile]);
        }
        throw new NotFoundHttpException();
    }

    public function savAction(): Response
    {
        $session = $this->get('session');
        if ($session->has(AuthController::API_KEY)) {
            $apiKey = $session->get(AuthController::API_KEY);
            $profile = $this->get(UserService::class)->getProfileFromId($apiKey->getId(), $apiKey);

            return $this->render('legacy/profile/sav.html.twig', ['profile' => $profile]);
        }
        throw new NotFoundHttpException();
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
        $user = new User($data);

        $userService = $this->get(UserService::class);
        $userService->updateUser($user, $this->getApiKey());
        $userService->updateUserAdresses($user, $this->getApiKey());

        $referer =  $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function updateBillingAddressAction(Request $request)
    {
        $userService = $this->get(UserService::class);

        $userId = $request->request->get('user[id]');
        $user = $userService->getProfileFromId($userId, $this->getApiKey());
        $userData = $request->request->get('user');
    }

    private function getApiKey(): ApiKey
    {
        return $this->get('session')->get(\AppBundle\Controller\AuthController::API_KEY);
    }
}
