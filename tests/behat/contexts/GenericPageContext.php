<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\behat\contexts;

use Assert\Assertion;
use Behat\Mink\Exception\DriverException;

class GenericPageContext extends AbstractPageContext
{
    /**
     * @Given I am not logged in
     */
    public function givenIAmNotLoggedIn()
    {
        try {
            $this->getSession()->setCookie('PHPSESSID', null);
        } catch (DriverException $e) {
            // If we get an exception because no page was visited, it means we were not logged in.
            if ($e->getMessage() !== 'Unable to access the request before visiting a page') {
                throw $e;
            }
        }
    }

    /**
     * @Given /^I am logged in as "(?P<user>[^"]+)" with password "(?P<password>[^"]+)"$/
     */
    public function givenIAmLoggedIn(string $user, string $password)
    {
        try {
            $this->visitPath("/login");
            $this->waitForFind('input[name=email]')->setValue($user);
            $this->waitForFind('input[name=password]')->setValue($password);
            $this->waitForFind('button[type=submit].validate')->click();
            $this->assertSession()->statusCodeEquals(200);
        } catch (\Exception $e) {
            throw new \Exception("Failed to login", $e->getCode(), $e);
        }
    }

    /**
     * @Then /^the page meta title should be "(?P<expectedTitle>[^"]+)"$/
     */
    public function assertPageMetaTitleEquals(string $expectedTitle)
    {
        $actualTitle = $this->getSession()->getPage()->find("css", "html > head > title")->getText();
        Assertion::eq($actualTitle, $expectedTitle);
    }

    /**
     * @Then /^the page top title should be "(?P<expectedTitle>[^"]+)"$/
     */
    public function assertPageH1TitleEquals(string $expectedTitle)
    {
        $actualTitle = $this->getSession()->getPage()->find("css", "h1")->getText();
        Assertion::eq($actualTitle, $expectedTitle);
    }

    /**
     * @Then /^the page meta description should contain "(?P<expectedDescription>[^"]+)"$/
     */
    public function assertPageMetaDescriptionContains(string $expectedDescription)
    {
        $actualDescription = $this->getSession()->getPage()->find("css", "html > head > meta[name=description]")
            ->getAttribute('content');
        Assertion::contains($actualDescription, $expectedDescription);
    }
}
