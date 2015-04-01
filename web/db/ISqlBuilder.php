<?php

namespace Lce\web\db {
    interface ISqlBuilder
    {
        /**
         * @param null $partName
         * @return ISqlBuilder
         */
        public function resetPart($partName = null);

        /**
         * @param $tableName
         * @param null $alias
         * @return ISqlBuilder
         */
        public function from($tableName, $alias = null);

        /**
         * @param $columns
         * @return ISqlBuilder
         */
        public function select($columns);

        /**
         * @param $whereGroups
         * @return ISqlBuilder
         */
        public function where($whereGroups);

        /**
         * @param array $table
         * @param $on
         * @param string $leftRight
         * @return ISqlBuilder
         */
        public function join(array $table, $on, $leftRight = 'INNER');

        /**
         * @param $column
         * @param string $orderType
         * @return ISqlBuilder
         */
        public function order($column, $orderType = 'ASC');

        public function get($toResultSet = true, $start = 0, $end = 1);

        /**
         * @param $tableName
         * @param array $fieldValueMap
         * @return string lastInsertId
         */
        public function insert($tableName, array $fieldValueMap);

        /**
         * @param $tableName
         * @param array $fieldValueMap
         * @param array $whereGroups
         * @return int affectedRowsCount
         */
        public function update($tableName, array $fieldValueMap, array $whereGroups);

        /**
         * @param $tableName
         * @param array $whereGroups
         * @return int affectedRowsCount
         */
        public function delete($tableName, array $whereGroups);

        public function getParams();

        public function getPart($partName);
    }
}