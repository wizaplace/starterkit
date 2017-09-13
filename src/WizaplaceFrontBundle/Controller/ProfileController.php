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
use Wizaplace\SDK\Order\CreateOrderReturn;
use Wizaplace\SDK\Order\Order;
use Wizaplace\SDK\Order\OrderService;
use Wizaplace\SDK\Order\OrderStatus;
use Wizaplace\SDK\User\UpdateUserAddressCommand;
use Wizaplace\SDK\User\UpdateUserAddressesCommand;
use Wizaplace\SDK\User\UpdateUserCommand;
use Wizaplace\SDK\User\UserService;
use Wizaplace\SDK\User\UserTitle;
use WizaplaceFrontBundle\Security\User;

class ProfileController extends Controller
{
    protected const PASSWORD_MINIMUM_LENGTH = 6;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var UserService */
    protected $userService;

    public function __construct(TranslatorInterface $translator, UserService $userService)
    {
        $this->translator = $translator;
        $this->userService = $userService;
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
        $orderService = $this->get(OrderService::class);
        $orders = $orderService->getOrders();

        $validOrders = array_filter($orders, function (Order $order) {
            return $order->getStatus() !== OrderStatus::COMPLETED();
        });
        $reasons = $orderService->getReturnReasons();
        $returns = $orderService->getOrderReturns();

        return $this->render('@WizaplaceFront/profile/returns.html.twig', [
            'orders' => $validOrders,
            'reasons' => $reasons,
            'returns' => $returns,
        ]);
    }

    public function returnAction(int $orderReturnId): Response
    {
        $orderService = $this->get(OrderService::class);
        $orderReturn = $orderService->getOrderReturn($orderReturnId);
        $returnReasons = $orderService->getReturnReasons();

        return $this->render('@WizaplaceFront/profile/return.html.twig', [
            'orderReturn' => $orderReturn,
            'returnReasons' => $returnReasons,
        ]);
    }

    public function createOrderReturnAction(Request $request)
    {
        $orderService = $this->get(OrderService::class);
        $order = $orderService->getOrder((int) $request->get('order_id'));
        $createOrderReturn = new CreateOrderReturn((int) $request->get('order_id'), $request->get('return_message'));
        $selectedItems = array_keys($request->request->get('return')['selected']);
        $selectedReasons = $request->request->get('return')['reasons'];

        foreach ($order->getOrderItems() as $orderItem) {
            $id = $orderItem->getDeclinationId();
            if (in_array($id, $selectedItems)) {
                $createOrderReturn->addItem($id, (int) $selectedReasons[$id], $orderItem->getAmount());
            }
        }
        $orderService->createOrderReturn($createOrderReturn);

        return $this->redirectToRoute('profile_returns');
    }

    public function afterSalesServiceAction(): Response
    {
        $orders = $this->get(OrderService::class)->getOrders();
        $completedOrders = array_filter($orders, function (Order $order): bool {
            return $order->getStatus()->equals(OrderStatus::COMPLETED());
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
        $userService = $this->get(UserService::class);
        $updateUserCommand = new UpdateUserCommand();
        $updateUserCommand
            ->setUserId($this->getUser()->getWizaplaceUser()->getId())
            ->setEmail($data['email'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setTitle(empty($data['title']) ? null : new UserTitle($data['title']));
        $userService->updateUser($updateUserCommand);

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
                $api->authenticate($data['email'], $oldPassword);
            } catch (BadCredentials $e) {
                $message = $this->translator->trans('update_old_password_error_message');
                $this->addFlash('danger', $message);

                return $this->redirect($referer);
            }

            $userService->changePassword($this->getUser()->getWizaplaceUser()->getId(), $newPassword);

            // add a notification
            $message = $this->translator->trans('update_password_success_message');
            $this->addFlash('success', $message);
        }

        $message = $this->translator->trans('update_profile_success_message');
        $this->addFlash('success', $message);

        // update user's addresses
        if (! empty($data['addresses'])) {
            $this->updateUserAdresses($data);

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

    final protected function updateUserAdresses(array $data): void
    {
        $shippingAddress = new UpdateUserAddressCommand();
        $shippingAddress
            ->setFirstName($data['addresses']['shipping']['firstName'])
            ->setLastName($data['addresses']['shipping']['lastName'])
            ->setCompany($data['addresses']['shipping']['company'])
            ->setPhone($data['addresses']['shipping']['phone'])
            ->setAddress($data['addresses']['shipping']['address'])
            ->setAddressSecondLine($data['addresses']['shipping']['address_2'])
            ->setZipCode($data['addresses']['shipping']['zipcode'])
            ->setCity($data['addresses']['shipping']['city'])
            ->setCountry($data['addresses']['shipping']['country'])
            ->setTitle(new UserTitle($data['addresses']['shipping']['title']));
        $billingAddress = new UpdateUserAddressCommand();
        $billingAddress
            ->setFirstName($data['addresses']['billing']['firstName'])
            ->setLastName($data['addresses']['billing']['lastName'])
            ->setCompany($data['addresses']['billing']['company'])
            ->setPhone($data['addresses']['billing']['phone'])
            ->setAddress($data['addresses']['billing']['address'])
            ->setAddressSecondLine($data['addresses']['billing']['address_2'])
            ->setZipCode($data['addresses']['billing']['zipcode'])
            ->setCity($data['addresses']['billing']['city'])
            ->setCountry($data['addresses']['billing']['country'])
            ->setTitle(new UserTitle($data['addresses']['billing']['title']));
        $updateUserAddressesCommand = new UpdateUserAddressesCommand();
        $updateUserAddressesCommand
            ->setUserId($this->getUser()->getWizaplaceUser()->getId())
            ->setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
        ;

        $this->userService->updateUserAdresses($updateUserAddressesCommand);
    }
}
