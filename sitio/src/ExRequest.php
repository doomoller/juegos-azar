<?php

class RequestResult
{
    public int          $codigo             = 0;
    public string       $contenido          = "";
    public bool         $error              = false;
    public string       $error_msg          = '';
}

class ExRequest
{
    public function __construct()
    {

    }

    public static function post($url, $params) :RequestResult
    {
        $r = new RequestResult;
        $query = http_build_query($params);
        $curl_id = curl_init($url);

        curl_setopt($curl_id, CURLOPT_TIMEOUT_MS, 20000);
        curl_setopt($curl_id, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_id, CURLOPT_URL, $url);
        curl_setopt($curl_id, CURLOPT_POST, true);
        curl_setopt($curl_id, CURLOPT_POSTFIELDS, $query);

        try
        {
            $r->contenido = curl_exec($curl_id);
        }
        catch(Exception $e)
        {
            $r->error = true;
            $r->error_msg = $e->getMessage();
        }
        $r->codigo = curl_getinfo($curl_id, CURLINFO_HTTP_CODE);

        if($r->contenido === false)
        {
            $r->error = true;
            $r->error_msg .= ' Error: codigo: ' . $r->codigo . '';
            $r->error_msg .= ' ' . curl_error($curl_id) . '';
            $r->contenido = 'error';
        }
        
        curl_close($curl_id);
        return $r;
    }

}