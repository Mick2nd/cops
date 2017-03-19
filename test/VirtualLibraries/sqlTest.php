<?php

namespace VirtualLibraries;

require_once 'config.php';


/**
 * Tests for the columns module delivering SQL statements
 * The sql statements are logged, no unit tests !
 * @author Jürgen
 */
class sqlTest
{
    /**
     * Tests a given $table for correctness of generated sql
     * @param unknown $table
     */
    static public function test($table)
    {
        $ids = array(10, 11, 12, 13);
        $sql = ForeignColumns::getDefault()->getItem($table)->getSqlForeignIds($ids);
        
        $log = \Logger::getLogger(__CLASS__);
        $log->info("Printing Sql query for '$table' table:\n$sql");
    }
}

sqlTest::test("authors");

sqlTest::test("genre");
