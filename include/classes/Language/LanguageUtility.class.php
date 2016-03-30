<?php namespace QChecker\Language;
/************************************************************************/
/* QChecker (former AChecker)											*/
/* AChecker - https://github.com/inclusive-design/AChecker				*/
/************************************************************************/
/* Inclusive Design Institute, Copyright (c) 2008 - 2015                */
/* RELEASE Group And PT Innovation, Copyright (c) 2015 - 2016			*/
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

use charset;

if (!defined('AC_INCLUDE_PATH')) exit;

/**
 * Utility functions for language
 * @access    public
 * @author    Cindy Qi Li
 */
class LanguageUtility {
    /**
     * return language code from given AChecker language code
     * @access  public
     * @param   $code
     * @return  language code
     * @author  Cindy Qi Li
     */
    public static function getParentCode($code = ''){
        if (!$code && isset($this)) {
            $code = $this->code;
        }
        $peices = explode(AC_LANGUAGE_LOCALE_SEP, $code, 2);
        return $peices[0];
    }

    /**
     * return charset from given AChecker language code
     * @access  public
     * @param   $code
     * @return  charset
     * @author  Cindy Qi Li
     */
    public static function getLocale($code = ''){
        if (!$code && isset($this)) {
            $code = $this->code;
        }
        $peices = explode(AC_LANGUAGE_LOCALE_SEP, $code, 2);
        return $peices[1];
    }
}

?>