<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\behat\contexts;

class GenericPageContext extends AbstractPageContext
{
    /**
     * @Then /^the page meta title should be "(?P<expectedTitle>[^"]+)"$/
     */
    public function assertPageMetaTitleEquals(string $expectedTitle)
    {
        $actualTitle = $this->getSession()->getPage()->find("css", "html > head > title")->getHtml();
        assert($expectedTitle == $actualTitle);
    }

    /**
     * @Then /^the page top title should be "(?P<expectedTitle>[^"]+)"$/
     */
    public function assertPageH1TitleEquals(string $expectedTitle)
    {
        $actualTitle = $this->getSession()->getPage()->find("css", "h1")->getHtml();
        assert($expectedTitle == $actualTitle);
    }

    /**
     * @Then /^the page meta description should contain "(?P<expectedDescription>[^"]+)"$/
     */
    public function assertPageMetaDescriptionContains(string $expectedDescription)
    {
        $actualDescription = $this->getSession()->getPage()->find("css", "html > head > meta[name=description]")->getHtml();
        assert(strpos($actualDescription, $expectedDescription) !== false);
    }
}
