<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use WizaplaceFrontBundle\Controller\CmsController as BaseController;

class CmsController extends BaseController
{
    public function pageAction(string $slug): Response
    {
        return parent::pageAction($slug);
    }
}
