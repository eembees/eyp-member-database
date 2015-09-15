<?php

// Register autoloader
spl_autoload_register(function($classname) {
	//echo "Looking for $classname:\n";

	// Split into namespace and class
	$aClassname = explode('\\', $classname);

	// Everything is namespaced
	if(count($aClassname) != 2) {
		return false;
	}

	$namespace = $aClassname[0];
	$class = $aClassname[1];

	// Only load know namespaces
	// (For compatibility with Slim, which has its own autoloader)
	$known_namespaces = ['Core', 'Helpers', 'Modules'];
	if(!in_array($namespace, $known_namespaces)) {
		return false;
	}

	// Load file
	if($namespace === 'Modules') {
		$filename = '_'.$namespace.'/'.$class.'/controller.php';
	} else {
		$filename = '_'.$namespace.'/'.$class.'.php';
	}

	include_once($filename);

	// Check if we were successful
	if(class_exists($classname)) {
		return true;
	} else {
		return false;
	}
});

// Initialize all modules
\Core\App::loadModules();

// Start the app
\Core\App::run();

?>