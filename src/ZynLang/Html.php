<?php
namespace ZynLang;

use ZynLang\Html\ElementA;

/**
 * Class XElem
 *
 * @package Msm
 */
class Html {
    public static function a() {
        return new ElementA('a');
    }
}