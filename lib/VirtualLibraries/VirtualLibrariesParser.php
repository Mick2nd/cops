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
    private $savedSearches;
    private $clientSite;
    private $id;

    private $attachedColumnIds;
    private $attachedColumnIdCombinations;
    private $attachedColumnId;
    private $log;
    
    /**
     * Allows us to invoke a child parser, if we need to evaluate a sub expression
     * @var Lazy
     */
    private $childParser;
    
    /**
     * Ctor.
     * @param string $expr
     * @param IClientSite $clientSite
     * @param array $savedSearches
     */
    public function __construct($expr, IClientSite $clientSite = null, array $savedSearches = null)
    {
        parent::__construct($expr);
        
        $this->savedSearches = $savedSearches;
        $this->clientSite = $clientSite;
        
        $this->log = \Logger::getLogger(__CLASS__);
        $this->log->info("Parsing string '$expr'");
        
        $this->childParser = new Lazy(array($this, 'cloneMe'));
    }
    
    /**
     * This function performs the real test, if the given $id passes the search expression
     * @param integer $id
     * @param $expr string|null
     * @return boolean
     */
    public function test($id, $expr = null)
    {
        try
        {
        	if ($expr !== null)																			// we allow a redefinition of the parse expression
        	{
        		$this->string = $expr;
        		$this->log->info("Parsing string '$expr'");
        	}
        	
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
     * Creates a clone(child)
     * @return \VirtualLibraries\VirtualLibrariesParser
     */
    public function cloneMe()
    {
    	$clone = new VirtualLibrariesParser(
    			$this->string,															// with the same parse string
    			$this->clientSite,														// with the same IClientSite
    			$this->savedSearches);													// and saved searches
    			
    			return $clone;
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


/* NonDelim: /[^ \t:]* / */
protected $match_NonDelim_typestack = array('NonDelim');
function match_NonDelim ($stack = array()) {
	$matchrule = "NonDelim"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/[^ \t:]* /' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* NonQuote: /[^"]* / */
protected $match_NonQuote_typestack = array('NonQuote');
function match_NonQuote ($stack = array()) {
	$matchrule = "NonQuote"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/[^"]* /' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* Name: getBuiltinName: (CommonName | DateName | SizeName | IdentifiersName) | getCustomName: CustomName */
protected $match_Name_typestack = array('Name');
function match_Name ($stack = array()) {
	$matchrule = "Name"; $result = $this->construct($matchrule, $matchrule, null);
	$_20 = NULL;
	do {
		$res_3 = $result;
		$pos_3 = $this->pos;
		$stack[] = $result; $result = $this->construct( $matchrule, "getBuiltinName" ); 
		$_17 = NULL;
		do {
			$_15 = NULL;
			do {
				$res_4 = $result;
				$pos_4 = $this->pos;
				$matcher = 'match_'.'CommonName'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_15 = TRUE; break;
				}
				$result = $res_4;
				$this->pos = $pos_4;
				$_13 = NULL;
				do {
					$res_6 = $result;
					$pos_6 = $this->pos;
					$matcher = 'match_'.'DateName'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres );
						$_13 = TRUE; break;
					}
					$result = $res_6;
					$this->pos = $pos_6;
					$_11 = NULL;
					do {
						$res_8 = $result;
						$pos_8 = $this->pos;
						$matcher = 'match_'.'SizeName'; $key = $matcher; $pos = $this->pos;
						$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
						if ($subres !== FALSE) {
							$this->store( $result, $subres );
							$_11 = TRUE; break;
						}
						$result = $res_8;
						$this->pos = $pos_8;
						$matcher = 'match_'.'IdentifiersName'; $key = $matcher; $pos = $this->pos;
						$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
						if ($subres !== FALSE) {
							$this->store( $result, $subres );
							$_11 = TRUE; break;
						}
						$result = $res_8;
						$this->pos = $pos_8;
						$_11 = FALSE; break;
					}
					while(0);
					if( $_11 === TRUE ) { $_13 = TRUE; break; }
					$result = $res_6;
					$this->pos = $pos_6;
					$_13 = FALSE; break;
				}
				while(0);
				if( $_13 === TRUE ) { $_15 = TRUE; break; }
				$result = $res_4;
				$this->pos = $pos_4;
				$_15 = FALSE; break;
			}
			while(0);
			if( $_15 === FALSE) { $_17 = FALSE; break; }
			$_17 = TRUE; break;
		}
		while(0);
		if( $_17 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getBuiltinName' );
			$_20 = TRUE; break;
		}
		if( $_17 === FALSE) { $result = array_pop($stack); }
		$result = $res_3;
		$this->pos = $pos_3;
		$matcher = 'match_'.'CustomName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCustomName" );
			$_20 = TRUE; break;
		}
		$result = $res_3;
		$this->pos = $pos_3;
		$_20 = FALSE; break;
	}
	while(0);
	if( $_20 === TRUE ) { return $this->finalise($result); }
	if( $_20 === FALSE) { return FALSE; }
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

/* GenericName: getBuiltinName: (CommonName | DateName | SizeName) | getCustomName: CustomName */
protected $match_GenericName_typestack = array('GenericName');
function match_GenericName ($stack = array()) {
	$matchrule = "GenericName"; $result = $this->construct($matchrule, $matchrule, null);
	$_35 = NULL;
	do {
		$res_22 = $result;
		$pos_22 = $this->pos;
		$stack[] = $result; $result = $this->construct( $matchrule, "getBuiltinName" ); 
		$_32 = NULL;
		do {
			$_30 = NULL;
			do {
				$res_23 = $result;
				$pos_23 = $this->pos;
				$matcher = 'match_'.'CommonName'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_30 = TRUE; break;
				}
				$result = $res_23;
				$this->pos = $pos_23;
				$_28 = NULL;
				do {
					$res_25 = $result;
					$pos_25 = $this->pos;
					$matcher = 'match_'.'DateName'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres );
						$_28 = TRUE; break;
					}
					$result = $res_25;
					$this->pos = $pos_25;
					$matcher = 'match_'.'SizeName'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres );
						$_28 = TRUE; break;
					}
					$result = $res_25;
					$this->pos = $pos_25;
					$_28 = FALSE; break;
				}
				while(0);
				if( $_28 === TRUE ) { $_30 = TRUE; break; }
				$result = $res_23;
				$this->pos = $pos_23;
				$_30 = FALSE; break;
			}
			while(0);
			if( $_30 === FALSE) { $_32 = FALSE; break; }
			$_32 = TRUE; break;
		}
		while(0);
		if( $_32 === TRUE ) {
			$subres = $result; $result = array_pop($stack);
			$this->store( $result, $subres, 'getBuiltinName' );
			$_35 = TRUE; break;
		}
		if( $_32 === FALSE) { $result = array_pop($stack); }
		$result = $res_22;
		$this->pos = $pos_22;
		$matcher = 'match_'.'CustomName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCustomName" );
			$_35 = TRUE; break;
		}
		$result = $res_22;
		$this->pos = $pos_22;
		$_35 = FALSE; break;
	}
	while(0);
	if( $_35 === TRUE ) { return $this->finalise($result); }
	if( $_35 === FALSE) { return FALSE; }
}

public function GenericName_getBuiltinName (&$res, $sub)
	{
		$res['custom'] = false ;
		$res['text'] = $sub['text'] ;
	}

public function GenericName_getCustomName (&$res, $sub)
	{
		$res['custom'] = true ;
		$res['text'] = $sub['text'] ;
	}

/* CommonName: 'title' | 'author_sort' | 'authors' | 'author' | 'cover' | 'ondevice' | 'publisher' |
	'rating' | 'series_index' | 'series_sort' | 'series' | 'tags' | 'comments' | 
	'formats' | 'languages' | 'uuid' | 'identifiersType' | 'identifiersValue' */
protected $match_CommonName_typestack = array('CommonName');
function match_CommonName ($stack = array()) {
	$matchrule = "CommonName"; $result = $this->construct($matchrule, $matchrule, null);
	$_104 = NULL;
	do {
		$res_37 = $result;
		$pos_37 = $this->pos;
		if (( $subres = $this->literal( 'title' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$_104 = TRUE; break;
		}
		$result = $res_37;
		$this->pos = $pos_37;
		$_102 = NULL;
		do {
			$res_39 = $result;
			$pos_39 = $this->pos;
			if (( $subres = $this->literal( 'author_sort' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_102 = TRUE; break;
			}
			$result = $res_39;
			$this->pos = $pos_39;
			$_100 = NULL;
			do {
				$res_41 = $result;
				$pos_41 = $this->pos;
				if (( $subres = $this->literal( 'authors' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_100 = TRUE; break;
				}
				$result = $res_41;
				$this->pos = $pos_41;
				$_98 = NULL;
				do {
					$res_43 = $result;
					$pos_43 = $this->pos;
					if (( $subres = $this->literal( 'author' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$_98 = TRUE; break;
					}
					$result = $res_43;
					$this->pos = $pos_43;
					$_96 = NULL;
					do {
						$res_45 = $result;
						$pos_45 = $this->pos;
						if (( $subres = $this->literal( 'cover' ) ) !== FALSE) {
							$result["text"] .= $subres;
							$_96 = TRUE; break;
						}
						$result = $res_45;
						$this->pos = $pos_45;
						$_94 = NULL;
						do {
							$res_47 = $result;
							$pos_47 = $this->pos;
							if (( $subres = $this->literal( 'ondevice' ) ) !== FALSE) {
								$result["text"] .= $subres;
								$_94 = TRUE; break;
							}
							$result = $res_47;
							$this->pos = $pos_47;
							$_92 = NULL;
							do {
								$res_49 = $result;
								$pos_49 = $this->pos;
								if (( $subres = $this->literal( 'publisher' ) ) !== FALSE) {
									$result["text"] .= $subres;
									$_92 = TRUE; break;
								}
								$result = $res_49;
								$this->pos = $pos_49;
								$_90 = NULL;
								do {
									$res_51 = $result;
									$pos_51 = $this->pos;
									if (( $subres = $this->literal( 'rating' ) ) !== FALSE) {
										$result["text"] .= $subres;
										$_90 = TRUE; break;
									}
									$result = $res_51;
									$this->pos = $pos_51;
									$_88 = NULL;
									do {
										$res_53 = $result;
										$pos_53 = $this->pos;
										if (( $subres = $this->literal( 'series_index' ) ) !== FALSE) {
											$result["text"] .= $subres;
											$_88 = TRUE; break;
										}
										$result = $res_53;
										$this->pos = $pos_53;
										$_86 = NULL;
										do {
											$res_55 = $result;
											$pos_55 = $this->pos;
											if (( $subres = $this->literal( 'series_sort' ) ) !== FALSE) {
												$result["text"] .= $subres;
												$_86 = TRUE; break;
											}
											$result = $res_55;
											$this->pos = $pos_55;
											$_84 = NULL;
											do {
												$res_57 = $result;
												$pos_57 = $this->pos;
												if (( $subres = $this->literal( 'series' ) ) !== FALSE) {
													$result["text"] .= $subres;
													$_84 = TRUE; break;
												}
												$result = $res_57;
												$this->pos = $pos_57;
												$_82 = NULL;
												do {
													$res_59 = $result;
													$pos_59 = $this->pos;
													if (( $subres = $this->literal( 'tags' ) ) !== FALSE) {
														$result["text"] .= $subres;
														$_82 = TRUE; break;
													}
													$result = $res_59;
													$this->pos = $pos_59;
													$_80 = NULL;
													do {
														$res_61 = $result;
														$pos_61 = $this->pos;
														if (( $subres = $this->literal( 'comments' ) ) !== FALSE) {
															$result["text"] .= $subres;
															$_80 = TRUE; break;
														}
														$result = $res_61;
														$this->pos = $pos_61;
														$_78 = NULL;
														do {
															$res_63 = $result;
															$pos_63 = $this->pos;
															if (( $subres = $this->literal( 'formats' ) ) !== FALSE) {
																$result["text"] .= $subres;
																$_78 = TRUE; break;
															}
															$result = $res_63;
															$this->pos = $pos_63;
															$_76 = NULL;
															do {
																$res_65 = $result;
																$pos_65 = $this->pos;
																if (( $subres = $this->literal( 'languages' ) ) !== FALSE) {
																	$result["text"] .= $subres;
																	$_76 = TRUE; break;
																}
																$result = $res_65;
																$this->pos = $pos_65;
																$_74 = NULL;
																do {
																	$res_67 = $result;
																	$pos_67 = $this->pos;
																	if (( $subres = $this->literal( 'uuid' ) ) !== FALSE) {
																		$result["text"] .= $subres;
																		$_74 = TRUE; break;
																	}
																	$result = $res_67;
																	$this->pos = $pos_67;
																	$_72 = NULL;
																	do {
																		$res_69 = $result;
																		$pos_69 = $this->pos;
																		if (( $subres = $this->literal( 'identifiersType' ) ) !== FALSE) {
																			$result["text"] .= $subres;
																			$_72 = TRUE; break;
																		}
																		$result = $res_69;
																		$this->pos = $pos_69;
																		if (( $subres = $this->literal( 'identifiersValue' ) ) !== FALSE) {
																			$result["text"] .= $subres;
																			$_72 = TRUE; break;
																		}
																		$result = $res_69;
																		$this->pos = $pos_69;
																		$_72 = FALSE; break;
																	}
																	while(0);
																	if( $_72 === TRUE ) { $_74 = TRUE; break; }
																	$result = $res_67;
																	$this->pos = $pos_67;
																	$_74 = FALSE; break;
																}
																while(0);
																if( $_74 === TRUE ) { $_76 = TRUE; break; }
																$result = $res_65;
																$this->pos = $pos_65;
																$_76 = FALSE; break;
															}
															while(0);
															if( $_76 === TRUE ) { $_78 = TRUE; break; }
															$result = $res_63;
															$this->pos = $pos_63;
															$_78 = FALSE; break;
														}
														while(0);
														if( $_78 === TRUE ) { $_80 = TRUE; break; }
														$result = $res_61;
														$this->pos = $pos_61;
														$_80 = FALSE; break;
													}
													while(0);
													if( $_80 === TRUE ) { $_82 = TRUE; break; }
													$result = $res_59;
													$this->pos = $pos_59;
													$_82 = FALSE; break;
												}
												while(0);
												if( $_82 === TRUE ) { $_84 = TRUE; break; }
												$result = $res_57;
												$this->pos = $pos_57;
												$_84 = FALSE; break;
											}
											while(0);
											if( $_84 === TRUE ) { $_86 = TRUE; break; }
											$result = $res_55;
											$this->pos = $pos_55;
											$_86 = FALSE; break;
										}
										while(0);
										if( $_86 === TRUE ) { $_88 = TRUE; break; }
										$result = $res_53;
										$this->pos = $pos_53;
										$_88 = FALSE; break;
									}
									while(0);
									if( $_88 === TRUE ) { $_90 = TRUE; break; }
									$result = $res_51;
									$this->pos = $pos_51;
									$_90 = FALSE; break;
								}
								while(0);
								if( $_90 === TRUE ) { $_92 = TRUE; break; }
								$result = $res_49;
								$this->pos = $pos_49;
								$_92 = FALSE; break;
							}
							while(0);
							if( $_92 === TRUE ) { $_94 = TRUE; break; }
							$result = $res_47;
							$this->pos = $pos_47;
							$_94 = FALSE; break;
						}
						while(0);
						if( $_94 === TRUE ) { $_96 = TRUE; break; }
						$result = $res_45;
						$this->pos = $pos_45;
						$_96 = FALSE; break;
					}
					while(0);
					if( $_96 === TRUE ) { $_98 = TRUE; break; }
					$result = $res_43;
					$this->pos = $pos_43;
					$_98 = FALSE; break;
				}
				while(0);
				if( $_98 === TRUE ) { $_100 = TRUE; break; }
				$result = $res_41;
				$this->pos = $pos_41;
				$_100 = FALSE; break;
			}
			while(0);
			if( $_100 === TRUE ) { $_102 = TRUE; break; }
			$result = $res_39;
			$this->pos = $pos_39;
			$_102 = FALSE; break;
		}
		while(0);
		if( $_102 === TRUE ) { $_104 = TRUE; break; }
		$result = $res_37;
		$this->pos = $pos_37;
		$_104 = FALSE; break;
	}
	while(0);
	if( $_104 === TRUE ) { return $this->finalise($result); }
	if( $_104 === FALSE) { return FALSE; }
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
	$_114 = NULL;
	do {
		$res_107 = $result;
		$pos_107 = $this->pos;
		if (( $subres = $this->literal( 'timestamp' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$_114 = TRUE; break;
		}
		$result = $res_107;
		$this->pos = $pos_107;
		$_112 = NULL;
		do {
			$res_109 = $result;
			$pos_109 = $this->pos;
			if (( $subres = $this->literal( 'last_modified' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_112 = TRUE; break;
			}
			$result = $res_109;
			$this->pos = $pos_109;
			if (( $subres = $this->literal( 'pubdate' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_112 = TRUE; break;
			}
			$result = $res_109;
			$this->pos = $pos_109;
			$_112 = FALSE; break;
		}
		while(0);
		if( $_112 === TRUE ) { $_114 = TRUE; break; }
		$result = $res_107;
		$this->pos = $pos_107;
		$_114 = FALSE; break;
	}
	while(0);
	if( $_114 === TRUE ) { return $this->finalise($result); }
	if( $_114 === FALSE) { return FALSE; }
}


/* IdentifiersName: 'identifiers' */
protected $match_IdentifiersName_typestack = array('IdentifiersName');
function match_IdentifiersName ($stack = array()) {
	$matchrule = "IdentifiersName"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->literal( 'identifiers' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* CustomName: .'#' /[a-zA-Z][a-zA-Z0-9]* /  */
protected $match_CustomName_typestack = array('CustomName');
function match_CustomName ($stack = array()) {
	$matchrule = "CustomName"; $result = $this->construct($matchrule, $matchrule, null);
	$_119 = NULL;
	do {
		if (substr($this->string,$this->pos,1) == '#') { $this->pos += 1; }
		else { $_119 = FALSE; break; }
		if (( $subres = $this->rx( '/[a-zA-Z][a-zA-Z0-9]* /' ) ) !== FALSE) { $result["text"] .= $subres; }
		else { $_119 = FALSE; break; }
		$_119 = TRUE; break;
	}
	while(0);
	if( $_119 === TRUE ) { return $this->finalise($result); }
	if( $_119 === FALSE) { return FALSE; }
}


/* Float: Integer ( '.' /[0-9]* / )?  */
protected $match_Float_typestack = array('Float');
function match_Float ($stack = array()) {
	$matchrule = "Float"; $result = $this->construct($matchrule, $matchrule, null);
	$_126 = NULL;
	do {
		$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_126 = FALSE; break; }
		$res_125 = $result;
		$pos_125 = $this->pos;
		$_124 = NULL;
		do {
			if (substr($this->string,$this->pos,1) == '.') {
				$this->pos += 1;
				$result["text"] .= '.';
			}
			else { $_124 = FALSE; break; }
			if (( $subres = $this->rx( '/[0-9]* /' ) ) !== FALSE) { $result["text"] .= $subres; }
			else { $_124 = FALSE; break; }
			$_124 = TRUE; break;
		}
		while(0);
		if( $_124 === FALSE) {
			$result = $res_125;
			$this->pos = $pos_125;
			unset( $res_125 );
			unset( $pos_125 );
		}
		$_126 = TRUE; break;
	}
	while(0);
	if( $_126 === TRUE ) { return $this->finalise($result); }
	if( $_126 === FALSE) { return FALSE; }
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
	$_148 = NULL;
	do {
		$res_129 = $result;
		$pos_129 = $this->pos;
		$_134 = NULL;
		do {
			$stack[] = $result; $result = $this->construct( $matchrule, "getSizeInK" ); 
			$_132 = NULL;
			do {
				$matcher = 'match_'.'Float'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_132 = FALSE; break; }
				if (substr($this->string,$this->pos,1) == 'k') { $this->pos += 1; }
				else { $_132 = FALSE; break; }
				$_132 = TRUE; break;
			}
			while(0);
			if( $_132 === TRUE ) {
				$subres = $result; $result = array_pop($stack);
				$this->store( $result, $subres, 'getSizeInK' );
			}
			if( $_132 === FALSE) {
				$result = array_pop($stack);
				$_134 = FALSE; break;
			}
			$_134 = TRUE; break;
		}
		while(0);
		if( $_134 === TRUE ) { $_148 = TRUE; break; }
		$result = $res_129;
		$this->pos = $pos_129;
		$_146 = NULL;
		do {
			$res_136 = $result;
			$pos_136 = $this->pos;
			$_141 = NULL;
			do {
				$stack[] = $result; $result = $this->construct( $matchrule, "getSizeInM" ); 
				$_139 = NULL;
				do {
					$matcher = 'match_'.'Float'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) { $this->store( $result, $subres ); }
					else { $_139 = FALSE; break; }
					if (substr($this->string,$this->pos,1) == 'M') { $this->pos += 1; }
					else { $_139 = FALSE; break; }
					$_139 = TRUE; break;
				}
				while(0);
				if( $_139 === TRUE ) {
					$subres = $result; $result = array_pop($stack);
					$this->store( $result, $subres, 'getSizeInM' );
				}
				if( $_139 === FALSE) {
					$result = array_pop($stack);
					$_141 = FALSE; break;
				}
				$_141 = TRUE; break;
			}
			while(0);
			if( $_141 === TRUE ) { $_146 = TRUE; break; }
			$result = $res_136;
			$this->pos = $pos_136;
			$_144 = NULL;
			do {
				$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getSize" );
				}
				else { $_144 = FALSE; break; }
				$_144 = TRUE; break;
			}
			while(0);
			if( $_144 === TRUE ) { $_146 = TRUE; break; }
			$result = $res_136;
			$this->pos = $pos_136;
			$_146 = FALSE; break;
		}
		while(0);
		if( $_146 === TRUE ) { $_148 = TRUE; break; }
		$result = $res_129;
		$this->pos = $pos_129;
		$_148 = FALSE; break;
	}
	while(0);
	if( $_148 === TRUE ) { return $this->finalise($result); }
	if( $_148 === FALSE) { return FALSE; }
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
	$_155 = NULL;
	do {
		$_153 = NULL;
		do {
			$res_150 = $result;
			$pos_150 = $this->pos;
			$matcher = 'match_'.'RelativeDate'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getSubdate" );
				$_153 = TRUE; break;
			}
			$result = $res_150;
			$this->pos = $pos_150;
			$matcher = 'match_'.'AbsoluteDate'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getSubdate" );
				$_153 = TRUE; break;
			}
			$result = $res_150;
			$this->pos = $pos_150;
			$_153 = FALSE; break;
		}
		while(0);
		if( $_153 === FALSE) { $_155 = FALSE; break; }
		$_155 = TRUE; break;
	}
	while(0);
	if( $_155 === TRUE ) {
		$subres = $result; $result = array_pop($stack);
		$this->store( $result, $subres, 'getDate' );
		return $this->finalise($result);
	}
	if( $_155 === FALSE) {
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
	$_170 = NULL;
	do {
		$_158 = NULL;
		do {
			$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getYear" );
			}
			else { $_158 = FALSE; break; }
			$_158 = TRUE; break;
		}
		while(0);
		if( $_158 === FALSE) { $_170 = FALSE; break; }
		$res_169 = $result;
		$pos_169 = $this->pos;
		$_168 = NULL;
		do {
			if (substr($this->string,$this->pos,1) == '-') {
				$this->pos += 1;
				$result["text"] .= '-';
			}
			else { $_168 = FALSE; break; }
			$_162 = NULL;
			do {
				$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getMonth" );
				}
				else { $_162 = FALSE; break; }
				$_162 = TRUE; break;
			}
			while(0);
			if( $_162 === FALSE) { $_168 = FALSE; break; }
			$res_167 = $result;
			$pos_167 = $this->pos;
			$_166 = NULL;
			do {
				if (substr($this->string,$this->pos,1) == '-') {
					$this->pos += 1;
					$result["text"] .= '-';
				}
				else { $_166 = FALSE; break; }
				$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getDay" );
				}
				else { $_166 = FALSE; break; }
				$_166 = TRUE; break;
			}
			while(0);
			if( $_166 === FALSE) {
				$result = $res_167;
				$this->pos = $pos_167;
				unset( $res_167 );
				unset( $pos_167 );
			}
			$_168 = TRUE; break;
		}
		while(0);
		if( $_168 === FALSE) {
			$result = $res_169;
			$this->pos = $pos_169;
			unset( $res_169 );
			unset( $pos_169 );
		}
		$_170 = TRUE; break;
	}
	while(0);
	if( $_170 === TRUE ) {
		$subres = $result; $result = array_pop($stack);
		$this->store( $result, $subres, 'getDate' );
		return $this->finalise($result);
	}
	if( $_170 === FALSE) {
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
	$_193 = NULL;
	do {
		$res_172 = $result;
		$pos_172 = $this->pos;
		$_174 = NULL;
		do {
			$stack[] = $result; $result = $this->construct( $matchrule, "getToday" ); 
			if (( $subres = $this->literal( 'today' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$subres = $result; $result = array_pop($stack);
				$this->store( $result, $subres, 'getToday' );
			}
			else {
				$result = array_pop($stack);
				$_174 = FALSE; break;
			}
			$_174 = TRUE; break;
		}
		while(0);
		if( $_174 === TRUE ) { $_193 = TRUE; break; }
		$result = $res_172;
		$this->pos = $pos_172;
		$_191 = NULL;
		do {
			$res_176 = $result;
			$pos_176 = $this->pos;
			$_178 = NULL;
			do {
				$stack[] = $result; $result = $this->construct( $matchrule, "getYesterday" ); 
				if (( $subres = $this->literal( 'yesterday' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$subres = $result; $result = array_pop($stack);
					$this->store( $result, $subres, 'getYesterday' );
				}
				else {
					$result = array_pop($stack);
					$_178 = FALSE; break;
				}
				$_178 = TRUE; break;
			}
			while(0);
			if( $_178 === TRUE ) { $_191 = TRUE; break; }
			$result = $res_176;
			$this->pos = $pos_176;
			$_189 = NULL;
			do {
				$res_180 = $result;
				$pos_180 = $this->pos;
				$_184 = NULL;
				do {
					$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres, "getDaysAgo" );
					}
					else { $_184 = FALSE; break; }
					$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) { $this->store( $result, $subres ); }
					else { $_184 = FALSE; break; }
					if (( $subres = $this->literal( 'daysago' ) ) !== FALSE) { $result["text"] .= $subres; }
					else { $_184 = FALSE; break; }
					$_184 = TRUE; break;
				}
				while(0);
				if( $_184 === TRUE ) { $_189 = TRUE; break; }
				$result = $res_180;
				$this->pos = $pos_180;
				$_187 = NULL;
				do {
					$stack[] = $result; $result = $this->construct( $matchrule, "getThisMonth" ); 
					if (( $subres = $this->literal( 'thismonth' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$subres = $result; $result = array_pop($stack);
						$this->store( $result, $subres, 'getThisMonth' );
					}
					else {
						$result = array_pop($stack);
						$_187 = FALSE; break;
					}
					$_187 = TRUE; break;
				}
				while(0);
				if( $_187 === TRUE ) { $_189 = TRUE; break; }
				$result = $res_180;
				$this->pos = $pos_180;
				$_189 = FALSE; break;
			}
			while(0);
			if( $_189 === TRUE ) { $_191 = TRUE; break; }
			$result = $res_176;
			$this->pos = $pos_176;
			$_191 = FALSE; break;
		}
		while(0);
		if( $_191 === TRUE ) { $_193 = TRUE; break; }
		$result = $res_172;
		$this->pos = $pos_172;
		$_193 = FALSE; break;
	}
	while(0);
	if( $_193 === TRUE ) { return $this->finalise($result); }
	if( $_193 === FALSE) { return FALSE; }
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

/* Bool: (getBool: ('true' | 'false')) | (.'"' getBoolRe: Bool .'"') */
protected $match_Bool_typestack = array('Bool');
function match_Bool ($stack = array()) {
	$matchrule = "Bool"; $result = $this->construct($matchrule, $matchrule, null);
	$_210 = NULL;
	do {
		$res_195 = $result;
		$pos_195 = $this->pos;
		$_203 = NULL;
		do {
			$stack[] = $result; $result = $this->construct( $matchrule, "getBool" ); 
			$_201 = NULL;
			do {
				$_199 = NULL;
				do {
					$res_196 = $result;
					$pos_196 = $this->pos;
					if (( $subres = $this->literal( 'true' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$_199 = TRUE; break;
					}
					$result = $res_196;
					$this->pos = $pos_196;
					if (( $subres = $this->literal( 'false' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$_199 = TRUE; break;
					}
					$result = $res_196;
					$this->pos = $pos_196;
					$_199 = FALSE; break;
				}
				while(0);
				if( $_199 === FALSE) { $_201 = FALSE; break; }
				$_201 = TRUE; break;
			}
			while(0);
			if( $_201 === TRUE ) {
				$subres = $result; $result = array_pop($stack);
				$this->store( $result, $subres, 'getBool' );
			}
			if( $_201 === FALSE) {
				$result = array_pop($stack);
				$_203 = FALSE; break;
			}
			$_203 = TRUE; break;
		}
		while(0);
		if( $_203 === TRUE ) { $_210 = TRUE; break; }
		$result = $res_195;
		$this->pos = $pos_195;
		$_208 = NULL;
		do {
			if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
			else { $_208 = FALSE; break; }
			$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getBoolRe" );
			}
			else { $_208 = FALSE; break; }
			if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
			else { $_208 = FALSE; break; }
			$_208 = TRUE; break;
		}
		while(0);
		if( $_208 === TRUE ) { $_210 = TRUE; break; }
		$result = $res_195;
		$this->pos = $pos_195;
		$_210 = FALSE; break;
	}
	while(0);
	if( $_210 === TRUE ) { return $this->finalise($result); }
	if( $_210 === FALSE) { return FALSE; }
}

public function Bool_getBool (&$res, $sub)
	{
		$res['origtext'] = $sub['text']; 
		$res['val'] = $sub['text'] === 'true';
	}

public function Bool_getBoolRe (&$res, $sub)
	{
		$res['origtext'] = $sub['origtext']; 
		$res['val'] = $sub['val'];
	}

/* String: (. '"' getCompareOpString: CompareOperatorString getString: NonQuote . '"') | 
	(getCompareOpString: CompareOperatorString getString: NonDelim Ws) */
protected $match_String_typestack = array('String');
function match_String ($stack = array()) {
	$matchrule = "String"; $result = $this->construct($matchrule, $matchrule, null);
	$_224 = NULL;
	do {
		$res_212 = $result;
		$pos_212 = $this->pos;
		$_217 = NULL;
		do {
			if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
			else { $_217 = FALSE; break; }
			$matcher = 'match_'.'CompareOperatorString'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getCompareOpString" );
			}
			else { $_217 = FALSE; break; }
			$matcher = 'match_'.'NonQuote'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getString" );
			}
			else { $_217 = FALSE; break; }
			if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
			else { $_217 = FALSE; break; }
			$_217 = TRUE; break;
		}
		while(0);
		if( $_217 === TRUE ) { $_224 = TRUE; break; }
		$result = $res_212;
		$this->pos = $pos_212;
		$_222 = NULL;
		do {
			$matcher = 'match_'.'CompareOperatorString'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getCompareOpString" );
			}
			else { $_222 = FALSE; break; }
			$matcher = 'match_'.'NonDelim'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getString" );
			}
			else { $_222 = FALSE; break; }
			$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) { $this->store( $result, $subres ); }
			else { $_222 = FALSE; break; }
			$_222 = TRUE; break;
		}
		while(0);
		if( $_222 === TRUE ) { $_224 = TRUE; break; }
		$result = $res_212;
		$this->pos = $pos_212;
		$_224 = FALSE; break;
	}
	while(0);
	if( $_224 === TRUE ) { return $this->finalise($result); }
	if( $_224 === FALSE) { return FALSE; }
}

public function String_getCompareOpString (&$res, $sub)
	{
		$res['origtext'] = $sub['text']; 
	    $this->getCompareOpString($res, $sub);
	}

public function String_getString (&$res, $sub)
	{
		$res['origtext'] .= $sub['text'];
		$res['puretext'] = $sub['text'];
		$res['text'] = "'" . $res['anchor'] . $sub['text'] . "'";
	}

/* CompareOperatorString: ('=.' | '=' | '~' | '') */
protected $match_CompareOperatorString_typestack = array('CompareOperatorString');
function match_CompareOperatorString ($stack = array()) {
	$matchrule = "CompareOperatorString"; $result = $this->construct($matchrule, $matchrule, null);
	$_239 = NULL;
	do {
		$_237 = NULL;
		do {
			$res_226 = $result;
			$pos_226 = $this->pos;
			if (( $subres = $this->literal( '=.' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_237 = TRUE; break;
			}
			$result = $res_226;
			$this->pos = $pos_226;
			$_235 = NULL;
			do {
				$res_228 = $result;
				$pos_228 = $this->pos;
				if (substr($this->string,$this->pos,1) == '=') {
					$this->pos += 1;
					$result["text"] .= '=';
					$_235 = TRUE; break;
				}
				$result = $res_228;
				$this->pos = $pos_228;
				$_233 = NULL;
				do {
					$res_230 = $result;
					$pos_230 = $this->pos;
					if (substr($this->string,$this->pos,1) == '~') {
						$this->pos += 1;
						$result["text"] .= '~';
						$_233 = TRUE; break;
					}
					$result = $res_230;
					$this->pos = $pos_230;
					if (( $subres = $this->literal( '' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$_233 = TRUE; break;
					}
					$result = $res_230;
					$this->pos = $pos_230;
					$_233 = FALSE; break;
				}
				while(0);
				if( $_233 === TRUE ) { $_235 = TRUE; break; }
				$result = $res_228;
				$this->pos = $pos_228;
				$_235 = FALSE; break;
			}
			while(0);
			if( $_235 === TRUE ) { $_237 = TRUE; break; }
			$result = $res_226;
			$this->pos = $pos_226;
			$_237 = FALSE; break;
		}
		while(0);
		if( $_237 === FALSE) { $_239 = FALSE; break; }
		$_239 = TRUE; break;
	}
	while(0);
	if( $_239 === TRUE ) { return $this->finalise($result); }
	if( $_239 === FALSE) { return FALSE; }
}


/* CompareOperator: ('<=' | '>=' | '<' | '>' | '=' | '') */
protected $match_CompareOperator_typestack = array('CompareOperator');
function match_CompareOperator ($stack = array()) {
	$matchrule = "CompareOperator"; $result = $this->construct($matchrule, $matchrule, null);
	$_262 = NULL;
	do {
		$_260 = NULL;
		do {
			$res_241 = $result;
			$pos_241 = $this->pos;
			if (( $subres = $this->literal( '<=' ) ) !== FALSE) {
				$result["text"] .= $subres;
				$_260 = TRUE; break;
			}
			$result = $res_241;
			$this->pos = $pos_241;
			$_258 = NULL;
			do {
				$res_243 = $result;
				$pos_243 = $this->pos;
				if (( $subres = $this->literal( '>=' ) ) !== FALSE) {
					$result["text"] .= $subres;
					$_258 = TRUE; break;
				}
				$result = $res_243;
				$this->pos = $pos_243;
				$_256 = NULL;
				do {
					$res_245 = $result;
					$pos_245 = $this->pos;
					if (substr($this->string,$this->pos,1) == '<') {
						$this->pos += 1;
						$result["text"] .= '<';
						$_256 = TRUE; break;
					}
					$result = $res_245;
					$this->pos = $pos_245;
					$_254 = NULL;
					do {
						$res_247 = $result;
						$pos_247 = $this->pos;
						if (substr($this->string,$this->pos,1) == '>') {
							$this->pos += 1;
							$result["text"] .= '>';
							$_254 = TRUE; break;
						}
						$result = $res_247;
						$this->pos = $pos_247;
						$_252 = NULL;
						do {
							$res_249 = $result;
							$pos_249 = $this->pos;
							if (substr($this->string,$this->pos,1) == '=') {
								$this->pos += 1;
								$result["text"] .= '=';
								$_252 = TRUE; break;
							}
							$result = $res_249;
							$this->pos = $pos_249;
							if (( $subres = $this->literal( '' ) ) !== FALSE) {
								$result["text"] .= $subres;
								$_252 = TRUE; break;
							}
							$result = $res_249;
							$this->pos = $pos_249;
							$_252 = FALSE; break;
						}
						while(0);
						if( $_252 === TRUE ) { $_254 = TRUE; break; }
						$result = $res_247;
						$this->pos = $pos_247;
						$_254 = FALSE; break;
					}
					while(0);
					if( $_254 === TRUE ) { $_256 = TRUE; break; }
					$result = $res_245;
					$this->pos = $pos_245;
					$_256 = FALSE; break;
				}
				while(0);
				if( $_256 === TRUE ) { $_258 = TRUE; break; }
				$result = $res_243;
				$this->pos = $pos_243;
				$_258 = FALSE; break;
			}
			while(0);
			if( $_258 === TRUE ) { $_260 = TRUE; break; }
			$result = $res_241;
			$this->pos = $pos_241;
			$_260 = FALSE; break;
		}
		while(0);
		if( $_260 === FALSE) { $_262 = FALSE; break; }
		$_262 = TRUE; break;
	}
	while(0);
	if( $_262 === TRUE ) { return $this->finalise($result); }
	if( $_262 === FALSE) { return FALSE; }
}


/* StringComp: getName: GenericName Ws .':' Ws getCompareResult: String */
protected $match_StringComp_typestack = array('StringComp');
function match_StringComp ($stack = array()) {
	$matchrule = "StringComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_269 = NULL;
	do {
		$matcher = 'match_'.'GenericName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_269 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_269 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_269 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_269 = FALSE; break; }
		$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_269 = FALSE; break; }
		$_269 = TRUE; break;
	}
	while(0);
	if( $_269 === TRUE ) { return $this->finalise($result); }
	if( $_269 === FALSE) { return FALSE; }
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
	$_278 = NULL;
	do {
		$matcher = 'match_'.'DateName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_278 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_278 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_278 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_278 = FALSE; break; }
		$matcher = 'match_'.'CompareOperator'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareOp" );
		}
		else { $_278 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_278 = FALSE; break; }
		$matcher = 'match_'.'Date'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_278 = FALSE; break; }
		$_278 = TRUE; break;
	}
	while(0);
	if( $_278 === TRUE ) { return $this->finalise($result); }
	if( $_278 === FALSE) { return FALSE; }
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
	$_287 = NULL;
	do {
		$matcher = 'match_'.'SizeName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_287 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_287 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_287 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_287 = FALSE; break; }
		$matcher = 'match_'.'CompareOperator'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareOp" );
		}
		else { $_287 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_287 = FALSE; break; }
		$matcher = 'match_'.'Size'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_287 = FALSE; break; }
		$_287 = TRUE; break;
	}
	while(0);
	if( $_287 === TRUE ) { return $this->finalise($result); }
	if( $_287 === FALSE) { return FALSE; }
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

/* ValueComp: getName: GenericName Ws .':' Ws getCompareOp: CompareOperator Ws getCompareResult: Integer */
protected $match_ValueComp_typestack = array('ValueComp');
function match_ValueComp ($stack = array()) {
	$matchrule = "ValueComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_296 = NULL;
	do {
		$matcher = 'match_'.'GenericName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_296 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_296 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_296 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_296 = FALSE; break; }
		$matcher = 'match_'.'CompareOperator'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareOp" );
		}
		else { $_296 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_296 = FALSE; break; }
		$matcher = 'match_'.'Integer'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_296 = FALSE; break; }
		$_296 = TRUE; break;
	}
	while(0);
	if( $_296 === TRUE ) { return $this->finalise($result); }
	if( $_296 === FALSE) { return FALSE; }
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

/* BoolComp: getName: GenericName Ws .':' Ws getCompareResult: Bool */
protected $match_BoolComp_typestack = array('BoolComp');
function match_BoolComp ($stack = array()) {
	$matchrule = "BoolComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_303 = NULL;
	do {
		$matcher = 'match_'.'GenericName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getName" );
		}
		else { $_303 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_303 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_303 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_303 = FALSE; break; }
		$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getCompareResult" );
		}
		else { $_303 = FALSE; break; }
		$_303 = TRUE; break;
	}
	while(0);
	if( $_303 === TRUE ) { return $this->finalise($result); }
	if( $_303 === FALSE) { return FALSE; }
}

public function BoolComp_getName (&$res, $sub)
	{
	    $this->getName($res, $sub);
	}

public function BoolComp_getCompareResult (&$res, $sub)
	{
	    $this->getCompareResultBool($res, $sub);    
	}

/* IdentifiersDef: (.'"' getResult: IdentifiersDef .'"') | 
	((getTypeDef: String) Ws .':' Ws (getBool: Bool)) | 
	((getTypeDef: String) Ws .':' Ws (getValueDef: String)) */
protected $match_IdentifiersDef_typestack = array('IdentifiersDef');
function match_IdentifiersDef ($stack = array()) {
	$matchrule = "IdentifiersDef"; $result = $this->construct($matchrule, $matchrule, null);
	$_336 = NULL;
	do {
		$res_305 = $result;
		$pos_305 = $this->pos;
		$_309 = NULL;
		do {
			if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
			else { $_309 = FALSE; break; }
			$matcher = 'match_'.'IdentifiersDef'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getResult" );
			}
			else { $_309 = FALSE; break; }
			if (substr($this->string,$this->pos,1) == '"') { $this->pos += 1; }
			else { $_309 = FALSE; break; }
			$_309 = TRUE; break;
		}
		while(0);
		if( $_309 === TRUE ) { $_336 = TRUE; break; }
		$result = $res_305;
		$this->pos = $pos_305;
		$_334 = NULL;
		do {
			$res_311 = $result;
			$pos_311 = $this->pos;
			$_321 = NULL;
			do {
				$_313 = NULL;
				do {
					$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres, "getTypeDef" );
					}
					else { $_313 = FALSE; break; }
					$_313 = TRUE; break;
				}
				while(0);
				if( $_313 === FALSE) { $_321 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_321 = FALSE; break; }
				if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
				else { $_321 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_321 = FALSE; break; }
				$_319 = NULL;
				do {
					$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres, "getBool" );
					}
					else { $_319 = FALSE; break; }
					$_319 = TRUE; break;
				}
				while(0);
				if( $_319 === FALSE) { $_321 = FALSE; break; }
				$_321 = TRUE; break;
			}
			while(0);
			if( $_321 === TRUE ) { $_334 = TRUE; break; }
			$result = $res_311;
			$this->pos = $pos_311;
			$_332 = NULL;
			do {
				$_324 = NULL;
				do {
					$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres, "getTypeDef" );
					}
					else { $_324 = FALSE; break; }
					$_324 = TRUE; break;
				}
				while(0);
				if( $_324 === FALSE) { $_332 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_332 = FALSE; break; }
				if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
				else { $_332 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_332 = FALSE; break; }
				$_330 = NULL;
				do {
					$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres, "getValueDef" );
					}
					else { $_330 = FALSE; break; }
					$_330 = TRUE; break;
				}
				while(0);
				if( $_330 === FALSE) { $_332 = FALSE; break; }
				$_332 = TRUE; break;
			}
			while(0);
			if( $_332 === TRUE ) { $_334 = TRUE; break; }
			$result = $res_311;
			$this->pos = $pos_311;
			$_334 = FALSE; break;
		}
		while(0);
		if( $_334 === TRUE ) { $_336 = TRUE; break; }
		$result = $res_305;
		$this->pos = $pos_305;
		$_336 = FALSE; break;
	}
	while(0);
	if( $_336 === TRUE ) { return $this->finalise($result); }
	if( $_336 === FALSE) { return FALSE; }
}

public function IdentifiersDef_getTypeDef (&$res, $sub)
	{
		$res['type'] = 'identifiersType' . ':' . $sub['origtext'];
        $this->diagnosticPrint($res, $sub);
	}

public function IdentifiersDef_getBool (&$res, $sub)
	{
		$rule = $res['type'];
		if (! $sub['val'])
		{
			$rule = 'not ' . $rule;
		}
		$res['val'] = $this->invokeChildParser($rule);
        $this->diagnosticPrint($res, $sub);
	}

public function IdentifiersDef_getValueDef (&$res, $sub)
	{
		$rule = 'identifiersValue' . ':"' . $sub['origtext'] . '"';
		$rule .= ' and ';
		$rule .= $res['type'];
		$res['val'] = $this->invokeChildParser($rule);
        $this->diagnosticPrint($res, $sub);
	}

public function IdentifiersDef_getResult (&$res, $sub)
	{
		$res['val'] = $sub['val'];
	}

/* IdentifiersComp: IdentifiersName Ws .':' Ws (getBool: Bool | getResult: IdentifiersDef) */
protected $match_IdentifiersComp_typestack = array('IdentifiersComp');
function match_IdentifiersComp ($stack = array()) {
	$matchrule = "IdentifiersComp"; $result = $this->construct($matchrule, $matchrule, null);
	$_349 = NULL;
	do {
		$matcher = 'match_'.'IdentifiersName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_349 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_349 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ':') { $this->pos += 1; }
		else { $_349 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_349 = FALSE; break; }
		$_347 = NULL;
		do {
			$_345 = NULL;
			do {
				$res_342 = $result;
				$pos_342 = $this->pos;
				$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getBool" );
					$_345 = TRUE; break;
				}
				$result = $res_342;
				$this->pos = $pos_342;
				$matcher = 'match_'.'IdentifiersDef'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getResult" );
					$_345 = TRUE; break;
				}
				$result = $res_342;
				$this->pos = $pos_342;
				$_345 = FALSE; break;
			}
			while(0);
			if( $_345 === FALSE) { $_347 = FALSE; break; }
			$_347 = TRUE; break;
		}
		while(0);
		if( $_347 === FALSE) { $_349 = FALSE; break; }
		$_349 = TRUE; break;
	}
	while(0);
	if( $_349 === TRUE ) { return $this->finalise($result); }
	if( $_349 === FALSE) { return FALSE; }
}

public function IdentifiersComp_getBool (&$res, $sub)
	{
		$rule = 'identifiersType' . ':' . $sub['origtext'];
		$res['val'] = $this->invokeChildParser($rule);
	}

public function IdentifiersComp_getResult (&$res, $sub)
	{
		$res['val'] = $sub['val'];
	}

/* Search: .'search:' Ws execSearch: String */
protected $match_Search_typestack = array('Search');
function match_Search ($stack = array()) {
	$matchrule = "Search"; $result = $this->construct($matchrule, $matchrule, null);
	$_354 = NULL;
	do {
		if (( $subres = $this->literal( 'search:' ) ) !== FALSE) {  }
		else { $_354 = FALSE; break; }
		$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_354 = FALSE; break; }
		$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "execSearch" );
		}
		else { $_354 = FALSE; break; }
		$_354 = TRUE; break;
	}
	while(0);
	if( $_354 === TRUE ) { return $this->finalise($result); }
	if( $_354 === FALSE) { return FALSE; }
}

public function Search_execSearch (&$res, $sub)                                                            // represents one of the saved searches
	{
	    $this->execSearch($res, $sub);    
	}

/* Term: Search | DateComp | SizeComp | IdentifiersComp | ValueComp | BoolComp | StringComp    */
protected $match_Term_typestack = array('Term');
function match_Term ($stack = array()) {
	$matchrule = "Term"; $result = $this->construct($matchrule, $matchrule, null);
	$_379 = NULL;
	do {
		$res_356 = $result;
		$pos_356 = $this->pos;
		$matcher = 'match_'.'Search'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_379 = TRUE; break;
		}
		$result = $res_356;
		$this->pos = $pos_356;
		$_377 = NULL;
		do {
			$res_358 = $result;
			$pos_358 = $this->pos;
			$matcher = 'match_'.'DateComp'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_377 = TRUE; break;
			}
			$result = $res_358;
			$this->pos = $pos_358;
			$_375 = NULL;
			do {
				$res_360 = $result;
				$pos_360 = $this->pos;
				$matcher = 'match_'.'SizeComp'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_375 = TRUE; break;
				}
				$result = $res_360;
				$this->pos = $pos_360;
				$_373 = NULL;
				do {
					$res_362 = $result;
					$pos_362 = $this->pos;
					$matcher = 'match_'.'IdentifiersComp'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres );
						$_373 = TRUE; break;
					}
					$result = $res_362;
					$this->pos = $pos_362;
					$_371 = NULL;
					do {
						$res_364 = $result;
						$pos_364 = $this->pos;
						$matcher = 'match_'.'ValueComp'; $key = $matcher; $pos = $this->pos;
						$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
						if ($subres !== FALSE) {
							$this->store( $result, $subres );
							$_371 = TRUE; break;
						}
						$result = $res_364;
						$this->pos = $pos_364;
						$_369 = NULL;
						do {
							$res_366 = $result;
							$pos_366 = $this->pos;
							$matcher = 'match_'.'BoolComp'; $key = $matcher; $pos = $this->pos;
							$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
							if ($subres !== FALSE) {
								$this->store( $result, $subres );
								$_369 = TRUE; break;
							}
							$result = $res_366;
							$this->pos = $pos_366;
							$matcher = 'match_'.'StringComp'; $key = $matcher; $pos = $this->pos;
							$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
							if ($subres !== FALSE) {
								$this->store( $result, $subres );
								$_369 = TRUE; break;
							}
							$result = $res_366;
							$this->pos = $pos_366;
							$_369 = FALSE; break;
						}
						while(0);
						if( $_369 === TRUE ) { $_371 = TRUE; break; }
						$result = $res_364;
						$this->pos = $pos_364;
						$_371 = FALSE; break;
					}
					while(0);
					if( $_371 === TRUE ) { $_373 = TRUE; break; }
					$result = $res_362;
					$this->pos = $pos_362;
					$_373 = FALSE; break;
				}
				while(0);
				if( $_373 === TRUE ) { $_375 = TRUE; break; }
				$result = $res_360;
				$this->pos = $pos_360;
				$_375 = FALSE; break;
			}
			while(0);
			if( $_375 === TRUE ) { $_377 = TRUE; break; }
			$result = $res_358;
			$this->pos = $pos_358;
			$_377 = FALSE; break;
		}
		while(0);
		if( $_377 === TRUE ) { $_379 = TRUE; break; }
		$result = $res_356;
		$this->pos = $pos_356;
		$_379 = FALSE; break;
	}
	while(0);
	if( $_379 === TRUE ) { return $this->finalise($result); }
	if( $_379 === FALSE) { return FALSE; }
}

public function Term_STR (&$res, $sub)                                                                    // Term is either one of the comparisons or a Search
	{
	    $res['val'] = $this->getResult($res, $sub);    
	}

/* Boolean: getBool: Bool | ( .'(' Ws getDisjunction: Disjunction Ws .')' ) | getTerm: Term  */
protected $match_Boolean_typestack = array('Boolean');
function match_Boolean ($stack = array()) {
	$matchrule = "Boolean"; $result = $this->construct($matchrule, $matchrule, null);
	$_394 = NULL;
	do {
		$res_381 = $result;
		$pos_381 = $this->pos;
		$matcher = 'match_'.'Bool'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "getBool" );
			$_394 = TRUE; break;
		}
		$result = $res_381;
		$this->pos = $pos_381;
		$_392 = NULL;
		do {
			$res_383 = $result;
			$pos_383 = $this->pos;
			$_389 = NULL;
			do {
				if (substr($this->string,$this->pos,1) == '(') { $this->pos += 1; }
				else { $_389 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_389 = FALSE; break; }
				$matcher = 'match_'.'Disjunction'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "getDisjunction" );
				}
				else { $_389 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_389 = FALSE; break; }
				if (substr($this->string,$this->pos,1) == ')') { $this->pos += 1; }
				else { $_389 = FALSE; break; }
				$_389 = TRUE; break;
			}
			while(0);
			if( $_389 === TRUE ) { $_392 = TRUE; break; }
			$result = $res_383;
			$this->pos = $pos_383;
			$matcher = 'match_'.'Term'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "getTerm" );
				$_392 = TRUE; break;
			}
			$result = $res_383;
			$this->pos = $pos_383;
			$_392 = FALSE; break;
		}
		while(0);
		if( $_392 === TRUE ) { $_394 = TRUE; break; }
		$result = $res_381;
		$this->pos = $pos_381;
		$_394 = FALSE; break;
	}
	while(0);
	if( $_394 === TRUE ) { return $this->finalise($result); }
	if( $_394 === FALSE) { return FALSE; }
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
	$_403 = NULL;
	do {
		$res_396 = $result;
		$pos_396 = $this->pos;
		$matcher = 'match_'.'Boolean'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "notNegated" );
			$_403 = TRUE; break;
		}
		$result = $res_396;
		$this->pos = $pos_396;
		$_401 = NULL;
		do {
			if (( $subres = $this->literal( 'not' ) ) !== FALSE) { $result["text"] .= $subres; }
			else { $_401 = FALSE; break; }
			$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) { $this->store( $result, $subres ); }
			else { $_401 = FALSE; break; }
			$matcher = 'match_'.'Boolean'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "negated" );
			}
			else { $_401 = FALSE; break; }
			$_401 = TRUE; break;
		}
		while(0);
		if( $_401 === TRUE ) { $_403 = TRUE; break; }
		$result = $res_396;
		$this->pos = $pos_396;
		$_403 = FALSE; break;
	}
	while(0);
	if( $_403 === TRUE ) { return $this->finalise($result); }
	if( $_403 === FALSE) { return FALSE; }
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
	$_412 = NULL;
	do {
		$matcher = 'match_'.'Negation'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "Operand1" );
		}
		else { $_412 = FALSE; break; }
		while (true) {
			$res_411 = $result;
			$pos_411 = $this->pos;
			$_410 = NULL;
			do {
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_410 = FALSE; break; }
				if (( $subres = $this->literal( 'and' ) ) !== FALSE) { $result["text"] .= $subres; }
				else { $_410 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_410 = FALSE; break; }
				$matcher = 'match_'.'Negation'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "Operand2" );
				}
				else { $_410 = FALSE; break; }
				$_410 = TRUE; break;
			}
			while(0);
			if( $_410 === FALSE) {
				$result = $res_411;
				$this->pos = $pos_411;
				unset( $res_411 );
				unset( $pos_411 );
				break;
			}
		}
		$_412 = TRUE; break;
	}
	while(0);
	if( $_412 === TRUE ) { return $this->finalise($result); }
	if( $_412 === FALSE) { return FALSE; }
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
	$_421 = NULL;
	do {
		$matcher = 'match_'.'Conjunction'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "Operand1" );
		}
		else { $_421 = FALSE; break; }
		while (true) {
			$res_420 = $result;
			$pos_420 = $this->pos;
			$_419 = NULL;
			do {
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_419 = FALSE; break; }
				if (( $subres = $this->literal( 'or' ) ) !== FALSE) { $result["text"] .= $subres; }
				else { $_419 = FALSE; break; }
				$matcher = 'match_'.'Ws'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_419 = FALSE; break; }
				$matcher = 'match_'.'Conjunction'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "Operand2" );
				}
				else { $_419 = FALSE; break; }
				$_419 = TRUE; break;
			}
			while(0);
			if( $_419 === FALSE) {
				$result = $res_420;
				$this->pos = $pos_420;
				unset( $res_420 );
				unset( $pos_420 );
				break;
			}
		}
		$_421 = TRUE; break;
	}
	while(0);
	if( $_421 === TRUE ) { return $this->finalise($result); }
	if( $_421 === FALSE) { return FALSE; }
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
        $res['name'] = $sub['text'];
        $res['sqlname'] = $sub['text'];
    
        $this->diagnosticPrint($res, $sub);
    }

    /**
     * Returns the value comparison operator from the DateComp or ValueComp rules
     * @param unknown $res
     * @param unknown $sub
     */
    private function getCompareOp(&$res, $sub)
    {
        switch($sub['text'])
        {
            case '':
                $res['comp'] = '=' ;
                break;
            default:
                $res['comp'] = $sub['text'] ;
                break;
        }
    
        $this->diagnosticPrint($res, $sub);
    }
    
    /**
     * Returns the string comparison operator from the StringComp rule
     * @param unknown $res
     * @param unknown $sub
     */
    private function getCompareOpString(&$res, $sub)
    {
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
    
        $this->diagnosticPrint($res, $sub);
    }
    
    /**
     * Returns the compare result for one of the compare rules
     * @param unknown $res
     * @param unknown $sub
     */
    private function getCompareResult(&$res, $sub)
    {
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
        
        $this->diagnosticPrint($res, $sub);
    }
    
    /**
     * Returns the compare result for the BoolComp rule
     * @param unknown $res
     * @param unknown $sub
     */
    private function getCompareResultBool(&$res, $sub)
    {
        $query = ColumnInfo::getDefault()->getSqlExists($res['name'], $this->id);               // prepare the query
        if ($this->clientSite)
        {
            $res['val'] = $this->clientSite->test($query) === $sub['val'];                      // then perform the test and store result in the tree
        }
    
        $this->diagnosticPrint($res, $sub);
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
        	$res['val'] = $this->childParser->getValue()->test($this->id, $query);				// invoke the child parser to evaluate the search
        }
        else 
        {
        	$res['val'] = false;
        }
    }
    
    /**
     * Invoke the child parser to evaluate some assembled replacement rule
     * @param string $query
     * @return bool
     */
    private function invokeChildParser($query)
    {
    	return $this->childParser->getValue()->test($this->id, $query);							// invoke te child parser to evaluate the replacement rule
    }
    
    /**
     * Returns the result of a sub rule for several rules
     * @param unknown $res
     * @param unknown $sub
     * @return unknown
     */
    private function getResult(&$res, $sub)                                                     // Term is either one of the comparisons or a Search
    {
        $this->diagnosticPrint($res, $sub);
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
    
    /**
     * Performs a diagnostic print
     * @param unknown $res
     * @param unknown $sub
     */
    private function diagnosticPrint($res, $sub)
    {
    	$rule = $res["_matchrule"];
    	$subrule = $sub["_matchrule"];
    	
    	Diagnostic::diagnosticPrint(
    			"In $rule, sub rule $subrule, detected: " . 
    			var_export($sub, true) . 
    			", result: " . 
    			var_export($res, true));    	
    }
}
