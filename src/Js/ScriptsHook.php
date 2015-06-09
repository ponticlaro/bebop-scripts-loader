<?php

namespace Ponticlaro\Bebop\ScriptsLoader\Js;

class ScriptsHook extends \Ponticlaro\Bebop\ScriptsLoader\Patterns\ScriptsHook {

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