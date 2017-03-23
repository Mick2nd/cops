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
            $res = match_Disjunction();                                                                 // invoke parser
            
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
                $res = match_Disjunction();                                                             // invoke parser
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
    

/*!* VirtualLibraryParser

Ws: ./[ \t]* /

Integer: /[0-9]+/

Date: getDate: (Integer ( '-' Integer ( '-' Integer )? )?)
    function getDate (&$res, $sub)
    {
		$res['text'] = "Date('" . $sub['text'] . "')" ;
    } 

Bool: getBool: ('true' | 'false')
	function getBool(&$res, $sub)
	{
		$res['val'] = $sub['text'] === 'true';
	}

Name: getCustomFlag: ('#'?) getName: (/[a-zA-Z][a-zA-Z0-9]* /)
    function getCustomFlag(&$res, $sub)
    {
        $res['custom'] = ($sub['text'] === '#') ;
    }
    function getName(&$res, $sub)
    {
		$res['text'] = $sub['text'] ;
    }

String: .'"' getCompareOpString: ('=.' | '=' | '~' | '') getString: /[^"]* / .'"'
    function getCompareOpString(&$res, $sub)
    {
        $this->getCompareOpString($res, $sub);
    }
    function getString(&$res, $sub)
    {
		$res['comptext'] = $sub['text'] . "'";
		$res['puretext'] = $sub['text'];
    }
    
StringComp: getName: Name Ws .':' Ws getCompareResult: String
    function getName(&$res, $sub)
    {
        $this->getName($res, $sub);
    }
    function getCompareResult(&$res, $sub)
    {
        $res['comp'] = $sub['comp'];    
        $this->getCompareResult($res, $sub);
    }
    
DateComp: getName: ('pubdate' | 'timestamp' | 'last_modified') Ws .':' Ws getCompareOp: ('<=' | '>=' | '<' | '>' | '=' | '') Ws getCompareResult: Date     
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

ValueComp: getName: Name Ws .':' Ws getCompareOp: ('<=' | '>=' | '<' | '>' | '=' | '') Ws getCompareResult: Integer
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

BoolComp: getName: Name Ws .':' Ws getCompareResult: Bool
    function getName(&$res, $sub)
    {
        $this->getName($res, $sub);
    }
    function getCompareResult(&$res, $sub)
    {
        $this->getCompareResultBool($res, $sub);    
    }

Search: .'search:' Ws execSearch: String
    function execSearch(&$res, $sub)                                                            // represents one of the saved searches
    {
        $this->execSearch($res, $sub);    
    }

Term: Search | DateComp | StringComp | ValueComp | BoolComp   
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
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
        $res['name'] = $sub['text'];
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
    private function getCompareResult(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        Diagnostic::diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
        $compText = array_key_exists('comptext', $sub) ? $sub['comptext'] : $sub['text'];
        $name = $res['name'];
        $res['text'] = $name . $res['comp'] . $compText;                                        // prepare the comparison
        
        $column = ColumnInfo::getDefault()->getItem($name);                                     // request the column info
        if ($column)
        {
            $this->getAttachedColumnIds($column);                                               // more than 1 entry of attached table can be assigned
            $query = $column->getSqlWhere($this->id) . $res['text'];                            // and embbed it into Sql
            $this->updateQuery($column, $query);                                                // modify query for stricter test
        }
        
        if ($this->clientSite && isset($query))
        {
            $res['val'] = $this->clientSite->test($query);                                      // then perform the test and store result in the tree
        }
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
