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
 * DAO for "language_text" table
 * @access    public
 * @author    Cindy Qi Li
 * @package    DAO
 */
class LanguageTextDAO extends DAO {

    /**
     * Create a new entry
     * @access  public
     * @param   $language_code : language code
     *          $variable: '_msgs', '_template', '_check', '_guideline', '_test'
     *          $term
     *          $text
     *          $context
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function Create($language_code, $variable, $term, $text, $context) {


        $sql = "INSERT INTO " . TABLE_PREFIX . "language_text
		        (`language_code`, `variable`, `term`, `text`, `revised_date`, `context`)
		        VALUES
		        ('" . $this->db->real_escape_string($language_code) . "',
		         '" . $this->db->real_escape_string($variable) . "',
		         '" . $this->db->real_escape_string($term) . "',
		         '" . $this->db->real_escape_string($text) . "',
		         now(),
		         '" . $this->db->real_escape_string($context) . "')";

        return $this->execute($sql);
    }

    /**
     * Insert new record if not exists, replace the existing one if already exists.
     * Record is identified by primary key: $language_code, variable, $term
     * @access  public
     * @param   $language_code : language code
     *          $variable: '_msgs', '_template', '_check', '_guideline', '_test'
     *          $term
     *          $text
     *          $context
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function Replace($language_code, $variable, $term, $text, $context) {


        $sql = "REPLACE INTO " . TABLE_PREFIX . "language_text
		        (`language_code`, `variable`, `term`, `text`, `revised_date`, `context`)
		        VALUES
		        ('" . $this->db->real_escape_string($language_code) . "',
		         '" . $this->db->real_escape_string($variable) . "',
		         '" . $this->db->real_escape_string($term) . "',
		         '" . $this->db->real_escape_string($text) . "',
		         now(),
		         '" . $this->db->real_escape_string($context) . "')";

        return $this->execute($sql);
    }

    /**
     * Delete a record by $variable and $term
     * @access  public
     * @param   $language_code : language code
     *          $variable: '_msgs', '_template', '_check', '_guideline', '_test'
     *          $term
     * @return  true / false
     * @author  Cindy Qi Li
     */
    function DeleteByVarAndTerm($variable, $term) {


        $sql = "DELETE FROM " . TABLE_PREFIX . "language_text
		        WHERE `variable` = '" . $this->db->real_escape_string($variable) . "'
		          AND `term` = '" . $this->db->real_escape_string($term) . "'";

        return $this->execute($sql);
    }

    /**
     * Return message text of given term and language
     * @access  public
     * @param   term : language term
     *          lang : language code
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getMsgByTermAndLang($term, $lang) {


        $term = $this->db->real_escape_string($term);
        $lang = $this->db->real_escape_string($lang);

        $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'language_text
						WHERE term="' . $term . '"
						AND variable="_msgs"
						AND language_code="' . $lang . '"
						ORDER BY variable';

        return $this->execute($sql);
    }

    /**
     * Return rows of handbook rows by matching given text and language
     * @access  public
     * @param   term : language term
     *          lang : language code
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getHelpByMatchingText($text, $lang) {


        $text = $this->db->real_escape_string( strtolower($text));
        $lang = $this->db->real_escape_string( $lang);

        $sql = "SELECT * FROM " . TABLE_PREFIX . "language_text
						WHERE term like 'AC_HELP_%'
						AND lower(cast(text as char)) like '%" . $text . "%'
						AND language_code='" . $lang . "'
						ORDER BY variable";

        return $this->execute($sql);
    }

    /**
     * Return text of given term and language
     * @access  public
     * @param   term : language term
     *          lang : language code
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getByTermAndLang($term, $lang) {


        $term = $this->db->real_escape_string($term);
        $lang = $this->db->real_escape_string($lang);

        $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'language_text
						WHERE term="' . $term . '"
						AND language_code="' . $lang . '"
						ORDER BY variable';

        return $this->execute($sql);
    }

    /**
     * Return all template info of given language
     * @access  public
     * @param   lang : language code
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getAllByLang($lang) {


        $lang = $this->db->real_escape_string($lang);

        $sql = "SELECT * FROM " . TABLE_PREFIX . "language_text
						WHERE language_code='" . $lang . "'
						ORDER BY variable, term ASC";

        return $this->execute($sql);
    }

    /**
     * Return all template info of given language
     * @access  public
     * @param   lang : language code
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getAllTemplateByLang($lang) {


        $lang = $this->db->real_escape_string($lang);

        $sql = "SELECT * FROM " . TABLE_PREFIX . "language_text
						WHERE language_code='" . $lang . "'
						AND variable='_template'
						ORDER BY variable ASC";

        return $this->execute($sql);
    }

    /**
     * Update text based on given primary key
     * @access  public
     * @param   $languageCode : language_text.language_code
     *          $variable : language_text.variable
     *          $term : language_text.term
     *          $text : text to update into language_text.text
     * @return  true : if successful
     *          false: if unsuccessful
     * @author  Cindy Qi Li
     */
    function setText($languageCode, $variable, $term, $text) {


        $languageCode = $this->db->real_escape_string($languageCode);
        $variable = $this->db->real_escape_string($variable);
        $term = $this->db->real_escape_string($term);
        $text = $this->db->real_escape_string($text);

        if (strlen($term)<1)
            throw new \Exception("Something's gone wrong");
        else{
            $sql = "UPDATE " . TABLE_PREFIX . "language_text
		           SET text='" . $text . "',
		               revised_date = now()
                   WHERE language_code = '" . $languageCode . "'
		           AND variable='" . $variable . "'
		           AND term = '" . $term . "'";
            return($this->execute($sql));
        }

        return false;
    }
}

?>