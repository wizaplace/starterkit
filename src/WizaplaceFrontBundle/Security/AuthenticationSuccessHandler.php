<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(HttpUtils $httpUtils, array $options = array(), FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
        parent::__construct($httpUtils, $options);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $this->flashBag->add('success', 'Vous vous êtes connecté avec succès.');

        return parent::onAuthenticationSuccess($request, $token);
    }
}
