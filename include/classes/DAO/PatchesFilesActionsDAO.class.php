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
 * DAO for "patches_files_actions" table
 * @access    public
 * @author    Cindy Qi Li
 * @package    DAO
 */
class PatchesFilesActionsDAO extends DAO
{

    /**
     * Create new row
     * @access  public
     * @param   $patches_files_id , $action, $code_from, $code_to
     * @return  patches_files_actions_id, if successful
     *          false and add error into global var $msg, if unsuccessful
     * @author  Cindy Qi Li
     */
    public function Create($patches_files_id, $action, $code_from, $code_to) {
        global $msg;
        $sql = "INSERT INTO " . TABLE_PREFIX . "patches_files_actions " .
            "(patches_files_id,
					   action,
					   code_from,
					   code_to)
					  VALUES
					  (" . $patches_files_id . ",
					   '" . $action . "',
					   '" . $this->db->real_escape_string($code_from) . "',
					   '" . $this->db->real_escape_string($code_to) . "')";

        if (!$this->execute($sql)) {
            $msg->addError('DB_NOT_UPDATED');
            return false;
        } else {
            return $this->db->insert_id;
        }
    }
}

?>