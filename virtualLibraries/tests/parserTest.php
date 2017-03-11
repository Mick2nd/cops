<?php

namespace VirtualLibraries
{
    require_once dirname(__DIR__) . '/virtualLibrariesParser.php';
    require_once dirname(dirname(__DIR__)) . '/log4php/Logger.php';
    
    \Logger::configure(dirname(dirname(__DIR__)) . '/config.xml');
	date_default_timezone_set('Europe/Berlin');
    
    class parserTest
    {
        /*
         * Match a single string
         */
        function matchString($parse_string)
        {
            print "Printing a String parse\n";
        
            $parser= new VirtualLibraryParser($parse_string);
            $res = $parser->match_StringComp() ;
            if ($res !== FALSE)
            {
                print("$parse_string is parsed as:\n");
                var_dump($res);
            }
            else
            {
                print "No Match\n" ;
            }
        }
        
        /*
         * Prints a single Logical Relation
         */
        function printRelation($op, $operand1, $operand2 = null)
        {
            $parse_string = "$operand1 $op $operand2";
            if ($operand2 === null)
                $parse_string = "$op $operand1";
        
                $parser= new VirtualLibraryParser($parse_string);
                $res = $parser->match_Disjunction() ;
                if ($res !== FALSE)
                {
                    $bool_string = var_export($res['val'], true);
                    print("$parse_string = $bool_string\n");
                }
                else
                {
                    print "No Match\n" ;
                }
        }
        
        /*
         * Prints Logical Relations for Unary and Binary operations
         */
        function printTruthTables()
        {
            $operators = array("and" , "or");
            $truth_values = array("false", "true");
        
            print "Printing a truth table\n";
        
            print "For operator " . "not" . "\n";
            foreach($truth_values as $val)
            {
                self::printRelation("not", $val);
            }
        
            foreach($operators as $op)
            {
                print "For operator " . $op . "\n";
                foreach($truth_values as $val1)
                {
                    foreach($truth_values as $val2)
                    {
                        self::printRelation($op, $val1, $val2);
                    }
                }
            }
        }        
    }
    

    parserTest::printTruthTables();
    
    parserTest::matchString('authors:"All of this is inside!"');    
}
