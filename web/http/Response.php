<?php

namespace Lce\web\http {
    class Response
    {
        private static $_INSTANCE;

        private $_headers = array();
        private $_body = null;

        private $_needRedirect = false;
        private $_isRunning = false;

        public static $HTTP_STATUS_CODE = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        );

        private function __construct()
        {
        }

        public static function getInstance()
        {
            if (self::$_INSTANCE === null)
                self::$_INSTANCE = new self;

            return self::$_INSTANCE;
        }

        public function start()
        {
            if ($this->_isRunning === false) {
                $this->_isRunning = true;
                ob_start();
            }
        }

        public function setBody($body)
        {
            $this->_body = $body;
            return $this;
        }

        public function eraseBody()
        {
            ob_clean();
            return $this;
        }

        /**
         * addHeader(500);
         * addHeader('Location: http://www.example.com/');
         * addHeader('Location', 'http://www.example.com/');
         *
         * @param $key
         * @param null $value
         * @param bool $replace
         * @param $code
         * @return $this
         */
        public function addHeader($key, $value = null, $replace = true, $code = null)
        {
            if (is_int($key)) {
                if (isset(self::$HTTP_STATUS_CODE[$key])) {
                    $this->_headers[] = array(
                        'key' => Request::getInstance()->getServerParam(ServerParamKey::SERVER_PROTOCOL) . ' ' .
                            $key . ' ' . self::$HTTP_STATUS_CODE[$key],
                        'value' => null,
                        'replace' => true,
                        'code' => $key
                    );
                }
            } else {
                $this->_headers[] = array(
                    'key' => $key,
                    'value' => $value,
                    'replace' => $replace,
                    'code' => $code
                );
            }

            return $this;
        }

        public function removeHeader($key)
        {
            foreach ($this->_headers as $index => $header) {
                if ($header['key'] === $key) {
                    unset($this->_headers[$index]);
                }
            }

            return $this;
        }

        /**
         * setRedirect('Location: http://www.example.com/?k=v);
         * setRedirect('Location: http://www.example.com/, array('k' => 'v'));
         *
         * @param null $url
         * @param null $params
         * @param int $code
         * @return $this
         */
        public function setRedirect($url = null, $params = null, $code = 301)
        {
            if (is_string($url)) {
                $this->_needRedirect = true;

                if (is_object($params)) {
                    $params = get_object_vars($params);
                }

                if (is_array($params) && count($params)) {
                    $url .= '?' . http_build_query($params);
                }

                $this->addHeader('Location', $url, true, $code);
            } else {
                $this->_needRedirect = false;
                $this->removeHeader('Location');
            }

            return $this;
        }

        private function _sendHeaders()
        {
            foreach ($this->_headers as $header) {
                $string = empty($header['value']) ? $header['key'] : $header['key'] . ': ' . $header['value'];

                if (empty($header['code'])) {
                    header($string, $header['replace']);
                } else {
                    header($string, $header['replace'], $header['code']);
                }
            }
        }

        public function getOutputCache()
        {
            $ret = ob_get_contents();
            ob_end_clean();
            return $ret;
        }

        /**
         * if you've invoked Response::setBody('some body strings') before this method, just echo the
         * 'some body strings' you've set, otherwise echo the string from ob_get_contents()
         */
        public function send()
        {
            $this->_sendHeaders();

            if ($this->_body === null)
                $this->_body = $this->getOutputCache();
            else
                ob_end_clean();

            echo $this->_body;
        }
    }
}