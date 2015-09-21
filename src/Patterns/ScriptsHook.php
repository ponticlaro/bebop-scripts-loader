<?php

namespace Ponticlaro\Bebop\ScriptsLoader\Patterns;

use Ponticlaro\Bebop\Common\Collection;
use Ponticlaro\Bebop\Common\EnvManager;
use Ponticlaro\Bebop\Common\PathManager;
use Ponticlaro\Bebop\ScriptsLoader\Patterns\Script;

class ScriptsHook {

    /**
     * Hook ID
     * 
     * @var string   
     */
    protected $id;

    /**
     * WordPress hook
     * 
     * @var string   
     */
    protected $hook;

    /**
     * Scripts base URL
     * 
     * @var string
     */
    protected $base_url;

    /**
     * Holds all scripts objects
     * 
     * @var \Ponticlaro\Bebop\Common\Collection
     */
    protected $scripts;

    /**
     * Holds scripts to be deregistered
     * 
     * @var \Ponticlaro\Bebop\Common\Collection
     */
    protected $deregister_list;

    /**
     * Holds scripts to be dequeued
     * 
     * @var \Ponticlaro\Bebop\Common\Collection
     */
    protected $dequeue_list;

    /**
     * Holds scripts to be registered
     * 
     * @var \Ponticlaro\Bebop\Common\Collection
     */
    protected $register_list;

    /**
     * Holds scripts to be localized
     * 
     * @var \Ponticlaro\Bebop\Common\Collection
     */
    protected $localize_list;

    /**
     * Holds scripts to be enqueued
     * 
     * @var \Ponticlaro\Bebop\Common\Collection
     */
    protected $enqueue_list;

    /**
     * Holds environment specific configuration modifications
     * 
     * @var \Ponticlaro\Bebop\Common\Collection
     */
    protected $env_configs;

    /**
     * Instantiates a new Scripts registration hook
     * 
     * @param string $id   Registration hook ID
     * @param string $hook WordPress hook ID
     */
    public function __construct($id, $hook)
    {
        if (!is_string($id) || !is_string($hook))
            throw new \UnexpectedValueException('Both $id and $hook must be strings');

        $this->id              = $id;
        $this->hook            = $hook;
        $this->scripts         = (new Collection())->disableDottedNotation();
        $this->deregister_list = (new Collection())->disableDottedNotation();
        $this->dequeue_list    = (new Collection())->disableDottedNotation();
        $this->register_list   = (new Collection())->disableDottedNotation();
        $this->localize_list   = (new Collection())->disableDottedNotation();
        $this->enqueue_list    = (new Collection())->disableDottedNotation();
        $this->env_configs     = (new Collection())->disableDottedNotation();

        // Register and enqueue scripts on target hook
        add_action($hook, array($this, 'run'));
    }

    /**
     * Returns ID
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a script by id
     * 
     * @param  string                           $id Target script ID
     * @return \Ponticlaro\Bebop\Scripts\Script     Target script object
     */
    public function getFile($id)
    {
        return $this->scripts->get($id);
    }

    /**
     * Sets a base URL for all scripts
     * 
     * @param string $base_url
     */
    public function setBaseUrl($base_url)
    {
        if(is_string($base_url)) $this->base_url = $base_url;

        return $this;
    }

    /**
     * Returns base URL
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * Registers a single script
     * 
     * @param string $id Script ID
     */
    public function register($id, $path, array $dependencies = array(), $version = null)
    {
        $script = new Script();
        $script->setId($id)
               ->setPath($path)
               ->setDependencies($dependencies)
               ->setVersion($version);

        $this->scripts->set($script->getid(), $script);
        $this->register_list->push($script->getid());

        return $this;
    }

    /**
     * Localize scripts
     * 
     * @param  string $id             Script IDS
     * @param  string $variable_name  Name for the variable withing the script
     * @param  array  $variable_value Data to be assigned as the variable value
     */
    public function localize($id, $variable_name, array $variable_value)
    {
        if (!is_string($id) || !is_string($variable_name))
            throw new \Exception('Both $id and $variable_name need to be a string');
        
        $this->localize_list->push([
            'variable_name'  => $variable_name,
            'variable_value' => $variable_value
        ], $id);

        return $this;
    }

    /**
     * Enqueues scripts
     * You can pass as many file IDs as individual parameters as you need
     * 
     */
    public function enqueue()
    {
        foreach (func_get_args() as $file_id) {
            
            if (is_string($file_id)) $this->enqueue_list->push($file_id);
        }

        return $this;
    }

    /**
     * Deregisters scripts
     * You can pass as many file IDs as individual parameters as you need
     * 
     */
    public function deregister()
    {
        foreach (func_get_args() as $file_id) {
            
            if (is_string($file_id)) $this->deregister_list->push($file_id);
        }

        return $this;
    }

    /**
     * Dequeues scripts
     * You can pass as many file IDs as individual parameters as you need
     * 
     */
    public function dequeue()
    {
        foreach (func_get_args() as $file_id) {
            
            if (is_string($file_id)) $this->dequeue_list->push($file_id);
        }

        return $this;
    }

    /**
     * Adds a function to execute when the target '$env' is active
     * 
     * @param string $env Target environment ID
     * @param string $fn  Function to execute
     */
    public function onEnv($envs, $fn)
    {
        if (is_callable($fn)) {

            if (is_string($envs)) {
               
                $this->env_configs->set($envs, $fn);
            }

            elseif (is_array($envs)) {
                
                foreach ($envs as $env) {
                   
                    $this->env_configs->set($env, $fn);
                }
            }
        }

        return $this;
    }

    /**
     * Function that runs when the target WordPress hook runs
     * 
     */
    public function run()
    {
        $this->__applyEnvModifications();
        $this->__deregisterScripts();
        $this->__dequeueScripts();
        $this->__registerScripts();
        $this->__localizeScripts();
        $this->__enqueueScripts();
    }

    /**
     * Deregisters all scripts
     * 
     */
    protected function __deregisterScripts()
    {
        foreach ($this->deregister_list->getAll() as $script_id) {

            if ($this->scripts->hasKey($script_id)) {
                
                $script_obj = $this->scripts->get($script_id);
                $script_obj->deregister();
            }

            else {

                $this->scriptAction('deregister', $script_id);
            }
        }

        return $this;
    }

    /**
     * Dequeues all scripts
     * 
     */
    protected function __dequeueScripts()
    {
        foreach ($this->dequeue_list->getAll() as $script_id) {
            
            if ($this->scripts->hasKey($script_id)) {
                
                $script_obj = $this->scripts->get($script_id);
                $script_obj->dequeue();
            }

            else {

                $this->scriptAction('dequeue', $script_id);
            }
        }

        return $this;
    }

    /**
     * Registers all scripts
     * 
     */
    protected function __registerScripts()
    {
        foreach ($this->register_list->getAll() as $script_id) {

            if ($this->scripts->hasKey($script_id)) {

                $base_url = $this->getBaseUrl();

                if (!$base_url) {
                    $this->setBaseUrl(PathManager::getInstance()->get('theme'));
                    $base_url = $this->getBaseUrl();
                }

                $script_obj = $this->scripts->get($script_id);

                if ($base_url && !$script_obj->getBaseUrl()) $script_obj->setBaseUrl($base_url);

                $script_obj->register();
            }
        }

        return $this;
    }

    /**
     * Localize all scripts
     * 
     */
    protected function __localizeScripts()
    {
        foreach ($this->localize_list->getAll() as $script_id => $localizations) {
            
            if (!$this->scripts->hasKey($script_id))
                throw new \Exception("There is no registered script with the following id: '". $script_id ."'");
            
            $script_obj = $this->scripts->get($script_id);

            foreach ($localizations as $data) {
                $script_obj->localize($data['variable_name'], $data['variable_value']);
            }
        }

        return $this;
    }


    /**
     * Enqueues all scripts
     * 
     */
    protected function __enqueueScripts()
    {
        foreach ($this->enqueue_list->getAll() as $script_id) {
            
            if ($this->scripts->hasKey($script_id)) {
                
                $script_obj = $this->scripts->get($script_id);
                $script_obj->enqueue();
            }

            else {

                $this->scriptAction('enqueue', $script_id);
            }
        }

        return $this;
    }

    /**
     * Executes default scripts actions using only the script ID
     */
    protected function scriptAction($action, $script_id)
    {
        return $this;
    }

    /**
     * Executes any function that exists for the current environment
     * 
     */
    protected function __applyEnvModifications()
    {
        // Get current environment
        $current_env = EnvManager::getInstance()->getCurrentKey();

        // Execute current environment function
        if ($this->env_configs->hasKey($current_env))
            call_user_func_array($this->env_configs->get($current_env), array($this));
    }
}