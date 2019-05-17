# phpunit-jira-connect

** internal test only **


## usage

Add this to your tests/bootstrap.php

    /* load custom jira trait */
    define('JIRA_URL', 'https://YOUR_URL.atlassian.net');
    define('JIRA_LOGIN', 'YOUR_EMAIL');
    define('JIRA_PASS', 'YOUR_PASSWORD');
    require dirname(__DIR__) . '/vendor/chobie/jira-api-restclient/src/Jira/Api.php';
    /* end of loading custom jira trait */