<?php

namespace Lce\web\http {
    use Lce\web\Application;
    use Lce\web\config\Configurable;
    use Lce\web\util\HandyObject;

    class Session extends HandyObject
    {
        public $startTime;
        public $expire;

        public function isExpired()
        {
            return $this->startTime + $this->expire < time();
        }
    }

    class SessionManager extends Configurable
    {
        const SESSION_KEY = 'lce_web_http_session';

        /**
         * only contains the fields can be set at PHP_INI_ALL level,
         * details see: http://www.php.net/manual/zh/session.configuration.php
         * @var array
         */
        private static $_SESSION_INI_ITEMS = array(
            'session.save_path',
            'session.name',
            'session.save_handler',
            'session.auto_start',
            'session.gc_probability',
            'session.gc_divisor',
            'session.gc_maxlifetime',
            'session.serialize_handler',
            'session.cookie_lifetime',
            'session.cookie_path',
            'session.cookie_domain',
            'session.cookie_secure',
            'session.cookie_httponly',
            'session.use_cookies',
            'session.use_only_cookies',
            'session.referer_check',
            'session.entropy_file',
            'session.entropy_length',
            'session.cache_limiter',
            'session.cache_expire',
            'session.use_trans_sid',
            'session.bug_compat_42',
            'session.bug_compat_warn',
            'session.hash_function',
            'session.hash_bits_per_character',
            'url_rewriter.tags'
        );

        public $initSettings;
        public $expire = 3600;

        /**
         * @var $_session Session
         */
        private $_session;

        private static $_instance = null;

        /**
         * @return string
         */
        public static function getConfigSectionKey()
        {
            return 'session';
        }

        protected function __construct()
        {
            parent::__construct();

            if (!isset($_SESSION)) {
                $settings = is_array($this->initSettings) ?
                    array_merge($this->getDefaultInitSettings(), $this->initSettings) :
                    $this->getDefaultInitSettings();

                foreach ($settings as $settingName => $settingValue) {
                    if (in_array($settingName, self::$_SESSION_INI_ITEMS)) {
                        ini_set($settingName, $settingValue);
                    }
                }

                session_start();
            }

            /**
             * @var $session Session
             */
            if (!isset($_SESSION[self::SESSION_KEY]) ||
                !($_SESSION[self::SESSION_KEY] instanceof Session) ||
                (($session = $_SESSION[self::SESSION_KEY]) && $session->isExpired())
            ) {
                $newSession = new Session();
                $newSession->startTime = time();
                $newSession->expire = $this->expire;

                $_SESSION[self::SESSION_KEY] = $newSession;
            }

            $this->_session = $_SESSION[self::SESSION_KEY];
            $this->_session->startTime = time();
        }

        public static function getInstance()
        {
            if (self::$_instance === null)
                self::$_instance = new self;

            return self::$_instance;
        }

        public function getDefaultInitSettings()
        {
            return array(
                'session.name' => 'LCE_SESSION',
                'session.save_path' => Application::getInstance()->getVarDir() . DS . 'session',
                'session.cookie_lifetime' => $this->expire,
                'session.gc_maxlifetime' => $this->expire
            );
        }

        public function set($key, $value)
        {
            $this->_session->set($key, $value);
            return $this;
        }

        public function get($key, $defaultValue = null)
        {
            return $this->_session->get($key, $defaultValue);
        }

        public function has($key)
        {
            return $this->_session->has($key);
        }

        public function getData()
        {
            return $this->_session->getData();
        }
    }
}