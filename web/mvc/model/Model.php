<?php

namespace Lce\web\mvc\model {
    abstract class Model implements IModel
    {
        const HAS_ONE = 'has_one';
        const HAS_MANY = 'has_many';
        const BELONGS_TO = 'belongs_to';
        const MANY_MANY = 'many_many';

        public static $RELATION_TYPES = array(
            self::HAS_ONE,
            self::HAS_MANY,
            self::BELONGS_TO,
            self::MANY_MANY
        );

        protected $_data = array();

        protected $_hasDataChange = false;

        protected $_dataChange = array();

        protected $_id = null;

        protected $_relationsCache = array();

        public function setSourceData(array $data)
        {
            $this->_data = $data;
            $pk = static::getTablePrimaryKey();

            if (isset($this->_data[$pk]))
                $this->_id = $this->_data[$pk];

            return $this;
        }

        public function getId()
        {
            return $this->_id;
        }

        public static function getAutoFillFields()
        {
            return array();
        }

        /**
         * @param array $autoFillFields
         * @param $modelClass IModel
         * @return array|string
         */
        protected static function _fixSelectFields(array $autoFillFields, $modelClass)
        {
            $fields = count($autoFillFields) ? $autoFillFields : '*';
            if ($fields !== '*') {
                /**
                 * @var $modelClass IModel
                 */
                $pk = $modelClass::getTablePrimaryKey();
                $fields = in_array($pk, $fields) ? array_merge(array($pk), $fields) : $fields;
            }

            return $fields;
        }

        /**
         * @param $whereGroups
         * @param string $fields
         * @return ModelCollection
         */
        public static function find($whereGroups, $fields = '*')
        {
            $args = func_get_args();
            $autoFillFields = static::getAutoFillFields();

            if (is_string($whereGroups)) {
                /**
                 * find('email=', 'example@example.com') or
                 * find('email=', 'example@example.com', 'sex')
                 * find('email=', 'example@example.com', array('sex', 'age'))
                 */

                $whereGroups = array($args[0], $args[1]);
                $fields = isset($args[2]) ? $args[2] : null;
            }

            if (!$fields) {
                $fields = static::_fixSelectFields($autoFillFields, static::getModelClass());
            } else if (is_string($fields) && trim($fields, ' ') !== '*') {
                $fields = array($fields);
            }

            $pk = static::getTablePrimaryKey();
            $fields = is_array($fields) && !in_array($pk, $fields) ? array_merge(array($pk), $fields) : $fields;

            $sqlBuilder = static::getSqlBuilder()->resetPart();
            $sqlBuilder->from(static::getTableName());

            /**
             * find(array('email=', 'example@example.com')) or
             *
             * find(array('email=', 'example@example.com'), 'sex')
             *
             * find(array('email=', 'example@example.com'), array('sex', 'age')) or
             *
             * find(
             *      array(
             *          array('name=', 'name1'),
             *          array(SqlBuilder::WHERE_RELATION, 'or'),
             *          array('name=', 'name2')
             *      ),
             *      array('sex', 'age')
             * )
             */
            $sqlBuilder->select($fields)->where($whereGroups);

            return new ModelCollection($sqlBuilder->get(false), static::getModelClass());
        }

        /**
         * @param $value
         * @param string $fields
         * @return IModel
         */
        public static function findByPk($value, $fields = '*')
        {
            $collection = static::find(static::getTablePrimaryKey() . '=', $value, $fields);
            return $collection->item(0);
        }

        protected function _beforeSave()
        {
        }

        protected function _afterSave()
        {
        }

        protected function _beforeDelete()
        {
        }

        protected function _afterDelete()
        {
        }

        /**
         * @throws \Exception
         */
        public function save()
        {
            if ($this->_hasDataChange && $this->_id !== null) {
                $this->_beforeSave();

                $sqlBuilder = static::getSqlBuilder();
                $sqlBuilder->update(
                    static::getTableName(),
                    $this->_dataChange,
                    array(static::getTablePrimaryKey() . '=', $this->_id)
                );

                $this->_dataChange = array();
                $this->_afterSave();
            }
        }

        /**
         * @throws \Exception
         */
        public function delete()
        {
            $this->_beforeDelete();

            $sqlBuilder = static::getSqlBuilder();
            $sqlBuilder->delete(static::getTableName(), array(static::getTablePrimaryKey() . '=', $this->_id));
            $this->_id = null;

            $this->_afterDelete();
        }

        public function getDataChange()
        {
            return $this->_dataChange;
        }

        /**
         * get field value, try to load it from database if it does not exist, if it does still not exist after
         * a lazy loading just return the defaultValue
         *
         * @param $key
         * @param null $defaultValue
         * @return null|mixed
         */
        public function getData($key, $defaultValue = null)
        {
            if (isset($this->_data[$key])) {
                return $this->_data[$key];
            } else {
                $sqlBuilder = static::getSqlBuilder()->resetPart();
                $rows = $sqlBuilder->from(static::getTableName())
                    ->select($key)
                    ->where(static::getTablePrimaryKey() . '=', $this->_id)
                    ->get(false);

                if (count($rows) && isset($rows[0][$key])) {
                    $this->_data[$key] = $rows[0][$key];
                    return $rows[0][$key];
                } else {
                    return $defaultValue;
                }
            }
        }

        public function hasData($key)
        {
            return isset($this->_data[$key]);
        }

        public function hasDataChange()
        {
            return $this->_hasDataChange;
        }

        public function setData($key, $value)
        {
            if ($key !== static::getTablePrimaryKey()) {
                $this->_hasDataChange = true;
                $this->_data[$key] = $value;
                $this->_dataChange[$key] = $value;
            }

            return $this;
        }

        /**
         * array(
         *      'author' => array(self::BELONGS_TO, 'User', 'author_id'),
         *      'categories' => array(self::MANY_MANY, 'Category', array(tbl_post_category, post_id, category_id)),
         *      'posts' => array(self::HAS_MANY, 'Post', 'author_id'),
         *      'profile' => array(self::HAS_ONE, 'Profile', 'owner_id')
         * )
         *
         * @return array
         */
        public function getRelations()
        {
            return array();
        }

        /**
         * since tatty working on namespace, you need to use a full class name, it means you need use the class name contains
         * namespace. this method specific a way you can use a short class name.
         * @param $modelClass
         * @return string
         */
        protected function _fixModelClass($modelClass)
        {
            if (strpos($modelClass, '\\') === false) {
                $reflectionClass = new \ReflectionClass(static::getModelClass());
                $namespace = $reflectionClass->getNamespaceName();

                return $namespace . '\\' . $modelClass;
            }

            return $modelClass;
        }

        public function getRelational($relationKey, $skipCache = false)
        {
            $relations = static::getRelations();
            if (isset($relations[$relationKey])) {
                $relationConfig = $relations[$relationKey];

                /**
                 * see below it just check the length of your relation config array is 3 - 'count($relationConfig) === 3',
                 * but does NOT check the key - isset($relationConfig[0]) etc, so you should to follow up the usage of relation config.
                 */
                if (is_array($relationConfig) && count($relationConfig) === 3 &&
                    in_array(($relationType = $relationConfig[0]), self::$RELATION_TYPES)
                ) {
                    if (!$skipCache && isset($this->_relationsCache[$relationKey]))
                        return $this->_relationsCache[$relationKey];

                    /**
                     * @var $relationModelClass IModel
                     */
                    $relationModelClass = $this->_fixModelClass($relationConfig[1]);
                    $foreignKeyConfig = $relationConfig[2];
                    $ret = null;

                    switch ($relationType) {
                        case self::HAS_ONE:
                            $ret = $relationModelClass::find($foreignKeyConfig . '=', $this->_id)->item(0);
                            break;
                        case self::HAS_MANY:
                            $ret = $relationModelClass::find($foreignKeyConfig . '=', $this->_id);
                            break;
                        case self::BELONGS_TO:
                            $ret = $relationModelClass::findByPk($this->getData($foreignKeyConfig));
                            break;
                        case self::MANY_MANY:
                            $sqlBuilder = static::getSqlBuilder()->resetPart();
                            $relationTable = $foreignKeyConfig[0];

                            $pkA = static::getTablePrimaryKey();
                            $pkB = $relationModelClass::getTablePrimaryKey();

                            $keyA = $foreignKeyConfig[1];
                            $keyB = $foreignKeyConfig[2];

                            $tableA = static::getTableName();
                            $tableB = $relationModelClass::getTableName();

                            $fields = $relationModelClass::getAutoFillFields();
                            $fields = static::_fixSelectFields($fields, $relationModelClass::getModelClass());

                            if ($fields === '*') {
                                $fields = 'B.*';
                            } else {
                                foreach ($fields as $key => $val) {
                                    $fields[$key] = 'B.' . $val;
                                }
                            }

                            $dataRows = $sqlBuilder->from($relationTable)
                                ->select($fields)
                                ->join(array($tableA => 'A'), 'main.' . $keyA . '=A.' . $pkA)
                                ->join(array($tableB => 'B'), 'main.' . $keyB . '=B.' . $pkB)
                                ->where('A.' . $pkA . '=', $this->_id)
                                ->get(false);

                            $ret = new ModelCollection($dataRows, $relationModelClass);

                            break;
                        default:
                            break;
                    }

                    return $ret;
                } else {
                    throw new \Exception('invalid relation config');
                }
            } else {
                throw new \Exception('undefined relation key');
            }
        }
    }
}