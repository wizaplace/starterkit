<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class MonitoringController extends Controller
{
    public function versionAction(): JsonResponse
    {
        return new JsonResponse([
            'deployId' => \file_get_contents($this->getParameter('kernel.project_dir').'/var/deployID') ?: null,
        ]);
    }
}
