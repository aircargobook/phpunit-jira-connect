<?php
namespace Aircargobook\PhpunitJiraConnect\Test\Traits;

use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\Basic;
use ReflectionMethod;
use stdClass;
use Throwable;

trait JiraConnectTrait
{
    /** @var mixed */
    private $ticket;

    /** @var mixed */
    private $currentTest;

//    private $failed = false;

    /** @var mixed */
    public static $testResults = [
        //ticket=>text
    ];

    /**
     * Store test results in file for cross-class results
     *
     * @param mixed $status
     *
     * @return array
     */
    public function saveJira($status)
    {
        if (!$this->ticket) {
            return;
        }

        $storage = __DIR__ . '/ticket_status.json';

        if (!file_exists($storage)) {
            touch($storage);
        }

        $s = json_decode(file_get_contents($storage), true);

        if (!isset($s['testResults'][$this->ticket])) {
            $s['testResults'][$this->ticket] = [];
        }
        $s['testResults'][$this->ticket][$this->currentTest] = ($status ? 'ok' : 'fail');

        $a = file_put_contents($storage, json_encode($s));
    }

    /**
     * Store test results in file for cross-class results
     *
     * @param mixed $filePath
     * @param mixed $unlinkFile
     * @param mixed $force
     *
     * @return array
     */
    public static function updateJiraTestStatus($filePath = 'ticket_status.json', $unlinkFile = true, $force = false)
    {
        //check if environment is staging
        // if ((!defined('UPDATE_JIRA_TEST_STATUS') || empty(UPDATE_JIRA_TEST_STATUS)) && !$force) {
        //  return;
        // }


        $api = new Api(
            JIRA_URL,
            new Basic(JIRA_LOGIN, JIRA_PASS)
        );

        $storage = __DIR__ . '/ticket_status.json';
        $s = json_decode(file_get_contents($storage), true);

        foreach ($s['testResults'] as $ticket => $testResults) {
            $sResult = "";

            foreach ($testResults as $class => $status) {
                      $sResult .= $status . ($class != 'gilink'? " — " . $class : '') . "\n";
            }

            $updObj = new stdClass();
            /* IMPORTANT ID! */
            $updObj->customfield_10300 = [
                ['set' => $sResult],
            ];

            $r = $api->editIssue($ticket, [
                "update" => $updObj,
            ]);
        }

        // if($unlinkFile) {
        //  unlink($storage);
        // }
    }

    /**
     * @param mixed $class
     * @param mixed $method
     *
     * @return array
     */
    public function usingMethod($class, $method)
    {
        $this->ticket = $this->getTicket($class, $method);
        $this->currentTest = $class . '::' . $method;

        return $this;
    }

    /**
     * @param mixed $class
     * @param mixed $method
     *
     * @return array
     */
    public function getTicket($class, $method)
    {
        $r = new ReflectionMethod($class, $method);
        $doc = $r->getDocComment();

        preg_match_all('#@ticket (.*?)\n#s', $doc, $annotations);

        if (isset($annotations[1][0])) {
            return $annotations[1][0];
        }
    }

    /**
     * Overwrites standard tearDown
     *
     * @return array
     */
    public function tearDown()
    {
        $className = explode('::', $this->toString())[0];
        $this->usingMethod($className, $this->getName());
        $this->saveJira(!$this->hasFailed());
    }

    /**
     * Overwrites tearDownAfterClass
     *
     * @return array
     */
    public static function tearDownAfterClass()
    {
        self::updateJiraTestStatus();
    }

    /**
     * @param \Throwable $e
     *
     * @throws \Throwable
     *
     * @return array
     */
    public function onNotSuccessfulTest(Throwable $e)
    {
        if (method_exists($e, 'getComparisonFailure') && $e->getComparisonFailure()) {
            $trace = $e->getComparisonFailure()->getTrace();
        } elseif (method_exists($e, 'getSerializableTrace')) {
            $trace = $e->getSerializableTrace();
        }

        if (isset($trace)) {
            $method = $trace[4]['function'];
            $class = $trace[4]['class'];

            $this->usingMethod($class, $method)->saveJira(false);
        }
        throw $e;
    }
}
