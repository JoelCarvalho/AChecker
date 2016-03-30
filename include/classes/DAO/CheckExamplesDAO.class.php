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
 * DAO for "check_examples" table
 * @access    public
 * @author    Cindy Qi Li
 * @package   DAO
 */
class CheckExamplesDAO extends DAO {

    /**
     * return rows with given check id and type
     * @access  public
     * @param   $checkID
     *          $type
     * @return  table rows
     * @author  Cindy Qi Li
     */
    public function Create($checkID, $type, $description, $content) {
        global $msg;
        $checkID = intval($checkID);
        $type = $this->db->real_escape_string($type);
        $description = $this->db->real_escape_string(trim($description));
        $content = $this->db->real_escape_string(trim($content));

        // don't insert if no desc and content
        if ($description == '' && $content == '') return true;

        if (!$this->isFieldsValid($checkID, $type)) return false;

        $sql = "INSERT INTO " . TABLE_PREFIX . "check_examples
				('check_id', 'type', 'description', 'content')
				VALUES
				(" . $checkID . "," . $type . ",'" . $description . "', " .
            "'" . $content . "')";

        if (!$this->execute($sql)) {
            $msg->addError('DB_NOT_UPDATED');
            return false;
        } else {
            return $this->db->insert_id;
        }
    }

    /**
     * return rows with given check id and type
     * @access  public
     * @param   $checkID
     *          $type
     * @return  table rows
     * @author  Cindy Qi Li
     */
    public function getByCheckIDAndType($checkID, $type) {
        $sql = "SELECT * FROM " . TABLE_PREFIX . "check_examples
				WHERE check_id = " . intval($checkID) . "
				  AND type = " . $this->db->real_escape_string($type);

        return $this->execute($sql);
    }

    /**
     * Return check id's of all examples
     * @access  public
     * @return  mixed check_id list
     * @author  Joel Carvalho
     */
    public function getAllCheckID() {
        $sql = "SELECT * FROM " . TABLE_PREFIX . "check_examples ORDER BY check_id";

        return $this->execute($sql);
    }

    /**
     * Delete all entries with given check id
     * @access  public
     * @param   $checkID
     * @return  true : if successful
     *          false : if not successful
     * @author  Cindy Qi Li
     */
    public function DeleteByCheckID($checkID) {
        $sql = "DELETE FROM " . TABLE_PREFIX . "check_examples
				WHERE check_id = " . intval($checkID);

        return $this->execute($sql);
    }

    /**
     * Validate fields preparing for insert and update
     * @access  private
     * @param   $checkID
     *          $type
     * @return  true    if all fields are valid
     *          false   if any field is not valid
     * @author  Cindy Qi Li
     */
    private function isFieldsValid($checkID, $type) {
        global $msg;

        $missing_fields = array();

        if ($checkID == '') {
            $missing_fields[] = _AC('check_id');
        }
        if ($type <> AC_CHECK_EXAMPLE_FAIL && $type <> AC_CHECK_EXAMPLE_PASS) {
            $missing_fields[] = _AC('example_type');
        }

        if ($missing_fields) {
            $missing_fields = implode(', ', $missing_fields);
            $msg->addError(array('EMPTY_FIELDS', $missing_fields));
        }

        if (!$msg->containsErrors())
            return true;
        else
            return false;
    }
}

?>