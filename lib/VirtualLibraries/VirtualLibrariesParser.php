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
    var $attachedColumnId;
    var $log;
    
    /**
     * Ctor.
     * @param string $parse_string
     * @param IClientSite $clientSite
     * @param array $savedSearches
     */
    public function __construct($parse_string, IClientSite $clientSite = null, array $savedSearches = null)
    {
        parent::__construct($parse_string);
        
        $this->savedSearches = $savedSearches;
        $this->clientSite = $clientSite;
        $this->savedFilters = array();
        
        $this->log = \Logger::getLogger(__CLASS__);
        $this->log->info("Parsing string '$parse_string'");
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
            $this->resetIdCache();                                                                      // reset the cache for Attached Column Ids            
            $this->prepareParse($id);                                                                   // prepare for parsing
            $res = $this->match_Disjunction();                                                          // invoke parser
            
            if ($this->isCacheEmpty())                                                                  // in the parser no attached columns with more than one id were detected
            {
                return $res['val'];                                                                     // then return the result
            }
            
            $combs = $this->prepareIds();                                                               // prepare the ids
            for ($i = 0; $i < $combs; $i++)                                                             // loop through all combinations
            {
                $this->attachedColumnId = array(
                    'data' => array_shift($this->attachedColumnIdCombinations['data']),                 // remove id from front and store
                    'identifiers' => array_shift($this->attachedColumnIdCombinations['identifiers']));                
                
                $this->prepareParse($id);                                                               // reset the parser
                $res = $this->match_Disjunction();                                                      // invoke parser
                if ($res['val'] === true)
                {
                    return true;                                                                        // return a true result
                }
            }
            
            return false;                                                                               // no test succeeded
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
        $this->pos = 0;                                                         // reset the state of the parser
        $this->depth = 0 ;
        $this->regexps = array();
        
        $this->id = $id;                                                        // store the given $id to be used during parse
    }
    
    /**
     * No attached columns with more than one id were detected in the parser 
     */
    private function isCacheEmpty()
    {
        if ($this->attachedColumnIds === null ||
            (!array_key_exists('data', $this->attachedColumnIds) &&
             !array_key_exists('identifiers', $this->attachedColumnIds)))
        {
            return true;
        }
        return false;
    }
    
    /**
     * Reset the cache for Attached Column Ids
     */
    private function resetIdCache()
    {
        $this->attachedColumnIds = null;
        $this->attachedColumnIdCombinations = null;
        $this->attachedColumnId = null;
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
    

/* Ws: . /[ \t]* / */
protected $match_Ws_typestack = array('Ws');
function match_Ws ($stack = array()) {
	$matchrule = "Ws"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/[ \t]* /' ) ) !== FALSE) { return $this->finalise($result); }
	else { return FALSE; }
}


/* Name: getBuiltinName: (CommonName | DateName | SizeName) | getCustomName: CustomName */
protected $match_Name_typestack = array('Name');
function match_Name ($stack = array()) {
	$matchrule = "Name"; $result = $this->construct($matchrule, $matchrule, null);
	$_14 = NULL;
	do {
		$res_1 = $result;
		$pos_1 = $this->pos;
		$stack[] = $result; $result = $this->construct( $matchrule, "getBuiltinName" ); 
		$_11 = NULL;
		do {
			$_9 = NULL;
			do {
				$res_2 = $result;
				$pos_2 = $this->pos;
				$matcher = 'match_'.'CommonName'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_9 = TRUE; break;
				}
				$result = $res_2;
				$this->pos = $pos_2;
				$_7 = NULL;
				do {
					$res_4 = $result;
					$pos_4 = $this->pos;
					$matcher = 'match_'.'DateName'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres );
						$_7 = TRUE; break;
					}
					$result = $res_4;
					$this->pos = $pos_4;
					$matcher = 'match_'.'SizeName'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres );
						$_7 = TRUE; break;
					}
					$result = $res_4;
					$this->pos = $pos_4;
					$_7 = FALSE; break;
				}
				while(0);
				if( $_7 === TRUE ) { $_9 = TRUE; break; }
				$result = $res_2;
				$this->pos = $pos_2;
				$_9 = FALSE; break;
			}
			while(0);
			if( $_9 === FALSE) { $_11 = FALSE; break; }
			$_11 = TRUE; break;
		}
		while(0);
		if( $_11 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getBuiltinName' );
			$_14 = TRUE; break;
		}
		if( $_11 === FALSE) { $result = array_pop($stack); }
		$result = $res_1;
		$this->pos = $pos_1;
		$matcher = 'match_'.'CustomName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCustomName" );
			$_14 = TRUE; break;
		}
		$result = $res_1;
		$this->pos = $pos_1;
		$_14 = FALSE; break;
	}
	while(0);
	if( $_14 === TRUE ) { return $this->finalise($result); }
	if( $_14 === FALSE) { return FALSE; }
}

public function Name_getBuiltinName (&$res, $sub)
	{
		$res['custom'] = false ;
		$res['text'] = $sub['text'] ;
	}

public function Name_getCustomName (&$res, $sub)
	{
		$res['custom'] = true ;
		$res['text'] = $sub['text'] ;
	}

/* CommonName: 'title' | 'author_sort' | 'authors' | 'author' | 'cover' | 'ondevice' | 'publisher' |
	'rating' | 'series_index' | 'series_sort' | 'series' | 'tags' | 'comments' | 
	'formats' | 'identifiers' | 'languages' | 'uuid' */
protected $match_CommonName_typestack = array('CommonName');
function match_CommonName ($stack = array()) {
	$matchrule = "CommonName"; $result = $this->construct($matchrule, $matchrule, null);
	$_79 = NULL;
	do {
		$res_16 = $result;
		$pos_16 = $this->pos;
		if (( $subres = $this->literal( 'title' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$_79 = TRUE; break;
		}
		$result = $res_16;
		$this->pos = $pos_16;
		$_77 = NULL;
		do {
			$res_18 = $result;
			$pos_18 = $this->pos;
			if (( $subres = $this->literal( 'author_sort' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_77 = TRUE; break;
			}
			$result = $res_18;
			$this->pos = $pos_18;
			$_75 = NULL;
			do {
				$res_20 = $result;
				$pos_20 = $this->pos;
				if (( $subres = $this->literal( 'authors' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_75 = TRUE; break;
				}
				$result = $res_20;
				$this->pos = $pos_20;
				$_73 = NULL;
				do {
					$res_22 = $result;
					$pos_22 = $this->pos;
					if (( $subres = $this->literal( 'author' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$_73 = TRUE; break;
					}
					$result = $res_22;
					$this->pos = $pos_22;
					$_71 = NULL;
					do {
						$res_24 = $result;
						$pos_24 = $this->pos;
						if (( $subres = $this->literal( 'cover' ) ) !== FALSE) {
							$result["text"] .= $subres;
							$_71 = TRUE; break;
						}
						$result = $res_24;
						$this->pos = $pos_24;
						$_69 = NULL;
						do {
							$res_26 = $result;
							$pos_26 = $this->pos;
							if (( $subres = $this->literal( 'ondevice' ) ) !== FALSE) {
								$result["text"] .= $subres;
								$_69 = TRUE; break;
							}
							$result = $res_26;
							$this->pos = $pos_26;
							$_67 = NULL;
							do {
								$res_28 = $result;
								$pos_28 = $this->pos;
								if (( $subres = $this->literal( 'publisher' ) ) !== FALSE) {
									$result["text"] .= $subres;
									$_67 = TRUE; break;
								}
								$result = $res_28;
								$this->pos = $pos_28;
								$_65 = NULL;
								do {
									$res_30 = $result;
									$pos_30 = $this->pos;
									if (( $subres = $this->literal( 'rating' ) ) !== FALSE) {
										$result["text"] .= $subres;
										$_65 = TRUE; break;
									}
									$result = $res_30;
									$this->pos = $pos_30;
									$_63 = NULL;
									do {
										$res_32 = $result;
										$pos_32 = $this->pos;
										if (( $subres = $this->literal( 'series_index' ) ) !== FALSE) {
											$result["text"] .= $subres;
											$_63 = TRUE; break;
										}
										$result = $res_32;
										$this->pos = $pos_32;
										$_61 = NULL;
										do {
											$res_34 = $result;
											$pos_34 = $this->pos;
											if (( $subres = $this->literal( 'series_sort' ) ) !== FALSE) {
												$result["text"] .= $subres;
												$_61 = TRUE; break;
											}
											$result = $res_34;
											$this->pos = $pos_34;
											$_59 = NULL;
											do {
												$res_36 = $result;
												$pos_36 = $this->pos;
												if (( $subres = $this->literal( 'series' ) ) !== FALSE) {
													$result["text"] .= $subres;
													$_59 = TRUE; break;
												}
												$result = $res_36;
												$this->pos = $pos_36;
												$_57 = NULL;
												do {
													$res_38 = $result;
													$pos_38 = $this->pos;
													if (( $subres = $this->literal( 'tags' ) ) !== FALSE) {
														$result["text"] .= $subres;
														$_57 = TRUE; break;
													}
													$result = $res_38;
													$this->pos = $pos_38;
													$_55 = NULL;
													do {
														$res_40 = $result;
														$pos_40 = $this->pos;
														if (( $subres = $this->literal( 'comments' ) ) !== FALSE) {
															$result["text"] .= $subres;
															$_55 = TRUE; break;
														}
														$result = $res_40;
														$this->pos = $pos_40;
														$_53 = NULL;
														do {
															$res_42 = $result;
															$pos_42 = $this->pos;
															if (( $subres = $this->literal( 'formats' ) ) !== FALSE) {
																$result["text"] .= $subres;
																$_53 = TRUE; break;
															}
															$result = $res_42;
															$this->pos = $pos_42;
															$_51 = NULL;
															do {
																$res_44 = $result;
																$pos_44 = $this->pos;
																if (( $subres = $this->literal( 'identifiers' ) ) !== FALSE) {
																	$result["text"] .= $subres;
																	$_51 = TRUE; break;
																}
																$result = $res_44;
																$this->pos = $pos_44;
																$_49 = NULL;
																do {
																	$res_46 = $result;
																	$pos_46 = $this->pos;
																	if (( $subres = $this->literal( 'languages' ) ) !== FALSE) {
																		$result["text"] .= $subres;
																		$_49 = TRUE; break;
																	}
																	$result = $res_46;
																	$this->pos = $pos_46;
																	if (( $subres = $this->literal( 'uuid' ) ) !== FALSE) {
																		$result["text"] .= $subres;
																		$_49 = TRUE; break;
																	}
																	$result = $res_46;
																	$this->pos = $pos_46;
																	$_49 = FALSE; break;
																}
																while(0);
																if( $_49 === TRUE ) { $_51 = TRUE; break; }
																$result = $res_44;
																$this->pos = $pos_44;
																$_51 = FALSE; break;
															}
															while(0);
															if( $_51 === TRUE ) { $_53 = TRUE; break; }
															$result = $res_42;
															$this->pos = $pos_42;
															$_53 = FALSE; break;
														}
														while(0);
														if( $_53 === TRUE ) { $_55 = TRUE; break; }
														$result = $res_40;
														$this->pos = $pos_40;
														$_55 = FALSE; break;
													}
													while(0);
													if( $_55 === TRUE ) { $_57 = TRUE; break; }
													$result = $res_38;
													$this->pos = $pos_38;
													$_57 = FALSE; break;
												}
												while(0);
												if( $_57 === TRUE ) { $_59 = TRUE; break; }
												$result = $res_36;
												$this->pos = $pos_36;
												$_59 = FALSE; break;
											}
											while(0);
											if( $_59 === TRUE ) { $_61 = TRUE; break; }
											$result = $res_34;
											$this->pos = $pos_34;
											$_61 = FALSE; break;
										}
										while(0);
										if( $_61 === TRUE ) { $_63 = TRUE; break; }
										$result = $res_32;
										$this->pos = $pos_32;
										$_63 = FALSE; break;
									}
									while(0);
									if( $_63 === TRUE ) { $_65 = TRUE; break; }
									$result = $res_30;
									$this->pos = $pos_30;
									$_65 = FALSE; break;
								}
								while(0);
								if( $_65 === TRUE ) { $_67 = TRUE; break; }
								$result = $res_28;
								$this->pos = $pos_28;
								$_67 = FALSE; break;
							}
							while(0);
							if( $_67 === TRUE ) { $_69 = TRUE; break; }
							$result = $res_26;
							$this->pos = $pos_26;
							$_69 = FALSE; break;
						}
						while(0);
						if( $_69 === TRUE ) { $_71 = TRUE; break; }
						$result = $res_24;
						$this->pos = $pos_24;
						$_71 = FALSE; break;
					}
					while(0);
					if( $_71 === TRUE ) { $_73 = TRUE; break; }
					$result = $res_22;
					$this->pos = $pos_22;
					$_73 = FALSE; break;
				}
				while(0);
				if( $_73 === TRUE ) { $_75 = TRUE; break; }
				$result = $res_20;
				$this->pos = $pos_20;
				$_75 = FALSE; break;
			}
			while(0);
			if( $_75 === TRUE ) { $_77 = TRUE; break; }
			$result = $res_18;
			$this->pos = $pos_18;
			$_77 = FALSE; break;
		}
		while(0);
		if( $_77 === TRUE ) { $_79 = TRUE; break; }
		$result = $res_16;
		$this->pos = $pos_16;
		$_79 = FALSE; break;
	}
	while(0);
	if( $_79 === TRUE ) { return $this->finalise($result); }
	if( $_79 === FALSE) { return FALSE; }
}


/* SizeName:  'size' */
protected $match_SizeName_typestack = array('SizeName');
function match_SizeName ($stack = array()) {
	$matchrule = "SizeName"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->literal( 'size' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* DateName: 'timestamp' | 'last_modified' | 'pubdate' */
protected $match_DateName_typestack = array('DateName');
function match_DateName ($stack = array()) {
	$matchrule = "DateName"; $result = $this->construct($matchrule, $matchrule, null);
	$_89 = NULL;
	do {
		$res_82 = $result;
		$pos_82 = $this->pos;
		if (( $subres = $this->literal( 'timestamp' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$_89 = TRUE; break;
		}
		$result = $res_82;
		$this->pos = $pos_82;
		$_87 = NULL;
		do {
			$res_84 = $result;
			$pos_84 = $this->pos;
			if (( $subres = $this->literal( 'last_modified' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_87 = TRUE; break;
			}
			$result = $res_84;
			$this->pos = $pos_84;
			if (( $subres = $this->literal( 'pubdate' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_87 = TRUE; break;
			}
			$result = $res_84;
			$this->pos = $pos_84;
			$_87 = FALSE; break;
		}
		while(0);
		if( $_87 === TRUE ) { $_89 = TRUE; break; }
		$result = $res_82;
		$this->pos = $pos_82;
		$_89 = FALSE; break;
	}
	while(0);
	if( $_89 === TRUE ) { return $this->finalise($result); }
	if( $_89 === FALSE) { return FALSE; }
}


/* CustomName: .'#' /[a-zA-Z][a-zA-Z0-9]* /  */
protected $match_CustomName_typestack = array('CustomName');
function match_CustomName ($stack = array()) {
	$matchrule = "CustomName"; $result = $this->construct($matchrule, $matchrule, null);
	$_93 = NULL;
	do {
		if (substr($this->string,$this->pos,1) == '#') { $this->pos += 1; }
		else { $_93 = FALSE; break; }
		if (( $subres = $this->rx( '/[a-zA-Z][a-zA-Z0-9]* /' ) ) !== FALSE) { $result["text"] .= $subres; }
		else { $_93 = FALSE; break; }
		$_93 = TRUE; break;
	}
	while(0);
	if( $_93 === TRUE ) { return $this->finalise($result); }
	if( $_93 === FALSE) { return FALSE; }
}


/* Float: Integer ( '.' /[0-9]* / )?  */
protected $match_Float_typestack = array('Float');
function match_Float ($stack = array()) {
	$matchrule = "Float"; $result = $this->construct($matchrule, $matchrule, null);
	$_100 = NULL;
	do {
		$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_100 = FALSE; break; }
		$res_99 = $result;
		$pos_99 = $this->pos;
		$_98 = NULL;
		do {
			if (substr($this->string,$this->pos,1) == '.') {
				$this->pos += 1;
				$result["text"] .= '.';
			}
			else { $_98 = FALSE; break; }
			if (( $subres = $this->rx( '/[0-9]* /' ) ) !== FALSE) { $result["text"] .= $subres; }
			else { $_98 = FALSE; break; }
			$_98 = TRUE; break;
		}
		while(0);
		if( $_98 === FALSE) {
			$result = $res_99;
			$this->pos = $pos_99;
			unset( $res_99 );
			unset( $pos_99 );
		}
		$_100 = TRUE; break;
	}
	while(0);
	if( $_100 === TRUE ) { return $this->finalise($result); }
	if( $_100 === FALSE) { return FALSE; }
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


/* Size: (getSizeInK: (Float .'k')) | (getSizeInM: (Float .'M')) | (getSize: Integer) */
protected $match_Size_typestack = array('Size');
function match_Size ($stack = array()) {
	$matchrule = "Size"; $result = $this->construct($matchrule, $matchrule, null);
	$_122 = NULL;
	do {
		$res_103 = $result;
		$pos_103 = $this->pos;
		$_108 = NULL;
		do {
			$stack[] = $result; $result = $this->construct( $matchrule, "getSizeInK" ); 
			$_106 = NULL;
			do {
				$matcher = 'match_'.'Float'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_106 = FALSE; break; }
				if (substr($this->string,$this->pos,1) == 'k') { $this->pos += 1; }
				else { $_106 = FALSE; break; }
				$_106 = TRUE; break;
			}
			while(0);
			if( $_106 === TRUE ) {
				$subres = $result; $result = array_pop($stack);
				$this->store( $result, $subres, 'getSizeInK' );
			}
			if( $_106 === FALSE) {
				$result = array_pop($stack);
				$_108 = FALSE; break;
			}
			$_108 = TRUE; break;
		}
		while(0);
		if( $_108 === TRUE ) { $_122 = TRUE; break; }
		$result = $res_103;
		$this->pos = $pos_103;
		$_120 = NULL;
		do {
			$res_110 = $result;
			$pos_110 = $this->pos;
			$_115 = NULL;
			do {
				$stack[] = $result; $result = $this->construct( $matchrule, "getSizeInM" ); 
				$_113 = NULL;
				do {
					$matcher = 'match_'.'Float'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) { $this->store( $result, $subres ); }
					else { $_113 = FALSE; break; }
					if (substr($this->string,$this->pos,1) == 'M') { $this->pos += 1; }
					else { $_113 = FALSE; break; }
					$_113 = TRUE; break;
				}
				while(0);
				if( $_113 === TRUE ) {
					$subres = $result; $result = array_pop($stack);
					$this->store( $result, $subres, 'getSizeInM' );
				}
				if( $_113 === FALSE) {
					$result = array_pop($stack);
					$_115 = FALSE; break;
				}
				$_115 = TRUE; break;
			}
			while(0);
			if( $_115 === TRUE ) { $_120 = TRUE; break; }
			$result = $res_110;
			$this->pos = $pos_110;
			$_118 = NULL;
			do {
				$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getSize" );
				}
				else { $_118 = FALSE; break; }
				$_118 = TRUE; break;
			}
			while(0);
			if( $_118 === TRUE ) { $_120 = TRUE; break; }
			$result = $res_110;
			$this->pos = $pos_110;
			$_120 = FALSE; break;
		}
		while(0);
		if( $_120 === TRUE ) { $_122 = TRUE; break; }
		$result = $res_103;
		$this->pos = $pos_103;
		$_122 = FALSE; break;
	}
	while(0);
	if( $_122 === TRUE ) { return $this->finalise($result); }
	if( $_122 === FALSE) { return FALSE; }
}

public function Size_getSize (&$res, $sub)
	{
		$res['text'] = $sub['text'] ;
	    $rule = $res["_matchrule"];
	    Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
	}

public function Size_getSizeInK (&$res, $sub)
	{
		$res['text'] = strval(intval($sub['text'] * 1024)) ;			// to be tested
	    $rule = $res["_matchrule"];
	    Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
	}

public function Size_getSizeInM (&$res, $sub)
	{
		$res['text'] = strval(intval($sub['text'] * 1024 * 1024)) ;		// to be tested
	    $rule = $res["_matchrule"];
	    Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
	}

/* Date: getDate: (getSubdate: RelativeDate | getSubdate: AbsoluteDate) */
protected $match_Date_typestack = array('Date');
function match_Date ($stack = array()) {
	$matchrule = "Date"; $result = $this->construct($matchrule, $matchrule, null);
	$stack[] = $result; $result = $this->construct( $matchrule, "getDate" ); 
	$_129 = NULL;
	do {
		$_127 = NULL;
		do {
			$res_124 = $result;
			$pos_124 = $this->pos;
			$matcher = 'match_'.'RelativeDate'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getSubdate" );
				$_127 = TRUE; break;
			}
			$result = $res_124;
			$this->pos = $pos_124;
			$matcher = 'match_'.'AbsoluteDate'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getSubdate" );
				$_127 = TRUE; break;
			}
			$result = $res_124;
			$this->pos = $pos_124;
			$_127 = FALSE; break;
		}
		while(0);
		if( $_127 === FALSE) { $_129 = FALSE; break; }
		$_129 = TRUE; break;
	}
	while(0);
	if( $_129 === TRUE ) {
		$subres = $result; $result = array_pop($stack);
		$this->store( $result, $subres, 'getDate' );
		return $this->finalise($result);
	}
	if( $_129 === FALSE) {
		$result = array_pop($stack);
		return FALSE;
	}
}

public function Date_getSubdate (&$res, $sub)
	{
		$res['date'] = $sub['date'];
		$res['accuracy'] = $sub['accuracy'];
		$res['displacement'] = $sub['displacement'];
	}

public function Date_getDate (&$res, $sub)
	{
		$date = $sub['date'];
		$accuracy = $sub['accuracy'];
		$displacement = $sub['displacement'];
		$params = array("'$date'", "'$accuracy'");
		if ($displacement !== null)
		{
			$params[2] = "'$displacement days'";
		}
		$params = implode(', ', $params);
		$res['text'] = "date($params)";
		$res['accuracy'] = $sub['accuracy'];
	    $rule = $res["_matchrule"];
	    Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($res, true) . "\n");
	}

/* AbsoluteDate: getDate: ((getYear: Integer) ( '-' (getMonth: Integer) ( '-' getDay: Integer )? )?) */
protected $match_AbsoluteDate_typestack = array('AbsoluteDate');
function match_AbsoluteDate ($stack = array()) {
	$matchrule = "AbsoluteDate"; $result = $this->construct($matchrule, $matchrule, null);
	$stack[] = $result; $result = $this->construct( $matchrule, "getDate" ); 
	$_144 = NULL;
	do {
		$_132 = NULL;
		do {
			$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getYear" );
			}
			else { $_132 = FALSE; break; }
			$_132 = TRUE; break;
		}
		while(0);
		if( $_132 === FALSE) { $_144 = FALSE; break; }
		$res_143 = $result;
		$pos_143 = $this->pos;
		$_142 = NULL;
		do {
			if (substr($this->string,$this->pos,1) == '-') {
				$this->pos += 1;
				$result["text"] .= '-';
			}
			else { $_142 = FALSE; break; }
			$_136 = NULL;
			do {
				$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getMonth" );
				}
				else { $_136 = FALSE; break; }
				$_136 = TRUE; break;
			}
			while(0);
			if( $_136 === FALSE) { $_142 = FALSE; break; }
			$res_141 = $result;
			$pos_141 = $this->pos;
			$_140 = NULL;
			do {
				if (substr($this->string,$this->pos,1) == '-') {
					$this->pos += 1;
					$result["text"] .= '-';
				}
				else { $_140 = FALSE; break; }
				$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getDay" );
				}
				else { $_140 = FALSE; break; }
				$_140 = TRUE; break;
			}
			while(0);
			if( $_140 === FALSE) {
				$result = $res_141;
				$this->pos = $pos_141;
				unset( $res_141 );
				unset( $pos_141 );
			}
			$_142 = TRUE; break;
		}
		while(0);
		if( $_142 === FALSE) {
			$result = $res_143;
			$this->pos = $pos_143;
			unset( $res_143 );
			unset( $pos_143 );
		}
		$_144 = TRUE; break;
	}
	while(0);
	if( $_144 === TRUE ) {
		$subres = $result; $result = array_pop($stack);
		$this->store( $result, $subres, 'getDate' );
		return $this->finalise($result);
	}
	if( $_144 === FALSE) {
		$result = array_pop($stack);
		return FALSE;
	}
}

public function AbsoluteDate_getYear (&$res, $sub)
	{
		$res['year'] = $sub['text'];
		$res['month'] = '01'; 
		$res['day'] = '01'; 
		$res['accuracy'] = 'start of year';
	}

public function AbsoluteDate_getMonth (&$res, $sub)
	{
		$res['month'] = $sub['text'];
		$res['accuracy'] = 'start of month';
	}

public function AbsoluteDate_getDay (&$res, $sub)
	{
		$res['day'] = $sub['text'];
		$res['accuracy'] = 'start of day';
	}

public function AbsoluteDate_getDate (&$res, $sub)
	{
		$year = $sub['year'];
		$month = $sub['month'];
		$day = $sub['day'];
		$res['date'] = "$year-$month-$day" ;
		$res['accuracy'] = $sub['accuracy'];
		$res['displacement'] = null;
	    $rule = $res["_matchrule"];
	    Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($res, true) . "\n");
	}

/* RelativeDate: (getToday: 'today') | (getYesterday: 'yesterday') | (getDaysAgo: Integer Ws 'daysago') | (getThisMonth: 'thismonth') */
protected $match_RelativeDate_typestack = array('RelativeDate');
function match_RelativeDate ($stack = array()) {
	$matchrule = "RelativeDate"; $result = $this->construct($matchrule, $matchrule, null);
	$_167 = NULL;
	do {
		$res_146 = $result;
		$pos_146 = $this->pos;
		$_148 = NULL;
		do {
			$stack[] = $result; $result = $this->construct( $matchrule, "getToday" ); 
			if (( $subres = $this->literal( 'today' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$subres = $result; $result = array_pop($stack);
				$this->store( $result, $subres, 'getToday' );
			}
			else {
				$result = array_pop($stack);
				$_148 = FALSE; break;
			}
			$_148 = TRUE; break;
		}
		while(0);
		if( $_148 === TRUE ) { $_167 = TRUE; break; }
		$result = $res_146;
		$this->pos = $pos_146;
		$_165 = NULL;
		do {
			$res_150 = $result;
			$pos_150 = $this->pos;
			$_152 = NULL;
			do {
				$stack[] = $result; $result = $this->construct( $matchrule, "getYesterday" ); 
				if (( $subres = $this->literal( 'yesterday' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$subres = $result; $result = array_pop($stack);
					$this->store( $result, $subres, 'getYesterday' );
				}
				else {
					$result = array_pop($stack);
					$_152 = FALSE; break;
				}
				$_152 = TRUE; break;
			}
			while(0);
			if( $_152 === TRUE ) { $_165 = TRUE; break; }
			$result = $res_150;
			$this->pos = $pos_150;
			$_163 = NULL;
			do {
				$res_154 = $result;
				$pos_154 = $this->pos;
				$_158 = NULL;
				do {
					$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres, "getDaysAgo" );
					}
					else { $_158 = FALSE; break; }
					$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) { $this->store( $result, $subres ); }
					else { $_158 = FALSE; break; }
					if (( $subres = $this->literal( 'daysago' ) ) !== FALSE) { $result["text"] .= $subres; }
					else { $_158 = FALSE; break; }
					$_158 = TRUE; break;
				}
				while(0);
				if( $_158 === TRUE ) { $_163 = TRUE; break; }
				$result = $res_154;
				$this->pos = $pos_154;
				$_161 = NULL;
				do {
					$stack[] = $result; $result = $this->construct( $matchrule, "getThisMonth" ); 
					if (( $subres = $this->literal( 'thismonth' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$subres = $result; $result = array_pop($stack);
						$this->store( $result, $subres, 'getThisMonth' );
					}
					else {
						$result = array_pop($stack);
						$_161 = FALSE; break;
					}
					$_161 = TRUE; break;
				}
				while(0);
				if( $_161 === TRUE ) { $_163 = TRUE; break; }
				$result = $res_154;
				$this->pos = $pos_154;
				$_163 = FALSE; break;
			}
			while(0);
			if( $_163 === TRUE ) { $_165 = TRUE; break; }
			$result = $res_150;
			$this->pos = $pos_150;
			$_165 = FALSE; break;
		}
		while(0);
		if( $_165 === TRUE ) { $_167 = TRUE; break; }
		$result = $res_146;
		$this->pos = $pos_146;
		$_167 = FALSE; break;
	}
	while(0);
	if( $_167 === TRUE ) { return $this->finalise($result); }
	if( $_167 === FALSE) { return FALSE; }
}

public function RelativeDate_getToday (&$res, $sub)
	{
		$res['accuracy'] = 'start of day';
		$res['displacement'] = null;
		$res['date'] = 'now';
	}

public function RelativeDate_getYesterday (&$res, $sub)
	{
		$res['accuracy'] = 'start of day';
		$res['displacement'] = '-1';
		$res['date'] = 'now';
	}

public function RelativeDate_getDaysAgo (&$res, $sub)
	{
		$res['accuracy'] = 'start of day';
		$res['displacement'] = '-' . $sub['text'];
		$res['date'] = 'now';
	}

public function RelativeDate_getThisMonth (&$res, $sub)
	{
		$res['accuracy'] = 'start of month';
		$res['displacement'] = null;
		$res['date'] = 'now';
	}

/* Bool: getBool: ('true' | 'false' | (.'"' ('true' | 'false') .'"')) */
protected $match_Bool_typestack = array('Bool');
function match_Bool ($stack = array()) {
	$matchrule = "Bool"; $result = $this->construct($matchrule, $matchrule, null);
	$stack[] = $result; $result = $this->construct( $matchrule, "getBool" ); 
	$_188 = NULL;
	do {
		$_186 = NULL;
		do {
			$res_169 = $result;
			$pos_169 = $this->pos;
			if (( $subres = $this->literal( 'true' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_186 = TRUE; break;
			}
			$result = $res_169;
			$this->pos = $pos_169;
			$_184 = NULL;
			do {
				$res_171 = $result;
				$pos_171 = $this->pos;
				if (( $subres = $this->literal( 'false' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_184 = TRUE; break;
				}
				$result = $res_171;
				$this->pos = $pos_171;
				$_182 = NULL;
				do {
					if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
					else { $_182 = FALSE; break; }
					$_179 = NULL;
					do {
						$_177 = NULL;
						do {
							$res_174 = $result;
							$pos_174 = $this->pos;
							if (( $subres = $this->literal( 'true' ) ) !== FALSE) {
								$result["text"] .= $subres;
								$_177 = TRUE; break;
							}
							$result = $res_174;
							$this->pos = $pos_174;
							if (( $subres = $this->literal( 'false' ) ) !== FALSE) {
								$result["text"] .= $subres;
								$_177 = TRUE; break;
							}
							$result = $res_174;
							$this->pos = $pos_174;
							$_177 = FALSE; break;
						}
						while(0);
						if( $_177 === FALSE) { $_179 = FALSE; break; }
						$_179 = TRUE; break;
					}
					while(0);
					if( $_179 === FALSE) { $_182 = FALSE; break; }
					if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
					else { $_182 = FALSE; break; }
					$_182 = TRUE; break;
				}
				while(0);
				if( $_182 === TRUE ) { $_184 = TRUE; break; }
				$result = $res_171;
				$this->pos = $pos_171;
				$_184 = FALSE; break;
			}
			while(0);
			if( $_184 === TRUE ) { $_186 = TRUE; break; }
			$result = $res_169;
			$this->pos = $pos_169;
			$_186 = FALSE; break;
		}
		while(0);
		if( $_186 === FALSE) { $_188 = FALSE; break; }
		$_188 = TRUE; break;
	}
	while(0);
	if( $_188 === TRUE ) {
		$subres = $result; $result = array_pop($stack);
		$this->store( $result, $subres, 'getBool' );
		return $this->finalise($result);
	}
	if( $_188 === FALSE) {
		$result = array_pop($stack);
		return FALSE;
	}
}

public function Bool_getBool (&$res, $sub)
	{
		$res['val'] = $sub['text'] === 'true';
	}

/* String: .'"' getCompareOpString: ('=.' | '=' | '~' | '') getString: /[^"]* / .'"' */
protected $match_String_typestack = array('String');
function match_String ($stack = array()) {
	$matchrule = "String"; $result = $this->construct($matchrule, $matchrule, null);
	$_208 = NULL;
	do {
		if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
		else { $_208 = FALSE; break; }
		$stack[] = $result; $result = $this->construct( $matchrule, "getCompareOpString" ); 
		$_204 = NULL;
		do {
			$_202 = NULL;
			do {
				$res_191 = $result;
				$pos_191 = $this->pos;
				if (( $subres = $this->literal( '=.' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_202 = TRUE; break;
				}
				$result = $res_191;
				$this->pos = $pos_191;
				$_200 = NULL;
				do {
					$res_193 = $result;
					$pos_193 = $this->pos;
					if (substr($this->string,$this->pos,1) == '=') {
						$this->pos += 1;
						$result["text"] .= '=';
						$_200 = TRUE; break;
					}
					$result = $res_193;
					$this->pos = $pos_193;
					$_198 = NULL;
					do {
						$res_195 = $result;
						$pos_195 = $this->pos;
						if (substr($this->string,$this->pos,1) == '~') {
							$this->pos += 1;
							$result["text"] .= '~';
							$_198 = TRUE; break;
						}
						$result = $res_195;
						$this->pos = $pos_195;
						if (( $subres = $this->literal( '' ) ) !== FALSE) {
							$result["text"] .= $subres;
							$_198 = TRUE; break;
						}
						$result = $res_195;
						$this->pos = $pos_195;
						$_198 = FALSE; break;
					}
					while(0);
					if( $_198 === TRUE ) { $_200 = TRUE; break; }
					$result = $res_193;
					$this->pos = $pos_193;
					$_200 = FALSE; break;
				}
				while(0);
				if( $_200 === TRUE ) { $_202 = TRUE; break; }
				$result = $res_191;
				$this->pos = $pos_191;
				$_202 = FALSE; break;
			}
			while(0);
			if( $_202 === FALSE) { $_204 = FALSE; break; }
			$_204 = TRUE; break;
		}
		while(0);
		if( $_204 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getCompareOpString' );
		}
		if( $_204 === FALSE) {
			$result = array_pop($stack);
			$_208 = FALSE; break;
		}
		$stack[] = $result; $result = $this->construct( $matchrule, "getString" ); 
		if (( $subres = $this->rx( '/[^"]* /' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getString' );
		}
		else {
			$result = array_pop($stack);
			$_208 = FALSE; break;
		}
		if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
		else { $_208 = FALSE; break; }
		$_208 = TRUE; break;
	}
	while(0);
	if( $_208 === TRUE ) { return $this->finalise($result); }
	if( $_208 === FALSE) { return FALSE; }
}

public function String_getCompareOpString (&$res, $sub)
	{
	    $this->getCompareOpString($res, $sub);
	}

public function String_getString (&$res, $sub)
	{
		$res['text'] = "'" . $res['anchor'] . $sub['text'] . "'";
	}

/* CompareOperator: ('<=' | '>=' | '<' | '>' | '=' | '') */
protected $match_CompareOperator_typestack = array('CompareOperator');
function match_CompareOperator ($stack = array()) {
	$matchrule = "CompareOperator"; $result = $this->construct($matchrule, $matchrule, null);
	$_231 = NULL;
	do {
		$_229 = NULL;
		do {
			$res_210 = $result;
			$pos_210 = $this->pos;
			if (( $subres = $this->literal( '<=' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_229 = TRUE; break;
			}
			$result = $res_210;
			$this->pos = $pos_210;
			$_227 = NULL;
			do {
				$res_212 = $result;
				$pos_212 = $this->pos;
				if (( $subres = $this->literal( '>=' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_227 = TRUE; break;
				}
				$result = $res_212;
				$this->pos = $pos_212;
				$_225 = NULL;
				do {
					$res_214 = $result;
					$pos_214 = $this->pos;
					if (substr($this->string,$this->pos,1) == '<') {
						$this->pos += 1;
						$result["text"] .= '<';
						$_225 = TRUE; break;
					}
					$result = $res_214;
					$this->pos = $pos_214;
					$_223 = NULL;
					do {
						$res_216 = $result;
						$pos_216 = $this->pos;
						if (substr($this->string,$this->pos,1) == '>') {
							$this->pos += 1;
							$result["text"] .= '>';
							$_223 = TRUE; break;
						}
						$result = $res_216;
						$this->pos = $pos_216;
						$_221 = NULL;
						do {
							$res_218 = $result;
							$pos_218 = $this->pos;
							if (substr($this->string,$this->pos,1) == '=') {
								$this->pos += 1;
								$result["text"] .= '=';
								$_221 = TRUE; break;
							}
							$result = $res_218;
							$this->pos = $pos_218;
							if (( $subres = $this->literal( '' ) ) !== FALSE) {
								$result["text"] .= $subres;
								$_221 = TRUE; break;
							}
							$result = $res_218;
							$this->pos = $pos_218;
							$_221 = FALSE; break;
						}
						while(0);
						if( $_221 === TRUE ) { $_223 = TRUE; break; }
						$result = $res_216;
						$this->pos = $pos_216;
						$_223 = FALSE; break;
					}
					while(0);
					if( $_223 === TRUE ) { $_225 = TRUE; break; }
					$result = $res_214;
					$this->pos = $pos_214;
					$_225 = FALSE; break;
				}
				while(0);
				if( $_225 === TRUE ) { $_227 = TRUE; break; }
				$result = $res_212;
				$this->pos = $pos_212;
				$_227 = FALSE; break;
			}
			while(0);
			if( $_227 === TRUE ) { $_229 = TRUE; break; }
			$result = $res_210;
			$this->pos = $pos_210;
			$_229 = FALSE; break;
		}
		while(0);
		if( $_229 === FALSE) { $_231 = FALSE; break; }
		$_231 = TRUE; break;
	}
	while(0);
	if( $_231 === TRUE ) { return $this->finalise($result); }
	if( $_231 === FALSE) { return FALSE; }
}


/* StringComp: getName: Name Ws .':' Ws getCompareResult: String */
protected $match_StringComp_typestack = array('StringComp');
function match_StringComp ($stack = array()) {
	$matchrule = "StringComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_238 = NULL;
	do {
		$matcher = 'match_'.'Name'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_238 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_238 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_238 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_238 = FALSE; break; }
		$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_238 = FALSE; break; }
		$_238 = TRUE; break;
	}
	while(0);
	if( $_238 === TRUE ) { return $this->finalise($result); }
	if( $_238 === FALSE) { return FALSE; }
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

/* DateComp: getName: DateName Ws .':' Ws getCompareOp: CompareOperator Ws getCompareResult: Date      */
protected $match_DateComp_typestack = array('DateComp');
function match_DateComp ($stack = array()) {
	$matchrule = "DateComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_247 = NULL;
	do {
		$matcher = 'match_'.'DateName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_247 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_247 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_247 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_247 = FALSE; break; }
		$matcher = 'match_'.'CompareOperator'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareOp" );
		}
		else { $_247 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_247 = FALSE; break; }
		$matcher = 'match_'.'Date'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_247 = FALSE; break; }
		$_247 = TRUE; break;
	}
	while(0);
	if( $_247 === TRUE ) { return $this->finalise($result); }
	if( $_247 === FALSE) { return FALSE; }
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
	    $name = $res['name'];
	    $accuracy = $sub['accuracy'];
	    $res['sqlname'] = "date('$name', '$accuracy')";
	    $this->getCompareResult($res, $sub);
	}

/* SizeComp: getName: SizeName Ws .':' Ws getCompareOp: CompareOperator Ws getCompareResult: Size */
protected $match_SizeComp_typestack = array('SizeComp');
function match_SizeComp ($stack = array()) {
	$matchrule = "SizeComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_256 = NULL;
	do {
		$matcher = 'match_'.'SizeName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_256 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_256 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_256 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_256 = FALSE; break; }
		$matcher = 'match_'.'CompareOperator'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareOp" );
		}
		else { $_256 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_256 = FALSE; break; }
		$matcher = 'match_'.'Size'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_256 = FALSE; break; }
		$_256 = TRUE; break;
	}
	while(0);
	if( $_256 === TRUE ) { return $this->finalise($result); }
	if( $_256 === FALSE) { return FALSE; }
}

public function SizeComp_getName (&$res, $sub)
	{
	    $this->getName($res, $sub);
	}

public function SizeComp_getCompareOp (&$res, $sub)
	{
	    $this->getCompareOp($res, $sub);
	}

public function SizeComp_getCompareResult (&$res, $sub)
	{
	    $this->getCompareResult($res, $sub);
	}

/* ValueComp: getName: Name Ws .':' Ws getCompareOp: CompareOperator Ws getCompareResult: Integer */
protected $match_ValueComp_typestack = array('ValueComp');
function match_ValueComp ($stack = array()) {
	$matchrule = "ValueComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_265 = NULL;
	do {
		$matcher = 'match_'.'Name'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_265 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_265 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_265 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_265 = FALSE; break; }
		$matcher = 'match_'.'CompareOperator'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareOp" );
		}
		else { $_265 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_265 = FALSE; break; }
		$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_265 = FALSE; break; }
		$_265 = TRUE; break;
	}
	while(0);
	if( $_265 === TRUE ) { return $this->finalise($result); }
	if( $_265 === FALSE) { return FALSE; }
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
	$_272 = NULL;
	do {
		$matcher = 'match_'.'Name'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_272 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_272 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_272 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_272 = FALSE; break; }
		$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_272 = FALSE; break; }
		$_272 = TRUE; break;
	}
	while(0);
	if( $_272 === TRUE ) { return $this->finalise($result); }
	if( $_272 === FALSE) { return FALSE; }
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
	$_277 = NULL;
	do {
		if (( $subres = $this->literal( 'search:' ) ) !== FALSE) {  }
		else { $_277 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_277 = FALSE; break; }
		$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "execSearch" );
		}
		else { $_277 = FALSE; break; }
		$_277 = TRUE; break;
	}
	while(0);
	if( $_277 === TRUE ) { return $this->finalise($result); }
	if( $_277 === FALSE) { return FALSE; }
}

public function Search_execSearch (&$res, $sub)                                                            // represents one of the saved searches
	{
	    $this->execSearch($res, $sub);    
	}

/* Term: Search | DateComp | StringComp | SizeComp | ValueComp | BoolComp    */
protected $match_Term_typestack = array('Term');
function match_Term ($stack = array()) {
	$matchrule = "Term"; $result = $this->construct($matchrule, $matchrule, null);
	$_298 = NULL;
	do {
		$res_279 = $result;
		$pos_279 = $this->pos;
		$matcher = 'match_'.'Search'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_298 = TRUE; break;
		}
		$result = $res_279;
		$this->pos = $pos_279;
		$_296 = NULL;
		do {
			$res_281 = $result;
			$pos_281 = $this->pos;
			$matcher = 'match_'.'DateComp'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_296 = TRUE; break;
			}
			$result = $res_281;
			$this->pos = $pos_281;
			$_294 = NULL;
			do {
				$res_283 = $result;
				$pos_283 = $this->pos;
				$matcher = 'match_'.'StringComp'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_294 = TRUE; break;
				}
				$result = $res_283;
				$this->pos = $pos_283;
				$_292 = NULL;
				do {
					$res_285 = $result;
					$pos_285 = $this->pos;
					$matcher = 'match_'.'SizeComp'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres );
						$_292 = TRUE; break;
					}
					$result = $res_285;
					$this->pos = $pos_285;
					$_290 = NULL;
					do {
						$res_287 = $result;
						$pos_287 = $this->pos;
						$matcher = 'match_'.'ValueComp'; $key = $matcher; $pos = $this->pos;
						$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
						if ($subres !== FALSE) {
							$this->store( $result, $subres );
							$_290 = TRUE; break;
						}
						$result = $res_287;
						$this->pos = $pos_287;
						$matcher = 'match_'.'BoolComp'; $key = $matcher; $pos = $this->pos;
						$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
						if ($subres !== FALSE) {
							$this->store( $result, $subres );
							$_290 = TRUE; break;
						}
						$result = $res_287;
						$this->pos = $pos_287;
						$_290 = FALSE; break;
					}
					while(0);
					if( $_290 === TRUE ) { $_292 = TRUE; break; }
					$result = $res_285;
					$this->pos = $pos_285;
					$_292 = FALSE; break;
				}
				while(0);
				if( $_292 === TRUE ) { $_294 = TRUE; break; }
				$result = $res_283;
				$this->pos = $pos_283;
				$_294 = FALSE; break;
			}
			while(0);
			if( $_294 === TRUE ) { $_296 = TRUE; break; }
			$result = $res_281;
			$this->pos = $pos_281;
			$_296 = FALSE; break;
		}
		while(0);
		if( $_296 === TRUE ) { $_298 = TRUE; break; }
		$result = $res_279;
		$this->pos = $pos_279;
		$_298 = FALSE; break;
	}
	while(0);
	if( $_298 === TRUE ) { return $this->finalise($result); }
	if( $_298 === FALSE) { return FALSE; }
}

public function Term_STR (&$res, $sub)                                                                    // Term is either one of the comparisons or a Search
	{
	    $res['val'] = $this->getResult($res, $sub);    
	}

/* Boolean: getBool: Bool | ( .'(' Ws getDisjunction: Disjunction Ws .')' ) | getTerm: Term  */
protected $match_Boolean_typestack = array('Boolean');
function match_Boolean ($stack = array()) {
	$matchrule = "Boolean"; $result = $this->construct($matchrule, $matchrule, null);
	$_313 = NULL;
	do {
		$res_300 = $result;
		$pos_300 = $this->pos;
		$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getBool" );
			$_313 = TRUE; break;
		}
		$result = $res_300;
		$this->pos = $pos_300;
		$_311 = NULL;
		do {
			$res_302 = $result;
			$pos_302 = $this->pos;
			$_308 = NULL;
			do {
				if (substr($this->string,$this->pos,1) == '(') { $this->pos += 1; }
				else { $_308 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_308 = FALSE; break; }
				$matcher = 'match_'.'Disjunction'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getDisjunction" );
				}
				else { $_308 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_308 = FALSE; break; }
				if (substr($this->string,$this->pos,1) == ')') { $this->pos += 1; }
				else { $_308 = FALSE; break; }
				$_308 = TRUE; break;
			}
			while(0);
			if( $_308 === TRUE ) { $_311 = TRUE; break; }
			$result = $res_302;
			$this->pos = $pos_302;
			$matcher = 'match_'.'Term'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getTerm" );
				$_311 = TRUE; break;
			}
			$result = $res_302;
			$this->pos = $pos_302;
			$_311 = FALSE; break;
		}
		while(0);
		if( $_311 === TRUE ) { $_313 = TRUE; break; }
		$result = $res_300;
		$this->pos = $pos_300;
		$_313 = FALSE; break;
	}
	while(0);
	if( $_313 === TRUE ) { return $this->finalise($result); }
	if( $_313 === FALSE) { return FALSE; }
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
	$_322 = NULL;
	do {
		$res_315 = $result;
		$pos_315 = $this->pos;
		$matcher = 'match_'.'Boolean'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "notNegated" );
			$_322 = TRUE; break;
		}
		$result = $res_315;
		$this->pos = $pos_315;
		$_320 = NULL;
		do {
			if (( $subres = $this->literal( 'not' ) ) !== FALSE) { $result["text"] .= $subres; }
			else { $_320 = FALSE; break; }
			$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) { $this->store( $result, $subres ); }
			else { $_320 = FALSE; break; }
			$matcher = 'match_'.'Boolean'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "negated" );
			}
			else { $_320 = FALSE; break; }
			$_320 = TRUE; break;
		}
		while(0);
		if( $_320 === TRUE ) { $_322 = TRUE; break; }
		$result = $res_315;
		$this->pos = $pos_315;
		$_322 = FALSE; break;
	}
	while(0);
	if( $_322 === TRUE ) { return $this->finalise($result); }
	if( $_322 === FALSE) { return FALSE; }
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
	$_331 = NULL;
	do {
		$matcher = 'match_'.'Negation'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "Operand1" );
		}
		else { $_331 = FALSE; break; }
		while (true) {
			$res_330 = $result;
			$pos_330 = $this->pos;
			$_329 = NULL;
			do {
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_329 = FALSE; break; }
				if (( $subres = $this->literal( 'and' ) ) !== FALSE) { $result["text"] .= $subres; }
				else { $_329 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_329 = FALSE; break; }
				$matcher = 'match_'.'Negation'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "Operand2" );
				}
				else { $_329 = FALSE; break; }
				$_329 = TRUE; break;
			}
			while(0);
			if( $_329 === FALSE) {
				$result = $res_330;
				$this->pos = $pos_330;
				unset( $res_330 );
				unset( $pos_330 );
				break;
			}
		}
		$_331 = TRUE; break;
	}
	while(0);
	if( $_331 === TRUE ) { return $this->finalise($result); }
	if( $_331 === FALSE) { return FALSE; }
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
	$_340 = NULL;
	do {
		$matcher = 'match_'.'Conjunction'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "Operand1" );
		}
		else { $_340 = FALSE; break; }
		while (true) {
			$res_339 = $result;
			$pos_339 = $this->pos;
			$_338 = NULL;
			do {
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_338 = FALSE; break; }
				if (( $subres = $this->literal( 'or' ) ) !== FALSE) { $result["text"] .= $subres; }
				else { $_338 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_338 = FALSE; break; }
				$matcher = 'match_'.'Conjunction'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "Operand2" );
				}
				else { $_338 = FALSE; break; }
				$_338 = TRUE; break;
			}
			while(0);
			if( $_338 === FALSE) {
				$result = $res_339;
				$this->pos = $pos_339;
				unset( $res_339 );
				unset( $pos_339 );
				break;
			}
		}
		$_340 = TRUE; break;
	}
	while(0);
	if( $_340 === TRUE ) { return $this->finalise($result); }
	if( $_340 === FALSE) { return FALSE; }
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
    private function getName(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
        $res['name'] = $sub['text'];
        $res['sqlname'] = $sub['text'];
    }

    /**
     * Returns the value comparison operator from the DateComp or ValueComp rules
     * @param unknown $res
     * @param unknown $sub
     */
    private function getCompareOp(&$res, $sub)
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
    private function getCompareOpString(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
        switch($sub['text'])
        {
            case '=':
                $res['comp'] = "=" ;
                $res['anchor'] = '';
                break;
            case '=.':
                $res['comp'] = " regexp " ;
                $res['anchor'] = '^';
                break;
            default:
                $res['comp'] = " regexp ";
                $res['anchor'] = '';
                break;
        }
    }
    
    /**
     * Returns the compare result for one of the compare rules
     * @param unknown $res
     * @param unknown $sub
     */
    private function getCompareResult(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true));
        
        $compText = $sub['text'];
        $name = $res['name'];
        $res['text'] = $res['sqlname']. $res['comp'] . $compText;                               // prepare the comparison
        
        $column = ColumnInfo::getDefault()->getItem($name);                                     // request the column info
        if ($column)
        {
            $this->getAttachedColumnIds($column);                                               // more than 1 entry of attached table can be assigned
            $query = $column->getSqlWhere($this->id) . $res['text'];                            // and embbed it into Sql
            $this->updateQuery($column, $query);                                                // modify query for stricter test
        }
        else 
        {
        	$res['val'] = "No column $name found";
        }
        
        if ($this->clientSite && isset($query))
        {
            $res['val'] = $this->clientSite->test($query);                                      // then perform the test and store result in the tree
        }
        
		Diagnostic::diagnosticPrint("In $rule, result: " . var_export($res, true));
    }
    
    /**
     * Returns the compare result for the BoolComp rule
     * @param unknown $res
     * @param unknown $sub
     */
    private function getCompareResultBool(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub['val'], true) . "\n");
    
        $query = ColumnInfo::getDefault()->getSqlExists($res['name'], $this->id);               // prepare the query
        if ($this->clientSite)
        {
            $res['val'] = $this->clientSite->test($query) === $sub['val'];                      // then perform the test and store result in the tree
        }
    }    

    /**
     * Executes a saved search given by its search name
     * @param unknown $res
     * @param unknown $sub
     */
    private function execSearch(&$res, $sub)                                                    // represents one of the saved searches
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
    private function getResult(&$res, $sub)                                                     // Term is either one of the comparisons or a Search
    {
        $rule = $res["_matchrule"];
        $subrule = $sub["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, sub rule $subrule, detected: " . var_export($sub['val'], true));
        
        return $sub['val'];
    }

    /**
     * Determines the ids of an attached column/table assigned to a book with $id
     * @param Column $column
     */
    private function getAttachedColumnIds($column)
    {
        if (!$column->isAttachedColumn() || $column->DataTable === 'comments')                          // column is not an attached column 
        {
            return;
        }
        
        if (!$this->isCacheEmpty() && array_key_exists($column->DataTable, $this->attachedColumnIds))   // data for column already present
        {
            return;
        }
        
        if ($this->clientSite)
        {
            $ids = $this->clientSite->getIds($column->getSqlExists($this->id));                         // query the ids of the attached table assigned to the book
            if (count($ids) > 1)                                                                        // more than 1 id
            {
                $this->attachedColumnIds[$column->DataTable] = $ids;                                    // contribute to combinations                
            }
        }
    }
    
    /**
     * Updates given query for an attached column
     * @param Column $column
     * @param string $query
     */
    private function updateQuery($column, &$query)
    {
        if ($this->attachedColumnId &&
            array_key_exists($column->DataTable, $this->attachedColumnId))
        {
            $id = $this->attachedColumnId[$column->DataTable];
            if ($id > 0)
            {
                $colName = $column->getDataTableId();
                $query .= " and $colName=$id";
            }
        }        
    }
}
