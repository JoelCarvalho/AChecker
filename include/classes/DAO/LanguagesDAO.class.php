<?php namespace QChecker\DAO;
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

if (!defined('AC_INCLUDE_PATH')) exit;

require_once(AC_INCLUDE_PATH . 'classes/DAO/DAO.class.php');

/**
 * DAO for "config" table
 * @access    public
 * @author    Cindy Qi Li
 * @package    DAO
 */
class LanguagesDAO extends DAO {

    /**
     * Insert table languages
     * @access  public
     * @param   $langCode , $charset, $regExp, $nativeName, $englishName, $status
     * @return  true / false
     * @author  Cindy Qi Li
     */
    function Create($langCode, $charset, $regExp, $nativeName, $englishName, $status) {
        global $languageManager, $msg;

        // check if the required fields are filled
        if (!$this->ValidateFields($langCode, $charset, $nativeName, $englishName)) return false;

        // check if the language already exists
        if ($languageManager->exists($langCode)) $msg->addError('LANG_EXISTS');

        if ($msg->containsErrors()) return false;

        $langCode = $this->db->real_escape_string($langCode);
        $charset = $this->db->real_escape_string($charset);
        $regExp = $this->db->real_escape_string($regExp);
        $nativeName = $this->db->real_escape_string($nativeName);
        $englishName = $this->db->real_escape_string($englishName);
        $status = intval($status);

        $sql = "INSERT INTO " . TABLE_PREFIX . "languages (language_code, charset, reg_exp, native_name, english_name, status)
		        VALUES ('" . $langCode . "', '" . $charset . "', '" . $regExp . "', '" . $nativeName . "', '" . $englishName . "', " . $status . ")";
        return $this->execute($sql);
    }

    /**
     * Update a row
     * @access  public
     * @param   $langCode : required
     *          $charset: required
     * @return  true / false
     * @author  Cindy Qi Li
     */
    function Update($langCode, $charset, $regExp, $nativeName, $englishName, $status) {


        // check if the required fields are filled
        if (!$this->ValidateFields($langCode, $charset, $nativeName, $englishName)) return false;

        $langCode = $this->db->real_escape_string($langCode);
        $charset = $this->db->real_escape_string($charset);
        $regExp = $this->db->real_escape_string($regExp);
        $nativeName = $this->db->real_escape_string($nativeName);
        $englishName = $this->db->real_escape_string($englishName);
        $status = intval($status);

        $sql = "UPDATE " . TABLE_PREFIX . "languages
		           SET reg_exp='" . $regExp . "',
		               native_name = '" . $nativeName . "',
		               english_name = '" . $englishName . "',
		               status = " . $status . "
		         WHERE language_code = '" . $langCode . "'
		           AND charset = '" . $charset . "'";
        return $this->execute($sql);
    }

    /**
     * Delete a row
     * @access  public
     * @param   $langCode
     *          $charset
     * @return  true / false
     * @author  Cindy Qi Li
     */
    function Delete($langCode) {

        $langCode = $this->db->real_escape_string($langCode);

        $sql = "DELETE FROM " . TABLE_PREFIX . "languages
		         WHERE language_code = '" . $langCode . "'";
        if (!$this->execute($sql)) return false;

        $sql = "DELETE FROM " . TABLE_PREFIX . "language_text
	             WHERE language_code = '" . $langCode . "'";

        return $this->execute($sql);
    }

    /**
     * Return all languages
     * @access  public
     * @param   none
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getAll() {
        $sql = "SELECT * FROM " . TABLE_PREFIX . "languages l
	             ORDER BY l.native_name";
        return $this->execute($sql);
    }

    /**
     * Return all enabled languages
     * @access  public
     * @param   none
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getAllEnabled() {
        $sql = "SELECT * FROM " . TABLE_PREFIX . "languages l
	             WHERE status = " . AC_STATUS_ENABLED . "
	             ORDER BY l.native_name";
        return $this->execute($sql);
    }

    /**
     * Return language with given language code
     * @access  public
     * @param   $langCode
     *          $charset
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getByLangCodeAndCharset($langCode, $charset) {

        $langCode = $this->db->real_escape_string($langCode);
        $charset = $this->db->real_escape_string($charset);

        $sql = "SELECT * FROM " . TABLE_PREFIX . "languages l
	             WHERE l.language_code = '" . $langCode . "'
	               AND l.charset='" . $charset . "'
	             ORDER BY l.native_name";

        if ($rows = $this->execute($sql)) {
            return $rows[0];
        }
    }

    /**
     * Return all languages except the ones with language code in the given string
     * @access  public
     * @param   $langCode : one language codes, for example: en
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getAllExceptLangCode($langCode) {


        if (trim($langCode) == '')
            return $this->getAll();
        else {
            $langCode = $this->db->real_escape_string($langCode);

            $sql = "SELECT * FROM " . TABLE_PREFIX . "languages
					 WHERE language_code <> '" . $langCode . "'
			         ORDER BY native_name";
            return $this->execute($sql);
        }
    }

    /**
     * Return all languages except the ones with language code in the given string
     * @access  public
     * @param   $langCode : one language codes, for example: en
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function ValidateFields($langCode, $charset, $nativeName, $englishName) {
        global $msg;

        $missing_fields = array();

        if ($langCode == '') {
            $missing_fields[] = _AC('lang_code');
        }
        if ($charset == '') {
            $missing_fields[] = _AC('charset');
        }
        if ($nativeName == '') {
            $missing_fields[] = _AC('name_in_language');
        }
        if ($englishName == '') {
            $missing_fields[] = _AC('name_in_english');
        }

        if ($missing_fields) {
            $missing_fields = implode(', ', $missing_fields);
            $msg->addError(array('EMPTY_FIELDS', $missing_fields));
            return false;
        }
        return true;
    }
}

?>