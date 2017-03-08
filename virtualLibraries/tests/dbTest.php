<?php

namespace VirtualLibraries
{
    require_once dirname(__DIR__) . '/dbProxy.php';
    require_once dirname(dirname(__DIR__)) . '/log4php/Logger.php';
    
    \Logger::configure(dirname(dirname(__DIR__)) . '/config.xml');
    date_default_timezone_set('Europe/Berlin');
    
    class dbTest
    {
        /**
         * Performs a Simple Query to the database encapsulated by dbProxy
         */
        static function simpleQuery()
        {
            echo ("Trying to open db");
            $proxy = new DbProxy();
            
            if ($proxy->open())
            {
                echo (" ... success !\n");
                if ($proxy->test("select id from books where id=3") &&
                    ! $proxy->test("select id from books where id=1000"))
                {
                    echo ("Performed my first succesful query.\n");
                }
            
                $proxy->close();
            }            
        }
        
        /**
         * Performs a query with regexp call to the database encapsulated by dbProxy
         */
        static function regexpQuery()
        {
            echo ("Trying to open db");
            $proxy = new DbProxy();
            
            if ($proxy->open())
            {
                echo (" ... success !\n");
                $result = $proxy->executeQueryAll("select id, title from books where title regexp 'tolldreist'");
                if ($result !== false)
                {
                    $title = $result[0]['title'];
                    echo ("Found with regexp: '$title'.\n");
                }
            
                $proxy->close();
            }
        }
        
        /**
         * Performs a query with group_concat aggregation call to the database encapsulated by dbProxy
         */
        static function groupConcatQuery()
        {
            echo ("Trying to open db");
            $proxy = new DbProxy();
            
            if ($proxy->open())
            {
                echo (" ... success !\n");
                $embedded = ForeignColumns::getDefault()->getItem("authors")->getSelect(0);
                
                $result = $proxy->executeQueryAll("select id, title, $embedded \nfrom books where title regexp 'tolldreist'");
                if ($result !== false)
                {
                    $title = $result[0]['title'];
                    $authors = $result[0]['authors'];
                    echo ("Found using group_concat: '$title' by '$authors'.\n");
                }
            
                $proxy->close();
            }            
        }
    }
    
    dbTest::simpleQuery();
    
    dbTest::regexpQuery();

    dbTest::groupConcatQuery();
    
}
