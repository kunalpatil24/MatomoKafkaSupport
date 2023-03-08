<?php

namespace Piwik\Plugins\MatomoKafkaSupport\Tracker;

use Piwik\Common;
use Piwik\SettingsPiwik;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Request;
use Piwik\Tracker;
use Piwik\Tracker\Visit\VisitProperties;


use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer;
use RdKafka\TopicConf;



class MatomoKafkaSupportRequestProcessor extends Tracker\RequestProcessor
{
	
    /**
     * This is the first method called when processing a tracker request.
     *
     * Derived classes can use this method to manipulate a tracker request before the request
     * is handled. Plugins could change the URL, add custom variables, etc.
     *
     * @param Request $request
     */
    public function manipulateRequest(Request $request)
    {
        // empty
    }

    /**
     * This is the second method called when processing a tracker request.
     *
     * Derived classes should use this method to set request metadata based on the tracking
     * request alone. They should not try to access request metadata from other plugins,
     * since they may not be set yet.
     *
     * When this method is called, `$visitProperties->visitorInfo` will be empty.
     *
     * @param VisitProperties $visitProperties
     * @param Request $request
     * @return bool If `true` the tracking request will be aborted.
     */
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        return false;
    }

    /**
     * This is the third method called when processing a tracker request.
     *
     * Derived classes should use this method to set request metadata that needs request metadata
     * from other plugins, or to override request metadata from other plugins to change
     * tracking behavior.
     *
     * When this method is called, you can assume all available request metadata from all plugins
     * will be initialized (but not at their final value). Also, `$visitProperties->visitorInfo`
     * will contain the values of the visitor's last known visit (if any).
     *
     * @param VisitProperties $visitProperties
     * @param Request $request
     * @return bool If `true` the tracking request will be aborted.
     */
    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {

        return false;
    }

    /**
     * This method is called before recording a new visit. You can set/change visit information here
     * to change what gets inserted into `log_visit`.
     *
     * Only implement this method if you cannot use a Dimension for the same thing.
     * 
     * Please note that the `onNewAction` hook in an action dimension is executed after this method.
     *
     * @param VisitProperties $visitProperties
     * @param Request $request
     */
    public function onNewVisit(VisitProperties $visitProperties, Request $request)
    {
        // empty
    }

    /**
     * This method is called before updating an existing visit. You can set/change visit information
     * here to change what gets recorded in `log_visit`.
     *
     * Only implement this method if you cannot use a Dimension for the same thing.
     *
     * Please note that the `onNewAction` hook in an action dimension is executed before this method.
     *
     * @param array &$valuesToUpdate
     * @param VisitProperties $visitProperties
     * @param Request $request
     */
    public function onExistingVisit(&$valuesToUpdate, VisitProperties $visitProperties, Request $request)
    {
        // empty
    }

    /**
     * This method is called last. Derived classes should use this method to insert log data. They
     * should also only read request metadata, and not set it.
     *
     * When this method is called, you can assume all request metadata have their final values. Also,
     * `$visitProperties->visitorInfo` will contain the properties of the visitor's current visit (in
     * other words, the values in the array were persisted to the DB before this method was called).
     *
     * @param VisitProperties $visitProperties
     * @param Request $request
     */
    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        // empty
        $txt = "For Kafka - ".$request->getParam('action_name')."-".$request->getParam('e_c')."-".$request->getParam('e_a');

        // Use specifc request data
        //$myfile = file_put_contents('./logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);

        // Use All request data
        $myfile = file_put_contents('./matomo_kafka_logs.txt', json_encode($request->getParams())."\n".PHP_EOL , FILE_APPEND | LOCK_EX);
        //print_r($request->getParams());

        /*
        // Send captured data to Kafka using Curl Request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"http://localhost:8080/kafka/kafka.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request->getParams()));

        // SSL Fix
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $server_output = curl_exec($ch);

        curl_close($ch);

        $myfile = file_put_contents('./logs0.txt', $server_output.PHP_EOL , FILE_APPEND | LOCK_EX); 

        // Further processing ...
        if ($server_output == "OK") { 
            
        }else { 
        }

        */

        // Send Message to Kafka - Produser
        $conf = new Conf();
        $conf->set('bootstrap.servers', 'your-kafka:9092'); // configure/update your correct brokers list here
        $conf->set('socket.timeout.ms', (string) 50);
        $conf->set('queue.buffering.max.messages', (string) 1000);
        $conf->set('max.in.flight.requests.per.connection', (string) 1);
        $conf->setDrMsgCb(
            function (Producer $producer, Message $message): void {
                if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) {
                    //var_dump($message->errstr());
                } else {
                    //echo "success";
                }
                var_dump($message);
            }
        );
        //$conf->set('log_level', (string) LOG_DEBUG);
        //$conf->set('debug', 'all');
        $conf->setLogCb(
            function (Producer $producer, int $level, string $facility, string $message): void {
                //echo sprintf('log: %d %s %s', $level, $facility, $message) . PHP_EOL;
            }
        );
        $conf->set('statistics.interval.ms', (string) 1000);
        $conf->setStatsCb(
            function (Producer $producer, string $json, int $json_len, $opaque = null): void {
                //echo "stats: {$json}" . PHP_EOL;
            }
        );

        $topicConf = new TopicConf();
        $topicConf->set('message.timeout.ms', (string) 30000);
        $topicConf->set('request.required.acks', (string) -1);
        $topicConf->set('request.timeout.ms', (string) 5000);
        $producer = new Producer($conf);
        $topic = $producer->newTopic('your-topic-name', $topicConf); // configure/update your correct topic name
        $metadata = $producer->getMetadata(false, $topic, 1000);
        $key = 100;
        //$payload = sprintf('Real Test matomo to KAFKA message');
        $payload = json_encode($request->getParams());
        //echo sprintf('produce msg: %s', $payload) . PHP_EOL;
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $payload, (string) $key);
        // triggers log output
        $events = $producer->poll(1);
        $producer->flush(5000);

    }

}
