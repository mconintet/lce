<?php

namespace Lce\web\db {
    interface IConnection
    {
        /**
         * @param bool $createNew
         * @param bool $debug
         * @return ISqlBuilder
         */
        public function getSqlBuilder($createNew = false, $debug = false);
    }
}