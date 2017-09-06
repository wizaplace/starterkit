<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WizaplaceFrontBundle\Controller\NewsletterController as BaseController;

class NewsletterController extends BaseController
{
    public function subscribeAction(Request $request): JsonResponse
    {
        return parent::subscribeAction($request);
    }
}
