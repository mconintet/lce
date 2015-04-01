<?php

namespace Lce\test\code\models;

use Lce\web\db\IConnection;
use Lce\web\db\ISqlBuilder;
use Lce\web\db\mysql\Connection;
use Lce\web\mvc\model\Model;

class Vendor extends Model
{
    private static $_DB_CONNECTION = null;

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'vendor';
    }

    /**
     * @return IConnection
     */
    public static function getDbConnection()
    {
        if (self::$_DB_CONNECTION === null) {
            $connectionSettings = array(
                'connectionString' => 'mysql:dbname=coke;host=localhost',
                'username' => 'root',
                'password' => 'll.1314',
                'initParams' => array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                )
            );

            self::$_DB_CONNECTION = new Connection($connectionSettings);
        }

        return self::$_DB_CONNECTION;
    }

    /**
     * @return ISqlBuilder
     */
    public static function getSqlBuilder()
    {
        return static::getDbConnection()->getSqlBuilder(false, true);
    }

    /**
     * @return string
     */
    public static function getTablePrimaryKey()
    {
        return 'id';
    }

    /**
     * @return string
     */
    public static function getEventPrefix()
    {
        // TODO: Implement getEventPrefix() method.
    }

    /**
     * @return array
     */
    public static function getBeforeSaveValidations()
    {
        // TODO: Implement getBeforeSaveValidations() method.
    }

    /**
     * @return mixed
     */
    public static function getModelClass()
    {
        return __CLASS__;
    }

    public function getRelations()
    {
        return array(
            'user' => array(static::MANY_MANY, 'User', array('vendor_customer', 'vendor_id', 'customer_id'))
        );
    }
}