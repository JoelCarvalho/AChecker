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
 * DAO for "function_examples" table
 * @access    public
 * @author    Joel Carvalho
 * @package   DAO
 */
class FunctionExamplesDAO extends DAO {

    /**
     * Add row with given function name, type, description and content
     * @access  public
     * @param   string $functionName
     * @param   int $type
     * @param   string $description
     * @param   string $content
     * @return  mixed id inserted
     * @author  Joel Carvalho
     */
    public function Create($functionName, $type, $description, $content) {
        global $msg;
        $functionName=$this->db->real_escape_string($functionName);
        $type = $this->db->real_escape_string($type);
        $description = $this->db->real_escape_string(trim($description));
        $content = $this->db->real_escape_string(trim($content));

        // don't insert when there is no desc and content
        if ($description == '' && $content == '') return true;

        $sql = "INSERT INTO " . TABLE_PREFIX . "function_examples
				(`function_name`, `type`, `description`, `content`)
				VALUES
				('" . $functionName . "'," . $type . ",'" . $description . "', " .
            "'" . $content . "')";

        if (!$this->execute($sql)) {
            $msg->addError('DB_NOT_UPDATED');
            return false;
        } else {
            return $this->db->insert_id;
        }
    }

    /**
     * Return rows with given function name and type
     * @access  public
     * @param   string $functionName
     * @param   string $functionName
     * @return  mixed table rows
     * @author  Joel Carvalho
     */
    public function getByFunctionNameAndType($functionName, $type) {


        $sql = "SELECT * FROM " . TABLE_PREFIX . "function_examples
				WHERE function_name = '" . $this->db->real_escape_string($functionName) . "'
				  AND type = " . $this->db->real_escape_string($type);

        return $this->execute($sql);
    }

    /**
     * Delete all entries with given function name
     * @access  public
     * @param   string $functionName
     * @return  bool
     * @author  Joel Carvalho
     */
    public function DeleteByCheckID($functionName) {
        $sql = "DELETE FROM " . TABLE_PREFIX . "function_examples
				WHERE function_name = " . intval($functionName);

        return $this->execute($sql);
    }

}