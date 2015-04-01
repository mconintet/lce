<?php

namespace Lce\web\db\mysql {
    use Lce\web\db\IResultSet;

    class ResultSet implements IResultSet
    {
        private $_dataRows = array();

        private $_position = 0;

        public function __construct($dataRows)
        {
            if (is_array($dataRows))
                $this->_dataRows = $dataRows;
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Return the current element
         * @link http://php.net/manual/en/iterator.current.php
         * @return mixed Can return any type.
         */
        public function current()
        {
            return new ResultRow($this->_dataRows[$this->_position]);
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Move forward to next element
         * @link http://php.net/manual/en/iterator.next.php
         * @return void Any returned value is ignored.
         */
        public function next()
        {
            ++$this->_position;
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Return the key of the current element
         * @link http://php.net/manual/en/iterator.key.php
         * @return mixed scalar on success, or null on failure.
         */
        public function key()
        {
            return $this->_position;
        }

        private function _valid($position)
        {
            return isset($this->_dataRows[$position]);
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Checks if current position is valid
         * @link http://php.net/manual/en/iterator.valid.php
         * @return boolean The return value will be casted to boolean and then evaluated.
         * Returns true on success or false on failure.
         */
        public function valid()
        {
            return $this->_valid($this->_position);
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Rewind the Iterator to the first element
         * @link http://php.net/manual/en/iterator.rewind.php
         * @return void Any returned value is ignored.
         */
        public function rewind()
        {
            $this->_position = 0;
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Count elements of an object
         * @link http://php.net/manual/en/countable.count.php
         * @return int The custom count as an integer.
         * </p>
         * <p>
         * The return value is cast to an integer.
         */
        public function count()
        {
            return count($this->_dataRows);
        }

        /**
         * @param $position
         * @return null|ResultRow
         */
        public function rowAt($position)
        {
            if ($this->_valid($position)) {
                return new ResultRow($this->_dataRows[$position]);
            }

            return null;
        }

        public function getDataRows()
        {
            return $this->_dataRows;
        }
    }
}