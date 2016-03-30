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
 * DAO for "patches_files" table
 * @access    public
 * @author    Cindy Qi Li
 * @package    DAO
 */
class PatchesFilesDAO extends DAO
{

    /**
     * Create new row
     * @access  public
     * @param   patch_id , action, $name, $location
     * @return  patches_files_id, if successful
     *          false and add error into global var $msg, if unsuccessful
     * @author  Cindy Qi Li
     */
    public function Create($patch_id, $action, $name, $location) {
        global $msg;
        $sql = "INSERT INTO " . TABLE_PREFIX . "patches_files " .
            "(patches_id,
					   action,
					   name,
					   location)
					  VALUES
					  (" . $patch_id . ",
					   '" . $action . "',
					   '" . $this->db->real_escape_string($name) . "',
					   '" . $this->db->real_escape_string($location) . "')";

        if (!$this->execute($sql)) {
            $msg->addError('DB_NOT_UPDATED');
            return false;
        } else {
            return $this->db->insert_id;
        }
    }

    /**
     * Return number of times that the given file have been updated by Updater
     * @access  public
     * @param   $file : file name
     * @return  number of times
     * @author  Cindy Qi Li
     */
    public function getNumOfUpdatesOnFile($file) {
        $sql = "SELECT count(*) num_of_updates FROM " . TABLE_PREFIX . "patches patches, " . TABLE_PREFIX . "patches_files patches_files " .
            "WHERE patches.applied_version = '" . QCHECKER_VERSION . "' " .
            "  AND patches.status = 'Installed' " .
            "  AND patches.patches_id = patches_files.patches_id " .
            "  AND patches_files.name = '" . $file . "'";

        return $this->execute($sql);
    }
}

?>