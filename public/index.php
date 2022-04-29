<?php

if (!empty($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] == 'development') {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	

	function exception_error_handler($severity, $message, $file, $line) {
	    if (!(error_reporting() & $severity))return;
	    
	    throw new ErrorException($message, 0, $severity, $file, $line);
	}
	set_error_handler("exception_error_handler", E_ALL);
	
}
else
{
	error_reporting(0);
	ini_set("display_errors", 0);
}
chdir(dirname(__DIR__));
require_once "sitio/src/Main.php";

$opts = array(
        'tiemposesion' => 600,  //minutos
		'routers' => array(
		    'home' 	=> array(
		        'controller' 		=> 'Controller',
		        'controller_path'	=> 'sitio/controllers/Controller.php',
		        'default'			=> 'historial',
		    ),
		    'no-found' 	=> array(
		        'controller' 		=> 'Controller',
		        'controller_path'	=> 'sitio/controllers/Controller.php',
		        'default'			=> 'nofound',
		    ),
		    'bolillas'	=> array(
		        'controller' 		=> 'Controller',
		        'controller_path'	=> 'sitio/controllers/Controller.php',
		        'default'			=> 'estadisticas',
		        'match'				=> '/estadisticas|recomendadas/',
		    ),	    
		    'sorteos'	=> array(
		        'controller' 		=> 'Controller',
		        'controller_path'	=> 'sitio/controllers/Controller.php',
		        'default'			=> 'historial',
		        'match'				=> '/aciertos|historial/',
		    ),
		    
		),
		'layout' => 'sitio/layouts/default.phtml',
		
		);

$c = new Main($opts);
$c->run();