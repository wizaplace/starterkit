<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use WizaplaceFrontBundle\Controller\HomeController as BaseController;

class HomeController extends BaseController
{
    /** @see \WizaplaceFrontBundle\Controller\HomeController::LATEST_PRODUCTS_MAX_COUNT */
    protected const LATEST_PRODUCTS_MAX_COUNT = 6;

    public function homeAction(): Response
    {
        return parent::homeAction();
    }
}
