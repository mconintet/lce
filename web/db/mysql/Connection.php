<?php
namespace Lce\web\db\mysql {
    use Lce\web\Application;
    use Lce\web\config\IConfigurable;
    use Lce\web\db\IConnection;
    use Lce\web\util\BaseUtil;

    class Connection extends \PDO implements IConfigurable, IConnection
    {
        public $connectionString;
        public $username;
        public $password;
        public $initParams;

        private $_sqlBuilder = null;
        private static $_instance = null;

        public function __construct($configArray = null)
        {
            if (empty($configArray))
                $configArray = Application::getInstance()->getConfig(self::getConfigSectionKey());

            if (is_array($configArray))
                BaseUtil::objectApplyPropertiesFromArray($this, $configArray);
            else
                throw new \Exception(sprintf("Section '%s' must be an array type.", self::getConfigSectionKey()));

            parent::__construct($this->connectionString, $this->username, $this->password, $this->initParams);
        }

        /**
         * @return Connection
         */
        public static function getInstance()
        {
            if (self::$_instance === null)
                self::$_instance = new self;

            return self::$_instance;
        }

        public function getSqlBuilder($createNew = false, $debug = false)
        {
            if ($this->_sqlBuilder === null || $createNew) {
                $this->_sqlBuilder = new SqlBuilder($this, $debug);
            }

            return $this->_sqlBuilder;
        }

        /**
         * @return string
         */
        public static function getConfigSectionKey()
        {
            return 'mySqlConnection';
        }
    }
}