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
use WizaplaceFrontBundle\Controller\DiscussionController as BaseController;

class DiscussionController extends BaseController
{
    public function createAction(Request $request): Response
    {
        return parent::createAction($request);
    }

    public function createMessageAction(Request $request): Response
    {
        return parent::createMessageAction($request);
    }
}
