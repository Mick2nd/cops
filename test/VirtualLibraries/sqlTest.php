<?php

namespace VirtualLibraries;

require_once dirname(__DIR__) . '/sqlUtilities.php';
require_once dirname(dirname(__DIR__)) . '/log4php/Logger.php';

\Logger::configure(dirname(dirname(__DIR__)) . '/config.xml');
date_default_timezone_set('Europe/Berlin');

/**
 * Tests for the columns module delivering SQL statements
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
        
        print("\n$sql\n");
    }
}

sqlTest::test("authors");

sqlTest::test("genre");
