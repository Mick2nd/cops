<?php

namespace VirtualLibraries
{

    /**
     * Implements a filter for books in the Calibre (Sqlite) DB
     * @author Jürgen
     */
    class BookFilter implements IClientSite
    {
        private $log;
        
        /**
         * Ctor.
         */
        public function __construct()
        {
            $this->log = \Logger::getLogger(__CLASS__);
            $this->log->debug("Test log with log4php");            
        }
        
        /**
         * Prepare the filter for as many tests as we need permitted the search expression remains the same
         * @param string $vlib - the virtual library test string
         * @param DbProxy $dbProxy
         * @return boolean
         */
        public function prepareFilter($vlib, DbProxy $dbProxy = null)
        {
            if ($dbProxy === null)
            {
                $this->dbProxy = new DbProxy();                                     // we open a db connection here
                $res = $this->dbProxy->open();                                      // to perform callbacks
            }
            else
            {
                $this->log->debug("Invoked prepareFilter with dbProxy: " . ($dbProxy !== null));
                $this->dbProxy = $dbProxy;
                $res = true;
            }
            $this->vlib = $vlib;
            $searches = $this->getDbProxy()->getSetting('saved_searches');
            $this->parser = new VirtualLibrariesParser(
                $this->vlib,
                $this,
                json_decode($searches, true, 2));                                   // instantiates a new virtual library parser
            
            return $res;
        }

        /**
         * Closes the db connection
         */
        public function close()
        {
            $this->dbProxy->close();
            $this->parser = null;
        }

        /**
         * Creates a new instance of this class for recursive calls
         * {@inheritDoc}
         * @see \VirtualLibraries\IClientSite::create()
         */
        public function create($parseString)
        {
            $nested = new BookFilter();
            $nested->prepareFilter($parseString, $this->dbProxy);
            
            return $nested;
        }

        /**
         * Test for a given book id
         * @param integer $id
         * @return bool
         */
        public function isSelected($id)
        {
            try 
            {
                $res = $this->parser->test($id);                
                
                $this->log->debug("Testing in 'isSelected' for id $id");
                $this->log->debug("Result is: " . var_export($res, true));
                
                return $res;                
            }
            catch (\Exception $ex)
            {
                $this->log->error($ex);
                return false;
            }
        }

        /**
         * For a given array of ids returns only those passing the isSelected predicate 
         * @param array $ids
         * @return array
         */
        public function filterIds(array $ids)
        {
            $this->log->debug("Invoked filterIds.");
            $result = array();
            foreach($ids as $id)
            {
                if ($this->isSelected($id))
                    array_push($result, $id);
            }
            return $result;
        }
        
        /**
         * Returns the book ids as selected by the Virtual Library in $vlib
         * @return array
         */
        public function getSelectedIds()
        {
            $ids = $this->dbProxy->executeQuery("select id from books", 0);
            sort($ids);
            $ids = $this->filterIds($ids);
            
            return $ids;
        }

        /**
         * Builds an overall Sql query for the books in the db and returns it
         * @param array $requiredColumns
         * @return string
         */
        public function getSql(array $requiredColumns = array( "id", "title", "authors", "genre" ))
        {
            $ids = $this->getSelectedIds();
            $SQL = ColumnInfo::getDefault()->getSqlAll($requiredColumns, $ids);

            return $SQL;
        }

        /**
         * Callback for the Parser (IClientSite) to perform a single sql query and return the result
         * @param string $sql
         * @return boolean
         */
        public function test($sql)
        {
            $res = $this->dbProxy->test($sql);
            
            Diagnostic::diagnosticPrint("Test in test: $res\n");
            Diagnostic::diagnosticPrint("Sql: \n$sql\n");

            return $res;
        }
        
        /**
         * Callback for the Parser (IclientSite) to perform a single query and return the result
         * as array of ids.
         * {@inheritDoc}
         * @see \VirtualLibraries\IClientSite::getIds()
         */
        public function getIds($sql)
        {
            $res = $this->dbProxy->executeQuery($sql, 0);
            if ($res)
            {
                return $res;
            }
            
            return array();
        }

        /**
         * Just to work with the DB instantiated inside
         * @return \VirtualLibraries\DbProxy
         */
        public function getDbProxy()
        {
            return $this->dbProxy;
        }

        private $savedSearches = array(
            "Verstecke Calibre und Literatur" => "not authors:\"=calibre\" and  not #genre:\"=.Literatur\"");
        private $parser;
        private $dbProxy;
        private $vlib;
    }
}

// TODO: Kommentare anpassen und verbessern (überall)
// TODO: Integrationsarbeit (z.B. savedSearches)
