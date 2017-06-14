<?php

namespace VirtualLibraries
{
    /**
     * This class contains the complete set of column info for
     * - foreign columns (augmenting data for the books)
     * -- linked columns (m:n relationship)
     * -- attached columns (n:1 relationship)
     * - native columns
     * @author Jürgen
     *
     */
    class ColumnInfo
    {
    	use Singleton;

    	private $columns;
    
        /**
         * Ctor.
         */
        private function __construct()
        {
            $this->addLinkedColumn("authors", "name", "authors", "author", "books_authors_link");
            $this->addLinkedColumn("languages", "lang_code", "languages", "lang_code", "books_languages_link");
            $this->addLinkedColumn("series", "name", "series", "series", "books_series_link");
            $this->addLinkedColumn("publisher", "name", "publishers", "publisher", "books_publishers_link");
            $this->addLinkedColumn("rating", "rating", "ratings", "rating", "books_ratings_link");
            $this->addLinkedColumn("tags", "name", "tags", "tag", "books_tags_link");
            
            $this->addAttachedColumn("comments", "text", "comments");
            $this->addAttachedColumn("format", "format", "data");
            $this->addAttachedColumn("size", "uncompressed_size", "data");
            $this->addAttachedColumn("name", "name", "data");
            $this->addAttachedColumn("identifiersType", "type", "identifiers");                        	// TBD
            $this->addAttachedColumn("identifiersValue", "val", "identifiers");                      	// TBD
        }
    
        /**
         * Adds (initialises) a single linked column entry.
         * @param string $name
         * @param string $dataField
         * @param string $dataTable
         * @param string $linkField
         * @param string $linkTable
         */
        private function addLinkedColumn($name, $dataField, $dataTable, $linkField, $linkTable)
        {
            if ($this->columns == null)
            {
                $this->columns = array();
            }
    
            $column = new LinkedColumn();
            $column->Name = $name;
            $column->DataTable = $dataTable;
            $column->DataField = $dataField;
            $column->LinkTable = $linkTable;
            $column->LinkField = $linkField;

            $this->columns[$name] = $column;
        }
        
        /**
         * Adds (initialises) a single attached column entry.
         * @param string $name
         * @param string $dataField
         * @param string $dataTable
         */
        private function addAttachedColumn($name, $dataField, $dataTable)
        {
            $column = new AttachedColumn();
            $column->Name = $name;
            $column->DataTable = $dataTable;
            $column->DataField = $dataField;
            
            $this->columns[$name] = $column;
        }
        
        /**
         * Adds (initialises) a single native column entry.
         * Invoked by setColumnInfo.
         * @param string $name
         * @param string $type
         */
        private function addNativeColumn($name, $type)
        {
            $column = new NativeColumn();
            $column->Name = $name;
            $column->setType($type);

            $this->columns[$name] = $column;
        }
    
        /**
         * The one and only instance
         * @return ColumnInfo
         */
        static public function getDefault()
        {
        	return self::getInstance();
        }

        /**
         * Serves for property injection of the columns' type info
         * Before the first usage this property must be injected
         * @param array $values
         */
        public function setColumnInfo($values)
        {
            foreach ($values as $key => $val)
            {
                $this->addNativeColumn($key, $val);
            }
        }
        
        /**
         * Sets the Custom Column info
         * @param array $values
         */
        public function setCustomColumnInfo($values)
        {
            foreach ($values as $val)
            {
                $key = $val['label'];
                $id = $val['id'];
                $this->addLinkedColumn(
                        $key, 
                        "value", 
                        "custom_column_$id", 
                        "value", 
                        "books_custom_column_{$id}_link");
            }
        }
            
        /**
         * Indexer to access the individual Foreign Columns by name
         * @param string $key
         * @return Column|NULL
         */
        public function getItem($key)
        {
            if (array_key_exists($key, $this->columns))
                return $this->columns[$key];
    
            return null;
        }
    
        /**
         * Checks if a name is a present column and returns sql query for text/value comparison appropriately
         * @param string $name
         * @param integer $id
         * @return string
         */
        public function getSqlWhere($name, $id)
        {
            if ($this->getItem($name) != null)
            {
                return $this->getItem($name)->getSqlWhere($id);
            }
            
            return null;
        }
    
        /**
         * Checks if a name is a present column and returns sql query for boolean comparison appropriately
         * @param string $name
         * @param integer $id
         * @return string
         */
        public function getSqlExists($name, $id)
        {
            if ($this->getItem($name) != null)                                  // for a present column
            {
                return $this->getItem($name)->GetSqlExists($id);                // we check for existence of an entry in the foreign (link) table
            }
            
            return null;
        }
    
        /**
         * Returns the complete Sql statement for required columns and a set of book ids
         * @param array $requiredColumns
         * @param array $ids
         * @return string
         */
        public function GetSqlAll($requiredColumns, $ids)
        {
            $no = 0;
            $columnInfo = "";
    
            foreach ($requiredColumns as $col)
            {
                if (strlen($columnInfo) > 0)
                {
                    $columnInfo .= ",";                    
                }
                $columnInfo .= "\n ";
    
                if ($this->getItem($col) != null)                               // is it a known/present column
                {
                    $columnInfo .= $this->getItem($col)->getSelect(++$no);      // return nested select clause
                }
            }
    
            $no = 0;
            $idInfo = "\n ";
    
            foreach ($ids as $id)
            {
                $delim = strlen($idInfo) > 2 ? ", " : "";
                if (++$no % 10 == 1 && strlen($idInfo) > 2)
                {
                    $delim .= "\n ";
                }
    
                $idInfo .= $delim . "$id";                                      // aggregate the ids
            }
    
            return
                "select $columnInfo \nfrom books where books.id in ($idInfo)";  // finally return complete select statement
        }
    }
    
}