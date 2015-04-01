<?php

namespace Lce\web\config {
    use Lce\web\Application;
    use Lce\web\util\BaseUtil;

    interface IConfigurable
    {
        /**
         * @return string
         */
        public static function getConfigSectionKey();
    }

    abstract class Configurable implements IConfigurable
    {
        private $_configSection = null;

        protected function __construct()
        {
            $configArray = $this->getConfigSection();
            if (is_array($configArray))
                BaseUtil::objectApplyPropertiesFromArray($this, $configArray);
        }

        public function getConfigSection()
        {
            if ($this->_configSection === null)
                $this->_configSection = Application::getInstance()->getConfig(static::getConfigSectionKey());
            return $this->_configSection;
        }
    }
}