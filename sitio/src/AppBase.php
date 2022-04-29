<?php

require_once 'sitio/src/Main.php';
require_once 'sitio/src/Request.php';
require_once 'sitio/src/Renders.php';
require_once 'sitio/src/Utils.php';

class AppBase 
{
	protected $cliente_ip;
	protected $csrf;
	protected Main $main;
	protected $metas;

	public function __construct(Main $main)
	{
		$this->main = $main;
		$this->cliente_ip = $_SERVER['REMOTE_ADDR'];
		$this->metas = new Meta();
		$this->metas->setTitle("5 de ORO");
		
		if(empty($_SESSION['csrf']))
		{
		    $_SESSION["csrf"] = substr(str_shuffle(md5(time().$this->cliente_ip)),0, 30);
		    $_SESSION["cliente_ip"] = $this->cliente_ip;
		}
		$this->csrf = $_SESSION["csrf"];
		
	}
	public function comprobarCSRF(array $post)
	{
		return (isset($post['csrf']) && $post['csrf'] == $_SESSION["csrf"]);
	}

	public function nofoundAction($msg = null)
	{
	    return $this->view(
	                   "sitio/layouts/default.phtml", 
	                   array("msg" => ($msg == null ? "La página no está disponible." : $msg)), 
	                   $this->metas, 
	                   "sitio/views/nofound.phtml", 
	                   404
	               );
	}
	public function errorAction($msg = null, $codigo = 503)
	{
	    return $this->view(
	                   "sitio/layouts/default.phtml", 
	                   array("msg" => ($msg == null ? "A ocurrido un error." : $msg)), 
	                   $this->metas, 
	                   "sitio/views/error.phtml", 
	                   $codigo
	               );
	}
	public function view($layout_file, $data, $metas, $vista_file, $codigo = 200)
	{
	    $data['csrf'] = $this->csrf;

		if($this->main->getRequest()->isApi())
		{
			return new Response(new RenderJSON($data), $codigo);
		}
	    
	    $l = new Layout($layout_file, $metas);
	    $l->setData($data);		
	    return new Response(new RenderPHP($data, $vista_file, $l), $codigo);
	}

}


