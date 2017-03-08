<?php

namespace VirtualLibraries;

require_once 'sqlUtilities.php';
require_once 'utilities.php';
require_once dirname(__DIR__) . '/php-peg/autoloader.php';

use hafriedlander\Peg\Parser;

/**
 * Parser to test whether a certain book(id) belongs to a virtual library 
 * @author Jürgen
 */
class VirtualLibraryParser extends Parser\Basic 
{
    var $savedSearches;
    var $clientSite;
    var $id;
    var $savedFilters;
    
    function __construct($parse_string, IClientSite $clientSite = null, array $savedSearches = null)
    {
        parent::__construct($parse_string);
        
        $this->savedSearches = $savedSearches;
        $this->clientSite = $clientSite;
        $this->savedFilters = array();
    }

    /**
     * Use this function to
     * 1. reset the parse stream pointer
     * 2. provide another id to test for
     * @param number $id
     */
    public function prepare($id = 0)
    {
        $this->pos = 0;
        $this->depth = 0 ;
        $this->regexps = array() ;
        
        $this->id = $id;
    }


/*!* VirtualLibraryParser

# New design of parser

# Instead of using free names for columns in tables we use predefined ones
# We assign them properties like field name, table name and format for later use

TextColumn: 'title' | 'sort' | 'author_sort' | 'isbn' | 'lccn' | 'path' | 'uuid' |
	authors: ('authors' | 'author') | languages: 'languages' | publishers: ('publishers' | 'publisher') | 
	ratings: 'ratings' | series: 'series' | series_sort: 'series_sort' | tags: 'tags' |
	comments: 'comments'
	function * (&$res, $sub)
	{
		$res['name'] = $sub['text'];
		$res['field'] = $sub['text'];
		$res['format'] = 'TEXT';
		$res['table'] = 'books';
	}
	function authors(&$res, $sub)
	{
		$res['table'] = 'authors';
		$res['field'] = 'name';
	}
	function languages(&$res, $sub)
	{
		$res['table'] = 'languages';
		$res['field'] = 'lang_code';
	}
	function publishers(&$res, $sub)
	{
		$res['table'] = 'publishers';
		$res['field'] = 'name';
	}
	function ratings(&$res, $sub)
	{
		$res['table'] = 'ratings';
		$res['field'] = 'rating';
	}
	function series(&$res, $sub)
	{
		$res['table'] = 'series';
		$res['field'] = 'name';
	}
	function series_sort(&$res, $sub)
	{
		$res['table'] = 'series';
		$res['field'] = 'sort';
	}
	function tags(&$res, $sub)
	{
		$res['table'] = 'tags';
		$res['field'] = 'name';
	}
	function comments(&$res, $sub)
	{
		$res['table'] = 'comments';
		$res['field'] = 'text';
	}

TimestampColumn: 'timestamp' | 'pubdate' | 'last_modified'
	function * (&$res, $sub)
	{
		$res['name'] = $sub['text'];
		$res['field'] = $sub['text'];
		$res['format'] = 'TIMESTAMP';
		$res['table'] = 'books';
	}

RealColumn: 'series_index'
	function * (&$res, $sub)
	{
		$res['name'] = $sub['text'];
		$res['field'] = $sub['text'];
		$res['format'] = 'REAL';
		$res['table'] = 'books';
	}

IntegerColumn: 'id' | 'flags'
	function * (&$res, $sub)
	{
		$res['name'] = $sub['text'];
		$res['field'] = $sub['text'];
		$res['format'] = 'INTEGER';
		$res['table'] = 'books';
	}

BoolColumn: 'cover'
	function * (&$res, $sub)
	{
		$res['name'] = $sub['text'];
		$res['field'] = 'has_cover';
		$res['format'] = 'BOOL';
		$res['table'] = 'books';
	}


*/

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

Boolean: Bool | ( '(' Ws Disjunction Ws ')' ) | Term 
	function * (&$res, $sub)                                                               // The Boolean is either a Bool constant, a complex expression in parenthesis or a Term
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
    function getName(&$res, $sub)
    {
        $rule = $res["_matchrule"];
        diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
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
        diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
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
        diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
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
        diagnosticPrint("In $rule, detected: " . var_export($sub, true) . "\n");
        
        $compText = $sub['comptext'] ? $sub['comptext'] : $sub['text'];  
        $res['text'] = $res['name'] . $res['comp'] . $compText;                                 // prepare the comparison
        $query = ForeignColumns::getDefault()->GetSql($res['name'], $this->id) . $res['text'];  // and embbed it into Sql
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
        diagnosticPrint("In $rule, detected: " . var_export($sub['val'], true) . "\n");
    
        $query = ForeignColumns::getDefault()->GetSqlExists($res['name'], $this->id);           // prepare the query
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
        diagnosticPrint("In $rule, detected: " . var_export($sub['val'], true) . "\n");
        
        return $sub['val'];
    }
}

/// <summary>
/// Use this iface to query an outside db about book
/// </summary>
interface IClientSite
{
    public function create($parseString);
    public function isSelected($id);
    public function test($sql);
}

// TODO: Parser komplettieren (search, callbacks zu db, bool vergleich etc.)
