<?php

namespace VirtualLibraries
{
    /**
     * Contains data and a few helper properties to establish the connection of a book to augmenting data
     * @author Jürgen
     *
     */
    class LinkedColumn extends Column
    {
        /**
         * The target Data Table which is connected to the Books table
         * @var string
         */
        var $DataTable;
        
        /**
         * The target Data Table's field containing the real data
         * @var string
         */
        var $DataField;
        
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
         * The complete column name of the target data tables id
         * @return string
         */
        public function getDataTableId()
        {
            return $this->DataTable . ".id";
        }

        /**
         * The complete column name of the target data in the Data Table
         * @return string
         */
        public function getDataField()
        {
            return $this->DataTable . "." . $this->DataField;
        }
        
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
         * Returns the Join clause for a Linked Column
         * @return string
         */
        protected function getJoin()
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
         * Returns the nested Select statement for the given Linked Column
         * @param integer $no
         * @return string
         */
        public function getSelect($no)
        {
            $localTable = "t$no";
            $dataField = $this->getDataField();
            $join = $this->getJoin();
            $name = $this->Name;
    
            return
                "(select group_concat($dataField, ', ') \n  " .
                "from books $localTable$join\n  group by $localTable.id ) as $name";
    }
    
        /**
         * For a given Foreign Column returns the Sql query to check for existance of a single entry
         * (Meant for value/text comparison)
         * @param unknown $id
         * @return string
         */
        public function getSqlWhere($id)
        {
            $dataField = $this->getDataField();
            $join = $this->getJoin();
            $name = $this->Name;
        
            return
                "select books.id, $dataField as $name from books $join \nwhere books.id=$id and ";
        }
        
        /**
         * For a given Foreign Column returns the Sql query to check for existance of a single entry
         * (Meant for bool comparison)
         * @param unknown $id
         * @return string
         */
        public function getSqlExists($id)
        {
            $linkedBook = $this->getLinkedBook();
            return
                "select id from $this->LinkTable where $linkedBook=$id";
        }
        
        /**
         * Returns a Sql query to request the ids of a linked table
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
    
}