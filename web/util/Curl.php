<?php

namespace Lce\web\util {
    class CurlResult
    {
        public $header;
        public $body;
    }

    /**
     * TODO:: add method to set http header
     */
    class Curl
    {
        private $_target = '';
        private $_useTransfer = true;

        private $_cookiePath = '';
        private $_curlHandle = null;
        private $_usePost = false;

        private $_useDebug = false;
        private $_log = null;

        /**
         * @var null|CurlResult
         */
        private $_result = null;

        public function __construct($target)
        {
            $this->_target = trim($target, ' ');

            if ($this->_curlHandle === null && $this->_isUrl($this->_target)) {
                $this->_curlHandle = curl_init($this->_target);

                return $this;
            } else {
                throw new \Exception('target ' . $this->_target . ' is not a valid url!');
            }
        }

        private function _isUrl($url)
        {
            return (bool)parse_url($url);
        }

        public function useDebug($flag)
        {
            if (is_bool($flag))
                $this->_useDebug = $flag;

            return $this;
        }

        public function useTransfer($flag)
        {
            if (is_bool($flag))
                $this->_useTransfer = $flag;

            return $this;
        }

        public function usePost($flag)
        {
            if (is_bool($flag))
                $this->_usePost = $flag;

            return $this;
        }

        public function useCookie($filePath)
        {
            if (is_string($filePath) && ($filePath = realpath($filePath)) && file_exists($filePath))
                $this->_cookiePath = $filePath;

            return $this;
        }

        private function _parseParams4Get($params)
        {
            $retVal = '?';

            if (is_array($params)) {
                foreach ($params as $key => $val) {
                    $retVal .= $key . '=' . $val . '&';
                }
            } else if (is_object($params)) {
                $retVal = $this->_parseParams4Get(get_object_vars($params));
            } else {
                $retVal = false;
            }

            return $retVal;
        }

        public function reTarget($url)
        {
            if ($this->_isUrl($url) && is_resource($this->_curlHandle)) {
                $this->_target = $url;
                curl_setopt($this->_curlHandle, CURLOPT_URL, $url);
            }

            return $this;
        }

        public function exec($params = null, $initParams = null)
        {
            $this->_result = new CurlResult();

            if (is_resource($this->_curlHandle)) {
                curl_setopt($this->_curlHandle, CURLOPT_HEADER, 1);
                curl_setopt($this->_curlHandle, CURLOPT_RETURNTRANSFER, $this->_useTransfer);
                curl_setopt($this->_curlHandle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");

                if (is_array($initParams)) {
                    foreach ($initParams as $key => $val) {
                        curl_setopt($this->_curlHandle, $key, $val);
                    }
                }

                if ($this->_useDebug) {
                    curl_setopt($this->_curlHandle, CURLOPT_VERBOSE, true);
                    $this->_log = fopen('php://temp', 'rw+');
                    curl_setopt($this->_curlHandle, CURLOPT_STDERR, $this->_log);
                } else {
                    curl_setopt($this->_curlHandle, CURLOPT_VERBOSE, false);
                }

                if ($this->_usePost) {
                    curl_setopt($this->_curlHandle, CURLOPT_POST, true);
                    $params = is_array($params) || is_object($params) ? http_build_query($params) : $params . '';

                    curl_setopt($this->_curlHandle, CURLOPT_POSTFIELDS, $params);
                } else {
                    curl_setopt($this->_curlHandle, CURLOPT_POST, false);
                    $params = $this->_parseParams4Get($params);

                    if ($params) {
                        $url = $this->_target . $params;
                        $this->reTarget($url);
                    }
                }

                if ($this->_cookiePath)
                    curl_setopt($this->_curlHandle, CURLOPT_COOKIEJAR, $this->_cookiePath);

                $result = curl_exec($this->_curlHandle);
                $headerSize = $this->getInfo(CURLINFO_HEADER_SIZE);

                $result = array(
                    substr($result, 0, $headerSize),
                    substr($result, $headerSize)
                );

                $this->_result->header = isset($result[0]) ? $result[0] : '';
                $this->_result->body = isset($result[1]) ? $result[1] : '';
            }

            return $this->_result;
        }

        public function parseHeader($headerStr)
        {
            $lines = explode("\n", $headerStr);
            $header = array();

            $index = 0;
            foreach ($lines as $line) {
                $line = rtrim($line, "\n");

                if ($index == 0)
                    $header['protocol'] = $line;
                else {
                    $parts = explode(': ', $line);

                    if (isset($parts[0])) {
                        $key = $parts[0];

                        if (isset($header[$key]) && !is_array($header[$key])) {
                            $header[$key] = array($header[$key]);
                        }

                        if (isset($header[$key]) && is_array($header[$key])) {
                            $header[$key][] = isset($parts[1]) ? $parts[1] : '';
                        } else {
                            $header[$key] = isset($parts[1]) ? $parts[1] : '';
                        }
                    }
                }

                ++$index;
            }

            return $header;
        }

        public function parseCookie($cookieStr)
        {
            $cookie = array();
            $keyValueArr = explode('; ', $cookieStr);

            $index = 0;
            foreach ($keyValueArr as $keyValue) {
                $keyValue = explode('=', $keyValue);

                if ($index == 0) {
                    $cookie['key'] = $keyValue[0];
                    $cookie['value'] = $keyValue[1];
                } else {
                    $cookie[$keyValue[0]] = $keyValue[1];
                }

                ++$index;
            }

            return $cookie;
        }

        public function getCookie($key = null)
        {
            $result = $this->_result;
            $cookies = array();

            if ($result !== null) {
                if (is_string($result->header))
                    $result->header = $this->parseHeader($result->header);

                $header = $result->header;
                $cookieStrArr = isset($header['Set-Cookie']) ? $header['Set-Cookie'] : null;
                is_string($cookieStrArr) ? $cookieStrArr = array($cookieStrArr) : null;

                if (is_array($cookieStrArr)) {
                    foreach ($cookieStrArr as $cookieStr) {
                        $cookie = $this->parseCookie($cookieStr);
                        $cookies[$cookie['key']] = $cookie;
                    }
                }
            }

            if (is_string($key) && isset($cookies[$key])) {
                return $cookies[$key]['value'];
            }

            return null;
        }

        public function isSuccess()
        {
            return $this->getInfo(CURLINFO_HTTP_CODE) == 200;
        }

        /**
         * @param $opt int see http://cn2.php.net/curl_getinfo
         * @return mixed|null
         */
        public function getInfo($opt = null)
        {
            if (is_resource($this->_curlHandle)) {
                return curl_getinfo($this->_curlHandle, $opt);
            }

            return null;
        }

        public function getError()
        {
            if (is_resource($this->_curlHandle)) {
                return curl_error($this->_curlHandle);
            }

            return null;
        }

        public function getLog()
        {
            rewind($this->_log);
            $log = stream_get_contents($this->_log);
            $log = "Verbose information:\n" . $log . "\n";

            return $log;
        }

        public function close()
        {
            if (is_resource($this->_curlHandle)) {
                curl_close($this->_curlHandle);
                $this->_curlHandle = null;
            }

            return $this;
        }
    }
}

