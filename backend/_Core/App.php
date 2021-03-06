<?php

namespace Core;

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

/**
 * A singleton class for the App
 */
class App {

	/**
	 * @var {Slim} $_instance The reference to the Singleton instance of the class
	 */
	private static $_instance;

    /**
     * @var {dict} $modules A dictionary holding all initilized modules
     */
    public static $_modules = array();

	/**
     * Returns the Singleton instance of this class.
     *
     * @return {Slim} The Singleton instance.
     */
    public static function getInstance() {
        if (null === static::$_instance) {
            static::$_instance = new \Slim\Slim(array(
			    'debug' => true,
			    'mode' => 'development'
			));
        }
        
        return static::$_instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * Singleton via the 'new' operator from outside of this class.
     */
    protected function __construct() {}

    /**
     * Private clone method to prevent cloning of the instance of the
     * Singleton instance.
     *
     * @return void
     */
    private function __clone() {}

    /**
     * Private unserialize method to prevent unserializing of the Singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup() {}

    /**
     * Used as middleware in testing before every request. Sleeps for a certain amount of time.
     *
     * @return void
     */
    public static function delayLoads($route) {
        sleep(1);
    }

    /**
     * Loads all enabled modules
     *
     * @return {boolean} Whether the modules were loaded successfully
     */
    public static function loadModules() {
		// Get all modules
		$enabled_modules = \Helpers\Database::getAllModules(true);

        // Get app instance
        $app = \Core\App::getInstance();

        if(!is_array($enabled_modules)) {
            return false;
        }

        // Load modules
		foreach($enabled_modules as $module) {
			$short_name = $module['name'];

			// Load module
			$module_classname = '\\Modules\\'.ucfirst($short_name);
			$module = new $module_classname();
            App::$_modules[$short_name] = $module;

            // Get all actions supported by the module
            $module_actions = $module->getActions();

            // Define all HTTP methods supported by the App
            $supported_methods = ['GET', 'POST', 'DELETE'];

            // Assign all possible routes
            foreach($supported_methods as $method) {
                if(isset($module_actions[$method])) {
                    foreach($module_actions[$method] as $action => $handler) {

                        // Dynamically call the appropriate function to register the route
                        $url_delimiter = '/';
                        if(strpos($action, $url_delimiter) === 0) {
                            $url_delimiter = '';
                        }
                        $action = '/' . $short_name . $url_delimiter . $action;
                        $method_name = strtolower($method);
                        $app->$method_name($action, /*['\\Core\\App', 'delayLoads'],*/ [$module, $handler]);
                    }
                }
            }
		}

        return true;
    }

    /**
     * Runs the app
     *
     * @return void
     */
    public static function run() {
    	\Core\App::getInstance()->run();
    }

}

?>