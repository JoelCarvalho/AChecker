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

use QChecker\Utils\Utility;

if (!defined('AC_INCLUDE_PATH')) exit;

require_once(AC_INCLUDE_PATH . 'classes/DAO/DAO.class.php');
require_once(AC_INCLUDE_PATH . 'classes/Utility.class.php');

/**
 * DAO for "mail_queue" table
 * @access    public
 * @author    Cindy Qi Li
 * @package    DAO
 */
class MailQueueDAO extends DAO
{

    /**
     * Create a record
     * @access  public
     * @param   infos
     * @return  mail_queue_id: if success
     *          false: if unsuccess
     * @author  Cindy Qi Li
     */
    function Create($to_email, $to_name, $from_email, $from_name, $subject, $body, $charset) {
        $to_email = $this->db->real_escape_string($to_email);
        $to_name = $this->db->real_escape_string($to_name);
        $from_email = $this->db->real_escape_string($from_email);
        $from_name = $this->db->real_escape_string($from_name);
        $subject = $this->db->real_escape_string($subject);
        $body = $this->db->real_escape_string($body);
        $charset = $this->db->real_escape_string($charset);

        $sql = "INSERT INTO " . TABLE_PREFIX . "mail_queue
						VALUES (NULL, '$to_email', '$to_name', '$from_email', '$from_name', '$charset', '$subject', '$body')";

        if ($this->execute($sql)) {
            return $this->db->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Return all records
     * @access  public
     * @param   none
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function GetAll() {
        $sql = "SELECT * FROM " . TABLE_PREFIX . "mail_queue";

        return $this->execute($sql);
    }

    /**
     * Delete a record by mail ids
     * @access  public
     * @param   $mids : mail IDs, for example: "1, 2, 3"
     * @return  true: if successful
     *          false: if unsuccessful
     * @author  Cindy Qi Li
     */
    function DeleteByIDs($mids) {
        if (!is_array($mids)) return false;

        $sanitized_mids = Utility::sanitizeIntArray($mids);
        $sanitized_mids_str = implode(",", $sanitized_mids);

        $sql = "DELETE FROM " . TABLE_PREFIX . "mail_queue WHERE mail_id IN (" . $sanitized_mids_str . ")";

        return $this->execute($sql);
    }

}

?>