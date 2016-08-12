<?php

namespace golibplugin\Plugin;

/**
 *
 * @author tziegler
 */
interface Plugin {

    /**
     * return the default name for the plugin
     * @return string The name of the plugin
     */
    public function getName ();

    /**
     * set the wayof plugin usage.
     * return true if this plugin
     * should be stored in a static storage
     * @return Boolean
     */
    public function isStatic ();
}
