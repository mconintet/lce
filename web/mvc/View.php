<?php

namespace Lce\web\mvc {

    use Lce\web\Application;
    use Lce\web\cache\ICacheAdapter;
    use Lce\web\http\Request;
    use Lce\web\util\HandyObject;

    class ViewContext extends HandyObject
    {
    }

    abstract class View
    {
        private $_useCache = false;

        /**
         * @var null|ICacheAdapter
         */
        private $_cacheAdapter = null;

        private $_cacheKey = '';

        private $_cacheExpire = 3600;

        private $_template = '';

        /**
         * @var null|ViewContext
         */
        private $_context = null;

        protected function _getRelativeTemplatePath($path)
        {
            $path = Application::getInstance()->getTemplateDir() . DS . $path;

            if (file_exists($path))
                return $path;
            else {
                throw new \Exception("Relative template path does not exists: '{$path}'");
            }
        }

        public function setTemplate($path, $abs = false)
        {
            if ($abs) {
                if (file_exists($path)) {
                    $this->_template = $path;
                } else {
                    throw new \Exception("Absolute template path does not exists: '{$path}'");
                }
            } else {
                $this->_template = $this->_getRelativeTemplatePath($path);
            }
        }

        public function setContext(ViewContext $context)
        {
            $this->_context = $context;
            return $this;
        }

        public function getContext()
        {
            return $this->_context;
        }

        public function getUrl($routeStr, $params = null, $https = false)
        {
            return Request::getInstance()->getUrl($routeStr, $params, $https);
        }

        public function getAssetsItemUrl($relativePath, $https = false)
        {
            return Request::getInstance()->getAssetsItemUrl($relativePath, $https);
        }

        public function getHomeUrl($https = false, $withBaseDir = true)
        {
            return Request::getInstance()->getHomeUrl($https, $withBaseDir);
        }

        public function getCurrentUrl($https = false)
        {
            return Request::getInstance()->getCurrentUrl($https);
        }

        /**
         * @return string return __METHOD__ in your implement
         */
        abstract function getDefaultCacheKey();

        public function useCache($onOff, $expire, $cacheKey = null)
        {
            $this->_useCache = $onOff;

            if ($this->_useCache) {
                $this->_cacheKey = empty($cacheKey) ?
                    Request::getInstance()->getControllerActionName() . $this->getDefaultCacheKey() :
                    $cacheKey;

                $this->_cacheExpire = $expire;
            }
        }

        public function setCacheAdapter(ICacheAdapter $cacheAdapter)
        {
            $this->_cacheAdapter = $cacheAdapter;
        }

        public function renderNoCache()
        {
            if (file_exists($this->_template)) {
                include($this->_template);
            } else {
                throw new \Exception("You must provide a valid template path: {$this->_template}");
            }
        }

        public function render()
        {
            if ($this->_useCache) {
                if ($this->_cacheAdapter->has($this->_cacheKey))
                    echo $this->_cacheAdapter->get($this->_cacheKey);
                else {
                    $this->renderNoCache();
                    $this->_cacheAdapter->set($this->_cacheKey, ob_get_clean(), $this->_cacheExpire);
                }
            } else {
                $this->renderNoCache();
            }
        }
    }
}