<?php

namespace Lce\test\code\controllers {

    use Lce\web\db\mysql\Connection;
    use Lce\web\db\mysql\SqlBuilder;
    use Lce\web\mvc\Controller;

    class Db extends Controller
    {

        /**
         * @return boolean return false to skip running real action
         */
        protected function _beforeDoActionMethod()
        {
            // TODO: Implement _beforeDoActionMethod() method.
        }

        public function indexAction()
        {
            $connectionSettings = array(
                'connectionString' => 'mysql:dbname=wxm;host=localhost',
                'username' => 'root',
                'password' => 'll.1314',
                'initParams' => array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                )
            );

            $connection = new Connection($connectionSettings);

            $sqlBuilder = $connection->getSqlBuilder(true);
            $sqlBuilder->where('id=', 1);

            $sqlBuilder = $connection->getSqlBuilder(true);
            $sqlBuilder->where(array(
                array('name=', 'name1'),
                array(SqlBuilder::WHERE_RELATION, 'or'),
                array('name=', 'name2')
            ));

            $sqlBuilder = $connection->getSqlBuilder(true);
            $sqlBuilder->where(array(
                array('id=', 1),
                array(SqlBuilder::WHERE_RELATION, 'and'),
                array(
                    array('name=', 'name1'),
                    array(SqlBuilder::WHERE_RELATION, 'or'),
                    array('name=', 'name2')
                )
            ));

            $sqlBuilder = $connection->getSqlBuilder(true, true);
            $resultSet = $sqlBuilder->select('*')
                ->from('user')
                ->where(array(
                    array('id=', 22),
                    array(SqlBuilder::WHERE_RELATION, 'and'),
                    array(
                        array('qq=', '1111'),
                        array(SqlBuilder::WHERE_RELATION, 'or'),
                        array('level_id=', 5)
                    )
                ))->get(0, 1);

            $sqlBuilder = $connection->getSqlBuilder(true, true);
            $resultSet = $sqlBuilder->select('main.un')
                ->from('user')
                ->join(array('user_agency' => 'ua'), 'main.agid=ua.id')
                ->order('main.id', SqlBuilder::ORDER_DESC)
                ->get();

            var_dump($resultSet->getDataRows());
            var_dump($resultSet->rowAt(1));

            $sqlBuilder = $connection->getSqlBuilder(true, true);
            $sqlBuilder->update('user', array('un' => 'test2'), array(
                array('id=', 15),
                array(SqlBuilder::WHERE_RELATION, 'or'),
                array('id=', 16)
            ));

            $id1 = $sqlBuilder->insert('user', array(
                'un' => 'test-insert',
                'pwd' => 'pwd'
            ));

            $id2 = $sqlBuilder->insert('user', array(
                'un' => 'test-insert',
                'pwd' => 'pwd'
            ));

        }
    }
}