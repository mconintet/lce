<?php

namespace Lce\web\db {
    interface IResultSet extends \Iterator, \Countable
    {
        public function rowAt($position);

        public function getDataRows();
    }
}