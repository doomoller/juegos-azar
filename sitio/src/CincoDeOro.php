<?php
require_once 'sitio/src/Log.php';
require_once 'sitio/src/ExDom.php';
require_once 'sitio/src/ExRequest.php';

class Sorteo
{
    public DateTime     $fecha;
    public array        $oro                = array(0, 0, 0, 0, 0);
    public int          $extra              = 0;
    public array        $revancha           = array(0, 0, 0, 0, 0);
    public int          $aciertos_oro       = 0;
    public int          $aciertos_plata     = 0;
    public int          $aciertos_revancha  = 0;
    public int          $monto_oro          = 0;
    public int          $monto_plata        = 0;
    public int          $monto_revancha     = 0;
}

class CincoDeOro extends Log
{
    private string      $res_pref                   = '';

    private float       $oro                        = 0;
    private float       $plata                      = 0;

    private string      $url_base                   = 'https://www3.labanca.com.uy';
    private string      $url_resultados             = '/resultados/cincodeoro';
    private string      $form_fecha                 = 'fecha_sorteo';
    private string      $form_commit                = 'commit';

    private array       $historial                  = array();      // fecha => Sorteo
    private array       $estadisticas_oro           = array();      // bolilla => veces que salio
    private array       $estadisticas_revancha      = array();      // bolilla => veces que salio
    private int         $total_veces_oro            = 0;
    private int         $total_veces_revancha       = 0;
    private array       $porcentaje_oro             = array();      // bolilla => porcentaje sobre el total que salio esa bolilla
    private array       $porcentaje_revancha        = array();      // bolilla => porcentaje sobre el total que salio esa bolilla

    public const        MAX_INTENTOS_HISTORIAL      = 100;          // maximo de intentos seguidos con error

    public function __construct(bool $_log = false)
    {   
        parent::__construct(false, '5-de-oro.log');

        $this->res_pref = getcwd() . DIRECTORY_SEPARATOR . 'sitio' . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR;

        $this->cargarEstadisticas();
        $this->cargarHistorialSorteos();
        $this->actualizarHistorialSorteos();
        krsort($this->historial);
    }
    /*
    *   Carga el historial de sorteos en xml. Formato: 
    *   <sorteo fecha="2022-02-16">
            <oro aciertos_oro="0" aciertos_plata="0" monto_oro="6623171" monto_plata="621894">
                <bolilla>10</bolilla><bolilla>17</bolilla><bolilla>25</bolilla><bolilla>33</bolilla><bolilla>46</bolilla>
                <extra>44</extra>
            </oro>
            <revancha aciertos_revancha="0" monto_revancha="28812185">
                <bolilla>11</bolilla><bolilla>17</bolilla><bolilla>21</bolilla><bolilla>38</bolilla><bolilla>41</bolilla>
            </revancha>
        </sorteo>
    */
    public function cargarHistorialSorteos($archivo = '')
    {
        $arch = ($archivo == '' ? $this->res_pref . 'historial-sorteos.xml' : $archivo);
        if(!file_exists($arch))return;

        $this->log('Carga historial de sorteos: ' . $arch);

        $this->historial = array();
        $xml = new SimpleXMLElement(file_get_contents($arch));
        foreach($xml->children() as $s)
        {
            $sorteo = new Sorteo();
            $sorteo->fecha = new DateTime($s->attributes()->fecha);

            for($b = 0; $b < 5; $b++)$sorteo->oro[$b] = (int)$s->oro->bolilla[$b];
            for($b = 0; $b < 5; $b++)$sorteo->revancha[$b] = (int)$s->revancha->bolilla[$b];
            
            $sorteo->extra = (int) $s->oro->extra;
            $sorteo->aciertos_oro = (int) $s->oro->attributes()->aciertos_oro;
            $sorteo->aciertos_plata = (int) $s->oro->attributes()->aciertos_plata;
            $sorteo->monto_oro = (int) $s->oro->attributes()->monto_oro;
            $sorteo->monto_plata = (int) $s->oro->attributes()->monto_plata;

            $sorteo->aciertos_revancha = (int) $s->revancha->attributes()->aciertos_revancha;
            $sorteo->monto_revancha = (int) $s->revancha->attributes()->monto_revancha;

            $this->historial[$sorteo->fecha->format('Y-m-d')] = $sorteo;

        }
        $this->log('Historial de sorteos: ' . count($this->historial));
    }
    /*
    *   Guarda el historial de sorteos en xml. Formato: 
    *   <sorteo fecha="2022-02-16">
            <oro aciertos_oro="0" aciertos_plata="0" monto_oro="6623171" monto_plata="621894">
                <bolilla>10</bolilla><bolilla>17</bolilla><bolilla>25</bolilla><bolilla>33</bolilla><bolilla>46</bolilla>
                <extra>44</extra>
            </oro>
            <revancha aciertos_revancha="0" monto_revancha="28812185">
                <bolilla>11</bolilla><bolilla>17</bolilla><bolilla>21</bolilla><bolilla>38</bolilla><bolilla>41</bolilla>
            </revancha>
        </sorteo>
    *   Devuelve true si puede guardar el archivo y false en caso se error.
    *
    *   @return bool
    */
    public function guardarHistorialSorteos($archivo = '') : bool
    {
        $this->log('Guarda el historial de sorteos: ' . $archivo);
        ksort($this->historial);

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
        foreach($this->historial as $sorteo)
        {
            $sorteox = $xml->addChild('sorteo');
            $sorteox->addAttribute('fecha', $sorteo->fecha->format('Y-m-d'));
            $oro = $sorteox->addChild('oro');
            $oro->addAttribute('aciertos_oro', $sorteo->aciertos_oro);
            $oro->addAttribute('aciertos_plata', $sorteo->aciertos_plata);
            $oro->addAttribute('monto_oro', $sorteo->monto_oro);
            $oro->addAttribute('monto_plata', $sorteo->monto_plata);
            foreach($sorteo->oro as $bolilla)$oro->addChild('bolilla', $bolilla);
            $oro->addChild('extra', $sorteo->extra);
            $revancha = $sorteox->addChild('revancha');
            $revancha->addAttribute('aciertos_revancha', $sorteo->aciertos_revancha);
            $revancha->addAttribute('monto_revancha', $sorteo->monto_revancha);
            foreach($sorteo->revancha as $bolilla)$revancha->addChild('bolilla', $bolilla);
        }
        return $xml->asXML(($archivo == '' ? $this->res_pref . 'historial-sorteos.xml' : $archivo));
    }
    /*
    *   Actualiza el historial de sorteos buscando hacia atras.
    *   Devuelve la cantidad de nuevos sorteos agregados.
    *
    *   @return int
    */
    public function actualizarHistorialSorteos() : int
    {
        $this->log('Actualiza historial de sorteo.');

        $intentos = 0;
        $nuevos = 0;
        $fecha = new DateTime();
        $dia = new DateInterval('P1D');
        while($intentos < CincoDeOro::MAX_INTENTOS_HISTORIAL)
        {
            $s = $this->colectarSorteo($fecha);
            if($s === false)$intentos++;
            else
            {
                if(isset($this->historial[$fecha->format('Y-m-d')])) break;

                $nuevos++;
                $intentos = 0;
                $this->historial[$fecha->format('Y-m-d')] = $s;
                $this->actualizarEstadisticas($s);
            }
            $fecha->sub($dia);
        }
        $this->log('Nuevos sorteos: ' . $nuevos);
        if($nuevos > 0) $this->guardarHistorialSorteos();
        return $nuevos;
    }

   /*
    *   Carga las estadisticas de las veces que salieron los numeros. Formato: 
    *   <periodo inicio="1998-06-14" fin="2014-03-16">
            <oro>
                <b bolilla="1" veces="248" />
                <b bolilla="2" veces="248" />
                ...
            </oro>
            <revancha>
                <b bolilla="1" veces="248" />
                <b bolilla="2" veces="248" />
                ...
            </revancha>
        </periodo>
    */
    public function cargarEstadisticas()
    {
        $arch = $this->res_pref . 'estadisticas.xml';
        if(!file_exists($arch))return;

        $this->log('Carga estadisticas de bolillas: ' . $arch);

        $this->estadisticas_oro = array_fill(1, 48, 0);
        $this->estadisticas_revancha = array_fill(1, 48, 0);
        $this->total_veces_oro = 0;
        $this->total_veces_revancha = 0;

        $xml = new SimpleXMLElement(file_get_contents($arch));
        foreach($xml->children() as $periodo)
        {
            foreach($periodo->oro->children() as $b)
            {
                $this->estadisticas_oro[(int)$b->attributes()->bolilla] += (int) $b->attributes()->veces;
                $this->total_veces_oro += (int) $b->attributes()->veces;
            } 

            foreach($periodo->revancha->children() as $b)
            {
                $this->estadisticas_revancha[(int)$b->attributes()->bolilla] += (int) $b->attributes()->veces;
                $this->total_veces_revancha += (int) $b->attributes()->veces;
            }
        }
        /*
        *   Hasta el 2014 el sorteo solo tenia 44 bolillas, ahora 48. 
        *   Para usar los valores antiguos como base simplifica agregando
        *   el promedio de veces que salieron las anteriores al 2014 en
        *   las 45 a 48
        */
        $this->estadisticas_oro[45] += 220;
        $this->estadisticas_oro[46] += 220;
        $this->estadisticas_oro[47] += 220;
        $this->estadisticas_oro[48] += 220;

        $this->estadisticas_revancha[45] += 183;
        $this->estadisticas_revancha[46] += 183;
        $this->estadisticas_revancha[47] += 183;
        $this->estadisticas_revancha[48] += 183;

        $this->total_veces_oro += 220 * 4;
        $this->total_veces_revancha += 183 * 4;

        $this->actualizarPorcentajes();
    }
    private function actualizarPorcentajes()
    {
        $this->porcentaje_oro = array_fill(1, 48, 0.0);
        $this->porcentaje_revancha = array_fill(1, 48, 0.0);
        for($b = 1; $b < 49; $b++)
        {
            $this->porcentaje_oro[$b] = (float)$this->estadisticas_oro[$b] / (float)$this->total_veces_oro * 100.0;
            $this->porcentaje_revancha[$b] = (float)$this->estadisticas_revancha[$b] / (float)$this->total_veces_revancha * 100.0;
        }
    }
    /*
    *   Actualiza el archivo de estadisticas de las bolillas.
    *   Solo actualiza el periodo de sorteos nuevos. Los mas
    *   antiguos no se tocan.
    *
    *   @return bool
    */
    public function actualizarEstadisticas(Sorteo $sorteo)
    {
        $arch = $this->res_pref . 'estadisticas.xml';
        if(!file_exists($arch))return;

        $this->log('Actualiza estadisticas de bolillas: ' . $arch);

        $xml = new SimpleXMLElement(file_get_contents($arch));
        foreach($xml->children() as $periodo)
        {
            if($periodo->attributes()->fin == 'hoy')  // solo actualiza los ultimos sorteos, los antigos no se tocan
            {
                foreach($periodo->oro->children() as $b)
                {
                    if(in_array($b->attributes()->bolilla, $sorteo->oro))
                    {
                         $b->attributes()->veces = 1 + (int)$b->attributes()->veces;
                         $this->estadisticas_oro[(int)$b->attributes()->bolilla] += 1;
                         $this->total_veces_oro += 1;
                    }
                }
                foreach($periodo->revancha->children() as $b)
                {
                    if(in_array($b->attributes()->bolilla, $sorteo->revancha))
                    {
                        $b->attributes()->veces = 1 + (int)$b->attributes()->veces;
                        $this->total_veces_revancha += 1;
                    }
                }
            }
        }
        $this->actualizarPorcentajes();
        return $xml->asXML($arch);
    }

    /*
    *   Busca todos los sorteos desde hoy hacia atras en el tiempo.
    *   Lleva un conteo de errores seguidos para saber si el html 
    *   cambio o ya no hay sorteos.
    *   Devuelve el numero de sorteos cargados.
    *
    *   @return int
    */
    public function crearHistorialSorteos() : int
    {
        $this->historial = array();
        $intentos = 0;
        $fecha = new DateTime();
        $dia = new DateInterval('P1D');
        while($intentos < CincoDeOro::MAX_INTENTOS_HISTORIAL)
        {
            $s = $this->colectarSorteo($fecha);
            if($s === false)$intentos++;
            else
            {
                $intentos = 0;
                $this->historial[$fecha->format('Y-m-d')] = $s;
            }
            $fecha->sub($dia);
        }

        return count($this->historial);
    }
    private function logSorteo(Sorteo $sorteo)
    {
        echo 'Sorteo (' . $sorteo->fecha->format('Y-m-d') . ') oro: ' 
        . $sorteo->oro[0] . '-' . $sorteo->oro[1] . '-' . $sorteo->oro[2] . '-' . $sorteo->oro[3] . '-' . $sorteo->oro[4]
        . ' extra: ' . $sorteo->extra
        . ' revancha: ' . $sorteo->revancha[0] . '-' . $sorteo->revancha[1] . '-' . $sorteo->revancha[2] . '-' . $sorteo->revancha[3] . '-' . $sorteo->revancha[4]
        . ' aciertos oro: ' . $sorteo->aciertos_oro 
        . ' aciertos plata: ' . $sorteo->aciertos_plata
        . ' aciertos revancha ' . $sorteo->aciertos_revancha
        . ' monto oro: ' . $sorteo->monto_oro
        . ' monto plata: ' . $sorteo->monto_plata
        . ' monto revancha: ' . $sorteo->monto_revancha
        . $this->log_end;
    }
    /*
    *   Busca un sorteo en el sitio oficial para la fecha dada.
    *   Devuelve el Sorteo o false si hay error.
    *
    *   @return Sorteo | false
    */
    public function colectarSorteo(DateTime $fecha)
    {
        $request = ExRequest::post(
            $this->url_base . $this->url_resultados, 
            array($this->form_fecha => $fecha->format('Y-m-d') . '-22:00', $this->form_commit => 'Mostrar')
        );
        if($request->error)$this->log('Error post: al buscar sorteo: ' . $request->error_msg);
        if($request->codigo != 200)return false;

        $sorteo = $this->parsearSorteoHtml($request->contenido);
        if($sorteo !== false)$sorteo->fecha = clone $fecha;
        return $sorteo;
    }
    /*
    *   Busca un sorteo en el historial y lo devuelve
    *   Devuelve el Sorteo o false si no hay.
    *
    *   @return Sorteo | false
    */
    public function buscarSorteo(string $fecha)
    {
        if(isset($this->historial[$fecha])) return clone $this->historial[$fecha];
        return false;
    }
    /*
    *   Comprueva las bolillas dadas y devuelve todos los aciertos.
    *   Devuelve los sorteos donde hay aciertos parciales y completos.
    *   Para N aciertos un array de aciertos. Minimo de 2 aciertos (N = 2)
    *
    *   @return array(
                    'bolillas' => array(5)
                    'oro' => array(N => array(Sorteo)),
                    'revancha' => array(N => array(Sorteo))
                )
    */
    public function comprobarBolillas(array $bolillas) :array
    {
        $aciertos = array(
            'bolillas' => array(),
            'oro' => array(2 => array(), 3 => array(), 4 => array(), 5 => array(), 'plata' => array()),
            'revancha' => array(2 => array(), 3 => array(), 4 => array(), 5 => array())
        );

        if(count($bolillas) != 5) return $aciertos;

        foreach($this->historial as $fecha => $sorteo)
        {
            $aciertos_oro = 0;
            $aciertos_revancha = 0;
            for($b = 0; $b < 5; $b++)
            {
                if(in_array($sorteo->oro[$b], $bolillas)) $aciertos_oro++;

                if(in_array($sorteo->revancha[$b], $bolillas)) $aciertos_revancha++;
            }

            if($aciertos_oro > 1) $aciertos['oro'][$aciertos_oro][] = clone $sorteo;

            if($aciertos_oro == 4 && in_array($sorteo->extra, $bolillas)) $aciertos['oro']['plata'][] = clone $sorteo;

            if($aciertos_revancha > 1) $aciertos['revancha'][$aciertos_revancha][] = clone $sorteo;
        }
        $aciertos['bolillas'] = $bolillas;
        return $aciertos;
    }
    /*
    *   Extrae los datos de un sorteo de 5 de oro del html.
    *
    *   @ Sorteo | false
    */
    private function parsearSorteoHtml(string $doc)
    {
        $s = new Sorteo();
        $dom = new ExDom($doc);
        
        $res_tag = $dom->getByClass('resultados');
        if(!count($res_tag))
        {
            $this->log('Error paresear html: no se puede encontrar resultados.');
            return false;
        }
        /*
            ejemplo oro

            <ul class="bolillas small-block-grid-7">
                <li><img alt="10" src="/assets/bolillas/oro/10-b39ddc59907be6fffac2fc898410d5c3.png" /></li>
                <li><img alt="17" src="/assets/bolillas/oro/17-f8a89f8540752011cafce2a42f86bf29.png" /></li>
                <li><img alt="25" src="/assets/bolillas/oro/25-4c718856d01ae7406bd87d57ee0c3969.png" /></li>
                <li><img alt="33" src="/assets/bolillas/oro/33-160a8897533cccb1fa9e1da34a43d5a2.png" /></li>
                <li><img alt="46" src="/assets/bolillas/oro/46-b9af0bbbc6631105ae6f81297f41dd20.png" /></li>
                <li class="extra"><img alt="44" src="/assets/bolillas/oro/44-02a96affcfba7d29fb44c42237d99d1c.png" /></li>
                <li class="caption">Extra</li>
            </ul>

            ejemplo revancha

            <ul class="bolillas small-block-grid-7">
                <li><img alt="11" src="/assets/bolillas/oro/11-d8553f2cf726f4acfa6d509294992998.png" /></li>
                <li><img alt="17" src="/assets/bolillas/oro/17-f8a89f8540752011cafce2a42f86bf29.png" /></li>
                <li><img alt="21" src="/assets/bolillas/oro/21-d98e470dbce729831a65e52a00017fdf.png" /></li>
                <li><img alt="38" src="/assets/bolillas/oro/38-4547ace44131fc4d27cec00a8c2a35c9.png" /></li>
                <li><img alt="41" src="/assets/bolillas/oro/41-6db77eed53927a01ecfa0c6267b06f13.png" /></li>
            </ul>            
        */
        $bol_tag = $dom->getByClass('bolillas', $res_tag[0]);
        if(count($bol_tag) != 2)
        {
            $this->log('Error paresear html: no se puede encontrar las bolillas.');
            return false;
        }
        // busca 5 de oro
        $bol_imgs = $dom->getByTag('img', $bol_tag[0]);
        if(count($bol_imgs) != 6)
        {
            $this->log('Error paresear html: no se puede encontrar las bolillas del 5 de oro.');
            return false;
        }
        if($bol_imgs[0]->getAttribute('alt') == '')
        {
            $this->log('Error paresear html: las bolillas no tienen el numero (alt).');
            return false;
        }

        $s->oro[0] = (int)$bol_imgs[0]->getAttribute('alt');
        $s->oro[1] = (int)$bol_imgs[1]->getAttribute('alt');
        $s->oro[2] = (int)$bol_imgs[2]->getAttribute('alt');
        $s->oro[3] = (int)$bol_imgs[3]->getAttribute('alt');
        $s->oro[4] = (int)$bol_imgs[4]->getAttribute('alt');
        $s->extra = (int)$bol_imgs[5]->getAttribute('alt');

        // busca revancha
        $bol_imgs = $dom->getByTag('img', $bol_tag[1]);
        if(count($bol_imgs) != 5)
        {
            $this->log('Error paresear html: no se puede encontrar las bolillas del revancha.');
            return false;
        }
        if($bol_imgs[0]->getAttribute('alt') == '')
        {
            $this->log('Error paresear html: las bolillas del revancha no tienen el numero (alt).');
            return false;
        }

        $s->revancha[0] = (int)$bol_imgs[0]->getAttribute('alt');
        $s->revancha[1] = (int)$bol_imgs[1]->getAttribute('alt');
        $s->revancha[2] = (int)$bol_imgs[2]->getAttribute('alt');
        $s->revancha[3] = (int)$bol_imgs[3]->getAttribute('alt');
        $s->revancha[4] = (int)$bol_imgs[4]->getAttribute('alt');

        /*
            busca montos

            <div class="large-5 columns pozo">
                <span class="monto-pozo">$ 6.623.171</span>
                <span class="monto-pozo">$ 621.894</span>
            </div>
        */

        $montos_tag = $dom->getByClass('monto-pozo', $res_tag[0]);
        if(count($montos_tag) != 3)
        {
            $this->log('Error paresear html: no se puede encontrar los montos.');
            return false;
        }

        $s->monto_oro = (int) (str_replace('.', '', str_replace('$', '', $montos_tag[0]->nodeValue)));
        $s->monto_plata = (int) (str_replace('.', '', str_replace('$', '', $montos_tag[1]->nodeValue)));
        $s->monto_revancha = (int) (str_replace('.', '', str_replace('$', '', $montos_tag[2]->nodeValue)));


        /*
            busca aciertos

            <div class="large-3 columns pozo">
                <span class="aciertos">(0 aciertos) </span>
                <span class="aciertos">(0 aciertos)</span>
            </div>
        */

        $aciertos_tag = $dom->getByClass('aciertos', $res_tag[0]);
        if(count($aciertos_tag) != 3)
        {
            $this->log('Error paresear html: no se puede encontrar los aciertos.');
            return false;
        }

        $s->aciertos_oro = (int) (str_replace('(', '', str_replace('aciertos', '', str_replace(')', '', $aciertos_tag[0]->nodeValue))));
        $s->aciertos_plata = (int) (str_replace('(', '', str_replace('aciertos', '', str_replace(')', '', $aciertos_tag[1]->nodeValue))));
        $s->aciertos_revancha = (int) (str_replace('(', '', str_replace('aciertos', '', str_replace(')', '', $aciertos_tag[2]->nodeValue))));

        return $s;
    }
    public function probabilidadOro() :float
    {
        $this->oro = 0;
        $this->plata = 0;

        // 1.0 / 1712304.0
        $b1 = 5.0 / 48.0;
        $b2 = 4.0 / 47.0;
        $b3 = 3.0 / 46.0;
        $b4 = 2.0 / 45.0;
        $b5 = 1.0 / 44.0;

        $this->oro = ($b1 * $b2 * $b3 * $b4 * $b5);

        $this->log('5 de Oro: ' . number_format($this->oro, 10, ',', '.') . ' %' . number_format($this->oro * 100.0, 10, ',', '.') . ' Posibles: ' . number_format(round(1.0 / $this->oro, 0, PHP_ROUND_HALF_UP), 0, ',', '.'));
        
        return $this->oro;
    }
    public function probabilidadPlata() :float
    {
        $this->probabilidadOro();

        // plata 1 / 342.461
        $this->plata = $this->oro * 5.0;

        $this->log('Plata: ' . number_format($this->plata, 10, ',', '.') . ' %' . number_format($this->plata * 100.0, 10, ',', '.') . ' Posibles: ' . number_format(round(1.0 / $this->plata, 0, PHP_ROUND_HALF_UP), 0, ',', '.'));
        
        return $this->plata;
    }

    public function pruebaSorteo() :Sorteo
    {
        $s = new Sorteo();
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadHTMLFile('paginaTest.html', LIBXML_NOWARNING | LIBXML_NOERROR);
        $s = $this->parsearSorteoHtml($dom->saveHTML());
        return $s;
    }

    public function __get($n)
    {
        switch($n)
        {
            case 'historial': return $this->historial;
            case 'estadisticas_oro': return $this->estadisticas_oro;
            case 'estadisticas_revancha': return $this->estadisticas_revancha;
            case 'total_veces_oro': return $this->total_veces_oro;
            case 'total_veces_revancha': return $this->total_veces_revancha;
            case 'porcentaje_oro': return $this->porcentaje_oro;
            case 'porcentaje_revancha': return $this->porcentaje_revancha;
        }
        return null;
    }

}

