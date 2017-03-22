<?php

namespace VirtualLibraries
{
    /**
     * Attached Column represents a column for a n : 1 relationship between a foreign column and
     * the native books table
     * @author Jürgen
     *
     */
    class AttachedColumn extends Column
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
         * The complete column name of the book link in the Data Table
         * @return string
         */
        public function getLinkedBook()
        {
            return $this->DataTable . ".book";
        }

        /**
         * Returns the Join clause for an Attached Column
         * @return string
         */
        protected function getJoin()
        {
            return
                "\n  join " .
                $this->DataTable .
                "\n   on " .
                $this->getLinkedBook() . "=" . $this->getBookId();
        }
        
        public function getSelect($no)
        {
            
        }
        
        public function getSqlWhere($id)
        {
            $dataField = $this->getDataField();
            $join = $this->getJoin();
            $name = $this->Name;
            
            return
                "select books.id, $dataField as $name from books $join \nwhere books.id=$id and ";
        }
        
        public function getSqlExists($id)
        {
            $linkedBook = $this->getLinkedBook();
            return
                "select id from $this->DataTable where $linkedBook=$id";            
        }
    }
}
