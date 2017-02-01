<?php

namespace Ponticlaro\Bebop\ScriptsLoader\Js;

class ScriptsHook extends \Ponticlaro\Bebop\ScriptsLoader\Patterns\ScriptsHook {

    /**
     * Flags if we should async the loading of this script
     * 
     * @var boolean
     */
    protected $async = false;

    /**
     * Flags if we should defer the loading of this script
     * 
     * @var boolean
     */
    protected $defer = false;

    /**
     * Sets async loading
     * 
     * @param bool $value True to load with async, false otherwise
     */
    public function setAsync(bool $value)
    {
        $this->async = $value;

        if ($value)
            $this->defer = false;

        return $this;
    }

    /**
     * Returns async loading flag
     * 
     * @return bool True to load with async, false otherwise
     */
    public function getAsync()
    {
        return $this->async;
    }

    /**
     * Sets defer loading
     * 
     * @param bool $value True to load with defer, false otherwise
     */
    public function setDefer(bool $value)
    {
        $this->defer = $value;

        if ($value)
            $this->async = false;

        return $this;
    }

    /**
     * Returns defer loading flag
     * 
     * @return bool True to load with defer, false otherwise
     */
    public function getDefer()
    {
        return $this->defer;   
    }

    /**
     * Registers a single script
     * 
     * @param string  $id           Script ID
     * @param string  $path         Script path
     * @param array   $dependencies Script dependencies
     * @param string  $version      Script version
     * @param boolean $in_footer    If script should be loaded in the wp_footer hook
     */
    public function register($id, $path, array $dependencies = array(), $version = null, $in_footer = true)
    {
        $script = new Script($id, $path, $dependencies, $version, $in_footer);

        // Apply hook-wide async loading
        if ($this->getAsync())
            $script->setAsync(true);

        // Apply hook-wide defer loading
        if ($this->getDefer())
            $script->setDefer(true);

        $this->scripts->set($id, $script);
        $this->register_list->push($id);

        return $this;
    }

    /**
     * Executes script actions using only script ID
     * 
     */
    protected function scriptAction($action, $file_id)
    {
        switch ($action) {

            case 'deregister':
                
                wp_deregister_script($file_id);
                break;
            
            case 'dequeue':
                
                wp_dequeue_script($file_id);
                break;

            case 'enqueue':
                
                wp_enqueue_script($file_id);
                break;
        }

        return $this;
    }
}