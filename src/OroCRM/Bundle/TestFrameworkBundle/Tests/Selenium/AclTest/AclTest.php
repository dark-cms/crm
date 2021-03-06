<?php

namespace OroCRM\Bundle\TestsBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class AclTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

    protected $newRole = array('ROLE_NAME' => 'NEW_ROLE_', 'LABEL' => 'Role_label_');

    protected function setUp()
    {
        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort(intval(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT));
        $this->setBrowser(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER);
        $this->setBrowserUrl(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL);
    }

    protected function tearDown()
    {
        $this->cookie()->clear();
    }

    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openNavigation()
            ->tab('System')
            ->menu('Roles')
            ->openRoles(false)
            ->add()
            ->setName($this->newRole['ROLE_NAME'] . $randomPrefix)
            ->setLabel($this->newRole['LABEL'] . $randomPrefix)
            ->setOwner('Main')
            ->selectAcl('Template controller')
            ->selectAcl('Contact groups manipulation')
            ->selectAcl('Contact manipulation')
            ->selectAcl('Account manipulation')
            ->selectAcl('Oro Security')
            ->save()
            ->assertMessage('Role successfully saved');

        return ($this->newRole['LABEL'] . $randomPrefix);
    }

    /**
     * @param $roleName
     * @depends testCreateRole
     * @return string
     */
    public function testCreateUser($roleName)
    {
        $username = 'User_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->add()
            ->assertTitle('Create User - Users - System')
            ->setUsername($username)
            ->setOwner('Main')
            ->enable()
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstname('First_'.$username)
            ->setLastname('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array($roleName))
            ->save()
            ->assertMessage('User successfully saved')
            ->toGrid()
            ->close()
            ->assertTitle('Users - System');

        return $username;
    }

    /**
     * @param $username
     * @depends testCreateUser
     */
    public function testUserAccess($username)
    {
        $login = new Login($this);
        $login->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        $login->moveto($login->byXPath("//div[@id='main-menu']/ul/li/a[contains(.,'System')]"));
        $login->assertElementNotPresent(
            "//div[@id='main-menu']/ul/li[a[contains(.,'System')]]//a[contains(.,'Users')]",
            'Element present so ACL for Users do not work'
        );
        $login->assertElementNotPresent(
            "//div[@id='main-menu']/ul/li[a[contains(.,'System')]]//a[contains(.,'Roles')]",
            'Element present so ACL for User Roles do not work'
        );
        $login->assertElementNotPresent(
            "//div[@id='main-menu']/ul/li[a[contains(.,'System')]]//a[contains(.,'Groups')]",
            'Element present so ACL for User Groups do not work'
        );
        $login->assertElementNotPresent(
            "//div[@id='main-menu']/ul/li[a[contains(.,'System')]]//a[contains(.,'Data Audit')]",
            'Element present so ACL for Data Audit do not work'
        );
        $login->assertElementNotPresent("//div[@id='search-div']", 'Element present so ACL for Search do not work');
        $login->byXPath("//ul[@class='nav pull-right']//a[@class='dropdown-toggle']")->click();
        $login->assertElementNotPresent("//ul[@class='dropdown-menu']//a[contains(., 'My User')]", 'Element present so ACL for Search do not work');
    }

    /**
     * @param $username
     * @depends testCreateUser
     */
    public function testUserAccessDirectUrl($username)
    {
        $login = new Login($this);
        $login->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openUsers()
            ->assertTitle('403 - Forbidden')
            ->openRoles()
            ->assertTitle('403 - Forbidden')
            ->openGroups()
            ->assertTitle('403 - Forbidden')
            ->openDataAudit()
            ->assertTitle('403 - Forbidden');
    }

    /**
     * @param $roleName
     * @depends testCreateRole
     */
    public function testEditRole($roleName)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openNavigation()
            ->tab('System')
            ->menu('Roles')
            ->openRoles(false)
            ->open(array($roleName))
            ->selectAcl('Contact groups manipulation')
            ->selectAcl('Contact manipulation')
            ->selectAcl('Account manipulation')
            ->selectAcl('View Account')
            ->selectAcl('View List of Accounts')
            ->selectAcl('View Contact')
            ->selectAcl('View List of Contacts')
            ->selectAcl('View contact group')
            ->selectAcl('View Contact Group List')
            ->selectAcl('View user user')
            ->selectAcl('Edit user')
            ->save()
            ->assertMessage('Role successfully saved');
    }

    /**
     * @param $username
     * @depends testCreateUser
     * @depends testEditRole
     */
    public function testViewAccountsContacts($username)
    {
        $login = new Login($this);
        $login->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openAccounts()
            ->assertTitle('Accounts - Customers')
            ->assertElementNotPresent("//div[@class='container-fluid']//a[@title='Create account']")
            ->openContacts()
            ->assertTitle('Contacts - Customers')
            ->assertElementNotPresent("//div[@class='container-fluid']//a[@title='Create contact']")
            ->openContactGroups()
            ->assertTitle('Contact Groups - Customers')
            ->assertElementNotPresent("//div[@class='container-fluid']//a[@title='Create contact group']")
            ->openAclCheck()
            ->assertAcl('account/create')
            ->assertAcl('contact/create')
            ->assertAcl('contact/group/create')
            ->assertAcl('contact/group/create');
    }

    /**
     * @return integer
     */
    public function testGetAdminId()
    {
        $username = 'admin';
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->filterBy('Username', $username)
            ->open(array($username));
        $array = explode('/', ($this->url()));
        $adminId = end($array);

        return $adminId;
    }

    /**
     * @param $username
     * @param $adminId
     * @depends testCreateUser
     * @depends testGetAdminId
     * @depends testEditRole
     */
    public function testViewUserInfo($username, $adminId)
    {
        $this->markTestSkipped('Due bug CRM-126');
        $login = new Login($this);
        $login->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openUser()
            ->viewInfo($username)
            ->openAclCheck()
            ->assertAcl('user/view/' . $adminId);
    }
}
