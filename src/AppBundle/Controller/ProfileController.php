<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WizaplaceFrontBundle\Controller\ProfileController as BaseController;

class ProfileController extends BaseController
{
    public function viewAction(): Response
    {
        return parent::viewAction();
    }

    public function addressesAction(): Response
    {
        return parent::addressesAction();
    }

    public function ordersAction(): Response
    {
        return parent::ordersAction();
    }

    public function returnsAction(): Response
    {
        return parent::returnsAction();
    }

    public function returnAction(int $orderReturnId): Response
    {
        return parent::returnAction($orderReturnId);
    }

    public function createOrderReturnAction(Request $request)
    {
        return parent::createOrderReturnAction($request);
    }

    public function afterSalesServiceAction(Request $request): Response
    {
        return parent::afterSalesServiceAction($request);
    }

    public function favoritesAction(): Response
    {
        return parent::favoritesAction();
    }

    public function updateProfileAction(Request $request)
    {
        return parent::updateProfileAction($request);
    }

    public function newsletterAction(): Response
    {
        return parent::newsletterAction();
    }
}
