<?php

namespace Lce\cli\argument {

    class ArgumentGroup
    {
        private $_arguments = array();
        private $_argv;

        function __construct($argv)
        {
            $this->_argv = $argv;
        }

        public function add(Argument $argument)
        {
            $argument->argumentsGroup = $this;
            $this->_arguments[] = $argument;
        }

        private function _injectArgumentValue(Argument $argument)
        {
            foreach ($this->_argv as $arg) {
                $pattern = '/^' . $argument->name . (!$argument->isFlag ? '=(.*)/' : '(.*)/');
                if (preg_match($pattern, $arg, $matches) === 1) {
                    $argument->value = $matches[1];
                }
            }
        }

        public function isValid()
        {
            $isValid = true;
            /**
             * @var $argument Argument
             */
            foreach ($this->_arguments as $argument) {
                $this->_injectArgumentValue($argument);
                $_isValid = $argument->isValid();

                if ($isValid === true)
                    $isValid = $_isValid;
            }

            return $isValid;
        }

        public function usage()
        {
            /**
             * @var $argument Argument
             */
            $rows = array('Usage:' . PHP_EOL);
            foreach ($this->_arguments as $argument) {
                $rows[] = implode("\t", array($argument->name, $argument->comment));
            }

            return implode(PHP_EOL, $rows) . PHP_EOL . PHP_EOL;
        }
    }
}