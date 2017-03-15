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
	require_once 'testParser.php';

	use PHPUnit\Framework\TestCase;
	

	/**
	 * Performs a few tests how the generated parser works
	 * The results as the tests provide them are used to generate/adapt the unit tests 
	 * @author Jürgen
	 *
	 */
	class testParserTest extends TestCase
    {

        /**
         * Empty test, just for test functionality
         */
        function testNullTest()
        {
        	parent::assertTrue(true, 'Test should succeed.');
        }
        
        /**
         * test of rule without function
         */
        function testWithoutFunction()
        {
        	$parser = new testParser('true');
        	$res = $parser->match_Expr1() ;
        	parent::assertFalse(is_null($res), 'match_Expr1 should return a valid result');
        	parent::assertFalse(array_key_exists('val', $res), 'match_Expr1 should not return a result \'val\'');
        	parent::assertTrue(array_key_exists('text', $res), 'match_Expr1 should return a result \'text\'');
        }
        
        /**
         * test of rewind
     	 * restart of parser possible, simply call rewind
         */
        function testRewind()
        {
        	$parser = new testParser('true');
        	$res = $parser->match_Expr2() ;
        	parent::assertFalse(is_null($res), 'match_Expr2 should return a valid result');
        	
        	$parser->rewind();
        	$res = $parser->match_Expr2() ;
        	parent::assertFalse(is_null($res), 'match_Expr2 should return a valid result after rewind');
        }
        
        /**
         * test of Multiple Function calls, order not guaranteed
         * Result of test: 
         * - only two functions are invoked, but the All function, called finally, overwrites the 
         *   result of Bool, e.g. the complete array
         * - but it can reach the $sub result from Bool through
         */
        function testMultipleFunctions()
        {
        	$parser = new testParser('true');
        	$res = $parser->match_Expr3() ;

        	parent::assertEquals(false, $parser->invokedGeneric, 'function * should not be invoked');
        	parent::assertEquals(true, $parser->invokedBool, 'function Bool should be invoked');
        	parent::assertEquals(true, $parser->invokedAll, 'function All should be invoked');
        	 
        	parent::assertFalse(array_key_exists('text*', $res), 'match_Expr3 should not return a result \'text*\'');
        	parent::assertFalse(array_key_exists('textBool', $res), 'match_Expr3 should not return a result \'textBool\'');
        	parent::assertTrue(array_key_exists('textAll', $res), 'match_Expr3 should return a result \'textAll\'');
        	parent::assertTrue(array_key_exists('sub', $res), 'match_Expr3 should return a result \'sub\'');
        }
    }
    
    /*
     * Results of this test:
     * 1. restart of parser possible, simply set pos = 0
     * 2. rules without function do not return other than "text" item
     * 3. rules with a common function cannot provide "val" items from alternative rules
     *    remark: they can but the common function must reach them through !!, see Expr3
     * 4. the generic * function will not be invoked if special function is  
     */
}
        