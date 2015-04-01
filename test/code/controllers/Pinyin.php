<?php

namespace Lce\test\code\controllers {
    use Lce\web\mvc\Controller;
    use Lce\web\util\pinyin\PinyinUtil;

    class Pinyin extends Controller
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
            var_dump(PinyinUtil::getInstance()->fromChinese('乐于助人', array(
                'format' => PinyinUtil::FORMAT_CAPITAL|PinyinUtil::FORMAT_TONE
            )));

            var_dump(PinyinUtil::getInstance()->fromChinese('音乐', array(
                'format' => PinyinUtil::FORMAT_LOWERCASE
            )));
        }
    }
}