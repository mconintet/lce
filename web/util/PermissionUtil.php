<?php

namespace Lce\web\util {
    class PermissionUtil
    {
        const ACTION_READ = 4;
        const ACTION_WRITE = 2;
        const ACTION_EXEC = 1;

        const ROLE_USER = 1;
        const ROLE_GROUP = 2;
        const ROLE_OTHER = 3;

        public static function can($permissionHex, $role, $action)
        {
            $permissionHex = is_string($permissionHex) ? hexdec($permissionHex) : $permissionHex;

            switch ($role) {
                case self::ROLE_USER:
                    $permissionPart = $permissionHex >> 8;
                    break;
                case self::ROLE_GROUP:
                    $permissionPart = ($permissionHex & 0x0000F0) >> 4;
                    break;
                case self::ROLE_OTHER:
                    $permissionPart = $permissionHex & 0x00000F;
                    break;
                default:
                    $permissionPart = null;
                    break;
            }

            if ($permissionPart !== null) {
                return (bool)($permissionPart & $action);
            }

            return -1;
        }
    }
}