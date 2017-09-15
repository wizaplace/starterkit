<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */

namespace WizaplaceFrontBundle\Tests\TestEnv\Controller;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ExceptionController extends \Symfony\Bundle\TwigBundle\Controller\ExceptionController
{
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        // Force txt format so we can display the page content in the test output without getting flooded by HTML
        $request->setRequestFormat('txt');

        return parent::showAction($request, $exception, $logger);
    }
}
