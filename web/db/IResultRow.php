<?php

namespace Lce\web\db {
    interface IResultRow
    {
        public function has($key);

        public function get($key, $defaultValue = null);

        public function getDataRow();
    }
}