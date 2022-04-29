<?php
require_once 'sitio/src/AppBase.php';

class Main
{
	private $router = null;
	private $router_home = null;
	private $router_no_found = null;
	private $routers = null;
	private $request = null;
	private $response = null;
	
	public function __construct(array $opts)
	{
		session_start();
		
		date_default_timezone_set('America/Argentina/Buenos_Aires');
		
		if(isset($opts['tiemposesion']) && isset($_SESSION['time']))
		{
		    if($_SESSION['time'] + (60 * $opts['tiemposesion']) < time())
			{
				session_destroy();
				session_start();
			}
		}
		$_SESSION['time'] = time();
		
		$this->request = new Request();		
		$this->loadConfig($opts);
	}
	private function loadConfig($c)
	{
		
		/*
		 * Carga routers.
		 * ===================================
		 * Hay 2 routers requeridos, "home" y "no-found". Y en cada route hay 2 parametros requeridos, "controller" y "default", 
		 * siendo "default" la accion por defecto. La primera parte de la uri debe coinsidir con la clave del route, simple, y
		 * la parte siguiente en la uri siempre ser� la acci�n. Las acciones permitidas para el route se registran en "match"
		 * separadas por "|". El restro de las partes de la uri se consideran parametros y se pasan en el orden de la uri. 
		 */
		if(!isset($c['routers']))$this->terminar("Error: missing routers.");
		$this->routers = array();
		foreach ($c['routers'] as $k => $v)
		{
			$this->routers[$k] = new Router($k, $v);
		}
		if(!isset($this->routers['home']))$this->terminar("Error: missing home router.");
		if(!isset($this->routers['no-found']))$this->terminar("Error: missing no-found router.");
		
		$this->router_home = $this->routers['home'];
		$this->router_no_found = $this->routers['no-found'];
		
		$this->router = null;
		foreach($this->routers as $r)
		{
			if($r->match($this->request)){$this->router = $r;break;}
		}
		
		if($this->router == null)
		{
			$this->router = $this->router_no_found;
		}
		
	}
	public function run()
	{
		if($this->router == null)$this->terminar("Error no router.");
		
		$controller = $this->getController($this->router->getController(), $this->router->getControllerPath());
		$method = $this->router->getAction()."Action";
		
		if(!$this->validAction($controller, $method))
		{
		    $controller = $this->getController($this->router_no_found->getController(), $this->router_no_found->getControllerPath());
		    $method = $this->router_no_found->getAction()."Action";
		}
		
		$this->response = $controller->{$method}();
		
		if($this->response instanceof Response)
		{
		    header("HTTP/1.1 ".$this->response->getCode());
		    header("Content-Type: ".$this->response->getContentType());
		    header("Expires: 31 Jan 2000 12:00:00 GMT");
			header("Pragma: no-cache");
			header("Cache-Control: no-cache");

			try
			{
		    	$render = $this->response->getRender();
		    	echo $render->render();
			}
			catch(\Exception $e)
			{
				$this->terminar("Error: No render.");
			}
		}
		else
		{
		    $this->terminar("Error: No Request returned.");
		}
		
	}
	private function getController($controller, $controller_path)
	{
		if(!file_exists($controller_path))$this->terminar("Error: Missing file: $controller_path");
		
		require_once $controller_path;
		
		if(!class_exists($controller))$this->terminar("Error: Missing ".$controller." Controller.");
		
		return new $controller($this);
	}
	private function validAction($controller, $method)
	{
		if(!method_exists($controller, $method)){return false;}
		$ref = new \ReflectionMethod($controller, $method);
		return $ref->isPublic() === true;
	}
	private function terminar($msg)
	{
		logToFile('main.log', $msg);
		header("Location: http://".$_SERVER['SERVER_NAME']."/errorinterno.html");
		exit();
	}
	public function getRequest(){return $this->request;}
	public function getRouter(){return $this->router;}
	public function getResponse(){return $this->response;}
	public function getDBConfig($p){if(isset($this->db_config[$p])){return $this->db_config[$p];}else{return null;}}
}