<?php

namespace Ponticlaro\Bebop\ScriptsLoader\Helpers;

use Ponticlaro\Bebop\Common\Collection;
use Ponticlaro\Bebop\ScriptsLoader\Patterns\ScriptsHook;

class ScriptsManager {

    /**
     * Holds hooks for scripts registration
     * 
     * @var \Ponticlaro\Bebop\Common\Collection
     */
    protected $hooks;

    /**
     * Base URL for all hooks
     * 
     * @var string
     */
    protected $base_url;

    /**
     * Instantiates a Scripts Manager
     * 
     */
    public function __construct()
    {   
        $this->hooks = (new Collection())->disableDottedNotation();
    }

    /**
     * Sets base url for all hooks
     * 
     * @param string $url Base URL
     */
    public function setBaseUrl($url)
    {
        if (is_string($url)) {

            $this->base_url = $url;
            
            foreach ($this->hooks as $hook) {
                
                if (!$hook->getBaseUrl())
                    $hook->setBaseUrl($url);
            }
        }

        return $this;
    }

    /**
     * Adds a script registration hook
     * 
     * @param \Ponticlaro\Bebop\ScriptsLoader\Patterns\ScriptsHook $hook
     */
    public function addHook(ScriptsHook $hook)
    {
        if (!$hook->getBaseUrl() && !is_null($this->base_url))
            $hook->setBaseUrl($this->base_url);

        $this->hooks->set($hook->getId(), $hook);

        return $this;
    }

    /**
     * Returns a single registration hook by ID
     * 
     * @param  string                                               $id  ID of the target registration hook
     * @return \Ponticlaro\Bebop\ScriptsLoader\Patterns\ScriptsHook
     */
    public function getHook($id)
    {
        if (!is_string($id)) 
            return null;

        return $this->hooks->get($id);
    }
}