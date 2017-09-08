<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\SDK\ApiClient;
use Wizaplace\SDK\Authentication\BadCredentials;
use Wizaplace\SDK\Discussion\DiscussionService;
use Wizaplace\SDK\Favorite\FavoriteService;
use Wizaplace\SDK\Order\Order;
use Wizaplace\SDK\Order\OrderService;
use Wizaplace\SDK\User\User as WizaplaceUser;
use Wizaplace\SDK\User\UserService;
use WizaplaceFrontBundle\Security\User;

class ProfileController extends Controller
{
    protected const PASSWORD_MINIMUM_LENGTH = 6;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function viewAction(): Response
    {
        return $this->render('@WizaplaceFront/profile/profile.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
        ]);
    }

    public function addressesAction(): Response
    {
        $user = $this->getUser()->getWizaplaceUser();
        $addressesAreIdentical = $user->getBillingAddress() === $user->getShippingAddress();

        return $this->render('@WizaplaceFront/profile/addresses.html.twig', [
            'profile' => $user,
            'addressesAreIdentical' => $addressesAreIdentical,
        ]);
    }

    public function ordersAction(): Response
    {
        $orders = $this->get(OrderService::class)->getOrders();

        return $this->render('@WizaplaceFront/profile/orders.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
            'orders' => $orders,
        ]);
    }

    public function returnsAction(): Response
    {
        return $this->render('@WizaplaceFront/profile/returns.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
        ]);
    }

    public function afterSalesServiceAction(): Response
    {
        $orders = $this->get(OrderService::class)->getOrders();
        $completedOrders = array_filter($orders, function (Order $order): bool {
            return $order->getStatus() === "COMPLETED";
        });

        return $this->render('@WizaplaceFront/profile/after-sales-service.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
            'orders' => $completedOrders,
        ]);
    }

    public function favoritesAction(): Response
    {
        $favorites = $this->get(FavoriteService::class)->getAll();

        return $this->render('@WizaplaceFront/profile/favorites.html.twig', [
            'favorites' => $favorites,
        ]);
    }

    public function updateProfileAction(Request $request)
    {
        $data = $request->request->get('user');
        $referer = $request->request->get('return_url') ?? $request->headers->get('referer');
        $requestedUrl = $request->request->get('requested_url') ?? $referer;
        $submittedToken = $request->request->get('csrf_token');
        $addressesAreIdentical = $request->request->getBoolean('addresses_are_identical');

        // CSRF token validation
        if (! $this->isCsrfTokenValid('profile_update_token', $submittedToken)) {
            $message = $this->translator->trans('csrf_error_message');
            $this->addFlash('warning', $message);

            return $this->redirect($referer);
        }

        // Override shipping address fields with billing ones if both are the same (else do nothing)
        if ($addressesAreIdentical) {
            // actual override
            $data['addresses']['shipping'] = $data['addresses']['billing'];
        }

        // update user's profile
        $user = new WizaplaceUser($data);
        $userService = $this->get(UserService::class);
        $userService->updateUser($user->getId(), $user->getEmail(), $user->getFirstname(), $user->getLastname());

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

        $message = $this->translator->trans('update_profile_success_message');
        $this->addFlash('success', $message);

        // update user's addresses
        if (! empty($data['addresses'])) {
            $userService->updateUserAdresses($user);

            $message = $this->translator->trans('update_addresses_success_message');
            $this->addFlash('success', $message);
        }

        return $this->redirect($requestedUrl);
    }

    public function discussionsAction(): Response
    {
        $discussionService = $this->get(DiscussionService::class);
        $discussions = $discussionService->getDiscussions();

        return $this->render('@App/profile/discussions.html.twig', [
            'profile' => $this->getUser()->getWizaplaceUser(),
            'discussions' => $discussions,
        ]);
    }

    public function discussionAction(int $id): Response
    {
        $discussionService = $this->get(DiscussionService::class);

        $discussion = $discussionService->getDiscussion($id);
        $messages = $discussionService->getMessages($id);

        return $this->render('@App/profile/discussion.html.twig', [
            'discussion' => $discussion,
            'messages' => $messages,
            'profile' => $this->getUser()->getWizaplaceUser(),
        ]);
    }

    // This method sole purpose is the return type hint.
    protected function getUser(): User
    {
        return parent::getUser();
    }
}
