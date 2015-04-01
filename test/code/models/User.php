<?php

namespace Lce\test\code\models;

use Lce\web\db\ISqlBuilder;
use Lce\web\db\mysql\Connection;
use Lce\web\mvc\model\Model;

class User extends Model
{
    private static $_DB_CONNECTION = null;

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

    public static function getTableName()
    {
        return 'customer';
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
        return array();
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
            'vendor' => array(self::MANY_MANY, 'Vendor', array('vendor_customer', 'customer_id', 'vendor_id'))
        );
    }

    public static function getAutoFillFields()
    {
        return array('id');
    }
}