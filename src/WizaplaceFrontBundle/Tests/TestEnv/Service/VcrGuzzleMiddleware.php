<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\TestEnv\Service;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use VCR\Configuration;
use VCR\Request;
use VCR\VCRFactory;
use VCR\Videorecorder;

class VcrGuzzleMiddleware
{
    /** @var int */
    private $index = 0;

    /** @var Videorecorder */
    private $vcr;

    public function __construct(Configuration $vcrConfig)
    {
        $this->vcr = VCRFactory::getInstance($vcrConfig)->getOrCreate('VCR\Videorecorder');
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) {
            $request = $request->withHeader('VCR-index', (string) $this->index)->withoutHeader('User-Agent');
            $this->index++;

            $vcrRequest = new Request(
                $request->getMethod(),
                (string) $request->getUri(),
                array_map(static function (array $headers) : string {
                    return reset($headers);
                }, $request->getHeaders())
            );
            if ($request->getBody()->getSize() > 0) {
                $vcrRequest->setBody($request->getBody()->getContents());
            }
            $vcrResponse = $this->vcr->handleRequest($vcrRequest);

            $psr7Response = new Response(
                (int) $vcrResponse->getStatusCode(),
                $vcrResponse->getHeaders(),
                $vcrResponse->getBody(),
                $vcrResponse->getHttpVersion(),
                $vcrResponse->getStatusMessage()
            );

            return new FulfilledPromise($psr7Response);
        };
    }

    public function getVcr(): Videorecorder
    {
        return $this->vcr;
    }
}
