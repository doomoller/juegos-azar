<?php

class Meta
{
    private $title = "";
    private $keywords = "";
    private $description = "";
    
    public function __construct($title = "", $keys = "", $desc = "")
    {
        $this->title = $title;
        $this->keywords = $keys;
        $this->description = $desc;
    }
    public function setTitle($t){$this->title = $t;}
    public function getTitle(){return $this->title;}
    public function setKeywords($k){$this->keywords = $k;}
    public function getKeywords(){return $this->keywords;}
    public function setDescription($d){$this->description = $d;}
    public function getDescription(){return $this->description;}
}
class Layout
{
    private $rendered;
    private $content;
    private $file;
    private $meta;
    private $data;
    
    public function __construct($f, Meta $meta = null)
    {
        $this->file = $f;
        $this->meta = $meta;
        if($this->meta == null)$this->meta = new Meta();
    }
    public function render($content)
    {
        $this->content = $content;
        $this->rendered = '';
        try
        {
            ob_start();
            $include = include $this->file;
            $this->rendered = ob_get_clean();
        } catch (\Exception $ex) {
            ob_end_clean();
            throw $ex;
        }
        if($include === false)
        {
            throw new Exception("Include fails: ".$this->file);
        }
        return $this->rendered;
    }
    public function partial($file, $data)
    {
        $r = new Render($data, $file);
        return $r->render();
    }
    public function __get($p)
    {
        if($p == "meta")return $this->meta;
        if(isset($this->data[$p]))return $this->data[$p];
        return null;
    }
    public function linkEscape($p)
    {
        return preg_replace('/[^a-zA-Z0-9_Ã¡Ã©Ã­Ã³ÃºÃ�Ã‰Ã�Ã“ÃšÃ±Ã‘â€˜=:Â¡!-]+/', '-', $p);
    }
    public function setData($d){$this->data = $d;}
}
class Render
{
    protected $terminate_in_view = false;
    protected $data = array();
    protected $view = '';
    protected $content = '';
    protected $layout;
    
    public function __construct($data = null, $view = '', Layout $layout = null)
    {
        $this->data = $data;
        $this->view = $view;
        $this->layout = $layout;
    }
    public function render()
    {
        try
        {
            ob_start();
            $include = include $this->view;
            $this->content = ob_get_clean();
        }
        catch (\Exception $ex)
        {
            ob_end_clean();
            throw $ex;
        }
        if($include === false)
        {
            throw new Exception("Include fails: ".$this->view);
        }
        return $this->content;
    }
    public function partial($file, $data)
    {
        $r = new Render($data, $file);
        return $r->render();
    }
    public function __get($p)
    {
        if(isset($this->data[$p]))return $this->data[$p];
        return null;
    }
    public function linkEscape($p)
    {
        return preg_replace('/[^a-zA-Z0-9_Ã¡Ã©Ã­Ã³ÃºÃ�Ã‰Ã�Ã“ÃšÃ±Ã‘â€˜=:Â¡!-]+/', '-', $p);
    }
    public function setData($d){$this->data = $d;}
    public function setTerminateInView($b){$this->terminate_in_view = $b;}
    public function getTerminateInView(){return $this->terminate_in_view;}
    public function getContent(){return $this->content;}
    public function setLayout(Layout $l){$this->layout = $l;}
    public function getLayout(){return $this->layout;}
    public function getContentType(){return "text/html; charset=UTF-8";}
}
class RenderPHP extends Render
{
    public function __construct($data, $view, $layout = null)
    {
        parent::__construct($data, $view, $layout);
        
    }
    public function render()
    {
        try
        {
            ob_start();
            $include = include $this->view;
            $this->content = ob_get_clean();
            
            if($this->terminate_in_view === false && $this->layout != null)
            {
                $this->content = $this->layout->render($this->content);
            }
            
        }
        catch (\Exception $ex)
        {
            ob_end_clean();
            throw $ex;
        }
        if($include === false)
        {
            throw new Exception("Include fails: ".$this->view);
        }
        return $this->content;
    }
    public function getContentType(){return "text/html; charset=UTF-8";}
}
class RenderJSON extends Render
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->terminate_in_view = true;
    }
    public function render()
    {
        $this->content = json_encode($this->data);
        return $this->content;
    }
    public function getContentType(){return "application/json; charset=UTF-8";}
}
class RenderHTML extends Render
{
    public function __construct($data, $layout = null)
    {
        parent::__construct($data, '', $layout);
    }
    public function render()
    {
        try
        {
            $this->content = $this->data;
            if($this->terminate_in_view === false && $this->layout != null)
            {
                $this->content = $this->layout->render($this->content);
            }
            
        }
        catch (\Exception $ex)
        {
            throw $ex;
        }
        return $this->content;
    }
    public function getContentType(){return "text/html; charset=UTF-8";}
}