<?php

namespace Lce\cli\argument {

    class Validation
    {
        public $regexpPattern;
        public $failedMsg;

        function __construct($regexpPattern, $failedMsg)
        {
            $this->regexpPattern = $regexpPattern;
            $this->failedMsg = $failedMsg;
        }

        /**
         * if you're using an anonymous function you should to return true when your validation passed
         * @param Argument $argument
         * @return bool
         */
        public function isValid(Argument $argument)
        {
            if ($this->regexpPattern instanceof \Closure) {
                $fn = $this->regexpPattern;
                /**
                 * @var $fn \Closure
                 */
                $fn = $fn->bindTo($argument);
                return $fn();
            }

            return preg_match($this->regexpPattern, $argument->value) === 1;
        }
    }
}