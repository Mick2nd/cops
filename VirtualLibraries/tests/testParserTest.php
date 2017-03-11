<?php


namespace VirtualLibraries
{
    require_once 'testParser.php';

    class testParserTest
    {
        /*
         * Match a Bool
         */
        function matchBool($parse_string)
        {
            print "Printing a Bool parse\n";

            $parser= new testParser($parse_string);
            $res = $parser->match_Expr1() ;
            if ($res !== FALSE)
            {
                print("$parse_string is parsed as (Expr1):\n");
                var_dump($res);
            }
            else
            {
                print "No Match\n" ;
            }

            $parser= new testParser($parse_string);
            $res = $parser->match_Expr2() ;
            if ($res !== FALSE)
            {
                print("$parse_string is parsed as (Expr2 / 1):\n");
                var_dump($res);
            }
            else
            {
                print "No Match\n" ;
            }
            
            $parser->rewind();
            $res = $parser->match_Expr2() ;
            if ($res !== FALSE)
            {
                print("$parse_string is parsed as (Expr2 / 2):\n");
                var_dump($res);
            }
            else
            {
                print "No Match\n" ;
            }
            
            $parser= new testParser($parse_string);
            $res = $parser->match_Expr3() ;
            
        }

    }

    testParserTest::matchBool('true');
    
    /*
     * Results of this test:
     * 1. restart of parser possible, simply set pos = 0
     * 2. rules without function do not return other than "text" item
     * 3. rules with a common function cannot provide "val" items from alternative rules
     * 4. the generic * function will not be invoked if special function is  
     */
}
        