<?php

namespace golibplugin\Plugin;

/**
 * Plugin-Provider Blueprint
 *
 * @author tziegler <thomas.zglr@googlemail.com>
 *
 * this is the baseclass if you want
 * to extend youre own classes with plugins
 *
 *
 */
abstract class Provider {

    /**
     * stored non static plugins
     * @var Plugin[]
     */
    private $plugins = array();

    /**
     * stored static plugins
     * @var Plugin[]
     */
    private static $staticPlugins = array();

    /**
     * contains the last method Arguments
     * @var array
     */
    private $lastParams = NULL;

    /**
     * is not null then on
     * any Plugin-Call it will be
     * checkedif the plugin parent
     * class matches to this expected type
     * @var string
     */
    private $currentTypeExpected = NULL;

    /**
     * registers a static plugin if they not allready registered.
     * this plugin have to be a static plugin.
     * @param \golibplugin\Plugin\Plugin $plugin
     * @param type $name
     * @return boolean
     */
    public static function registerStaticPluginOnce ( Plugin $plugin,
                                                      $name = null ) {
        if ($name == NULL || !is_string( $name )) {
            $name = $plugin->getName();
        }
        if (isset( self::$staticPlugins[$name] )) {
            return false;
        }

        self::registerStaticPlugin( $plugin, $name );
        return true;
    }

    /**
     * Registers a static Plugin. This Plugin MUST
     * BE DEFINED AS STATIC.
     * @param \golibplugin\Plugin\Plugin $plugin the plugin himself
     * @param type $name the name of the plugin. (optional)
     * @throws \Exception
     */
    public static function registerStaticPlugin ( Plugin $plugin, $name = null ) {
        if ($name == NULL || !is_string( $name )) {
            $name = $plugin->getName();
        }

        if ($plugin->isStatic() != true) {
            throw new \Exception( "nonstatic plugins can not registered as static plugin " );
        }
        if (isset( self::$staticPlugins[$name] )) {
            throw new \Exception( "there is already a plugin {$name} regisered " );
        }
        self::$staticPlugins[$name] = $plugin;
    }

    /**
     * sets the Classname (including namespaces) that are the parent
     * of the plugin. this must be done for EVEREY callPlugins because
     * this filter will be resetted after any run.
     * @param type $parentClass name of the expected parent class
     */
    public function setExpectedparent ( $parentClass ) {
        $this->currentTypeExpected = $parentClass;
    }

    /**
     * returns the last used Methods call.
     * willbe overwritten on any execute
     * @return array
     */
    public function getLastParams () {
        return $this->lastParams;
    }

    /**
     * constructor
     * calls method constructor
     */
    public function __construct () {
        $this->callPlugins( 'constructor', func_get_args() );
    }

    /**
     * destructor
     * calls method destructor
     */
    public function __destruct () {
        $this->callPlugins( 'destruct', func_get_args() );
    }

    /**
     * checks by a name if this plugin
     * is already registered
     * @param string $name name of the plugin
     * @return boolean
     */
    public function pluginRegistered ( $name ) {
        return (isset( self::$staticPlugins[$name] ) || isset( $this->plugins[$name] ));
    }

    /**
     * returns the countof registered plugins
     * @return int
     */
    public function pluginsCount () {
        return count( $this->plugins ) + count( self::$staticPlugins );
    }

    /**
     * if via setExpectedparent method a expected
     * plugin defined,if the current plugin
     * matches to this type.
     * @param \golibplugin\Plugin\Plugin $plugin
     * @return boolean
     */
    private function checkPluginType ( Plugin $plugin ) {
        if ($this->currentTypeExpected == NULL) {
            return true; // no check requested
        }
        // get the parent class
        $parent = get_parent_class( $plugin );

        // simple check
        if ($parent == $this->currentTypeExpected) {
            return true;
        }

        // looking in the same namspace
        if ($parent == get_called_class() . '\\' . $this->currentTypeExpected) {
            return true;
        }

        //enough ..not valid
        return false;
    }

    /**
     * get a registered Plugin by name
     * @param type $name
     * @return Plugin
     */
    public function getPlugin ( $name ) {
        if (isset( $this->plugins[$name] )) {
            return $this->plugins[$name];
        }

        if (isset( self::$staticPlugins[$name] )) {
            return self::$staticPlugins[$name];
        }
    }

    /**
     * executes allplugins and returns
     * containerthat collects allreturn values
     * @param type $methodName
     * @return \GLib\Plugin\PluginResponse
     */
    public function callPlugins ( $methodName ) {
        $parameters = array_slice( func_get_args(), 1 );
        $this->lastParams = $parameters;
        $plgResponse = new PluginResponse();
        foreach ($this->plugins as $name => $plugin) {

            if ($this->checkPluginType( $plugin ) && method_exists( $plugin,
                                                                    $methodName )) {
                $response = call_user_func_array( array(
                    $plugin,
                    $methodName), $parameters );
                $this->handleResponse( $response, $name, $plugin, $plgResponse );
            }
        }

        foreach (self::$staticPlugins as $name => $plugin) {
            if ($this->checkPluginType( $plugin ) && method_exists( $plugin,
                                                                    $methodName )) {
                $response = call_user_func_array( array(
                    $plugin,
                    $methodName), $parameters );
                $this->handleResponse( $response, $name, $plugin, $plgResponse );
            }
        }
        $this->currentTypeExpected = NULL; // resetted after any plugin call
        return $plgResponse;
    }

    /**
     * parse the response and fill
     * the PluginResponse
     * @param mixed $response
     * @param string $name
     * @param \golibplugin\Plugin\Plugin $plugin
     * @param \golibplugin\Plugin\PluginResponse $plgResponse
     */
    private function handleResponse ( $response, $name, Plugin $plugin,
                                      PluginResponse $plgResponse ) {
        if (is_bool( $response )) {
            $plgResponse->allBooleanSummerize &= $response;
        }

        $plgResponse->countOfExecutes++;
        $plgResponse->excutedPlugins[] = $name;
        $plgResponse->response[$name] = $response;
    }

    /**
     * register a plugin
     * @param \golibplugin\Plugin\Plugin $plugin
     * @param type $name
     * @throws \Exception
     */
    public function registerPlugin ( Plugin $plugin, $name = NULL ) {
        if ($name == NULL || !is_string( $name )) {
            $name = $plugin->getName();
        }
        if ($this->pluginRegistered( $name )) {
            throw new \Exception( "there is already a plugin {$name} regisered " );
        }
        if ($plugin->isStatic()) {
            self::$staticPlugins[$name] = $plugin;
        } else {
            $this->plugins[$name] = $plugin;
        }
    }

}
