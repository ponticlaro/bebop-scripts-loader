<?php

namespace Ponticlaro\Bebop\ScriptsLoader;

use Ponticlaro\Bebop\ScriptsLoader\Css\ScriptsHook;
use Ponticlaro\Bebop\ScriptsLoader\Helpers\ScriptsManager;
use Ponticlaro\Bebop\ScriptsLoader\Patterns\Script;

class Css extends \Ponticlaro\Bebop\Common\Patterns\SingletonAbstract
{

    /**
     * ScriptsManager instance
     *
     * @var ScriptsManager
     */
    protected static $manager;

    /**
     * Instantiates CSS Manager
     */
    protected function __construct()
    {
        self::$manager = new ScriptsManager();

        // Add default hooks
        $this->addHook('front', 'wp_enqueue_scripts')
             ->addHook('back', 'admin_enqueue_scripts')
             ->addHook('login', 'login_enqueue_scripts');
    }

    /**
     * Adds a script registration hook
     *
     * @param string $id   Registration hook ID
     * @param string $hook WordPress hook ID
     * @return self
     */
    public function addHook($id, $hook_id)
    {
        // Generate new CSS scripts hook
        $hook = new ScriptsHook($id, $hook_id);

        // Add hook to scripts manager
        self::$manager->addHook($hook);

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
        return self::$manager->getHook($id);
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
            static::$manager->setBaseUrl($url);

        return $this;
    }
}
