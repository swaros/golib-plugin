<?php

namespace golibplugin\Plugin;

/**
 * Description of PluginResponse
 *
 * @author tziegler
 *
 * collects all response
 * from all executed plugins
 */
class PluginResponse {

    /**
     * the count of executed plugins
     * @var int
     */
    public $countOfExecutes = 0;

    /**
     * summerize all boolean flags.
     * null response counts as true
     * @var boolean
     */
    public $allBooleanSummerize = true;

    /**
     * list of the names from
     * all executed plugins
     * @var array
     */
    public $excutedPlugins = array();

    /**
     * any response.the key will be the name of the
     * plugin
     * @var array
     */
    public $response = array();

}
