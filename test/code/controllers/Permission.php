<?php

namespace Lce\test\code\controllers {
    use Lce\web\mvc\Controller;
    use Lce\web\util\PermissionUtil;

    class Permission extends Controller
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
            //true
            assert(PermissionUtil::can(0x655, PermissionUtil::ROLE_USER, PermissionUtil::ACTION_READ) === true);

            //false
            assert(PermissionUtil::can(0x655, PermissionUtil::ROLE_USER, PermissionUtil::ACTION_EXEC) === false);

            //true
            assert(PermissionUtil::can(0x655, PermissionUtil::ROLE_USER, PermissionUtil::ACTION_WRITE) === true);

            //true
            assert(PermissionUtil::can(0x655, PermissionUtil::ROLE_GROUP, PermissionUtil::ACTION_EXEC) === true);

            //false
            assert(PermissionUtil::can(0x655, PermissionUtil::ROLE_GROUP, PermissionUtil::ACTION_WRITE) === false);

            //true
            assert(PermissionUtil::can(0x655, PermissionUtil::ROLE_GROUP, PermissionUtil::ACTION_READ) === true);

            //true
            assert(PermissionUtil::can(0x655, PermissionUtil::ROLE_OTHER, PermissionUtil::ACTION_EXEC) === true);

            //false
            assert(PermissionUtil::can(0x655, PermissionUtil::ROLE_OTHER, PermissionUtil::ACTION_WRITE) === false);

            //true
            assert(PermissionUtil::can(0x655, PermissionUtil::ROLE_OTHER, PermissionUtil::ACTION_READ) === true);


            //true
            assert(PermissionUtil::can(0x777, PermissionUtil::ROLE_USER, PermissionUtil::ACTION_READ) === true);

            //true
            assert(PermissionUtil::can(0x777, PermissionUtil::ROLE_USER, PermissionUtil::ACTION_EXEC) === true);

            //true
            assert(PermissionUtil::can(0x777, PermissionUtil::ROLE_USER, PermissionUtil::ACTION_WRITE) === true);

            //true
            assert(PermissionUtil::can(0x777, PermissionUtil::ROLE_GROUP, PermissionUtil::ACTION_EXEC) === true);

            //true
            assert(PermissionUtil::can(0x777, PermissionUtil::ROLE_GROUP, PermissionUtil::ACTION_WRITE) === true);

            //true
            assert(PermissionUtil::can(0x777, PermissionUtil::ROLE_GROUP, PermissionUtil::ACTION_READ) === true);

            //true
            assert(PermissionUtil::can(0x777, PermissionUtil::ROLE_OTHER, PermissionUtil::ACTION_EXEC) === true);

            //true
            assert(PermissionUtil::can(0x777, PermissionUtil::ROLE_OTHER, PermissionUtil::ACTION_WRITE) === true);

            //true
            assert(PermissionUtil::can(0x777, PermissionUtil::ROLE_OTHER, PermissionUtil::ACTION_READ) === true);
        }
    }
}