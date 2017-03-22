<?php

namespace VirtualLibraries;

require_once dirname(__DIR__) . '/../vendor/hafriedlander/php-peg/autoloader.php';

use hafriedlander\Peg\Parser;

/**
 * Parser to test whether a certain book(id) belongs to a virtual library 
 * @author Jürgen
 */
class VirtualLibrariesParser extends Parser\Basic 
{
    var $savedSearches;
    var $clientSite;
    var $id;
    var $savedFilters;
    var $attachedColumnIds;
    var $attachedColumnIdCombinations;
    var $dataId;
    var $identifiersId;
    var $log;
    
    /**
     * Ctor.
     * @param string $parse_string
     * @param IClientSite $clientSite
     * @param array $savedSearches
     */
    function __construct($parse_string, IClientSite $clientSite = null, array $savedSearches = null)
    {
        parent::__construct($parse_string);
        
        $this->savedSearches = $savedSearches;
        $this->clientSite = $clientSite;
        $this->savedFilters = array();
        
        $this->log = \Logger::getLogger(__CLASS__);
    }
    
    /**
     * This function performs the real test, if the given $id passes the search expression
     * @param integer $id
     * @return boolean
     */
    public function test($id)
    {
        try
        {
            $this->resetCache();                                                                        // reset the cache for Attached Column Ids            
            $this->prepareParse($id);                                                                   // prepare for parsing
            $res = match_Disjunction();                                                                 // invoke parser
            
            if ($this->attachedColumnIds === null ||                                                    // in the parser no attached columns with more than one id were detected
                (!array_key_exists('data', $this->attachedColumnIds) &&
                 !array_key_exists('identifiers', $this->attachedColumnIds)))
            {
                return $res['val'];                                                                     // then return the result
            }
            
            $combs = $this->prepareIds();                                                               // prepare the ids
            for ($i = 0; $i < $combs; $i++)                                                             // loop through all combinations
            {
                $this->dataId = array_shift($this->attachedColumnIdCombinations['data']);               // remove id from front and store
                $this->identifiersId = array_shift($this->attachedColumnIdCombinations['identifiers']);                
                
                $this->prepareParse($id);                                                               // reset the parser
                $res = match_Disjunction();                                                             // invoke parser
                if ($res['val'] === true)
                {
                    return true;                                                                        // return a true result
                }
            }
            
            return false;
        } 
        catch (Exception $e)
        {
            return false;
        }
        
    }

    /**
     * Use this function to
     * 1. reset the parse stream pointer
     * 2. provide another id to test for
     * @param number $id
     */
    private function prepareParse($id = 0)
    {
        $this->pos = 0;                                             // reset the state of the parser
        $this->depth = 0 ;
        $this->regexps = array();
        
        $this->id = $id;                                            // store the given $id to be used during parse
    }
    
    /**
     * Reset the cache for Attached Column Ids
     */
    private function resetIdCache()
    {
        $this->attachedColumnIds = null;
        $this->attachedColumnIdCombinations = null;
        $this->dataId = 0;
        $this->identifiersId = 0;        
    }
    
    /**
     * Prepare the ids for subsequent parse calls
     * @return number - combinations of ids
     */
    private function prepareIds()
    {
        $countDataIds = count('data');
        $countIdentifiersIds = count('identifiers');
        
        $dataIds = array();
        $identifiersIds = array();
        
        for ($i = 0; $i < $countDataIds; $i++)
        {
            for($j = 0; $j < $countIdentifiersIds; $j++)
            {
                array_push($dataIds, $countDataIds === 1 ? 0 : $this->attachedColumnIds['data'][$i]);
                array_push($identifiersIds, $countIdentifiersIds === 1 ? 0 : $this->attachedColumnIds['identifiers'][$j]);
            }
        }
        
        $this->attachedColumnIdCombinations['data'] = $dataIds;
        $this->attachedColumnIdCombinations['identifiers'] = $identifiersIds;
        
        return $countDataIds * $countIdentifiersIds;
    }
    
    /**
     * For the given $table returns the count of ids
     * @param string $table
     * @return number
     */
    private function count($table)
    {
        if (array_key_exists($table, $this->attachedColumnIds))
        {
            return count($this->attachedColumnIds[$table]);
        }
        
        return 1;
    }
    

/* Ws: ./[ \t]* / */
protected $match_Ws_typestack = array('Ws');
function match_Ws ($stack = array()) {
	$matchrule = "Ws"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/[ \t]* /' ) ) !== FALSE) { return $this->finalise($result); }
	else { return FALSE; }
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


/* Date: getDate: (Integer ( '-' Integer ( '-' Integer )? )?) */
protected $match_Date_typestack = array('Date');
function match_Date ($stack = array()) {
	$matchrule = "Date"; $result = $this->construct($matchrule, $matchrule, null);
	$stack[] = $result; $result = $this->construct( $matchrule, "getDate" ); 
	$_11 = NULL;
	do {
		$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_11 = FALSE; break; }
		$res_10 = $result;
		$pos_10 = $this->pos;
		$_9 = NULL;
		do {
			if (substr($this->string,$this->pos,1) == '-') {
				$this->pos += 1;
				$result["text"] .= '-';
			}
			else { $_9 = FALSE; break; }
			$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) { $this->store( $result, $subres ); }
			else { $_9 = FALSE; break; }
			$res_8 = $result;
			$pos_8 = $this->pos;
			$_7 = NULL;
			do {
				if (substr($this->string,$this->pos,1) == '-') {
					$this->pos += 1;
					$result["text"] .= '-';
				}
				else { $_7 = FALSE; break; }
				$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_7 = FALSE; break; }
				$_7 = TRUE; break;
			}
			while(0);
			if( $_7 === FALSE) {
				$result = $res_8;
				$this->pos = $pos_8;
				unset( $res_8 );
				unset( $pos_8 );
			}
			$_9 = TRUE; break;
		}
		while(0);
		if( $_9 === FALSE) {
			$result = $res_10;
			$this->pos = $pos_10;
			unset( $res_10 );
			unset( $pos_10 );
		}
		$_11 = TRUE; break;
	}
	while(0);
	if( $_11 === TRUE ) {
		$subres = $result; $result = array_pop($stack);
		$this->store( $result, $subres, 'getDate' );
		return $this->finalise($result);
	}
	if( $_11 === FALSE) {
		$result = array_pop($stack);
		return FALSE;
	}
}

public function Date_getDate (&$res, $sub)
    {
		$res['text'] = "Date('" . $sub['text'] . "')" ;
    } 

/* Bool: getBool: ('true' | 'false') */
protected $match_Bool_typestack = array('Bool');
function match_Bool ($stack = array()) {
	$matchrule = "Bool"; $result = $this->construct($matchrule, $matchrule, null);
	$stack[] = $result; $result = $this->construct( $matchrule, "getBool" ); 
	$_18 = NULL;
	do {
		$_16 = NULL;
		do {
			$res_13 = $result;
			$pos_13 = $this->pos;
			if (( $subres = $this->literal( 'true' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_16 = TRUE; break;
			}
			$result = $res_13;
			$this->pos = $pos_13;
			if (( $subres = $this->literal( 'false' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_16 = TRUE; break;
			}
			$result = $res_13;
			$this->pos = $pos_13;
			$_16 = FALSE; break;
		}
		while(0);
		if( $_16 === FALSE) { $_18 = FALSE; break; }
		$_18 = TRUE; break;
	}
	while(0);
	if( $_18 === TRUE ) {
		$subres = $result; $result = array_pop($stack);
		$this->store( $result, $subres, 'getBool' );
		return $this->finalise($result);
	}
	if( $_18 === FALSE) {
		$result = array_pop($stack);
		return FALSE;
	}
}

public function Bool_getBool (&$res, $sub)
	{
		$res['val'] = $sub['text'] === 'true';
	}

/* Name: getCustomFlag: ('#'?) getName: (/[a-zA-Z][a-zA-Z0-9]* /) */
protected $match_Name_typestack = array('Name');
function match_Name ($stack = array()) {
	$matchrule = "Name"; $result = $this->construct($matchrule, $matchrule, null);
	$_26 = NULL;
	do {
		$stack[] = $result; $result = $this->construct( $matchrule, "getCustomFlag" ); 
		$_21 = NULL;
		do {
			$res_20 = $result;
			$pos_20 = $this->pos;
			if (substr($this->string,$this->pos,1) == '#') {
				$this->pos += 1;
				$result["text"] .= '#';
			}
			else {
				$result = $res_20;
				$this->pos = $pos_20;
				unset( $res_20 );
				unset( $pos_20 );
			}
			$_21 = TRUE; break;
		}
		while(0);
		if( $_21 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getCustomFlag' );
		}
		if( $_21 === FALSE) {
			$result = array_pop($stack);
			$_26 = FALSE; break;
		}
		$stack[] = $result; $result = $this->construct( $matchrule, "getName" ); 
		$_24 = NULL;
		do {
			if (( $subres = $this->rx( '/[a-zA-Z][a-zA-Z0-9]* /' ) ) !== FALSE) { $result["text"] .= $subres; }
			else { $_24 = FALSE; break; }
			$_24 = TRUE; break;
		}
		while(0);
		if( $_24 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getName' );
		}
		if( $_24 === FALSE) {
			$result = array_pop($stack);
			$_26 = FALSE; break;
		}
		$_26 = TRUE; break;
	}
	while(0);
	if( $_26 === TRUE ) { return $this->finalise($result); }
	if( $_26 === FALSE) { return FALSE; }
}

public function Name_getCustomFlag (&$res, $sub)
    {
        $res['custom'] = ($sub['text'] === '#') ;
    }

public function Name_getName (&$res, $sub)
    {
		$res['text'] = $sub['text'] ;
    }

/* String: .'"' getCompareOpString: ('=.' | '=' | '~' | '') getString: /[^"]* / .'"' */
protected $match_String_typestack = array('String');
function match_String ($stack = array()) {
	$matchrule = "String"; $result = $this->construct($matchrule, $matchrule, null);
	$_46 = NULL;
	do {
		if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
		else { $_46 = FALSE; break; }
		$stack[] = $result; $result = $this->construct( $matchrule, "getCompareOpString" ); 
		$_42 = NULL;
		do {
			$_40 = NULL;
			do {
				$res_29 = $result;
				$pos_29 = $this->pos;
				if (( $subres = $this->literal( '=.' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_40 = TRUE; break;
				}
				$result = $res_29;
				$this->pos = $pos_29;
				$_38 = NULL;
				do {
					$res_31 = $result;
					$pos_31 = $this->pos;
					if (substr($this->string,$this->pos,1) == '=') {
						$this->pos += 1;
						$result["text"] .= '=';
						$_38 = TRUE; break;
					}
					$result = $res_31;
					$this->pos = $pos_31;
					$_36 = NULL;
					do {
						$res_33 = $result;
						$pos_33 = $this->pos;
						if (substr($this->string,$this->pos,1) == '~') {
							$this->pos += 1;
							$result["text"] .= '~';
							$_36 = TRUE; break;
						}
						$result = $res_33;
						$this->pos = $pos_33;
						if (( $subres = $this->literal( '' ) ) !== FALSE) {
							$result["text"] .= $subres;
							$_36 = TRUE; break;
						}
						$result = $res_33;
						$this->pos = $pos_33;
						$_36 = FALSE; break;
					}
					while(0);
					if( $_36 === TRUE ) { $_38 = TRUE; break; }
					$result = $res_31;
					$this->pos = $pos_31;
					$_38 = FALSE; break;
				}
				while(0);
				if( $_38 === TRUE ) { $_40 = TRUE; break; }
				$result = $res_29;
				$this->pos = $pos_29;
				$_40 = FALSE; break;
			}
			while(0);
			if( $_40 === FALSE) { $_42 = FALSE; break; }
			$_42 = TRUE; break;
		}
		while(0);
		if( $_42 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getCompareOpString' );
		}
		if( $_42 === FALSE) {
			$result = array_pop($stack);
			$_46 = FALSE; break;
		}
		$stack[] = $result; $result = $this->construct( $matchrule, "getString" ); 
		if (( $subres = $this->rx( '/[^"]* /' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getString' );
		}
		else {
			$result = array_pop($stack);
			$_46 = FALSE; break;
		}
		if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
		else { $_46 = FALSE; break; }
		$_46 = TRUE; break;
	}
	while(0);
	if( $_46 === TRUE ) { return $this->finalise($result); }
	if( $_46 === FALSE) { return FALSE; }
}

public function String_getCompareOpString (&$res, $sub)
    {
        $this->getCompareOpString($res, $sub);
    }

public function String_getString (&$res, $sub)
    {
		$res['comptext'] = $sub['text'] . "'";
		$res['puretext'] = $sub['text'];
    }

/* StringComp: getName: Name Ws .':' Ws getCompareResult: String */
protected $match_StringComp_typestack = array('StringComp');
function match_StringComp ($stack = array()) {
	$matchrule = "StringComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_53 = NULL;
	do {
		$matcher = 'match_'.'Name'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_53 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_53 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_53 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_53 = FALSE; break; }
		$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_53 = FALSE; break; }
		$_53 = TRUE; break;
	}
	while(0);
	if( $_53 === TRUE ) { return $this->finalise($result); }
	if( $_53 === FALSE) { return FALSE; }
}

public function StringComp_getName (&$res, $sub)
    {
        $this->getName($res, $sub);
    }

public function StringComp_getCompareResult (&$res, $sub)
    {
        $res['comp'] = $sub['comp'];    
        $this->getCompareResult($res, $sub);
    }

/* DateComp: getName: ('pubdate' | 'timestamp' | 'last_modified') Ws .':' Ws getCompareOp: ('<=' | '>=' | '<' | '>' | '=' | '') Ws getCompareResult: Date      */
protected $match_DateComp_typestack = array('DateComp');
function match_DateComp ($stack = array()) {
	$matchrule = "DateComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_94 = NULL;
	do {
		$stack[] = $result; $result = $this->construct( $matchrule, "getName" ); 
		$_64 = NULL;
		do {
			$_62 = NULL;
			do {
				$res_55 = $result;
				$pos_55 = $this->pos;
				if (( $subres = $this->literal( 'pubdate' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_62 = TRUE; break;
				}
				$result = $res_55;
				$this->pos = $pos_55;
				$_60 = NULL;
				do {
					$res_57 = $result;
					$pos_57 = $this->pos;
					if (( $subres = $this->literal( 'timestamp' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$_60 = TRUE; break;
					}
					$result = $res_57;
					$this->pos = $pos_57;
					if (( $subres = $this->literal( 'last_modified' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$_60 = TRUE; break;
					}
					$result = $res_57;
					$this->pos = $pos_57;
					$_60 = FALSE; break;
				}
				while(0);
				if( $_60 === TRUE ) { $_62 = TRUE; break; }
				$result = $res_55;
				$this->pos = $pos_55;
				$_62 = FALSE; break;
			}
			while(0);
			if( $_62 === FALSE) { $_64 = FALSE; break; }
			$_64 = TRUE; break;
		}
		while(0);
		if( $_64 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getName' );
		}
		if( $_64 === FALSE) {
			$result = array_pop($stack);
			$_94 = FALSE; break;
		}
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_94 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_94 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_94 = FALSE; break; }
		$stack[] = $result; $result = $this->construct( $matchrule, "getCompareOp" ); 
		$_90 = NULL;
		do {
			$_88 = NULL;
			do {
				$res_69 = $result;
				$pos_69 = $this->pos;
				if (( $subres = $this->literal( '<=' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_88 = TRUE; break;
				}
				$result = $res_69;
				$this->pos = $pos_69;
				$_86 = NULL;
				do {
					$res_71 = $result;
					$pos_71 = $this->pos;
					if (( $subres = $this->literal( '>=' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$_86 = TRUE; break;
					}
					$result = $res_71;
					$this->pos = $pos_71;
					$_84 = NULL;
					do {
						$res_73 = $result;
						$pos_73 = $this->pos;
						if (substr($this->string,$this->pos,1) == '<') {
							$this->pos += 1;
							$result["text"] .= '<';
							$_84 = TRUE; break;
						}
						$result = $res_73;
						$this->pos = $pos_73;
						$_82 = NULL;
						do {
							$res_75 = $result;
							$pos_75 = $this->pos;
							if (substr($this->string,$this->pos,1) == '>') {
								$this->pos += 1;
								$result["text"] .= '>';
								$_82 = TRUE; break;
							}
							$result = $res_75;
							$this->pos = $pos_75;
							$_80 = NULL;
							do {
								$res_77 = $result;
								$pos_77 = $this->pos;
								if (substr($this->string,$this->pos,1) == '=') {
									$this->pos += 1;
									$result["text"] .= '=';
									$_80 = TRUE; break;
								}
								$result = $res_77;
								$this->pos = $pos_77;
								if (( $subres = $this->literal( '' ) ) !== FALSE) {
									$result["text"] .= $subres;
									$_80 = TRUE; break;
								}
								$result = $res_77;
								$this->pos = $pos_77;
								$_80 = FALSE; break;
							}
							while(0);
							if( $_80 === TRUE ) { $_82 = TRUE; break; }
							$result = $res_75;
							$this->pos = $pos_75;
							$_82 = FALSE; break;
						}
						while(0);
						if( $_82 === TRUE ) { $_84 = TRUE; break; }
						$result = $res_73;
						$this->pos = $pos_73;
						$_84 = FALSE; break;
					}
					while(0);
					if( $_84 === TRUE ) { $_86 = TRUE; break; }
					$result = $res_71;
					$this->pos = $pos_71;
					$_86 = FALSE; break;
				}
				while(0);
				if( $_86 === TRUE ) { $_88 = TRUE; break; }
				$result = $res_69;
				$this->pos = $pos_69;
				$_88 = FALSE; break;
			}
			while(0);
			if( $_88 === FALSE) { $_90 = FALSE; break; }
			$_90 = TRUE; break;
		}
		while(0);
		if( $_90 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getCompareOp' );
		}
		if( $_90 === FALSE) {
			$result = array_pop($stack);
			$_94 = FALSE; break;
		}
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_94 = FALSE; break; }
		$matcher = 'match_'.'Date'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_94 = FALSE; break; }
		$_94 = TRUE; break;
	}
	while(0);
	if( $_94 === TRUE ) { return $this->finalise($result); }
	if( $_94 === FALSE) { return FALSE; }
}

public function DateComp_getName (&$res, $sub)
    {
        $this->getName($res, $sub);
    }

public function DateComp_getCompareOp (&$res, $sub)
    {
        $this->getCompareOp($res, $sub);
    }

public function DateComp_getCompareResult (&$res, $sub)
    {
        $this->getCompareResult($res, $sub);
    }

/* ValueComp: getName: Name Ws .':' Ws getCompareOp: ('<=' | '>=' | '<' | '>' | '=' | '') Ws getCompareResult: Integer */
protected $match_ValueComp_typestack = array('ValueComp');
function match_ValueComp ($stack = array()) {
	$matchrule = "ValueComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_125 = NULL;
	do {
		$matcher = 'match_'.'Name'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_125 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_125 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_125 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_125 = FALSE; break; }
		$stack[] = $result; $result = $this->construct( $matchrule, "getCompareOp" ); 
		$_121 = NULL;
		do {
			$_119 = NULL;
			do {
				$res_100 = $result;
				$pos_100 = $this->pos;
				if (( $subres = $this->literal( '<=' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_119 = TRUE; break;
				}
				$result = $res_100;
				$this->pos = $pos_100;
				$_117 = NULL;
				do {
					$res_102 = $result;
					$pos_102 = $this->pos;
					if (( $subres = $this->literal( '>=' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$_117 = TRUE; break;
					}
					$result = $res_102;
					$this->pos = $pos_102;
					$_115 = NULL;
					do {
						$res_104 = $result;
						$pos_104 = $this->pos;
						if (substr($this->string,$this->pos,1) == '<') {
							$this->pos += 1;
							$result["text"] .= '<';
							$_115 = TRUE; break;
						}
						$result = $res_104;
						$this->pos = $pos_104;
						$_113 = NULL;
						do {
							$res_106 = $result;
							$pos_106 = $this->pos;
							if (substr($this->string,$this->pos,1) == '>') {
								$this->pos += 1;
								$result["text"] .= '>';
								$_113 = TRUE; break;
							}
							$result = $res_106;
							$this->pos = $pos_106;
							$_111 = NULL;
							do {
								$res_108 = $result;
								$pos_108 = $this->pos;
								if (substr($this->string,$this->pos,1) == '=') {
									$this->pos += 1;
									$result["text"] .= '=';
									$_111 = TRUE; break;
								}
								$result = $res_108;
								$this->pos = $pos_108;
								if (( $subres = $this->literal( '' ) ) !== FALSE) {
									$result["text"] .= $subres;
									$_111 = TRUE; break;
								}
								$result = $res_108;
								$this->pos = $pos_108;
								$_111 = FALSE; break;
							}
							while(0);
							if( $_111 === TRUE ) { $_113 = TRUE; break; }
							$result = $res_106;
							$this->pos = $pos_106;
							$_113 = FALSE; break;
						}
						while(0);
						if( $_113 === TRUE ) { $_115 = TRUE; break; }
						$result = $res_104;
						$this->pos = $pos_104;
						$_115 = FALSE; break;
					}
					while(0);
					if( $_115 === TRUE ) { $_117 = TRUE; break; }
					$result = $res_102;
					$this->pos = $pos_102;
					$_117 = FALSE; break;
				}
				while(0);
				if( $_117 === TRUE ) { $_119 = TRUE; break; }
				$result = $res_100;
				$this->pos = $pos_100;
				$_119 = FALSE; break;
			}
			while(0);
			if( $_119 === FALSE) { $_121 = FALSE; break; }
			$_121 = TRUE; break;
		}
		while(0);
		if( $_121 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getCompareOp' );
		}
		if( $_121 === FALSE) {
			$result = array_pop($stack);
			$_125 = FALSE; break;
		}
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_125 = FALSE; break; }
		$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_125 = FALSE; break; }
		$_125 = TRUE; break;
	}
	while(0);
	if( $_125 === TRUE ) { return $this->finalise($result); }
	if( $_125 === FALSE) { return FALSE; }
}

public function ValueComp_getName (&$res, $sub)
    {
        $this->getName($res, $sub);
    }

public function ValueComp_getCompareOp (&$res, $sub)
    {
        $this->getCompareOp($res, $sub);
    }

public function ValueComp_getCompareResult (&$res, $sub)
    {
        $this->getCompareResult($res, $sub);
    }

/* BoolComp: getName: Name Ws .':' Ws getCompareResult: Bool */
protected $match_BoolComp_typestack = array('BoolComp');
function match_BoolComp ($stack = array()) {
	$matchrule = "BoolComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_132 = NULL;
	do {
		$matcher = 'match_'.'Name'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_132 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_132 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_132 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_132 = FALSE; break; }
		$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_132 = FALSE; break; }
		$_132 = TRUE; break;
	}
	while(0);
	if( $_132 === TRUE ) { return $this->finalise($result); }
	if( $_132 === FALSE) { return FALSE; }
}

public function BoolComp_getName (&$res, $sub)
    {
        $this->getName($res, $sub);
    }

public function BoolComp_getCompareResult (&$res, $sub)
    {
        $this->getCompareResultBool($res, $sub);    
    }

/* Search: .'search:' Ws execSearch: String */
protected $match_Search_typestack = array('Search');
function match_Search ($stack = array()) {
	$matchrule = "Search"; $result = $this->construct($matchrule, $matchrule, null);
	$_137 = NULL;
	do {
		if (( $subres = $this->literal( 'search:' ) ) !== FALSE) {  }
		else { $_137 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_137 = FALSE; break; }
		$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "execSearch" );
		}
		else { $_137 = FALSE; break; }
		$_137 = TRUE; break;
	}
	while(0);
	if( $_137 === TRUE ) { return $this->finalise($result); }
	if( $_137 === FALSE) { return FALSE; }
}

public function Search_execSearch (&$res, $sub)                                                            // represents one of the saved searches
    {
        $this->execSearch($res, $sub);    
    }

/* Term: Search | DateComp | StringComp | ValueComp | BoolComp    */
protected $match_Term_typestack = array('Term');
function match_Term ($stack = array()) {
	$matchrule = "Term"; $result = $this->construct($matchrule, $matchrule, null);
	$_154 = NULL;
	do {
		$res_139 = $result;
		$pos_139 = $this->pos;
		$matcher = 'match_'.'Search'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_154 = TRUE; break;
		}
		$result = $res_139;
		$this->pos = $pos_139;
		$_152 = NULL;
		do {
			$res_141 = $result;
			$pos_141 = $this->pos;
			$matcher = 'match_'.'DateComp'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_152 = TRUE; break;
			}
			$result = $res_141;
			$this->pos = $pos_141;
			$_150 = NULL;
			do {
				$res_143 = $result;
				$pos_143 = $this->pos;
				$matcher = 'match_'.'StringComp'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_150 = TRUE; break;
				}
				$result = $res_143;
				$this->pos = $pos_143;
				$_148 = NULL;
				do {
					$res_145 = $result;
					$pos_145 = $this->pos;
					$matcher = 'match_'.'ValueComp'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres );
						$_148 = TRUE; break;
					}
					$result = $res_145;
					$this->pos = $pos_145;
					$matcher = 'match_'.'BoolComp'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres );
						$_148 = TRUE; break;
					}
					$result = $res_145;
					$this->pos = $pos_145;
					$_148 = FALSE; break;
				}
				while(0);
				if( $_148 === TRUE ) { $_150 = TRUE; break; }
				$result = $res_143;
				$this->pos = $pos_143;
				$_150 = FALSE; break;
			}
			while(0);
			if( $_150 === TRUE ) { $_152 = TRUE; break; }
			$result = $res_141;
			$this->pos = $pos_141;
			$_152 = FALSE; break;
		}
		while(0);
		if( $_152 === TRUE ) { $_154 = TRUE; break; }
		$result = $res_139;
		$this->pos = $pos_139;
		$_154 = FALSE; break;
	}
	while(0);
	if( $_154 === TRUE ) { return $this->finalise($result); }
	if( $_154 === FALSE) { return FALSE; }
}

public function Term_STR (&$res, $sub)                                                                    // Term is either one of the comparisons or a Search
    {
        $res['val'] = $this->getResult($res, $sub);    
    }

/* Boolean: getBool: Bool | ( .'(' Ws getDisjunction: Disjunction Ws .')' ) | getTerm: Term  */
protected $match_Boolean_typestack = array('Boolean');
function match_Boolean ($stack = array()) {
	$matchrule = "Boolean"; $result = $this->construct($matchrule, $matchrule, null);
	$_169 = NULL;
	do {
		$res_156 = $result;
		$pos_156 = $this->pos;
		$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getBool" );
			$_169 = TRUE; break;
		}
		$result = $res_156;
		$this->pos = $pos_156;
		$_167 = NULL;
		do {
			$res_158 = $result;
			$pos_158 = $this->pos;
			$_164 = NULL;
			do {
				if (substr($this->string,$this->pos,1) == '(') { $this->pos += 1; }
				else { $_164 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_164 = FALSE; break; }
				$matcher = 'match_'.'Disjunction'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getDisjunction" );
				}
				else { $_164 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_164 = FALSE; break; }
				if (substr($this->string,$this->pos,1) == ')') { $this->pos += 1; }
				else { $_164 = FALSE; break; }
				$_164 = TRUE; break;
			}
			while(0);
			if( $_164 === TRUE ) { $_167 = TRUE; break; }
			$result = $res_158;
			$this->pos = $pos_158;
			$matcher = 'match_'.'Term'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getTerm" );
				$_167 = TRUE; break;
			}
			$result = $res_158;
			$this->pos = $pos_158;
			$_167 = FALSE; break;
		}
		while(0);
		if( $_167 === TRUE ) { $_169 = TRUE; break; }
		$result = $res_156;
		$this->pos = $pos_156;
		$_169 = FALSE; break;
	}
	while(0);
	if( $_169 === TRUE ) { return $this->finalise($result); }
	if( $_169 === FALSE) { return FALSE; }
}

public function Boolean_getBool (&$res, $sub)                                                               // The Boolean is either a Bool constant,
	{
        $res['val'] = $this->getResult($res, $sub);    
	}

public function Boolean_getDisjunction (&$res, $sub)                                                        // a complex expression in parenthesis
	{
        $res['val'] = $this->getResult($res, $sub);    
	}

public function Boolean_getTerm (&$res, $sub)                                                               //   or a Term
	{
        $res['val'] = $this->getResult($res, $sub);    
	}

/* Negation: notNegated: Boolean | ('not' Ws negated: Boolean) */
protected $match_Negation_typestack = array('Negation');
function match_Negation ($stack = array()) {
	$matchrule = "Negation"; $result = $this->construct($matchrule, $matchrule, null);
	$_178 = NULL;
	do {
		$res_171 = $result;
		$pos_171 = $this->pos;
		$matcher = 'match_'.'Boolean'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "notNegated" );
			$_178 = TRUE; break;
		}
		$result = $res_171;
		$this->pos = $pos_171;
		$_176 = NULL;
		do {
			if (( $subres = $this->literal( 'not' ) ) !== FALSE) { $result["text"] .= $subres; }
			else { $_176 = FALSE; break; }
			$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) { $this->store( $result, $subres ); }
			else { $_176 = FALSE; break; }
			$matcher = 'match_'.'Boolean'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "negated" );
			}
			else { $_176 = FALSE; break; }
			$_176 = TRUE; break;
		}
		while(0);
		if( $_176 === TRUE ) { $_178 = TRUE; break; }
		$result = $res_171;
		$this->pos = $pos_171;
		$_178 = FALSE; break;
	}
	while(0);
	if( $_178 === TRUE ) { return $this->finalise($result); }
	if( $_178 === FALSE) { return FALSE; }
}

public function Negation_notNegated (&$res, $sub)
	{
        $res['val'] = $this->getResult($res, $sub);    
	}

public function Negation_negated (&$res, $sub)
	{
        $res['val'] = !$this->getResult($res, $sub);    
	}

/* Conjunction: Operand1: Negation (Ws 'and' Ws Operand2: Negation)* */
protected $match_Conjunction_typestack = array('Conjunction');
function match_Conjunction ($stack = array()) {
	$matchrule = "Conjunction"; $result = $this->construct($matchrule, $matchrule, null);
	$_187 = NULL;
	do {
		$matcher = 'match_'.'Negation'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "Operand1" );
		}
		else { $_187 = FALSE; break; }
		while (true) {
			$res_186 = $result;
			$pos_186 = $this->pos;
			$_185 = NULL;
			do {
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_185 = FALSE; break; }
				if (( $subres = $this->literal( 'and' ) ) !== FALSE) { $result["text"] .= $subres; }
				else { $_185 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_185 = FALSE; break; }
				$matcher = 'match_'.'Negation'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "Operand2" );
				}
				else { $_185 = FALSE; break; }
				$_185 = TRUE; break;
			}
			while(0);
			if( $_185 === FALSE) {
				$result = $res_186;
				$this->pos = $pos_186;
				unset( $res_186 );
				unset( $pos_186 );
				break;
			}
		}
		$_187 = TRUE; break;
	}
	while(0);
	if( $_187 === TRUE ) { return $this->finalise($result); }
	if( $_187 === FALSE) { return FALSE; }
}

public function Conjunction_Operand1 (&$res, $sub)
	{
        $res['val'] = $this->getResult($res, $sub);    
	}

public function Conjunction_Operand2 (&$res, $sub)
	{
		$res['val'] = $res['val'] && $this->getResult($res, $sub);
	}

/* Disjunction: Operand1:Conjunction (Ws 'or' Ws Operand2:Conjunction)* */
protected $match_Disjunction_typestack = array('Disjunction');
function match_Disjunction ($stack = array()) {
	$matchrule = "Disjunction"; $result = $this->construct($matchrule, $matchrule, null);
	$_196 = NULL;
	do {
		$matcher = 'match_'.'Conjunction'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "Operand1" );
		}
		else { $_196 = FALSE; break; }
		while (true) {
			$res_195 = $result;
			$pos_195 = $this->pos;
			$_194 = NULL;
			do {
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_194 = FALSE; break; }
				if (( $subres = $this->literal( 'or' ) ) !== FALSE) { $result["text"] .= $subres; }
				else { $_194 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_194 = FALSE; break; }
				$matcher = 'match_'.'Conjunction'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "Operand2" );
				}
				else { $_194 = FALSE; break; }
				$_194 = TRUE; break;
			}
			while(0);
			if( $_194 === FALSE) {
				$result = $res_195;
				$this->pos = $pos_195;
				unset( $res_195 );
				unset( $pos_195 );
				break;
			}
		}
		$_196 = TRUE; break;
	}
	while(0);
	if( $_196 === TRUE ) { return $this->finalise($result); }
	if( $_196 === FALSE) { return FALSE; }
}

public function Disjunction_Operand1 (&$res, $sub)
	{
        $res['val'] = $this->getResult($res, $sub);    
	}

public function Disjunction_Operand2 (&$res, $sub)
	{
		$res['val'] = $res['val'] || $this->getResult($res, $sub);
	}


    /**
     * Returns the Name from the Name rule
     * @param unknown $res
     * @param unknown $sub
     */
    function getName(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
        $res['name'] = $sub['text'];
    }

    /**
     * Returns the value comparison operator from the DateComp or ValueComp rules
     * @param unknown $res
     * @param unknown $sub
     */
    function getCompareOp(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
        switch($sub['text'])
        {
            case '':
                $res['comp'] = '=' ;
                break;
            default:
                $res['comp'] = $sub['text'] ;
                break;
        }
    }
    
    /**
     * Returns the string comparison operator from the StringComp rule
     * @param unknown $res
     * @param unknown $sub
     */
    function getCompareOpString(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
        switch($sub['text'])
        {
            case '=':
                $res['comp'] = "='" ;
                break;
            case '=.':
                $res['comp'] = " regexp '^" ;
                break;
            default:
                $res['comp'] = " regexp '";
                break;
        }
    }
    
    /**
     * Returns the compare result for one of the compare rules
     * @param unknown $res
     * @param unknown $sub
     */
    function getCompareResult(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
        $compText = array_key_exists('comptext', $sub) ? $sub['comptext'] : $sub['text'];  
        $res['text'] = $res['name'] . $res['comp'] . $compText;                                 // prepare the comparison
        $query = ColumnInfo::getDefault()->GetSqlWhere($res['name'], $this->id) . $res['text']; // and embbed it into Sql
        if ($this->clientSite)
            $res['val'] = $this->clientSite->test($query);                                      // then perform the test and store result in the tree
    }
    
    /**
     * Returns the compare result for the BoolComp rule
     * @param unknown $res
     * @param unknown $sub
     */
    function getCompareResultBool(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub['val'], true) . "\n");
    
        $query = ColumnInfo::getDefault()->GetSqlExists($res['name'], $this->id);               // prepare the query
        if ($this->clientSite)
            $res['val'] = $this->clientSite->test($query) === $sub['val'];                      // then perform the test and store result in the tree
    }    

    /**
     * Executes a saved search given by its search name
     * @param unknown $res
     * @param unknown $sub
     */
    function execSearch(&$res, $sub)                                                            // represents one of the saved searches
    {
        $search = $sub['puretext'];                                                             // name of the saved search
        if ($this->savedSearches != null && array_key_exists($search, $this->savedSearches))    // search is present in dictionary as in db
        {
            $query = $this->savedSearches[$search];
            if (array_key_exists($search, $this->savedFilters))
            {
                $bookFilter = $this->savedFilters[$search];
            }
            else
            {
                $bookFilter = $this->clientSite->create($query);
            }
    
            $res['val'] = $bookFilter->isSelected($this->id);
        }
    }
    
    /**
     * Returns the result of a sub rule for several rules
     * @param unknown $res
     * @param unknown $sub
     * @return unknown
     */
    function getResult(&$res, $sub)                                                                    // Term is either one of the comparisons or a Search
    {
        $rule = $res["_matchrule"];
        $subrule = $sub["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, sub rule $subrule, detected: " . var_export($sub['val'], true));
        
        return $sub['val'];
    }
}

// TODO: Parser komplettieren (search, callbacks zu db, bool vergleich etc.)
