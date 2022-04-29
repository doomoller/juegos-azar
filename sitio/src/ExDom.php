<?php

class ExDom extends DOMDocument
{
    public DOMNode         $head;
    public DOMNode         $body;

    public function __construct(string $doc, string $version = "1.0", string $encoding = "UTF-8")
    {
        parent::__construct($version, $encoding);
        
        $this->preserveWhiteSpace = false;
        $this->loadHTML($doc, LIBXML_NOWARNING | LIBXML_NOERROR);

        $this->head = $this->getElementsByTagName('head')->item(0);
        $this->body = $this->getElementsByTagName('body')->item(0);


    }
   
    public function getByClass(string $class, DOMElement $in = null) :array // of DOMElement
    {
        $nodes = array();
        $from = is_null($in) ? $this->body : $in;
        foreach($from->childNodes as $child)
        {
            if(!($child instanceof DOMElement))continue;

            $attrs = explode(' ', $child->getAttribute('class'));
            if(in_array($class, $attrs))
            {
                array_push($nodes, $child);
            }
            $subs = $this->getByClass($class, $child);
            if(count($subs))
            {
                $nodes = array_merge($nodes, $subs);
            }
        }
        return $nodes;
    }
    public function getByTag(string $tag, DOMElement $in = null) :array // of DOMElement
    {
        $nodes = array();
        $from = is_null($in) ? $this->body : $in;
        foreach($from->childNodes as $child)
        {
            if(!($child instanceof DOMElement))continue;

            if($child->nodeName == $tag)
            {
                array_push($nodes, $child);
            }
            $subs = $this->getByTag($tag, $child);
            if(count($subs))
            {
                $nodes = array_merge($nodes, $subs);
            }
        }
        return $nodes;
    }

    public function getErrors() :array
    {
        $errors = libxml_get_errors();
        return $errors;
    }

}

