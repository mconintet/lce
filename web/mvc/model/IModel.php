<?php

namespace Lce\web\mvc\model {
    use Lce\web\db\IConnection;
    use Lce\web\db\ISqlBuilder;

    /**
     * if you want to use your customize model, you just need to create your model class extends Lce\web\mvc\model\Model,
     * and implements this interface. if you have multi model class in your project, I recommend you to create a base model class
     * extends Lce\web\mvc\model\Model and all other model classes extends it, in your base model class you can implement below
     * methods such as getDbConnection/getSqlBuilder etc, from this way, you'll not lost some performance.
     *
     * Interface IModel
     * @package Lce\web\mvc\model
     */
    interface IModel
    {
        /**
         * @return string
         */
        public static function getTableName();

        /**
         * @return IConnection
         */
        public static function getDbConnection();

        /**
         * @return ISqlBuilder
         */
        public static function getSqlBuilder();

        /**
         * @return string
         */
        public static function getTablePrimaryKey();

        /**
         * @return string
         */
        public static function getEventPrefix();

        /**
         * @return array
         */
        public static function getBeforeSaveValidations();

        /**
         * @return mixed
         */
        public static function getModelClass();

        /**
         * @param $whereGroups
         * @param string $fields
         * @return IModelCollection
         */
        public static function find($whereGroups, $fields = '*');

        /**
         * @param $value
         * @param string $fields
         * @return IModel
         */
        public static function findByPk($value, $fields = '*');

        /**
         * @return array
         */
        public static function getAutoFillFields();

        /**
         * @param array $data
         * @return IModel
         */
        public function setSourceData(array $data);

        public function getId();

        /**
         * @throws \Exception
         */
        public function save();

        /**
         * @throws \Exception
         */
        public function delete();

        public function getData($key, $defaultValue = null);

        public function hasData($key);

        public function hasDataChange();

        public function setData($key, $value);

        public function getDataChange();

        /**
         * @return array
         */
        public function getRelations();

        /**
         * @param $relationKey
         * @param bool $skipCache
         * @return IModel|IModelCollection
         */
        public function getRelational($relationKey, $skipCache = false);
    }
}