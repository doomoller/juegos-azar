<?php

require_once 'sitio/src/AppBase.php';
require_once 'sitio/src/CincoDeOro.php';

class Controller extends AppBase
{

    private CincoDeOro $oro5;

    public function __construct($main)
    {   
        parent::__construct($main);
        $this->metas = new Meta();
        $this->metas->setTitle("5 de ORO");
        
        $this->oro5 = new CincoDeOro();
        
    }
    public function estadisticasAction()
    {
        $data = array(
            "msg"		=> "Página estadisticas 5 de oro.",
            'tipo'      => 'estadisticas',
            'datos'     => array('oro' => $this->oro5->porcentaje_oro, 'revancha' => $this->oro5->porcentaje_revancha)
        );

        return $this->view("sitio/layouts/default.phtml", $data, $this->metas, "sitio/views/general.phtml");
    }
    public function historialAction()
    {
        $data = array(
            "msg"		=> "Página historial 5 de oro.",
            'tipo'      => 'historial',
            'datos'     => $this->oro5->historial
        );

        return $this->view("sitio/layouts/default.phtml", $data, $this->metas, "sitio/views/general.phtml");
    }
    public function recomendadasAction()
    {
        $bolillas = array();
        $oro = $this->oro5->porcentaje_oro;
        asort($oro);
        foreach($oro as $b => $p)
        {
            $bolillas[] = $b;
            if(count($bolillas) == 5)break;
        }
        $data = array(
            "msg"		=> "Página recomendadas 5 de oro.",
            'tipo'      => 'recomendadas',
            'datos'     => $bolillas
        );

        return $this->view("sitio/layouts/default.phtml", $data, $this->metas, "sitio/views/general.phtml");
    }
    public function aciertosAction()
    {
        $aciertos = array();
        $msg = "Página aciertos 5 de oro.";

        $req = $this->main->getRequest();
        if($req->isPost() && $req->getMetodoParametro('bolillas') != null)
        {
            $bolillas = explode(' ', $req->getMetodoParametro('bolillas'));
            if(count($bolillas) != 5)
            {
                $msg = 'Escriba los 5 números para comprobar.';
            }
            else
            {
                $aciertos = $this->oro5->comprobarBolillas($bolillas);
            }
        }
        
        $data = array(
            "msg"		=> $msg,
            'tipo'      => 'aciertos',
            'datos'     => $aciertos
        );

        return $this->view("sitio/layouts/default.phtml", $data, $this->metas, "sitio/views/general.phtml");
    }
        
}