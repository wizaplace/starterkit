<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options = array(), LoggerInterface $logger = null, FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof InvalidCsrfTokenException) {
            $this->flashBag->add('warning', "L'action n'a pas pu être effectuée car elle a expirée, merci de réessayer.");
        } elseif ($exception instanceof BadCredentialsException) {
            $this->flashBag->add('danger', 'Identifiants invalides, merci de réessayer.');
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}
