<?php

namespace VirtualLibraries
{
    /**
     * Base class for all column classes
     * @author Jürgen
     *
     */
    abstract class Column
    {
        /**
         * The Key name that's also used in the ColumnInfo array
         * @var string
         */
        public $Name;
        
        /**
         * The complete column name of the book id
         * @return string
         */
        public function getBookId()
        {
            return "books.id";
        }
        
        /**
         * Returns whether this is an Attached Column
         * @return boolean
         */
        public function isAttachedColumn()
        {
            return get_class($this) === 'AttachedColumn';
        }

        /**
         * Returns whether this is a Linked Column
         * @return boolean
         */
        public function isLinkedColumn()
        {
            return get_class($this) === 'LinkedColumn';
        }

        /**
         * Returns whether this is a Native Column
         * @return boolean
         */
        public function isNativeColumn()
        {
            return get_class($this) === 'NativeColumn';
        }
        
        abstract function getSelect($no);
        
        abstract function getSqlWhere($id);
        
        abstract function getSqlExists($id);        
    }
}