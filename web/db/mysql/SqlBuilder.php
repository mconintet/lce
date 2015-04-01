<?php
namespace Lce\web\db\mysql {
    use Lce\web\db\ISqlBuilder;

    class SqlBuilder implements ISqlBuilder
    {
        public static $MAIN_TABLE_ALIAS = 'main';

        const WHERE_AND = 'AND';
        const WHERE_OR = 'OR';

        const WHERE_RELATION = '$AND_OR';

        const ORDER_ASC = 'ASC';
        const ORDER_DESC = 'DESC';

        const JOIN_LEFT = 'LEFT';
        const JOIN_RIGHT = 'RIGHT';
        const JOIN_INNER = 'INNER';

        const PART_FROM = 'part_from';
        const PART_SELECT = 'part_select';
        const PART_WHERE = 'part_where';
        const PART_ORDER = 'part_order';
        const PART_JOIN = 'part_join';

        private static $WHERE_AND_OR = array(
            self::WHERE_AND,
            self::WHERE_OR
        );

        private static $ORDER_TYPE = array(
            self::ORDER_ASC,
            self::ORDER_DESC
        );

        private static $JOIN_TYPE = array(
            self::JOIN_LEFT,
            self::JOIN_RIGHT,
            self::JOIN_INNER
        );

        private $_from = null;
        private $_select = null;
        private $_where = null;
        private $_order = null;
        private $_join = null;

        private $_params = array();
        private $_debug = false;

        public function __construct(\PDO $connection, $debug = false)
        {
            $this->_connection = $connection;
            $this->_debug = (boolean)$debug;
        }

        public function resetPart($partName = null)
        {
            switch ($partName) {
                case self::PART_FROM:
                    $this->_from = null;
                    break;
                case self::PART_JOIN:
                    $this->_join = null;
                    break;
                case self::PART_ORDER:
                    $this->_order = null;
                    break;
                case self::PART_WHERE :
                    $this->_where = null;
                    break;
                case self::PART_SELECT:
                    $this->_select = null;
                    break;
                default:
                    $this->_select = null;
                    $this->_where = null;
                    $this->_order = null;
                    $this->_join = null;

                    break;
            }

            $this->_params = array();

            return $this;
        }

        /**
         * @param string $tableName
         * @param null|string $alias
         * @return SqlBuilder
         * @throws \Exception
         */
        public function from($tableName, $alias = null)
        {
            if (is_string($tableName) && (is_string($alias) || $alias === null)) {
                if ($alias === null)
                    $alias = self::$MAIN_TABLE_ALIAS;
                else
                    self::$MAIN_TABLE_ALIAS = $alias;

                $this->_from = ' FROM ' . $tableName . ' AS ' . $alias;

                /**
                 * reset params since run this method indicates to start a new sql build process
                 */
                $this->_params = array();
            } else {
                throw new \Exception('table name or it\'s alias must be a string type');
            }

            return $this;
        }

        /**
         * select('name')
         * select(array('name'))
         * select(array('name', 'sex));
         * select(array('name' => 'nameAlias', 'sex' => 'sexAlias'));
         *
         * @param string|array $columns
         * @return SqlBuilder
         * @throws \Exception
         */
        public function select($columns)
        {
            if (is_string($columns) || is_array($columns)) {
                if (is_string($columns)) {
                    if (strpos($columns, ',') !== false) {
                        $columns = trim($columns, ', ');
                        $columns = explode(',', $columns);
                    } else {
                        $columns = array($columns);
                    }
                }

                $select = array();
                foreach ($columns as $key => $val) {
                    $select[] = is_string($key) ? $key . ' AS ' . $val : $val;
                }

                $this->_select = ' ' . implode(',', $select);
            } else {
                throw new \Exception('columns must be a string type or array');
            }

            return $this;
        }

        private function _buildWhereGroups($whereGroups)
        {
            $ret = '(';

            foreach ($whereGroups as $whereGroup) {
                $count = count($whereGroup);

                if ($count === 2) {
                    if ($whereGroup[0] === self::WHERE_RELATION) {
                        $relation = strtoupper($whereGroup[1]);
                        if (in_array($relation, self::$WHERE_AND_OR))
                            $ret .= $relation . ' ';
                        else
                            throw new \Exception('invalid where relation');
                    } else {
                        $ret .= $whereGroup[0] . '? ';
                        $this->_params[] = $whereGroup[1];
                    }
                } else if ($count === 1) {
                    $ret .= $whereGroup[0] . ' ';
                } else {
                    $ret .= $this->_buildWhereGroups($whereGroup);
                }
            }

            $ret = rtrim($ret, ' ') . ')';
            return $ret;
        }

        /**
         * where('id=', 1);
         * where(array('id=', 1));
         * where(array(array('id=', 1)));
         *
         * where(
         *      array(
         *          array('name=', 'name1'),
         *          array(SqlBuilder::WHERE_RELATION, 'or'),
         *          array('name=', 'name2')
         *      )
         * );
         *
         * @param $whereGroups
         * @return $this|ISqlBuilder
         */
        public function where($whereGroups)
        {
            $args = func_get_args();

            if (count($args) > 1) {
                $whereGroups = array(array($args[0], $args[1]));
            }

            $whereGroupsCount = count($whereGroups);
            if ($whereGroupsCount === 1) {
                $whereGroup = $whereGroups[0];
                $this->_where = ' WHERE ' . $whereGroup[0] . '?';
                $this->_params[] = $whereGroup[1];
            } else if ($whereGroupsCount === 2) {
                $this->_where = ' WHERE ' . $whereGroups[0] . '?';
                $this->_params[] = $whereGroups[1];
            } else if ($whereGroupsCount > 2) {
                $this->_where = ' WHERE ' . $this->_buildWhereGroups($whereGroups);
            }

            return $this;
        }

        /**
         * join(array('tableName' => 'tableNameAlias', 'main.id=tableNameAlias.id'))
         *
         * @param array $table
         * @param $on
         * @param string $leftRight
         * @return $this|ISqlBuilder
         * @throws \Exception
         */
        public function join(array $table, $on, $leftRight = 'INNER')
        {
            $tableName = array_keys($table);
            $tableName = $tableName[0];
            $table = $tableName . ' AS ' . $table[$tableName];

            if (!is_string($on)) {
                throw new \Exception('on must be an string type');
            }

            $leftRight = strtoupper($leftRight);
            if (!in_array($leftRight, self::$JOIN_TYPE))
                throw new \Exception('invalid join type');

            $this->_join .= ' ' . $leftRight . ' JOIN ' . $table . ' ON ' . $on;
            return $this;
        }

        public function order($column, $orderType = 'ASC')
        {
            $orderType = strtoupper($orderType);
            if (!in_array($orderType, self::$ORDER_TYPE))
                throw new \Exception('invalid order type: ' . $orderType);

            $this->_order = ' ORDER BY ' . $column . ' ' . $orderType;

            return $this;
        }

        public function get($toResultSet = true, $start = 0, $end = 1)
        {
            $sql = 'SELECT';

            if ($this->_from === null)
                throw new \Exception('You should to provide a table name you want to get from');

            if ($this->_select === null)
                throw new \Exception('You should to provide some columns you want to select');

            $sql .= $this->_select . $this->_from . ' ';

            if ($this->_join !== null)
                $sql .= $this->_join;

            if ($this->_where !== null)
                $sql .= $this->_where . ' ';

            if ($this->_order !== null)
                $sql .= $this->_order;

            $args = func_get_args();
            $argsLen = count($args);
            if ($argsLen > 1) {
                $start = intval($start);
                $end = intval($end);

                $start = $start < 0 ? 0 : $start;
                $end = $end <= 0 ? 1 : $end;

                $sql .= ' LIMIT ' . $start . ',' . $end;
            } else if ($argsLen === 1) {
                $toResultSet = $args[0];
            }

            $stmt = $this->_connection->prepare($sql);

            try {
                if ($stmt->execute($this->_params)) {
                    $ret = null;

                    if ($toResultSet) {
                        $ret = new ResultSet($stmt->fetchAll(\PDO::FETCH_ASSOC));
                    } else {
                        $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    }

                    return $ret;

                } else {
                    throw new \Exception('query failed');
                }
            } catch (\Exception $e) {
                $errMsg = array(
                    'error code: ' . $stmt->errorCode(),
                    'error info: ' . implode("\n", $stmt->errorInfo())
                );

                if ($this->_debug) {
                    $errMsg['build_sql'] = $sql;
                    $errMsg['params'] = $this->_params;
                }

                throw new \Exception('query failed: ' . var_export($errMsg, true));
            }
        }

        /**
         * @param $tableName
         * @param array $fieldValueMap
         * @return string
         * @throws \Exception
         */
        public function insert($tableName, array $fieldValueMap)
        {
            $sql = 'INSERT INTO ' . $tableName;

            $fields = array_keys($fieldValueMap);
            $params = array_values($fieldValueMap);
            $placeholders = array_fill(0, count($fields), '?');

            $sql .= ' (' . implode(',', $fields) . ') VALUES(' . implode(',', $placeholders) . ')';
            $stmt = $this->_connection->prepare($sql);

            try {
                if ($stmt->execute($params)) {
                    return $this->_connection->lastInsertId();
                } else {
                    throw new \Exception('query failed');
                }
            } catch (\Exception $e) {
                $errMsg = array(
                    'error code: ' . $stmt->errorCode(),
                    'error info: ' . implode("\n", $stmt->errorInfo())
                );

                if ($this->_debug) {
                    $errMsg['build_sql'] = $sql;
                    $errMsg['params'] = $params;
                }

                throw new \Exception('query failed: ' . var_export($errMsg, true));
            }
        }

        /**
         * @param $tableName
         * @param array $fieldValueMap
         * @param array $whereGroups
         * @return int
         * @throws \Exception
         */
        public function update($tableName, array $fieldValueMap, array $whereGroups)
        {
            $sql = 'UPDATE ' . $tableName . ' SET ';

            $setParts = array();
            $params = array();

            foreach ($fieldValueMap as $key => $val) {
                $setParts[] = $key . '=?';
                $params[] = $val;
            }

            $whereGroupsCount = count($whereGroups);
            if ($whereGroupsCount === 2) {
                /**
                 * update('user', array('name' => 'example'), array('id=', 1))
                 */
                $where = 'WHERE ' . $whereGroups[0] . '?';
                $params[] = $whereGroups[1];
            } else if ($whereGroupsCount > 2) {
                /**
                 * update('user', array('un' => 'test2'), array(
                 *     array('id=', 15),
                 *     array(SqlBuilder::WHERE_RELATION, 'or'),
                 *     array('id=', 16)
                 * ))
                 */

                $where = 'WHERE ' . $this->_buildWhereGroups($whereGroups);
                $params = array_merge($params, $this->_params);
                $this->_params = array();
            } else {
                throw new \Exception('invalid whereGroups usage');
            }

            $sql .= implode(',', $setParts) . ' ' . $where;
            $stmt = $this->_connection->prepare($sql);

            try {
                if ($stmt->execute($params)) {
                    return $stmt->rowCount();
                } else {
                    throw new \Exception('query failed');
                }
            } catch (\Exception $e) {
                $errMsg = array(
                    'error code: ' . $stmt->errorCode(),
                    'error info: ' . implode("\n", $stmt->errorInfo())
                );

                if ($this->_debug) {
                    $errMsg['build_sql'] = $sql;
                    $errMsg['params'] = $params;
                }

                throw new \Exception('query failed: ' . var_export($errMsg, true));
            }
        }

        /**
         * @param $tableName
         * @param array $whereGroups
         * @return int
         * @throws \Exception
         */
        public function delete($tableName, array $whereGroups)
        {
            $sql = 'DELETE FROM ' . $tableName . ' ';
            $params = array();

            $whereGroupsCount = count($whereGroups);
            if ($whereGroupsCount === 2) {
                /**
                 * delete('user', array('id=', 1))
                 */
                $where = 'WHERE ' . $whereGroups[0] . '?';
                $params[] = $whereGroups[1];
            } else if ($whereGroupsCount > 2) {
                /**
                 * delete('user', array(
                 *     array('id=', 15),
                 *     array(SqlBuilder::WHERE_RELATION, 'or'),
                 *     array('id=', 16)
                 * ))
                 */

                $where = 'WHERE ' . $this->_buildWhereGroups($whereGroups);
                $params = array_merge($params, $this->_params);
                $this->_params = array();
            } else {
                throw new \Exception('invalid whereGroups usage');
            }

            $sql .= $where;
            $stmt = $this->_connection->prepare($sql);

            try {
                if ($stmt->execute($params)) {
                    return $stmt->rowCount();
                } else {
                    throw new \Exception('query failed');
                }
            } catch (\Exception $e) {
                $errMsg = array(
                    'error code: ' . $stmt->errorCode(),
                    'error info: ' . implode("\n", $stmt->errorInfo())
                );

                if ($this->_debug) {
                    $errMsg['build_sql'] = $sql;
                    $errMsg['params'] = $params;
                }

                throw new \Exception('query failed: ' . var_export($errMsg, true));
            }
        }

        public function getParams()
        {
            return $this->_params;
        }

        public function getPart($partName)
        {
            $ret = null;
            switch ($partName) {
                case self::PART_FROM:
                    $ret = $this->_from;
                    break;
                case self::PART_JOIN:
                    $ret = $this->_join;
                    break;
                case self::PART_ORDER:
                    $ret = $this->_order;
                    break;
                case self::PART_WHERE :
                    $ret = $this->_where;
                    break;
                case self::PART_SELECT:
                    $ret = $this->_select;
                    break;
                default:
                    break;
            }

            return $ret;
        }
    }
}