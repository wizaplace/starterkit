<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use WizaplaceFrontBundle\Controller\CategoryController as BaseController;

class CategoryController extends BaseController
{
    public function viewAction(string $slug) : Response
    {
        return parent::viewAction($slug);
    }
}
