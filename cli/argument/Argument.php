<?php

namespace Lce\cli\argument {

    class Argument
    {
        public $name;
        public $isFlag;
        public $value = null;
        public $comment;
        public $argumentsGroup;
        private $_validations = array();

        function __construct($name, $comment = null, $isFlag = false)
        {
            $this->name = $name;
            $this->comment = $comment;
            $this->isFlag = $isFlag;
        }

        public function addValidation(Validation $validation)
        {
            $this->_validations[] = $validation;
        }

        /**
         * @return bool|string true when pass or string when fail
         */
        public function isValid()
        {
            /**
             * @var $validation Validation
             */
            foreach ($this->_validations as $validation) {
                if ($validation->isValid($this) !== true)
                    return $validation->failedMsg;
            }

            return true;
        }
    }
}