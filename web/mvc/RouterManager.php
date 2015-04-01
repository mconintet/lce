<?php

namespace Lce\web\mvc {
    use Lce\web\config\Configurable;
    use Lce\web\http\Request;
    use Lce\web\http\Response;

    class RouterManager extends Configurable
    {
        public $rewrite;
        public $ignoreCase;
        public $normalRouters;
        public $regexpRouters;
        public $notFoundTemplate;
        public $defaultController = 'Index';
        public $defaultAction = 'index';

        private $_hasNormalRouters;
        private $_hasRegexpRouters;

        private $_currentController;
        private $_currentAction;

        const ROUTER_PARAM_NAME = 'r';
        const ROUTER_CONFIG_KEY_FROM = 'from';
        const ROUTER_CONFIG_KEY_TO = 'to';
        const ROUTER_CONFIG_KEY_PARAM_MAP = 'paramMap';

        private static $_instance = null;

        protected function __construct()
        {
            parent::__construct();

            $this->_hasNormalRouters = count($this->normalRouters);
            $this->_hasRegexpRouters = count($this->regexpRouters);
        }

        public static function getInstance()
        {
            if (self::$_instance === null)
                self::$_instance = new self;

            return self::$_instance;
        }

        /**
         * @return string
         */
        public static function getConfigSectionKey()
        {
            return 'router';
        }

        public static function getNormalRoutesConfigPath()
        {
            return self::getConfigSectionKey() . '/normalRouters';
        }

        public function _dispatchNormal($requestUri, $params = array())
        {
            $ret = false;
            if ($this->_hasNormalRouters) {

                foreach ($this->normalRouters as $router) {
                    $from = $router[self::ROUTER_CONFIG_KEY_FROM];
                    $from = $this->ignoreCase ? strtolower($from) : $from;
                    $requestUri = $this->ignoreCase ? strtolower($requestUri) : $requestUri;

                    if ($requestUri === $from) {
                        /**
                         * @var $to Controller
                         */
                        $to = new $router[self::ROUTER_CONFIG_KEY_TO]($params);
                        $controllerAction = explode('/', $from);
                        $this->_currentController = $controllerAction[0];
                        $this->_currentAction = $controllerAction[1];

                        $to->doActionMethod($this->_currentAction);
                        $ret = true;
                        break;
                    }
                }
            }

            return $ret;
        }

        public function _dispatchRegexp($requestUri)
        {
            $ret = false;
            if ($this->_hasRegexpRouters) {
                foreach ($this->regexpRouters as $router) {
                    if (isset($router[self::ROUTER_CONFIG_KEY_FROM]) &&
                        isset($router[self::ROUTER_CONFIG_KEY_TO]) &&
                        is_array($router[self::ROUTER_CONFIG_KEY_FROM])
                    ) {
                        $pattern = '/' . implode($router[self::ROUTER_CONFIG_KEY_FROM], '\/') . '/';
                        if (preg_match($pattern, $requestUri, $matches) === 1) {
                            array_shift($matches);
                            $params = array();

                            if (isset($router[self::ROUTER_CONFIG_KEY_PARAM_MAP]) &&
                                is_array($paramMap = $router[self::ROUTER_CONFIG_KEY_PARAM_MAP])
                            ) {
                                foreach ($matches as $index => $match) {
                                    if (isset($paramMap[$index])) {
                                        $params[$paramMap[$index]] = $match;
                                    }
                                }
                            } else {
                                $params = $matches;
                            }

                            $ret = $this->_dispatchNormal($router[self::ROUTER_CONFIG_KEY_TO], $params);
                        }
                    }
                }
            }

            return $ret;
        }

        private function _dispatch()
        {
            $requestUri = $this->rewrite ?
                Request::getInstance()->retrieveRequestUri() :
                Request::getInstance()->getGetParam(self::ROUTER_PARAM_NAME);

            $requestUri = trim($requestUri, '/');
            $requestUri = empty($requestUri) ? $this->defaultController . '/' . $this->defaultAction : $requestUri;

            if (strpos($requestUri, '/') === false)
                $requestUri = $requestUri . '/' . $this->defaultAction;

            $ret = $this->_dispatchRegexp($requestUri);

            if (!$ret)
                $ret = $this->_dispatchNormal($requestUri);

            return $ret;
        }

        public function dispatch()
        {
            if ($this->_dispatch() === false) {
                $requestUrl = Request::getInstance()->getCurrentUrl();
                Response::getInstance()->eraseBody()->addHeader(404);

                if ($this->notFoundTemplate && is_readable($this->notFoundTemplate)) {
                    include($this->notFoundTemplate);
                } else {
                    echo 'Not Found: ' . $requestUrl;
                }
            }
        }
    }
}