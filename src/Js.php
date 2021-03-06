<?php

namespace Ponticlaro\Bebop\ScriptsLoader;

use Ponticlaro\Bebop\ScriptsLoader\Helpers\ScriptsManager;
use Ponticlaro\Bebop\ScriptsLoader\Js\Script;
use Ponticlaro\Bebop\ScriptsLoader\Js\ScriptsHook;

class Js
{
    /**
     * Class instance
     * 
     * @var object
     */
    private static $instance;

    /**
     * ScriptsManager instance
     *
     * @var ScriptsManager
     */
    protected $manager;

    /**
     * Instantiates class
     * 
     * @return void
     */
    public function __construct()
    {
        $this->manager = new ScriptsManager();

        // Add default hooks
        $this->addHook('front', 'wp_enqueue_scripts')
             ->addHook('back', 'admin_enqueue_scripts')
             ->addHook('login', 'login_enqueue_scripts');
    }

    /**
     * Do not allow clones
     * 
     * @return void
    */
    private final function __clone() {}

    /**
     * Gets single instance of called class
     * 
     * @return object
     */
    public static function getInstance() 
    {
        if (!isset(static::$instance))
            static::$instance = new static();

        return static::$instance;
    }

    /**
     * Adds a script registration hook
     *
     * @param string $id   Registration hook ID
     * @param string $hook WordPress hook ID
     * @return self
     */
    public function addHook($id, $hook)
    {
        // Generate new CSS scripts hook
        $hook = new ScriptsHook($id, $hook);

        // Add hook to scripts manager
        $this->manager->addHook($hook);

        return $this;
    }

    /**
     * Returns a single registration hook by ID
     *
     * @param  string $id ID of the target registration hook
     * @return Script
     */
    public function getHook($id)
    {
        return $this->manager->getHook($id);
    }

    /**
     * Sets base url for all hooks
     *
     * @param string $url Base URL
     * @return self
     */
    public function setBaseUrl($url)
    {
        if (is_string($url))
            $this->manager->setBaseUrl($url);

        return $this;
    }
}