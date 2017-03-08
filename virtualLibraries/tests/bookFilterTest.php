<?php

namespace VirtualLibraries;

require_once dirname(__DIR__) . '/bookFilter.php';
require_once dirname(dirname(__DIR__)) . '/log4php/Logger.php';

\Logger::configure(dirname(dirname(__DIR__)) . '/config.xml');
date_default_timezone_set('Europe/Berlin');

class bookFilterTest
{
    static public function test($vlib)
    {
        $filter = new BookFilter();
        if ($filter->prepareFilter($vlib))
        {
            $sql = $filter->getSql(array( "id", "title", "authors", "genre", "pubdate" ));
            echo "\nPrinting a sql query for Calibre books table:\n$vlib\n";
            echo "$sql\n";
        }
    }
}

bookFilterTest::test('authors:"Balzac"');                               // a string comparison

bookFilterTest::test('pubdate:>2008-01-01');                            // a value comparison (pubdate earlier than 2008)

bookFilterTest::test('#genre:false');                                   // a bool comparison (no genre defined)

bookFilterTest::test('pubdate:true');                                   // a bool comparison (no pubdate defined)

enableDiagnosticPrint(false);
bookFilterTest::test('search:"Verstecke Calibre und Literatur"');       // a bool comparison (no pubdate defined)
enableDiagnosticPrint(false);

bookFilterTest::test('pubdate:=2008');
