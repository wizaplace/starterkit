<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wizaplace\SDK\Catalog\AttributeVariant;
use Wizaplace\SDK\Catalog\ProductAttributeValue;

class AttributeVariantUrlGenerator
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param AttributeVariant|ProductAttributeValue $attributeVariant
     * @return string
     */
    public function generateAttributeVariantUrl($attributeVariant): string
    {
        if ($attributeVariant instanceof AttributeVariant) {
            return $this->generateAttributeVariantUrlFromAttributeVariant($attributeVariant);
        }
        if ($attributeVariant instanceof ProductAttributeValue) {
            return $this->generateAttributeVariantUrlFromProductAttributeValue($attributeVariant);
        }

        throw new \InvalidArgumentException('Cannot generate an url from given $attributeVariant');
    }

    public function generateAttributeVariantUrlFromAttributeVariant(AttributeVariant $attributeVariant): string
    {
        return $this->generateUrl($attributeVariant->getSlug());
    }

    public function generateAttributeVariantUrlFromProductAttributeValue(ProductAttributeValue $productAttributeValue): string
    {
        return $this->generateUrl($productAttributeValue->getSlug());
    }

    private function generateUrl(string $slug): string
    {
        return $this->urlGenerator->generate('attribute_variant', ['slug' => $slug]);
    }
}
