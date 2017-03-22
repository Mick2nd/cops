<?php

namespace VirtualLibraries
{
    use PDO ;
    use ErrorException ;
    
    /**
     * Serves as proxy to a SQLite db
     * @author Jürgen
     *
     */
    class DbProxy
    {
    	static $log;
    	
    	/**
    	 * Ctor.
    	 */
    	public function __construct()
    	{
    		self::$log = \Logger::getLogger(__CLASS__);
    	}
    	 
    	 /**
         * This opens the db
         * @param mixed $db - path to db file, if not given an internal path is used
         * @return boolean
         */
        public function open($db = null)
        {
            try
            {
                if ($db === null)																	// as default
                {
                	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                	{
                		$db = 'K:\dev\metadata.db';													// use windows or
                	}
                	else
                	{
                		$db = '/share/MD0_DATA/Public/dev/metadata.db';								// NAS defaults for development
                	}
                }
                
                if (gettype($db) === 'string')
                {
                    $this->dbPath = $db;
                    $db = new PDO("sqlite:$db");
                }
                $this->db = $db;

                $this->db->sqliteCreateFunction('regexp', 'VirtualLibraries\DbProxy::regExp', 2);   // registers a user defined function for regular expression
                $this->db->sqliteCreateAggregate(                                                   // one for string aggregation
                    'group_concat', 
                    'VirtualLibraries\DbProxy::concatStep', 
                    'VirtualLibraries\DbProxy::concatFini', 2);
                
                ColumnInfo::getDefault()->setColumnInfo($this->getTypes("books"));                  // the Column Info singleton needs type info from books

                return true;
            }
            catch (Exception $ex)
            {
                return false;
            }
        }
        
        /**
         * Return an arbitrary preference values when $key is given
         * @param string $key
         * @return mixed
         */
        public function getSetting($key)
        {
            $query = "select val from preferences where key = '" . $key . "'";
            $result = $this->db->prepare ($query);
            $result->execute ();
            return $result->fetchColumn ();
        }
        
        /**
         * Executes a query and checks the result
         * @param string $query - the query to apply to the db
         * @return boolean - true: one or more rows are returned
         */
        public function test($query)
        {
            try
            {
                $result = $this->db->prepare ($query);
                if (!$result)
                {
                    Diagnostic::diagnosticPrint("Db returnes Nothing for query $query\n");
                    return false;
                }
                
                $result->execute ();
                return $result->fetchColumn () !== false;
            }
            catch (Exception $ex)
            {
                return false;
            }
        }

        /**
         * Executes a query on the Calibre db and returns the results as enumeration of a single selected column
         * @param string $query
         * @param integer $col
         * @return bool|array - of column items
         */
        public function executeQuery($query, $col)
        {
            $result = $this->db->prepare ($query);

            if ($result === false)
            {
                $this->printErrorInfo($query);            
                return false;
            }
            
            $result->execute ();
            return $result->fetchAll(PDO::FETCH_COLUMN);
        }

        /**
         * Executes a query on the Calibre db and returns the results as array of associative arrays 
         * with the fields stored a Key - Value - Pairs
         * @param string $query
         * @return array - of associative arrays
         */
        public function executeQueryAll($query)
        {
            $result = $this->db->prepare ($query);

            if ($result === false)
            {
                $this->printErrorInfo($query);
                return false;
            }
            
            $result->execute ();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Requests the current data row as associative array of key(name) - value(field) - pairs
         * @param \PDOStatement $reader
         * @return unknown
         */
        public function getRow($reader)
        {
            return $reader->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * From a given table name requests field Names and Types 
         * @param string $table
         * @return array[]
         */
        public function getTypes($table)
        {
            $query = "select * from $table where id=1";
            $result = $this->db->prepare ($query);
            $result->execute ();
            
            $columns = array();
            for ($col = 0; $col < $result->columnCount(); $col++)
            {
                $meta = $result->getColumnMeta($col);                
                $columns[$meta["name"]] = $meta["sqlite:decl_type"];
            }
            
            if (Diagnostic::enabled())
            {
                echo "getTypes determined types of books columns";
                var_dump($columns);
            }
            
            return $columns;
        }
        
        /**
         * Closes the db
         */
        public function close()
        {
            try
            {
                $this->db = null;
            }
            catch (Exception $e)
            {
            }
        }

        /**
         * The user function "regexp"
         * @param string $regex
         * @param string $str
         * @return bool
         */
        static function regExp($regex, $str)
        {
            if (gettype($regex) !== 'string' || gettype($str) !== 'string')
                throw new ErrorException("Argument type not string");

            if (self::$log)
            	self::$log->debug("regexp protocol: regex: $regex, str: $str");
            
            return preg_match('/' . $regex . '/', $str) === 1;
        }
        
        /**
         * The "step" function for the "group_concat" user function
         * @param unknown $context
         * @param unknown $rowNumber
         * @param unknown $value1
         * @param string $value2
         * @throws ErrorException
         * @return unknown|string
         */
        static function concatStep($context, $rowNumber, $value1, $value2 = "")
        {
            if (gettype($context) !== 'string' && $context !== null || gettype($value1) !== 'string' || gettype($value2) !== 'string')
                throw new ErrorException("Argument type not string");
            
            if ($context === null)
                return $value1;
                        
            return $context . $value2 . $value1;
        }
        
        /**
         * The "fini" function for the "group_concat" user function
         * @param unknown $context
         * @param unknown $rowNumber
         * @throws ErrorException
         * @return string|unknown
         */
        static function concatFini($context, $rowNumber)
        {
            if (gettype($context) !== 'string' && $context !== null)
                throw new ErrorException("Argument type not string");
            
            if ($context === null)
                return "";
                
            return $context;
        }
        
        /**
         * Prints Db Error Info
         * @param unknown $query
         */
        private function printErrorInfo($query)
        {
            if (Diagnostic::enabled())
            {
                echo "Query was: \n$query\n";
                echo "PDO::errorInfo():\n";
                print_r($this->db->errorInfo());                
            }            
        }
        
        private $db;
        private $dbPath;
    }
}
