<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\behat\contexts;

class HomePageContext extends AbstractPageContext
{
    /**
     * @When /^(?:|I )click on the category "(?P<categoryName>[^"]+)" under "(?P<rootCategoryName>[^"]+)" in the top menu$/
     */
    public function clickOnCategoryMenu(string $rootCategoryName, string $categoryName)
    {
        $topSelector = ".category-wrapper.inline a.category-name:contains('$rootCategoryName')";
        $categoryMenu = $this->waitForFind($topSelector);
        $categoryMenu->mouseOver();

        $subSelector = ":not(.category-sub-menu) .category-sub-menu a.category-name:contains('$categoryName')";
        $categoryItem = $this->waitForFind($subSelector);
        $categoryItem->click();
    }
}
