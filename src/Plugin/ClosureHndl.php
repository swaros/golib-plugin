<?php

namespace golibplugin\Plugin;

/**
 * Description of MethodInjector
 *
 * @author tziegler
 *
 * base class to inject methods like javascript
 *
 */
abstract class ClosureHndl {

    /**
     * binding current method
     * @var \Closure
     */
    protected $callBackMethod = NULL;

    /**
     * all registerd methods
     * @var array
     */
    private $registeredMethods = array();

    /**
     * if true methods can be overwritten
     * @var bool
     */
    protected $overWriteMethods = true;

    /**
     * just ignore methodcalls they not exists
     * @var type
     */
    protected $ignoreNotExistingMethods = true;

    /**
     * register a method by this name if this not exists
     * @param type $name
     * @param \Closure $func
     */
    public function registerMethod ( $name, \Closure $func ) {
        if (!$this->canAddMethod( $name )) {
            $this->registeredMethods[$name] = $func;
        }
    }

    /**
     * executes a closure
     * @param string $name
     * @throws \Exception
     */
    protected function exec ( $name ) {
        $arguments = array_slice( func_get_args(), 1 );
        if ($this->haveMethod( $name )) {
            $this->callBackMethod = $this->registeredMethods[$name];
            call_user_func_array(
                    array(
                $this,
                'callBackMethod'), $arguments
            );
        } elseif ($this->ignoreNotExistingMethods === false) {
            throw new \Exception( "Method not defined " . $name );
        }
    }

    /**
     * executes a closure
     * @param string $name
     * @throws \Exception
     */
    protected function execArray ( $name, array $arguments = NULL ) {
        if ($this->haveMethod( $name )) {
            $this->callBackMethod = $this->registeredMethods[$name];
            call_user_func_array(
                    array(
                $this,
                'callBackMethod'), $arguments
            );
        } elseif ($this->ignoreNotExistingMethods === false) {
            throw new \Exception( "Method not defined " . $name );
        }
    }

    /**
     * checks if the method exists
     * @param string $name
     * @return boolean
     */
    protected function haveMethod ( $name ) {
        if (in_array( $name, array_keys( $this->registeredMethods ) )) {
            return true;
        }
        return false;
    }

    /**
     * checks if the method exists for internal
     * checking. if overwriting true
     * @param string $name
     * @return boolean
     */
    protected function canAddMethod ( $name ) {
        if (method_exists( $this, $name )) {
            return true;
        }

        if (!$this->overWriteMethods && in_array( $name,
                                                  array_keys( $this->registeredMethods ) )) {
            return true;
        }
        return false;
    }

    /**
     * magic setter for unknown properties
     * used to register methods
     * @param type $name
     * @param \Closure $value
     */
    public function __set ( $name, $value ) {
        if ($value instanceof \Closure) {
            $this->registerMethod( $name, $value );
        }
    }

    /**
     * magic method to execute undefined methods
     * used to start \Closures
     * @param type $name
     * @param type $arguments
     * @return type
     */
    public function __call ( $name, $arguments ) {

        if (isset( $this->$name )) {
            return call_user_func_array( $this->$name, $arguments );
        }
    }

}
