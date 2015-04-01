<?php

namespace Lce\web\db\mysql {
    use Lce\web\db\IResultRow;

    class ResultRow implements IResultRow
    {
        private $_dataRow;

        public function __construct(array $row)
        {
            $this->_dataRow = $row;
        }

        public function has($key)
        {
            return isset($this->_dataRow[$key]);
        }

        public function get($key, $defaultValue = null)
        {
            if (isset($this->_dataRow[$key]))
                return $this->_dataRow[$key];

            return $defaultValue;
        }

        public function getDataRow()
        {
            return $this->_dataRow;
        }
    }
}