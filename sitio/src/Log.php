<?php

class Log
{
    private string      $log_end                = '<br />';
    private string      $archivo                = '';
    private bool        $salida                 = true;

    /*
    *   @param bool salida          envia logs a la salida, ya sea consola o al navegador.
    *   @param string archivo       guarda los logs en un archivo si se asigna
    */
    public function __construct(bool $_salida = true, string $_archivo = '')
    {
        $this->salida = $_salida;
        $this->archivo = $_archivo;
        if(substr(php_sapi_name(), 0, 3) == 'cli')$this->log_end = PHP_EOL;
    }
    public function log(string $msg)
    {
        $linea = "[" . date("Y/m/d H:i:s", time()) . "] " . $msg;

        if($this->salida) echo  $linea . $this->log_end;

        if($this->archivo != '')
        {
            $fd = fopen($this->archivo, "a");
            fwrite($fd, $linea . "\n");
            fclose($fd);
        }
    }
}