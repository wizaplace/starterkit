<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\behat\contexts;

use Behat\Mink\Element\ElementInterface;
use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\RawMinkContext;

abstract class PageContext extends RawMinkContext
{
    /**
     * Finds a DOM node by its CSS selector.
     */
    protected function waitForFind(string $cssSelector, int $timeoutInSeconds = 2, ?NodeElement $searchScope = null): NodeElement
    {
        try {
            return $this->waitForX(function (ElementInterface $searchScope) use ($cssSelector) : ?NodeElement {
                return $searchScope->find('css', $cssSelector);
            }, $timeoutInSeconds, $searchScope);
        } catch (Timeout $e) {
            throw new \Exception("No element found with selector '$cssSelector'", 0, $e);
        }
    }

    private function waitForX(callable $getter, int $timeoutInSeconds = 2, ?NodeElement $scope = null)
    {
        if (is_null($scope)) {
            $scope = $this->getSession()->getPage();
        }

        $result = $scope->waitFor($timeoutInSeconds, function (ElementInterface $scope) use ($getter) {
            return $getter($scope);
        });

        if (!$result) {
            throw new Timeout("Waited $timeoutInSeconds second(s), but still no result");
        }

        return $result;
    }
}
