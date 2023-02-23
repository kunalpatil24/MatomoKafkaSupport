<?php

namespace Piwik\Plugins\MatomoKafkaSupport\Tracker;

use Piwik\Common;
use Piwik\SettingsPiwik;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Request;
use Piwik\Tracker;
use Piwik\Tracker\Visit\VisitProperties;



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
        $myfile = file_put_contents('./logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
        $myfile = file_put_contents('./logs1.txt', json_encode($request->getParams())."\n".PHP_EOL , FILE_APPEND | LOCK_EX);
        //print_r($request->getParams());
    }

}