<?php

namespace Ponticlaro\Bebop\ScriptsLoader\Css;

class ScriptsHook extends \Ponticlaro\Bebop\ScriptsLoader\Patterns\ScriptsHook {

    /**
     * Registers a single script
     * 
     * @param string  $id           Script ID
     * @param string  $path         Script path
     * @param array   $dependencies Script dependencies
     * @param string  $version      Script version
     * @param string  $media        String specifying the media for which this stylesheet has been defined
     */
    public function register($id, $path, array $dependencies = array(), $version = null, $media = 'all')
    {
        $script = new Script($id, $path, $dependencies, $version, $media);

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
                
                wp_deregister_style($file_id);
                break;
            
            case 'dequeue':
                
                wp_dequeue_style($file_id);
                break;

            case 'enqueue':
                
                wp_enqueue_style($file_id);
                break;
        }

        return $this;
    }
}