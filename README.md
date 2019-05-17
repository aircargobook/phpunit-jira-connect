# phpunit-jira-connect

Adds info to jira tickets if tests were successful or not. 

## install

    composer require aircargobook/phpunit-jira-connect

## jira setup 

In order for Jira to store data for every issue, we need to add a custom field. To do that, go to Settings and add new custom field, Tests..

https://YOUR_JIRA_URL.atlassian.net/secure/admin/ViewCustomFields.jspa


## usage

Add this to your tests/bootstrap.php

    /* load custom jira trait */
    define('JIRA_URL', 'https://YOUR_JIRA_URL.atlassian.net');
    define('JIRA_LOGIN', 'YOUR_EMAIL');
    define('JIRA_PASS', 'YOUR_PASSWORD');
    require dirname(__DIR__) . '/vendor/chobie/jira-api-restclient/src/Jira/Api.php';
    /* end of loading custom jira trait */

Then link any testcase you where you want to use the trait

    <?php
    namespace App\Test\TestCase\Controller;

    use Aircargobook\PhpunitJiraConnect\Traits\JiraConnectTrait;
    use Cake\TestSuite\IntegrationTestCase;

    class ExampleControllerTest extends IntegrationTestCase
    {
        use JiraConnectTrait;
        ...

Now you can continue linking your issues with the tests:

## linking tests to issues

just put this on top of the test you wrote

    /**
     * @test
     *
     * @ticket ACB-18
     *
     * @return void
     */

Inspired by this article, I found some day somewhere linked on a blog: https://kurapov.ee/eng/qa/jira_connect_ticket_annotations/