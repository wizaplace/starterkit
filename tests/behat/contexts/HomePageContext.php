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

class HomePageContext extends RawMinkContext
{
    /**
     * @When /^(?:|I )click on the category "(?P<categoryName>[^"]+)" under "(?P<rootCategoryName>[^"]+)" in the top menu$/
     */
    public function clickOnCategoryMenu(string $rootCategoryName, string $categoryName)
    {
        $topSelector = "#categories-menu > li > a:contains('$rootCategoryName')";
        $categoryMenu = $this->find($topSelector);
        $categoryMenu->click();

        $subSelector = $topSelector." + .dropdown-menu a:contains('$categoryName')";
        $categoryItem = $this->find($subSelector);
        $categoryItem->click();
    }

    /**
     * Finds an DOM node by its CSS selector.
     * @TODO: put it in some trait or abstract context
     */
    private function find(string $cssSelector, int $timeoutInSeconds = 1): NodeElement
    {
        $element = $this->getSession()->getPage()
            ->waitFor($timeoutInSeconds, function (ElementInterface $page) use ($cssSelector) {
                return $page->find('css', $cssSelector);
            });

        if (is_null($element)) {
            throw new \Exception("Element '$cssSelector' not found in page, even after waiting $timeoutInSeconds second(s)'");
        }

        return $element;
    }
}
