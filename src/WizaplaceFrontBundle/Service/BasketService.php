<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Wizaplace\SDK\Basket\Basket;
use Wizaplace\SDK\Basket\Comment;
use Wizaplace\SDK\Basket\PaymentInformation;

class BasketService
{
    private const ID_SESSION_KEY = '_basketId';

    /** @var  \Wizaplace\SDK\Basket\BasketService */
    private $baseService;

    /** @var SessionInterface */
    private $session;

    /** @var null|Basket */
    private $basket;

    public function __construct(\Wizaplace\SDK\Basket\BasketService $baseService, SessionInterface $session)
    {
        $this->baseService = $baseService;
        $this->session = $session;
    }

    public function getBasket(): Basket
    {
        $basketId = $this->getBasketId();
        if (!$this->basket) {
            try {
                $this->basket = $this->baseService->getBasket($basketId);
            } catch (ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    $this->forgetBasket();

                    return $this->getBasket();
                }
            }
        }

        return $this->basket;
    }

    public function addProductToBasket(string $declinationId, int $quantity): int
    {
        $this->basket = null;

        return $this->baseService->addProductToBasket($this->getBasketId(), $declinationId, $quantity);
    }

    public function removeProductFromBasket(string $declinationId): void
    {
        $this->basket = null;

        $this->baseService->removeProductFromBasket($this->getBasketId(), $declinationId);
    }

    public function cleanBasket(): void
    {
        $this->basket = null;

        $this->baseService->cleanBasket($this->getBasketId());
    }

    public function updateProductQuantity(string $declinationId, int $quantity): int
    {
        $this->basket = null;

        return $this->baseService->updateProductQuantity($this->getBasketId(), $declinationId, $quantity);
    }

    public function addCoupon(string $coupon): void
    {
        $this->basket = null;
        $this->baseService->addCoupon($this->getBasketId(), $coupon);
    }

    public function removeCoupon(string $coupon): void
    {
        $this->basket = null;
        $this->baseService->removeCoupon($this->getBasketId(), $coupon);
    }

    public function getPayments(): array
    {
        return $this->baseService->getPayments($this->getBasketId());
    }

    public function selectShippings(array $selections): void
    {
        $this->basket = null;
        $this->baseService->selectShippings($this->getBasketId(), $selections);
    }

    public function checkout(int $paymentId, bool $acceptTerms, string $redirectUrl): PaymentInformation
    {
        $this->basket = null;

        return $this->baseService->checkout($this->getBasketId(), $paymentId, $acceptTerms, $redirectUrl);
    }

    public function forgetBasket(): void
    {
        $this->basket = null;
        $this->session->remove(self::ID_SESSION_KEY);
    }

    /**
     * @param $comments Comment[]
     */
    public function updateComments(array $comments): void
    {
        $this->baseService->updateComments($this->getBasketId(), $comments);
    }

    /**
     * Gets current basket ID, or create a new one
     * @return string
     */
    private function getBasketId(): string
    {
        $basketId = $this->session->get(self::ID_SESSION_KEY);

        if (null === $basketId) {
            $basketId = $this->baseService->create();
            $this->session->set(self::ID_SESSION_KEY, $basketId);
        }

        return $basketId;
    }
}
