<?php

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }
    /**
     * @Given I register a temporary email address at https://www.guerrillamail.com
     */
    public function registerTempEmail()
    {
        $this->visit('https://www.guerrillamail.com');
    }
    /**
     * @Given I register a Salesforce Developer account at https:\/\/developer.salesforce.com\/signup
     */
    public function registerSalesforceDevEmail()
    {
        $tempEmail = $this->getTempEmail();
        $session = $this->getSession();
        $page = $session->getPage();
        $session->visit('https://developer.salesforce.com/signup');
        $this->signupSalesforce('testname','testname', $tempEmail, 'testcompany',
            '12345', $tempEmail);
        $session->wait(8000, $page->hasContent('Almost there...'));
        $session->back();
    }

    /**
     * @Given I click the link in my temporary email to confirm my developer account
     */
    public function clickTempEmailLink()
    {
        $session = $this->getSession();
        $session->back();
        $page = $session->getPage();
        $dev_email_xpath = '//*[@id="email_list"]/tr[1]/td[2][contains(text(),\'developer@salesforce.com\')]';
        $session->wait(12000, $page->has('xpath', $dev_email_xpath));
        $page->find('xpath', $dev_email_xpath)->press();
        $session->wait(2000, $page->hasLink('Verify Account'));
        $this->clickLink('Verify Account');
        $windowNames = $session->getWindowNames();
        $session->switchToWindow($windowNames[1]);
    }

    /**
     * @When I complete the registration process by setting a password
     */
    public function setPassword()
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $session->wait(3000, $page->has('css','#newpassword'));
        $this->setUserPassword('abcd12345','abcd12345','answer');
    }

    /**
     * @Then I should be on the Salesforce Developer instance homepage
     */
    public function atSalesforceDevHomepage()
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $session->wait(5000, $page->hasContent('Home'));
    }

    private function getTempEmail()
    {
        $session = $this->getSession();
        $page = $session->getPage();
        return $page->find('css', '#email-widget')->getText();
    }

    public function signupSalesforce($firstName, $lastName, $email, $company, $postalCode, $username)
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $checkBox = $page->find('css', '#eula');
        $checkBox->click();

        $this->fillField('first_name',$firstName);
        $this->fillField('last_name',$lastName);
        $this->fillField('email',$email);
        $this->fillField('company',$company);
        $this->fillField('postal_code',$postalCode);
        $this->fillField('username',$username);

        //wait for captcha
        if ($page->hasContent('recaptcha')) {
            $session->wait(10000);
        }

        $signMeUpButton = $page->find('css', '#submit_btn');
        $signMeUpButton->click();
    }

    public function setUserPassword($newPassword, $confirmPassword, $answer)
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $this->fillField('newpassword', $newPassword);
        $this->fillField('confirmpassword', $confirmPassword);
        $this->fillField('answer', $answer);

        $passwordButton = $page->find('css','#password-button');
        $passwordButton->click();
    }
}
