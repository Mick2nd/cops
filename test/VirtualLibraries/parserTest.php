<?php

namespace VirtualLibraries
{
	/**
	 * COPS (Calibre OPDS PHP Server) test file
	 *
	 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
	 * @author     Jürgen Habelt <juergen@habelt-jena.de>
	 */
	
	require_once 'config.php';

	use PHPUnit\Framework\TestCase;
	use phpDocumentor\Reflection\Types\Boolean;
	
    class parserTest extends TestCase
    {
    	private $clientSite;
    	
    	/**
    	 * This helper tests that a rule is not matched by a given test string
    	 * @param string $rule
    	 * @param string $test
    	 */
    	private function notMatchRule($rule, $test)
    	{
    		$ruleFunc = 'match_' . $rule;										// from the rule name build the function name
    		$parser = new VirtualLibrariesParser($test, $this->clientSite);		// instantiate the parser with the test expression
    		$res = $parser->$ruleFunc();										// and invoke the rule
    		
    		parent::assertTrue(													// assert that we get NO result
    				$res === false,
    				"No result expected for rule $rule with test $test.");    				
    	}
    	
    	/**
    	 * This helper can be used to check a given rule of the parser for a certain outcome
    	 * @param string $rule - rule to be checked
    	 * @param string $test - test to check against
    	 * @param string $key - key of the results array where the checked result resides 
    	 * @param unknown $expected - the expected value
         * @return array|Boolean
    	 */
        private function matchRule($rule, $test, $key, $expected)
        {
        	$ruleFunc = 'match_' . $rule;										// from the rule name build the function name
        	$parser = new VirtualLibrariesParser($test, $this->clientSite);		// instantiate the parser with the test expression
        	$res = $parser->$ruleFunc();										// and invoke the rule
        	
        	parent::assertFalse(												// assert that we get a result
        			$res === false, 
        			"Result expected for rule $rule with test $test.");
        	parent::assertTrue(													// and we get one at given key
        			array_key_exists($key, $res),
        			"Result expected for rule $rule with test $test at key $key");
        	parent::assertEquals(												// and with the given value
        			$expected, $res[$key], 
        			"Result $expected expected for rule $rule");
        	
        	return $res;
        }
        
        /**
         * Helper simplifies the Name rule invocation
         * @param unknown $name
         * @param unknown $custom
         */
        private function matchName($name, $custom = '')
        {
       		$res = $this->matchRule('Name', $custom . $name, 'text', $name);
       		
       		$flag = $custom === '#';
   			parent::assertSame(
   					$flag, $res['custom'],
   					"Result $flag expected for rule Name at key custom");
        }

        /**
         * Helper simplifies the Float rule invocation
         * @param unknown $float
         */
        private function matchFloat($float)
        {
        	$this->matchRule('Float', $float, 'text', $float);
        }
        
        /**
         * Helper simplifies the Integer rule invocation
         * @param unknown $int
         */
        private function matchInteger($int)
        {
        	$this->matchRule('Integer', $int, 'text', $int);
        }
        
        /**
         * Helper simplifies the Bool rule invocation
         * Bool with and without double quotes is permitted
         * @param unknown $bool
         */
        private function matchBool($bool)
        {
        	$this->matchRule('Bool', $bool, 'val', $bool === 'true');
        	$this->matchRule('Bool', '"' . $bool . '"', 'val', $bool === 'true');
        }

       	/**
       	 * Preparation for each test
       	 * {@inheritDoc}
       	 * @see PHPUnit_Framework_TestCase::setUp()
       	 */
       	function setUp()
       	{
       		if (ColumnInfo::getDefault()->getItem('pubdate') === null)
       		{
       			$nativeColumns = array(
       				'pubdate' => 'TIMESTAMP',
       				'last_modified' => 'TIMESTAMP',
       				'timestamp' => 'TIMESTAMP'
       			);
       			ColumnInfo::getDefault()->setColumnInfo($nativeColumns);
       		}
       		
       		if (ColumnInfo::getDefault()->getItem('genre') === null)
       		{
       			$customColumnInfo = array(
       					array('label' => 'genre', 'id' => 1)
       			);
       			ColumnInfo::getDefault()->setCustomColumnInfo($customColumnInfo);
       		}
       		
       		
       		$this->clientSite = parent::getMockBuilder('VirtualLibraries\IClientSite')
       			->setMethods(array('test', 'getIds', 'create', 'isSelected'))
       			->getMock();
       		
       		$this->clientSite
       			->expects(parent::any())
       			->method('test')
       			->willReturn(true);
       		$this->clientSite
       			->expects(parent::any())
       			->method('getIds')
       			->willReturn(array());
       	}
       	
       	/**
       	 * Cleanup
       	 * {@inheritDoc}
       	 * @see PHPUnit_Framework_TestCase::tearDown()
       	 */
       	function tearDown()
       	{
       		$this->clientSite = null;
       	}
       	
       	/**
       	 * The data provider for the testValidName test
       	 * @return array
       	 */
       	function nameDataProvider()
       	{
       		return
       		array(
       				array('name' => 'title', 'custom' => ''),
       				array('name' => 'author', 'custom' => ''),
       				array('name' => 'authors', 'custom' => ''),
       				array('name' => 'author_sort', 'custom' => ''),
       				array('name' => 'cover', 'custom' => ''),
       				array('name' => 'ondevice', 'custom' => ''),
       				array('name' => 'publisher', 'custom' => ''),
       				array('name' => 'rating', 'custom' => ''),
       				array('name' => 'series', 'custom' => ''),
       				array('name' => 'series_index', 'custom' => ''),
       				array('name' => 'series_sort', 'custom' => ''),
       				array('name' => 'tags', 'custom' => ''),
       				array('name' => 'comments', 'custom' => ''),
       				array('name' => 'formats', 'custom' => ''),
       				array('name' => 'identifiers', 'custom' => ''),
       				array('name' => 'languages', 'custom' => ''),
       				array('name' => 'size', 'custom' => ''),
       				array('name' => 'uuid', 'custom' => ''),
       				array('name' => 'timestamp', 'custom' => ''),
       				array('name' => 'last_modified', 'custom' => ''),
       				array('name' => 'pubdate', 'custom' => ''),
       				array('name' => 'genre', 'custom' => '#'),
       		);
       	}

       	/**
       	 * The data provider for testInvalidName test
       	 * @return string[][]
       	 */
       	function invalidNameDataProvider()
       	{
       		return 
       		array(
       				array('name' => '###'),	
       				array('name' => 'identifierNotInSet'),
       		);
       	}
       	
       	/**
       	 * Test the recoginition of a valid name
       	 * @dataProvider nameDataProvider
       	 * @param string $name
       	 * @param string $custom
       	 */
       	function testValidName($name, $custom)
       	{
       		$this->matchName($name, $custom);
       	}
       	
       	/**
       	 * Test the recognition of invalid Name rules
       	 * @dataProvider invalidNameDataProvider
       	 * @param string $name
       	 */
       	function testInvalidName($name)
       	{
       		$this->notMatchRule('Name', $name);
       	}
       	
       	/**
       	 * The data provider for the testValidFloat test
       	 * @return number[][]
       	 */
       	function floatDataProvider()
       	{
       		return
       		array(
       				array('float' => 3.1415),
       				array('float' => 96.),
       				array('float' => 63748),
       		);
       	}       	
       	
       	/**
       	 * Test valid float
       	 * @dataProvider floatDataProvider
       	 * @param float $float
       	 */
       	function testValidFloat($float)
       	{
       		$this->matchFloat($float);
       	}
       	
       	/**
       	 * The data provider for the testValidInteger test
       	 * @return number[][]
       	 */
       	function integerDataProvider()
       	{
       		return
       		array(
       				array('int' => 96000),
       				array('int' => 63748),
       		);
       	}
       	
       	/**
       	 * Test valid integer
       	 * @dataProvider integerDataProvider
       	 * @param int $int
       	 */
       	function testValidInteger($int)
       	{
       		$this->matchInteger($int);
       	}
       	
       	/**
       	 * The data provider for the testValidBool test
       	 * @return bool[][]
       	 */
       	function boolDataProvider()
       	{
       		return
       		array(
       				array('bool' => 'true'),
       				array('bool' => 'false'),
       		);
       	}
       	
       	/**
       	 * Test valid bool
       	 * @dataProvider boolDataProvider
       	 * @param bool $bool
       	 */
       	function testValidBool($bool)
       	{
       		$this->matchBool($bool);
       	}
       	
       	/**
       	 * The data provider for the testValidDate test
       	 * @return string[][]
       	 */
       	function dateDataProvider()
       	{
       		return 
       		array(
       				array('date' => '2017-03-08', 'expected' => "date('2017-03-08', 'start of day')"),
       				array('date' => '2017-05', 'expected' => "date('2017-05-01', 'start of month')"),
       				array('date' => '2012', 'expected' => "date('2012-01-01', 'start of year')"),
       				array('date' => 'today', 'expected' => "date('now', 'start of day')"),
       				array('date' => 'yesterday', 'expected' => "date('now', 'start of day', '-1 days')"),
       				array('date' => 'thismonth', 'expected' => "date('now', 'start of month')"),
       				array('date' => '9 daysago', 'expected' => "date('now', 'start of day', '-9 days')"),
       		);
       	}
       	
       	/**
       	 * Test the recognition of a valid date
       	 * @dataProvider dateDataProvider
       	 * @param string $date
       	 * @param string $expected
       	 */
       	function testValidDate($date, $expected)
       	{
       		$this->matchRule('Date', $date, 'text', $expected);
       	}
       	
       	/**
       	 * Test the recognition of an invalid Date
       	 */
       	function testInvalidDate()
       	{
       		$this->notMatchRule('Date', 'shit');
       	}
       	
       	/**
       	 * The data provider for the testValidSize test
       	 * @return string[][]
       	 */
       	function sizeDataProvider()
       	{
       		return 
       		array(
       				array('size' => '345', 'expected' => '345'),
       				array('size' => '34k', 'expected' => '34816'),
       				array('size' => '1.2M', 'expected' => '1258291'),
       		);
       	}
       	
       	/**
       	 * Test a valid size
       	 * @dataProvider sizeDataProvider
       	 * @param string $size
       	 * @param string $expected
       	 */
       	function testValidSize($size, $expected)
       	{
       		$this->matchRule('Size', $size, 'text', $expected);
       	}
       	
       	/**
       	 * Test the recognition of invalid Size rules
       	 */
       	function testInvalidSize()
       	{
       		$this->notMatchRule('Size', 'AA');
       	}
       	
       	/**
       	 * The data provider for the testComplexRule test
       	 * @return string[][]|boolean[][]
       	 */
       	function complexRuleDataProvider()
       	{
       		return 
       		array(
       				array('rule' => 'String', 'test' => '"=TEST"', 'key' => 'text', 'expected' => "'TEST'"),
       				array('rule' => 'String', 'test' => '"=TEST"', 'key' => 'comp', 'expected' => '='),
       				
       				array('rule' => 'Disjunction', 'test' => '(true)', 'key' => 'val', 'expected' => true),
       				array('rule' => 'Disjunction', 'test' => '(false)', 'key' => 'val', 'expected' => false),
       		);
       	}
       	
       	/**
       	 * Test a large amount of Rules using the matchRule helper
       	 * @param string $rule
       	 * @param string $test
       	 * @param string $key
       	 * @param string|bool $expected
       	 * @dataProvider complexRuleDataProvider
       	 */
       	function testComplexRule($rule, $test, $key, $expected)
       	{
       		$this->matchRule($rule, $test, $key, $expected);
       	}
       	
       	/**
       	 * Data provider for the logical 'not' operation
       	 * @return string[][]|boolean[][]
       	 */
       	function unaryNotDataProvider()
       	{
       		return 
       		array(
       				array('truth' => 'false', 'expected' => true),
       				array('truth' => 'true', 'expected' => false),
       		);
       	}
        
       	/**
       	 * Test of unary not
         * @dataProvider unaryNotDataProvider
         * @param string $truth
         * @param bool $expected
       	 */
        function testUnaryNot($truth, $expected)
        {
        	$this->matchRule('Disjunction', "not $truth", 'val', $expected);
        }
        
        /**
         * Data provider for the logical 'or' operation
         * @return string[][]|boolean[][]
         */
        function binaryOrDataProvider()
        {
        	return
        	array(
        			array('first' => 'false', 'second' => 'false', 'expected' => false),
        			array('first' => 'false', 'second' => 'true', 'expected' => true),
        			array('first' => 'true', 'second' => 'false', 'expected' => true),
        			array('first' => 'true', 'second' => 'true', 'expected' => true),
        	);
        }
        
        /**
         * Test of binary or
         * @dataProvider binaryOrDataProvider
         * @param string $first
         * @param string $second
         * @param bool $expected
         */
        function testBinaryOr($first, $second, $expected)
        {
        	$this->matchRule('Disjunction', "$first or $second", 'val', $expected);
        }
        
        /**
         * Data provider for the logical 'and' operation
         * @return string[][]|boolean[][]
         */
        function binaryAndDataProvider()
        {
        	return 
        	array(
        			array('first' => 'false', 'second' => 'false', 'expected' => false),
        			array('first' => 'false', 'second' => 'true', 'expected' => false),
        			array('first' => 'true', 'second' => 'false', 'expected' => false),
        			array('first' => 'true', 'second' => 'true', 'expected' => true),
        	);
        }
        
        /**
         * Test of binary and
         * @dataProvider binaryAndDataProvider
         * @param string $first
         * @param string $second
         * @param bool $expected
         */
        function testBinaryAnd($first, $second, $expected)
        {
        	$this->matchRule('Disjunction', "$first and $second", 'val', $expected);
        }
        
        /**
         * Data provider for Compare operations
         * @return string[][]|boolean[][]
         */
        function comparisonDataProvider()
        {
        	return 
        	array(
        			array('test' => 'authors:"London"', 'expected' => true),
        			array('test' => '#genre:"=.Mathematik"', 'expected' => true),
        			
        			array('test' => 'authors:true', 'expected' => true),
        			array('test' => 'authors:false', 'expected' => false),
        			
        			array('test' => 'pubdate:=2007', 'expected' => true),
        			array('test' => 'last_modified:=2017', 'expected' => true),
        			
        			array('test' => 'not size:<5.1M', 'expected' => false),        			
        	);
        }
        
        /**
         * Test a few of the compare expressions
         * @dataProvider comparisonDataProvider
         * @param string $test
         * @param bool $expected
         */
        function testCompare($test, $expected)
        {
        	$this->matchRule('Disjunction', $test, 'val', $expected);
        }
    }
}
