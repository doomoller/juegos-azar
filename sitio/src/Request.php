<?php

class Request
{
    private $query = null;
    private $query_params = null;
    private $metodo = "";
    private $metodo_params = null;
    private $uri = "";
    private $uri_parts = array();
    private $is_api = false;
    
    public function __construct()
    {
        $req = explode("?", strtolower($_SERVER['REQUEST_URI']));
        if(count($req) > 1)
        {
            $this->uri = $req[0];
            $this->query = $req[1];
            
            $prs = explode("&", $this->query);
            for($i = 0; $i < count($prs); $i++)
            {
                if(trim($prs[$i]) == "")continue;
                
                $prs2 = explode("=", $prs[$i]);
                if(count($prs2) == 1 || trim($prs2[1]) == ""){$this->query_params[$prs2[0]] = null;}
                else{$this->query_params[$prs2[0]] = urldecode($prs2[1]);}
            }
        }
        else
        {
            $this->uri = $req[0];
        }
        
        $i = 0;
        foreach(explode("/", $this->uri) as $k => $p)
        {
            if($p != "")
            {
                if($p == 'api')
                {
                    $this->is_api = true;
                }
                else
                {
                    $this->uri_parts[$i++] = urldecode($p);
                }
            }
        }
        
        $this->metodo = $_SERVER['REQUEST_METHOD'];
        if($this->metodo == "POST"){$this->metodo_params = $_POST;}
        else if($this->metodo == "GET"){$this->metodo_params = $_GET;}
        else if($this->metodo == "PUT")
        {
            parse_str(file_get_contents('php://input', false , null, 0 , $_SERVER['CONTENT_LENGTH'] ), $this->metodo_params);
        }
        
    }
    public function getUri(){return $this->uri;}
    public function getUriParts(){return $this->uri_parts;}
    public function getUriPart($index){return isset($this->uri_parts[$index]) ? $this->uri_parts[$index] : null;}
    public function getQuery(){return $this->query;}
    public function getQueryParam($param, $default = null)
    {
        if(isset($this->query_params[$param]))return $this->query_params[$param];
        return $default;
    }
    public function getQueryParams(){return $this->query_params;}
    public function isGet(){return $this->metodo == "GET";}
    public function isPost(){return $this->metodo == "POST";}
    public function isPut(){return $this->metodo == "PUT";}
    public function isDelete(){return $this->metodo == "DELETE";}
    public function getMetodoParametros(){return $this->metodo_params;}
    public function getMetodoParametro($param, $default = null)
    {
        if(isset($this->metodo_params[$param]))return $this->metodo_params[$param];
        return $default;
    }
    public function isApi(){return $this->is_api;}
}
class Router
{
    private $name = "";
    private $controller = "";
    private $controller_path = '';
    private $default_action = "";
    private $match = "";
    private $action = "";
    private $params = array();
    
    public function __construct($name, array $opts)
    {
        $this->name = $name;
        if(isset($opts['controller']))$this->controller = $opts['controller'];
        if(isset($opts['controller_path']))$this->controller_path = $opts['controller_path'];
        if(isset($opts['default']))$this->default_action = $opts['default'];
        if(isset($opts['match']))$this->match = $opts['match'];
        if($this->controller == "")throw new Exception("Missing controller on route: $name");
        if($this->controller_path == "")throw new Exception("Missing controller path on route: $name");
        if($this->default_action == "")throw new Exception("Missing default action on route: $name");
    }
    public function match(Request $r)
    {
        if($this->name == "home" && ($r->getUri() == "/" || $r->getUri() == ""))
        {
            $this->action = $this->default_action;
            return true;
        }
        $uri_parts = $r->getUriParts();
        $parts = count($uri_parts);
        if($parts > 0)
        {
            if($this->name != $uri_parts[0])return false;
            // match, busca acción
            if($parts > 1)
            {
                if(preg_match($this->match, $uri_parts[1]) == 1)
                {
                    $this->action = $uri_parts[1];
                }
                else
                {
                    $this->action = $this->default_action;
                }
            }
            // el resto de las partes de la uri son parametros
            for($i = 2; $i < $parts; $i++)
            {
                $this->params[] = $uri_parts[$i];
            }
        }
        return true;
    }
    public function getName(){return $this->name;}
    public function getController(){return $this->controller;}
    public function getControllerPath(){return $this->controller_path;}
    public function getAction(){return $this->action == "" ? $this->default_action : $this->action;}
    public function getParams(){return $this->params;}
    public function getParam($index){return (isset($this->params[$index]) ? $this->params[$index] : null);}
}
class Response
{
    private $code;
    private $render;
    private $content_type = null;
    
    public function __construct($render, $code)
    {
        $this->code = $code;
        $this->render = $render;
    }
    public function setRender(Render $r){$this->render = $r;}
    public function getRender(){return $this->render;}
    public function setCode(int $c){$this->code = $c;}
    public function getCode(){return $this->code;}
    public function setContentType($c){$this->content_type = $c;}
    public function getContentType(){if($this->content_type != null){return $this->content_type;}else{return $this->render->getContentType();}}
}