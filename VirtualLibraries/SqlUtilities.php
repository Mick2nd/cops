<?php


namespace VirtualLibraries
{
	/**
	 * Abstract class as "interface" for both ForeignColumnComplex and ForeignColumnSimple
	 * @author Jürgen
	 *
	 */
	abstract class ForeignColumn
	{
		/**
		 * The Key name that's also used in the Foreign Columns dictionary
		 * @var string
		 */
		public $Name;

		/**
		 * The target Data Table which connects itself to the Books table
		 * @var string
		 */
		var $DataTable;
		
		/**
		 * The target Data Table's field containing the real data
		 * @var string
		 */
		var $DataField;
		
		/**
		 * The complete column name of the book id
		 * @return string
		 */
		public function getBookId()
		{
			return "books.id";
		}
		
		/**
		 * The complete column name of the target data tables id
		 * @return string
		 */
		public function getDataTableId()
		{
			return $this->DataTable . ".id";
		}
		
		abstract function getLinkedBook();
		
		abstract function getSql($id);
		
		abstract function getSqlId($id);
	}
	
	/**
	 * This is the simple type of augmenting data columns: with a n:1 relationsship to the books
	 * table.
	 * @author Jürgen
	 *
	 */
	class ForeignColumnSimple extends ForeignColumn
	{
		public function getLinkedBook()
		{
			
		}

		public function getSql($id)
		{
				
		}

		public function getSqlId($id)
		{
				
		}
	}
	
    /**
     * Contains data and a few helper properties to establish the connection of a book to augmenting data
     * @author Jürgen
     *
     */
    class ForeignColumnComplex extends ForeignColumn
    {
        /**
         * The table which constitutes a link ("link table") between the books table
         * and the "DataTable"
         * @var string
         */
        var $LinkTable;

        /**
         * The field in the link table constituting the link
         * @var string
         */
        var $LinkField;

        /**
         * The complete column name of the book link in the Link Table
         * @return string
         */
        public function getLinkedBook()
        {
            return $this->LinkTable . ".book";
        }

        /**
         * The complete column name of the target data link in the Link Table
         * @return string
         */
        public function getLinkedDataId()
        {
            return $this->LinkTable . "." . $this->LinkField;
        }

        /**
         * The complete column name of the target data in the Data Table
         * @return string
         */
        public function getLinkedDataField()
        {
            return $this->DataTable . "." . $this->DataField;
        }
        
        /**
         * Returns the Join clause for a Foreign Column
         * @return string
         */
        public function getJoin()
        {
            return
                "\n  join " .
                $this->LinkTable .
                "\n   on " .
                $this->getLinkedBook() . "=" . $this->getBookId() .
                "\n  join " .
                $this->DataTable .
                "\n   on " .
                $this->getLinkedDataId() . "=" . $this->getDataTableId() ;
        }
        
        /**
         * Returns the nested Select statement for the given column
         * @param unknown $no
         * @return string
         */
        public function getSelect($no)
        {
            $localTable = "t$no";
            $linkedDataField = $this->getLinkedDataField();
            $join = $this->getJoin();
            $name = $this->Name;
            
            return
                "(select group_concat(${linkedDataField}, ', ') \n  " . 
                "from books $localTable$join\n  group by $localTable.id ) as $name";
        }
        
        /**
         * For a given Foreign Column returns the Sql query to check for existance of a single entry
         * (Meant for value/text comparison)
         * @param unknown $id
         * @return string
         */
        public function getSql($id)
        {
            $linkedDataField = $this->getLinkedDataField();
            $join = $this->getJoin();
            $name = $this->Name;
            
            return
                "select books.id, $linkedDataField as $name from books $join \nwhere books.id=$id and ";
        }
        
        /**
         * For a given Foreign Column returns the Sql query to check for existance of a single entry
         * (Meant for bool comparison)
         * @param unknown $id
         * @return string
         */
        public function getSqlId($id)
        {
            $linkedBook = $this->getLinkedBook();
            return
                "select id from $this->LinkTable where $linkedBook=$id";
        }
        
         /**
          * Returns a Sql query to request the ids of a foreign table
          * @param unknown $ids
          * @return string
          */
        public function getSqlForeignIds($ids)
        {
            $dataTableId = $this->getDataTableId(); 
            $linkedDataId = $this->getLinkedDataId();
            $linkedBook = $this->getLinkedBook();
            $bookId = $this->getBookId();
            $idsString = implode(', ', $ids);
            
            return 
                "select distinct $dataTableId from $this->DataTable" . 
                "\n join $this->LinkTable on $linkedDataId = $dataTableId" .
                "\n join books on $linkedBook = $bookId" .
                "\n where $bookId in ($idsString)";
        }
    }

    /**
     * This class contains the complete set of augmenting data for the books
     * (info about foreign columns)
     * @author Jürgen
     *
     */
    class ForeignColumns
    {
        static private $default;
        private $foreignColumns;

        /**
         * Ctor.
         */
        private function __construct()
        {
            $this->Add("authors", "name", "authors", "author", "books_authors_link");
            $this->Add("languages", "lang_code", "languages", "lang_code", "books_languages_link");
            $this->Add("series", "name", "series", "series", "books_series_link");
            $this->Add("publisher", "name", "publishers", "publisher", "books_publishers_link");
            $this->Add("rating", "rating", "ratings", "rating", "books_ratings_link");
            $this->Add("tags", "name", "tags", "tag", "books_tags_link");
            $this->Add("genre", "value", "custom_column_1", "value", "books_custom_column_1_link");
        }

        /**
         * Adds (initialises) a single foreign column entry.
         * @param unknown $name
         * @param unknown $dataField
         * @param unknown $dataTable
         * @param unknown $linkField
         * @param unknown $linkTable
         */
        private function Add($name, $dataField, $dataTable, $linkField, $linkTable)
        {
            if ($this->foreignColumns == null)
                $this->foreignColumns = array();

            $foreignColumn = new ForeignColumnComplex();
            $foreignColumn->Name = $name;
            $foreignColumn->DataTable = $dataTable;
            $foreignColumn->DataField = $dataField;
            $foreignColumn->LinkTable = $linkTable;
            $foreignColumn->LinkField = $linkField;
            
            $this->foreignColumns[$name] = $foreignColumn;
        }

        /**
         * The one and only instance
         * @return ForeignColumns
         */
        static public function getDefault()
        {
            if (ForeignColumns::$default === null)
            {
                ForeignColumns::$default = new ForeignColumns();
            }
            return ForeignColumns::$default;
        }

        /**
         * Indexer to access the individual Foreign Columns by name
         * @param unknown $key
         * @return ForeignColumnComplex|NULL
         */
        public function getItem($key)
        {
            if (array_key_exists($key, $this->foreignColumns))
                return $this->foreignColumns[$key];

            return null;
        }

        /**
         * Returns all of the Join clauses for the Foreign Columns, given which of them are needed
         * @param unknown $requiredColumns
         * @return string
         * TODO: required
         */
        public function getJoins($requiredColumns)
        {
            $agg = "";
            foreach ($requiredColumns as $col)
            {
                if ($this->getItem($col))
                {
                    $agg .= $this->getItem($col)->getJoin();
                }
            }
            
            return $agg;
        }
        
        /**
         * Returns all of the Select arguments given which of them are required
         * A distiction is made whether the required column is native or foreign
         * @param unknown $requiredColumns
         * @return string
         * TODO: required
         */
        public function getSelections($requiredColumns)
        {
            $agg = "";
            foreach ($requiredColumns as $col)
            {
                if (strlen($agg) > 0)
                    $agg .= ",";
                $agg .= "\n ";
                    
                if ($this->getItem($col))
                {
                    $agg .= $this->getItem($col)->getLinkedDataField() . "as $col";
                }
                else 
                {
                    $agg .= "books.$col as $col";
                }
            }
            
            return $agg;
        }
        
        /**
         * Checks if a name is a foreign column and returns sql query for text/value comparison appropriately
         * @param unknown $name
         * @param unknown $id
         * @return string
         */
        public function getSql($name, $id)
        {
            if ($this->getItem($name) != null)
            {
                return $this->getItem($name)->getSql($id);
            }
            else
            {
                return "select books.id from books where books.id=$id and ";
            }
        }

        /**
         * Checks if a name is a foreign column and returns sql query for boolean comparison appropriately
         * @param unknown $name
         * @param unknown $id
         * @return string
         */
        public function getSqlExists($name, $id)
        {
            if ($this->getItem($name) != null)                                  // for a Foreign Column
            {
                return $this->getItem($name)->GetSqlId($id);                    // we check for existence of an entry in the foreign (link) table
            }
            else                                                                // for a Native Column
            {
                return
                    NativeColumns::getDefault()->getItem($name, $id);           // we delegate to the Native Columns instance
            }
        }
        
        /**
         * Returns the complete Sql statement for required columns and a set of book ids
         * @param unknown $requiredColumns
         * @param unknown $ids
         * @return string
         */
        public function GetSqlAll($requiredColumns, $ids)
        {
            $no = 0;
            $columnInfo = "";
            
            foreach ($requiredColumns as $col)
            {
                if (strlen($columnInfo) > 0)
                    $columnInfo .= ",";
                $columnInfo .= "\n ";
                    
                if ($this->getItem($col) != null)                               // is it a foreign column
                    $columnInfo .= $this->getItem($col)->getSelect(++$no);      // return nested Select clause
                else
                    $columnInfo .="books.$col as $col";                         // otherwise take the column immediately from books
            }
        
            $no = 0;
            $idInfo = "\n ";
            
            foreach ($ids as $id)
            {
                $delim = strlen($idInfo) > 2 ? ", " : "";
                if (++$no % 10 == 1 && strlen($idInfo) > 2)
                    $delim .= "\n ";
                
                $idInfo .= $delim . "$id";                                      // aggregate the ids
            }
        
            return
                "select $columnInfo \nfrom books where books.id in ($idInfo)";  // finally return complete select statement
        }
    }

    /**
     * This class maintains a dictionary of the "native" books table columns
     * and returns a sql test query for a given name/id pair on request
     * @author Jürgen
     *
     */
    class NativeColumns
    {
        /**
         * Ctor.
         */
        private function __construct()
        {
            $this->nullComparisons = array();
        }

        /**
         * "Singleton"
         * @return NativeColumns
         */
        static public function getDefault()
        {
            if (NativeColumns::$default == null)
                NativeColumns::$default = new NativeColumns();

            return NativeColumns::$default;
        }

        /**
         * Serves for property injection of the columns' type info
         * Before the first usage this property must be injected
         * @param unknown $values
         */
        public function setColumnInfo($values)
        {
            foreach ($values as $key => $val)
            {
                switch ($val)
                {
                    case "INTEGER":
                    case "REAL":
                        $comparison = "=0";
                        break;
                    case "TEXT":
                        $comparison = "=''";
                        break;
                    case "BOOL":
                        $comparison = "=false";
                        break;
                    case "TIMESTAMP":
                        $comparison = "<Date('0102-01-01')";
                        break;
                    default:
                        $comparison = "";
                        break;
                }

                $this->nullComparisons[$key] = $comparison;
            }
        }

        /**
         * Indexer for access to the Sql queries
         * @param unknown $name
         * @param unknown $id
         * @return string
         */
        public function getItem($name, $id)
        {
            $comparison = $this->nullComparisons[$name];
            return
                "select id, $name from books where id=$id and not $name$comparison";
        }

        private $nullComparisons;
        static private $default;
    }
}
