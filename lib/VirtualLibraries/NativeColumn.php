<?php

namespace VirtualLibraries
{
    /**
     * Native column represents all columns in the books table
     * @author Jürgen
     *
     */
    class NativeColumn extends Column
    {
        private $comparison;
        
        /**
         * Used to initialise the Native Column with its type.
         * Used to perform an empty comparison.
         * @param string $type
         */
        public function setType($type)
        {
            switch ($type)
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
            
            $this->comparison = $comparison;
        }
        
        /**
         * To select the (native) column as output
         * {@inheritDoc}
         * @see \VirtualLibraries\Column::getSelect()
         */
        public function getSelect($no)
        {
            $name = $this->Name;
            
            return "books.$name as $name";
        }
        
        /**
         * To perform a text/value comparison
         * {@inheritDoc}
         * @see \VirtualLibraries\Column::getSqlWhere()
         */
        public function getSqlWhere($id)
        {
            return "select books.id from books where books.id=$id and ";            
        }
        
        /**
         * To perform a Boolean comparison (presence of an entry)
         * {@inheritDoc}
         * @see \VirtualLibraries\Column::getSqlExists()
         */
        public function getSqlExists($id)
        {
            $name = $this->Name;
            $comparison = $this->comparison;
            
            return "select id, $name from books where id=$id and not $name$comparison";
            
        }        
    }
}