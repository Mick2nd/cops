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
    

/*!* VirtualLibraryParser

Ws: . /[ \t]* /

NonDelim: /[^ \t:]* /

NonQuote: /[^"]* /

Name: getBuiltinName: (CommonName | DateName | SizeName | IdentifiersName) | getCustomName: CustomName
	function getBuiltinName(&$res, $sub)
	{
		$res['custom'] = false ;
		$res['text'] = $sub['text'] ;
	}
	function getCustomName(&$res, $sub)
	{
		$res['custom'] = true ;
		$res['text'] = $sub['text'] ;
	}

GenericName: getBuiltinName: (CommonName | DateName | SizeName) | getCustomName: CustomName
	function getBuiltinName(&$res, $sub)
	{
		$res['custom'] = false ;
		$res['text'] = $sub['text'] ;
	}
	function getCustomName(&$res, $sub)
	{
		$res['custom'] = true ;
		$res['text'] = $sub['text'] ;
	}

CommonName: 'title' | 'author_sort' | 'authors' | 'author' | 'cover' | 'ondevice' | 'publisher' |
	'rating' | 'series_index' | 'series_sort' | 'series' | 'tags' | 'comments' | 
	'formats' | 'languages' | 'uuid' | 'identifiersType' | 'identifiersValue'

SizeName:  'size'

DateName: 'timestamp' | 'last_modified' | 'pubdate'

IdentifiersName: 'identifiers'

CustomName: .'#' /[a-zA-Z][a-zA-Z0-9]* / 

Float: Integer ( '.' /[0-9]* / )? 

Integer: /[0-9]+/

Size: (getSizeInK: (Float .'k')) | (getSizeInM: (Float .'M')) | (getSize: Integer)
	function getSize(&$res, $sub)
	{
		$res['text'] = $sub['text'] ;
	
	    $rule = $res["_matchrule"];
	    Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
	}
	function getSizeInK(&$res, $sub)
	{
		$res['text'] = strval(intval($sub['text'] * 1024)) ;			// to be tested
	
	    $rule = $res["_matchrule"];
	    Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
	}
	function getSizeInM(&$res, $sub)
	{
		$res['text'] = strval(intval($sub['text'] * 1024 * 1024)) ;		// to be tested
	
	    $rule = $res["_matchrule"];
	    Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
	}

Date: getDate: (getSubdate: RelativeDate | getSubdate: AbsoluteDate)
	function getSubdate(&$res, $sub)
	{
		$res['date'] = $sub['date'];
		$res['accuracy'] = $sub['accuracy'];
		$res['displacement'] = $sub['displacement'];
	}
	function getDate(&$res, $sub)
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

AbsoluteDate: getDate: ((getYear: Integer) ( '-' (getMonth: Integer) ( '-' getDay: Integer )? )?)
	function getYear(&$res, $sub)
	{
		$res['year'] = $sub['text'];
		$res['month'] = '01'; 
		$res['day'] = '01'; 
		$res['accuracy'] = 'start of year';
	}
	function getMonth(&$res, $sub)
	{
		$res['month'] = $sub['text'];
		$res['accuracy'] = 'start of month';
	}
	function getDay(&$res, $sub)
	{
		$res['day'] = $sub['text'];
		$res['accuracy'] = 'start of day';
	}
	function getDate(&$res, $sub)
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

RelativeDate: (getToday: 'today') | (getYesterday: 'yesterday') | (getDaysAgo: Integer Ws 'daysago') | (getThisMonth: 'thismonth')
	function getToday(&$res, $sub)
	{
		$res['accuracy'] = 'start of day';
		$res['displacement'] = null;
		$res['date'] = 'now';
	}
	function getYesterday(&$res, $sub)
	{
		$res['accuracy'] = 'start of day';
		$res['displacement'] = '-1';
		$res['date'] = 'now';
	}
	function getDaysAgo(&$res, $sub)
	{
		$res['accuracy'] = 'start of day';
		$res['displacement'] = '-' . $sub['text'];
		$res['date'] = 'now';
	}
	function getThisMonth(&$res, $sub)
	{
		$res['accuracy'] = 'start of month';
		$res['displacement'] = null;
		$res['date'] = 'now';
	}

Bool: (getBool: ('true' | 'false')) | (.'"' getBoolRe: Bool .'"')
	function getBool(&$res, $sub)
	{
		$res['origtext'] = $sub['text']; 
		$res['val'] = $sub['text'] === 'true';
	}
	function getBoolRe(&$res, $sub)
	{
		$res['origtext'] = $sub['origtext']; 
		$res['val'] = $sub['val'];
	}

String: (. '"' getCompareOpString: CompareOperatorString getString: NonQuote . '"') | 
	(getCompareOpString: CompareOperatorString getString: NonDelim Ws)
	function getCompareOpString(&$res, $sub)
	{
		$res['origtext'] = $sub['text']; 
	    $this->getCompareOpString($res, $sub);
	}
	function getString(&$res, $sub)
	{
		$res['origtext'] .= $sub['text'];
		$res['puretext'] = $sub['text'];
		$res['text'] = "'" . $res['anchor'] . $sub['text'] . "'";
	}

CompareOperatorString: ('=.' | '=' | '~' | '')

CompareOperator: ('<=' | '>=' | '<' | '>' | '=' | '')

StringComp: getName: GenericName Ws .':' Ws getCompareResult: String
	function getName(&$res, $sub)
	{
	    $this->getName($res, $sub);
	}
	function getCompareResult(&$res, $sub)
	{
	    $res['comp'] = $sub['comp'];    
	    $this->getCompareResult($res, $sub);
	}

DateComp: getName: DateName Ws .':' Ws getCompareOp: CompareOperator Ws getCompareResult: Date     
	function getName(&$res, $sub)
	{
	    $this->getName($res, $sub);
	}
	function getCompareOp(&$res, $sub)
	{
	    $this->getCompareOp($res, $sub);
	}
	function getCompareResult(&$res, $sub)
	{
	    $name = $res['name'];
	    $accuracy = $sub['accuracy'];
	    $res['sqlname'] = "date('$name', '$accuracy')";
	    $this->getCompareResult($res, $sub);
	}

SizeComp: getName: SizeName Ws .':' Ws getCompareOp: CompareOperator Ws getCompareResult: Size
	function getName(&$res, $sub)
	{
	    $this->getName($res, $sub);
	}
	function getCompareOp(&$res, $sub)
	{
	    $this->getCompareOp($res, $sub);
	}
	function getCompareResult(&$res, $sub)
	{
	    $this->getCompareResult($res, $sub);
	}

ValueComp: getName: GenericName Ws .':' Ws getCompareOp: CompareOperator Ws getCompareResult: Integer
	function getName(&$res, $sub)
	{
	    $this->getName($res, $sub);
	}
	function getCompareOp(&$res, $sub)
	{
	    $this->getCompareOp($res, $sub);
	}
	function getCompareResult(&$res, $sub)
	{
	    $this->getCompareResult($res, $sub);
	}

BoolComp: getName: GenericName Ws .':' Ws getCompareResult: Bool
	function getName(&$res, $sub)
	{
	    $this->getName($res, $sub);
	}
	function getCompareResult(&$res, $sub)
	{
	    $this->getCompareResultBool($res, $sub);    
	}

IdentifiersDef: (.'"' getResult: IdentifiersDef .'"') | 
	((getTypeDef: String) Ws .':' Ws (getBool: Bool)) | 
	((getTypeDef: String) Ws .':' Ws (getValueDef: String))
	function getTypeDef(&$res, $sub)
	{
		$res['type'] = 'identifiersType' . ':' . $sub['origtext'];
        $this->diagnosticPrint($res, $sub);
	}
	function getBool(&$res, $sub)
	{
		$rule = $res['type'];
		if (! $sub['val'])
		{
			$rule = 'not ' . $rule;
		}
		$res['val'] = $this->invokeChildParser($rule);
        $this->diagnosticPrint($res, $sub);
	}
	function getValueDef(&$res, $sub)
	{
		$rule = 'identifiersValue' . ':"' . $sub['origtext'] . '"';
		$rule .= ' and ';
		$rule .= $res['type'];
		$res['val'] = $this->invokeChildParser($rule);
        $this->diagnosticPrint($res, $sub);
	}
	function getResult(&$res, $sub)
	{
		$res['val'] = $sub['val'];
	}

IdentifiersComp: IdentifiersName Ws .':' Ws (getBool: Bool | getResult: IdentifiersDef)
	function getBool(&$res, $sub)
	{
		$rule = 'identifiersType' . ':' . $sub['origtext'];
		$res['val'] = $this->invokeChildParser($rule);
	}
	function getResult(&$res, $sub)
	{
		$res['val'] = $sub['val'];
	}

Search: .'search:' Ws execSearch: String
	function execSearch(&$res, $sub)                                                            // represents one of the saved searches
	{
	    $this->execSearch($res, $sub);    
	}

Term: Search | DateComp | SizeComp | IdentifiersComp | ValueComp | BoolComp | StringComp   
	function * (&$res, $sub)                                                                    // Term is either one of the comparisons or a Search
	{
	    $res['val'] = $this->getResult($res, $sub);    
	}

Boolean: getBool: Bool | ( .'(' Ws getDisjunction: Disjunction Ws .')' ) | getTerm: Term 
	function getBool(&$res, $sub)                                                               // The Boolean is either a Bool constant,
	{
	    $res['val'] = $this->getResult($res, $sub);    
	}
	function getDisjunction(&$res, $sub)                                                        // a complex expression in parenthesis
	{
	    $res['val'] = $this->getResult($res, $sub);    
	}
	function getTerm(&$res, $sub)                                                               //   or a Term
	{
	    $res['val'] = $this->getResult($res, $sub);    
	}

Negation: notNegated: Boolean | ('not' Ws negated: Boolean)
	function notNegated(&$res, $sub)
	{
	    $res['val'] = $this->getResult($res, $sub);    
	}
	function negated(&$res, $sub)
	{
	    $res['val'] = !$this->getResult($res, $sub);    
	}

Conjunction: Operand1: Negation (Ws 'and' Ws Operand2: Negation)*
	function Operand1(&$res, $sub)
	{
	    $res['val'] = $this->getResult($res, $sub);    
	}
	function Operand2(&$res, $sub)
	{
		$res['val'] = $res['val'] && $this->getResult($res, $sub);
	}

Disjunction: Operand1:Conjunction (Ws 'or' Ws Operand2:Conjunction)*
	function Operand1(&$res, $sub)
	{
	    $res['val'] = $this->getResult($res, $sub);    
	}
	function Operand2(&$res, $sub)
	{
		$res['val'] = $res['val'] || $this->getResult($res, $sub);
	}

*/
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
