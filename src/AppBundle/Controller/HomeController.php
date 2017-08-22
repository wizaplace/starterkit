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
    public function homeAction(): Response
    {
        return parent::homeAction();
    }
}
