<?php

namespace Lce\web\http {
    use Lce\web\config\Configurable;

    class CookeManager extends Configurable
    {
        private static $_INSTANCE = null;

        const COOKIE_PARAM_KEY_NAME = 'name';
        const COOKIE_PARAM_KEY_VALUE = 'value';
        const COOKIE_PARAM_KEY_EXPIRE = 'expire';
        const COOKIE_PARAM_KEY_PATH = 'path';
        const COOKIE_PARAM_KEY_DOMAIN = 'domain';
        const COOKIE_PARAM_KEY_SECURE = 'secure';
        const COOKIE_PARAM_KEY_HTTP_ONLY = 'httpOnly';

        public $path = '/';
        public $domain;
        public $expire = 0;
        public $secure = false;
        public $httpOnly = false;

        private $_defaultParams;

        protected function __construct()
        {
            parent::__construct();

            /**
             * more details about why set default domain like below please see:
             * http://stackoverflow.com/questions/5404811/php-get-domain-name
             */
            $this->domain = Request::getInstance()->getServerParam(ServerParamKey::HTTP_HOST);
            $this->domain = $this->domain ? $this->domain :
                Request::getInstance()->getServerParam(ServerParamKey::SERVER_NAME);

            $this->_defaultParams = array(
                self::COOKIE_PARAM_KEY_NAME => '',
                self::COOKIE_PARAM_KEY_VALUE => '',
                self::COOKIE_PARAM_KEY_EXPIRE => $this->expire,
                self::COOKIE_PARAM_KEY_PATH => $this->path,
                self::COOKIE_PARAM_KEY_DOMAIN => $this->domain,
                self::COOKIE_PARAM_KEY_SECURE => $this->secure,
                self::COOKIE_PARAM_KEY_HTTP_ONLY => $this->httpOnly
            );
        }

        public static function getInstance()
        {
            if (self::$_INSTANCE === null) {
                self::$_INSTANCE = new self;
            }

            return self::$_INSTANCE;
        }

        /**
         * @return string
         */
        public static function getConfigSectionKey()
        {
            return 'cookie';
        }

        public function set($name, $value = null, $expire = null)
        {
            if (is_array($name)) {
                $params = $name;
                $params = array_merge($this->_defaultParams, $params);

                setcookie(
                    $params[self::COOKIE_PARAM_KEY_NAME],
                    $params[self::COOKIE_PARAM_KEY_VALUE],
                    $params[self::COOKIE_PARAM_KEY_EXPIRE] > 0 ? time() + $params[self::COOKIE_PARAM_KEY_EXPIRE] : 0,
                    $params[self::COOKIE_PARAM_KEY_PATH],
                    $params[self::COOKIE_PARAM_KEY_DOMAIN],
                    $params[self::COOKIE_PARAM_KEY_SECURE],
                    $params[self::COOKIE_PARAM_KEY_HTTP_ONLY]
                );
            } else {
                $params = $this->getDefaultParams();
                $params[self::COOKIE_PARAM_KEY_NAME] = $name;
                $params[self::COOKIE_PARAM_KEY_VALUE] = $value;
                $params[self::COOKIE_PARAM_KEY_EXPIRE] = $expire;

                $this->set($params);
            }

            return $this;
        }

        public function get($name, $defaultValue = null)
        {
            if (isset($_COOKIE[$name]))
                return $_COOKIE[$name];

            return $defaultValue;
        }

        public function remove($name)
        {
            setcookie($name, null, time() - 3600);
            return $this;
        }

        public function getDefaultParams()
        {
            return $this->_defaultParams;
        }
    }
}