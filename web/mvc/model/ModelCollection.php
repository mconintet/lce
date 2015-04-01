<?php

namespace Lce\web\mvc\model {
    class ModelCollection implements IModelCollection
    {
        private $_dataRows = array();

        private $_position = 0;

        private $_modelClass = null;

        function __construct($dataRows, $modelClass)
        {
            if (is_array($dataRows))
                $this->_dataRows = $dataRows;

            $this->_modelClass = $modelClass;
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Return the current element
         * @link http://php.net/manual/en/iterator.current.php
         * @return IModel
         */
        public function current()
        {
            return new $this->_modelClass($this->_dataRows[$this->_position]);
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
         * @return IModel|null
         */
        public function item($position)
        {
            if ($this->_valid($position)) {
                /**
                 * @var $model IModel
                 */
                $model = new  $this->_modelClass();
                return $model->setSourceData($this->_dataRows[$position]);
            }

            return null;
        }

        /**
         * @return array source query results array
         */
        public function getDataRows()
        {
            return $this->_dataRows;
        }

        /**
         * @param $position
         * @param bool $soft
         * @throws \Exception
         */
        public function delete($position, $soft = false)
        {
            if (is_array($position)) {
                foreach ($position as $p) {
                    $this->delete($p, $soft);
                }
            } else if (is_numeric($position) && $this->_valid($position)) {
                if (!$soft)
                    $this->item($position)->delete();

                unset($this->_dataRows[$position]);
            } else if ($position === -1) {
                $this->delete(array_keys($this->_dataRows), $soft);
            } else {
                throw new \Exception('error position to delete');
            }
        }
    }
}