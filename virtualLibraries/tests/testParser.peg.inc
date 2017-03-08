<?php


namespace VirtualLibraries;

require_once dirname(dirname(__DIR__)) . '/php-peg/autoloader.php';
require_once dirname(dirname(__DIR__)) . '/log4php/Logger.php';

\Logger::configure(dirname(dirname(__DIR__)) . '/config.xml');
date_default_timezone_set('Europe/Berlin');

use hafriedlander\Peg\Parser;

class testParser extends Parser\Basic
{
    /**
     * Used to restart the Parser
     */
    public function rewind()
    {
        $this->pos = 0;
    }

/*!* testParser

Integer: /[0-9]+/

Bool: True: /[ \t]*true/ | False: /[ \t]*false/
    function True(&$res, $sub)
    {
        $res['val'] = true;
    }
    function False(&$res, $sub)
    {
        $res['val'] = false;
    }

Name: /[ \t]* / > Custom: ('#'?) > Name:(/[a-zA-Z][a-zA-Z0-9]* /)
    function Custom(&$res, $sub)
    {
        $res['custom'] = ($sub['text'] === '#') ;
    }
    function Name(&$res, $sub)
    {
        $res['text'] = $sub['text'] ;
    }

String: /[ \t]* / > '"' > String: /[^"]* /) > '"'
    function String(&$res, $sub)
    {
        $res['text'] = $sub['text'] ;
    }
    
Expr1: (Bool | Name | String)
    
Expr2:  All: (Bool | Name | String)
    function All(&$res, $sub)
    {
        $res['val'] = $sub['val'];    
        $res['text'] = 'FROM All function';
    }
    function Bool(&$res, $sub)
    {
        $res['val'] = $sub['val'];    
        $res['text'] = 'FROM Bool function';
    }

*/    
    
}

