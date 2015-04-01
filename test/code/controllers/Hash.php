<?php

namespace Lce\test\code\controllers {

    use Lce\web\mvc\Controller;
    use Lce\web\util\HashUtil;

    class Hash extends Controller
    {

        /**
         * @return boolean return false to skip running real action
         */
        protected function _beforeDoActionMethod()
        {
            // TODO: Implement _beforeDoActionMethod() method.
        }

        public function indexAction()
        {
            $hashUtil = HashUtil::getInstance();
            $str = 'mypassword';
            $generated = $hashUtil->generate($str, HashUtil::CRYPT_SHA256);
            var_dump($generated->hash);
            var_dump($generated->salt);
            var_dump($hashUtil->verify($str, $generated->hash, HashUtil::CRYPT_SHA256, $generated->salt));

            $generated = $hashUtil->generate($str, HashUtil::CRYPT_SHA512);
            var_dump($generated->hash);
            var_dump($generated->salt);
            var_dump($hashUtil->verify($str, $generated->hash, HashUtil::CRYPT_SHA512, $generated->salt));
        }
    }
}