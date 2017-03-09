<?php


namespace VirtualLibraries;

require_once dirname(dirname(__DIR__)) . '/php-peg/autoloader.php';
require_once dirname(dirname(__DIR__)) . '/log4php/Logger.php';

\Logger::configure(dirname(dirname(__DIR__)) . '/config.xml');
date_default_timezone_set('Europe/Berlin');

use hafriedlander\Peg\Parser;

class testParser extends Parser\Basic
{
	private $log;
	
	/**
	 * Ctor. Initializes the logger.
	 */
	public function __construct($expr)
	{
		parent::__construct($expr);
		
		$this->log = \Logger::getLogger(__CLASS__);
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

/* Integer: /[0-9]+/ */
protected $match_Integer_typestack = array('Integer');
function match_Integer ($stack = array()) {
	$matchrule = "Integer"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/[0-9]+/' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* Bool: True: /[ \t]*true/ | False: /[ \t]*false/ */
protected $match_Bool_typestack = array('Bool');
function match_Bool ($stack = array()) {
	$matchrule = "Bool"; $result = $this->construct($matchrule, $matchrule, null);
	$_4 = NULL;
	do {
		$res_1 = $result;
		$pos_1 = $this->pos;
		$stack[] = $result; $result = $this->construct( $matchrule, "True" ); 
		if (( $subres = $this->rx( '/[ \t]*true/' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'True' );
			$_4 = TRUE; break;
		}
		else { $result = array_pop($stack); }
		$result = $res_1;
		$this->pos = $pos_1;
		$stack[] = $result; $result = $this->construct( $matchrule, "False" ); 
		if (( $subres = $this->rx( '/[ \t]*false/' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'False' );
			$_4 = TRUE; break;
		}
		else { $result = array_pop($stack); }
		$result = $res_1;
		$this->pos = $pos_1;
		$_4 = FALSE; break;
	}
	while(0);
	if( $_4 === TRUE ) { return $this->finalise($result); }
	if( $_4 === FALSE) { return FALSE; }
}

public function Bool_True (&$res, $sub)
    {
        $res['val'] = true;
    }

public function Bool_False (&$res, $sub)
    {
        $res['val'] = false;
    }

/* Name: /[ \t]* / > Custom: ('#'?) > Name:(/[a-zA-Z][a-zA-Z0-9]* /) */
protected $match_Name_typestack = array('Name');
function match_Name ($stack = array()) {
	$matchrule = "Name"; $result = $this->construct($matchrule, $matchrule, null);
	$_15 = NULL;
	do {
		if (( $subres = $this->rx( '/[ \t]* /' ) ) !== FALSE) { $result["text"] .= $subres; }
		else { $_15 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		$stack[] = $result; $result = $this->construct( $matchrule, "Custom" ); 
		$_9 = NULL;
		do {
			$res_8 = $result;
			$pos_8 = $this->pos;
			if (substr($this->string,$this->pos,1) == '#') {
				$this->pos += 1;
				$result["text"] .= '#';
			}
			else {
				$result = $res_8;
				$this->pos = $pos_8;
				unset( $res_8 );
				unset( $pos_8 );
			}
			$_9 = TRUE; break;
		}
		while(0);
		if( $_9 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'Custom' );
		}
		if( $_9 === FALSE) {
			$result = array_pop($stack);
			$_15 = FALSE; break;
		}
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		$stack[] = $result; $result = $this->construct( $matchrule, "Name" ); 
		$_13 = NULL;
		do {
			if (( $subres = $this->rx( '/[a-zA-Z][a-zA-Z0-9]* /' ) ) !== FALSE) { $result["text"] .= $subres; }
			else { $_13 = FALSE; break; }
			$_13 = TRUE; break;
		}
		while(0);
		if( $_13 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'Name' );
		}
		if( $_13 === FALSE) {
			$result = array_pop($stack);
			$_15 = FALSE; break;
		}
		$_15 = TRUE; break;
	}
	while(0);
	if( $_15 === TRUE ) { return $this->finalise($result); }
	if( $_15 === FALSE) { return FALSE; }
}

public function Name_Custom (&$res, $sub)
    {
        $res['custom'] = ($sub['text'] === '#') ;
    }

public function Name_Name (&$res, $sub)
    {
        $res['text'] = $sub['text'] ;
    }

/* String: /[ \t]* / > '"' > String: /[^"]* /) > '"' */
protected $match_String_typestack = array('String');
function match_String ($stack = array()) {
	$matchrule = "String"; $result = $this->construct($matchrule, $matchrule, null);
	$_22 = NULL;
	do {
		if (( $subres = $this->rx( '/[ \t]* /' ) ) !== FALSE) { $result["text"] .= $subres; }
		else { $_22 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		if (substr($this->string,$this->pos,1) == '"') {
			$this->pos += 1;
			$result["text"] .= '"';
		}
		else { $_22 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		$stack[] = $result; $result = $this->construct( $matchrule, "String" ); 
		if (( $subres = $this->rx( '/[^"]* /' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'String' );
		}
		else {
			$result = array_pop($stack);
			$_22 = FALSE; break;
		}
		$_22 = TRUE; break;
	}
	while(0);
	if( $_22 === TRUE ) { return $this->finalise($result); }
	if( $_22 === FALSE) { return FALSE; }
}

public function String_String (&$res, $sub)
    {
        $res['text'] = $sub['text'] ;
    }

/* Expr1: (Bool | Name | String) */
protected $match_Expr1_typestack = array('Expr1');
function match_Expr1 ($stack = array()) {
	$matchrule = "Expr1"; $result = $this->construct($matchrule, $matchrule, null);
	$_33 = NULL;
	do {
		$_31 = NULL;
		do {
			$res_24 = $result;
			$pos_24 = $this->pos;
			$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_31 = TRUE; break;
			}
			$result = $res_24;
			$this->pos = $pos_24;
			$_29 = NULL;
			do {
				$res_26 = $result;
				$pos_26 = $this->pos;
				$matcher = 'match_'.'Name'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_29 = TRUE; break;
				}
				$result = $res_26;
				$this->pos = $pos_26;
				$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_29 = TRUE; break;
				}
				$result = $res_26;
				$this->pos = $pos_26;
				$_29 = FALSE; break;
			}
			while(0);
			if( $_29 === TRUE ) { $_31 = TRUE; break; }
			$result = $res_24;
			$this->pos = $pos_24;
			$_31 = FALSE; break;
		}
		while(0);
		if( $_31 === FALSE) { $_33 = FALSE; break; }
		$_33 = TRUE; break;
	}
	while(0);
	if( $_33 === TRUE ) { return $this->finalise($result); }
	if( $_33 === FALSE) { return FALSE; }
}


/* Expr2:  All: (Bool | Name | String) */
protected $match_Expr2_typestack = array('Expr2');
function match_Expr2 ($stack = array()) {
	$matchrule = "Expr2"; $result = $this->construct($matchrule, $matchrule, null);
	$stack[] = $result; $result = $this->construct( $matchrule, "All" ); 
	$_44 = NULL;
	do {
		$_42 = NULL;
		do {
			$res_35 = $result;
			$pos_35 = $this->pos;
			$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_42 = TRUE; break;
			}
			$result = $res_35;
			$this->pos = $pos_35;
			$_40 = NULL;
			do {
				$res_37 = $result;
				$pos_37 = $this->pos;
				$matcher = 'match_'.'Name'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_40 = TRUE; break;
				}
				$result = $res_37;
				$this->pos = $pos_37;
				$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_40 = TRUE; break;
				}
				$result = $res_37;
				$this->pos = $pos_37;
				$_40 = FALSE; break;
			}
			while(0);
			if( $_40 === TRUE ) { $_42 = TRUE; break; }
			$result = $res_35;
			$this->pos = $pos_35;
			$_42 = FALSE; break;
		}
		while(0);
		if( $_42 === FALSE) { $_44 = FALSE; break; }
		$_44 = TRUE; break;
	}
	while(0);
	if( $_44 === TRUE ) {
		$subres = $result; $result = array_pop($stack);
		$this->store( $result, $subres, 'All' );
		return $this->finalise($result);
	}
	if( $_44 === FALSE) {
		$result = array_pop($stack);
		return FALSE;
	}
}

public function Expr2_All (&$res, $sub)
    {
        $res['val'] = $sub['val'];    
        $res['text'] = 'FROM All function';
    }

public function Expr2_Bool (&$res, $sub)
    {
        $res['val'] = $sub['val'];    
        $res['text'] = 'FROM Bool function';
    }

/* Expr3: All: (Bool | Name | String) */
protected $match_Expr3_typestack = array('Expr3');
function match_Expr3 ($stack = array()) {
	$matchrule = "Expr3"; $result = $this->construct($matchrule, $matchrule, null);
	$stack[] = $result; $result = $this->construct( $matchrule, "All" ); 
	$_55 = NULL;
	do {
		$_53 = NULL;
		do {
			$res_46 = $result;
			$pos_46 = $this->pos;
			$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_53 = TRUE; break;
			}
			$result = $res_46;
			$this->pos = $pos_46;
			$_51 = NULL;
			do {
				$res_48 = $result;
				$pos_48 = $this->pos;
				$matcher = 'match_'.'Name'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_51 = TRUE; break;
				}
				$result = $res_48;
				$this->pos = $pos_48;
				$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_51 = TRUE; break;
				}
				$result = $res_48;
				$this->pos = $pos_48;
				$_51 = FALSE; break;
			}
			while(0);
			if( $_51 === TRUE ) { $_53 = TRUE; break; }
			$result = $res_46;
			$this->pos = $pos_46;
			$_53 = FALSE; break;
		}
		while(0);
		if( $_53 === FALSE) { $_55 = FALSE; break; }
		$_55 = TRUE; break;
	}
	while(0);
	if( $_55 === TRUE ) {
		$subres = $result; $result = array_pop($stack);
		$this->store( $result, $subres, 'All' );
		return $this->finalise($result);
	}
	if( $_55 === FALSE) {
		$result = array_pop($stack);
		return FALSE;
	}
}

public function Expr3_All (&$res, $sub)
    {
    	$this->log->info("In function All");
    }

public function Expr3_Bool (&$res, $sub)
    {
    	$this->log->info("In function Bool");
    }

    
    
}

