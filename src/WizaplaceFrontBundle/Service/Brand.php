<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use Psr\Http\Message\UriInterface;

class Brand
{
    /** @var string */
    private $name;
    /** @var UriInterface */
    private $url;

    public function __construct(string $name, UriInterface $url)
    {
        $this->name = $name;
        $this->url = $url;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): UriInterface
    {
        return $this->url;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
