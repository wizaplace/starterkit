<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\behat\contexts;

class HomePageContext extends PageContext
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
}
