<?php

namespace Ponticlaro\Bebop\ScriptsLoader\Js;

use Ponticlaro\Bebop\Common\Collection;

class Script extends \Ponticlaro\Bebop\ScriptsLoader\Patterns\Script {

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
     * Instantiates a new script object 
     * 
     * @param string  $id           Script ID
     * @param string  $path         Script path
     * @param array   $dependencies Script dependencies
     * @param string  $version      Script version
     * @param boolean $in_footer    If script should be loaded in the wp_footer hook
     */
    public function __construct($id, $path, array $dependencies = array(), $version = null, $in_footer = true)
    {
        parent::__construct();

        // Create config collection
        $this->config = new Collection(array(
            'id'           => $id,
            'path'         => ltrim($path, '/'),
            'in_footer'    => $in_footer,
            'base_url'     => null
        ));

        foreach ($dependencies as $dependency) {
            
            $this->dependencies->push($dependency);
        }
    }

    /**
     * Sets if the script should load in the footer or not
     * 
     * @param bool $in_footer True if it should be loaded in the footer, false otherwise
     */
    public function loadInFooter($in_footer)
    {
        if (is_bool($in_footer))
            $this->config->set('in_footer', $in_footer);

        return $this;
    }

    /**
     * Returns ture if it should be loaded in the footer, false otherwise
     * 
     * @return bool
     */
    public function getLoadInFooter()
    {
        return $this->config->get('in_footer');
    }

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
     * Applies async attribute on script tag
     * 
     * @param  string $tag    Script HTML tag
     * @param  string $handle Script handle
     * @param  string $src    Script sourc
     * @return string         Modified tag
     */
    public function applyAsyncAttrOnTag($tag, $handle, $src)
    {
        if ($handle != $this->getId())
            return $tag;

        return str_replace(' src', ' async src', $tag);
    }

    /**
     * Applies defer attribute on script tag
     * 
     * @param  string $tag    Script HTML tag
     * @param  string $handle Script handle
     * @param  string $src    Script sourc
     * @return string         Modified tag
     */
    public function applyDeferAttrOnTag($tag, $handle, $src)
    {
        if ($handle != $this->getId())
            return $tag;

        return str_replace(' src', ' defer src', $tag);
    }

    /**
     * Registers script
     * 
     */
    public function register()
    {   
        // Apply any environment specific modification
        $this->__applyEnvModifications();

        // Async loading
        if ($this->getAsync())
            add_filter('script_loader_tag', [$this, 'applyAsyncAttrOnTag'], 9999, 3);

        // Defer loading
        if ($this->getDefer())
            add_filter('script_loader_tag', [$this, 'applyDeferAttrOnTag'], 9999, 3);

        // Register script
        wp_register_script(
            $this->getId(),
            $this->getAbsoluteUrl(), 
            $this->getDependencies(), 
            $this->getVersion(), 
            $this->getLoadInFooter()
        );

        // Mark script as registered
        $this->is_registered = true;
    }

    /**
     * Deregisters script
     * 
     */
    public function deregister()
    {
        wp_deregister_script($this->getId());

        return $this;
    }

    /**
     * Localize script
     * 
     */
    public function localize($variable_name, array $variable_value)
    {
        // Register script if not already registered
        if (!$this->is_registered) $this->register();

        // Localize script
        wp_localize_script($this->getId(), $variable_name, $variable_value);

        return $this;
    }

    /**
     * Enqueues script
     * 
     */
    public function enqueue()
    {
        // Register script if not already registered
        if (!$this->is_registered) $this->register();

        // Enqueue script
        wp_enqueue_script($this->getId());

        return $this;
    }

    /**
     * Dequeues script
     * 
     */
    public function dequeue()
    {
        wp_dequeue_script($this->getId());

        return $this;
    }
}