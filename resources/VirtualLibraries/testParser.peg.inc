<?php


namespace VirtualLibraries;

require_once dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/../vendor/hafriedlander/php-peg/autoloader.php';

use hafriedlander\Peg\Parser;

class testParser extends Parser\Basic
{
	private $log;
	public $invokedAll;
	public $invokedBool;
	public $invokedGeneric;
	
	/**
	 * Ctor. Initializes the logger.
	 */
	public function __construct($expr)
	{
		parent::__construct($expr);
		
		$this->log = \Logger::getLogger(__CLASS__);
		
		$this->invokedAll = false;
		$this->invokedBool = false;
		$this->invokedGeneric = false;
	}
	
    /**
     * Used to restart the Parser
     */
    public function rewind()
    {
        $this->pos = 0;
        $this->depth = 0 ;
        $this->regexps = array() ;
    }
    
    /**
     * Used to redefine the parser with a new expression
     * @param string $expr
     */
    public function redefine($expr)
    {
    	$this->string = $expr;
    	$this->rewind();
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

Expr3: All: (Bool: Bool | Name | String)
    function *(&$res, $sub)
    {
        $res['text*'] = 'FROM * function';
		
		$this->invokedGeneric = true;
		
		$result = var_export($res, true);
    	$this->log->info("In function *, result is:\n $result");
    }
    function All(&$res, $sub)
    {
        $res['textAll'] = 'FROM All function';
        $res['sub'] = $sub['textBool'];
		
		$this->invokedAll = true;		
		
		$result = var_export($res, true);
    	$this->log->info("In function All, result is:\n $result");
    }
    function Bool(&$res, $sub)
    {
        $res['textBool'] = 'FROM Bool function';
		
		$this->invokedBool = true;
		
		$result = var_export($res, true);
    	$this->log->info("In function Bool, result is:\n $result");
    }
*/    
    
}

