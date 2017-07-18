<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests;

use Psr\Http\Message\RequestInterface;

class VcrGuzzleMiddleware
{
    private $index = 0;

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $request->withHeader('VCR-index', $this->index);
            $this->index++;

            return $handler($request, $options);
        };
    }
}
