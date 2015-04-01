<?php

namespace Lce\web\mvc\model {
    interface IModelCollection extends \Iterator, \Countable
    {
        /**
         * @param $position
         * @return IModel|null
         */
        public function item($position);

        /**
         * @return array source query results array
         */
        public function getDataRows();

        /**
         * @param $position
         * @param bool $soft
         * @throws \Exception
         */
        public function delete($position, $soft = false);
    }
}