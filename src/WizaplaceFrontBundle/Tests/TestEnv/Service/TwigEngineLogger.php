<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\TestEnv\Service;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface decorator.
 * Logs all templates rendered, along with their params.
 */
class TwigEngineLogger implements EngineInterface
{
    /** @var EngineInterface */
    private $decoratedEngine;

    /** @var array */
    private $rendered = [];

    public function __construct(EngineInterface $decoratedEngine)
    {
        $this->decoratedEngine = $decoratedEngine;
    }

    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        $this->rendered[$view][] = [
            'view' => $view,
            'parameters' => $parameters,
            'response' => $response,
        ];

        return $this->decoratedEngine->renderResponse($view, $parameters, $response);
    }

    public function render($name, array $parameters = array())
    {
        $this->rendered[$name][] = [
            'view' => $name,
            'parameters' => $parameters,
        ];

        return $this->decoratedEngine->render($name, $parameters);
    }

    public function exists($name)
    {
        return $this->decoratedEngine->exists($name);
    }

    public function supports($name)
    {
        return $this->decoratedEngine->supports($name);
    }

    public function getRenderedData(): array
    {
        return $this->rendered;
    }
}
