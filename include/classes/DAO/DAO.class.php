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

use \mysqli;

/**
 * Root Data Access Object
 * Each table has a DAO class, all inherits from this class
 * @package     DAO
 * @author      Cindy Qi Li
 * @author      Joel Carvalho
 * @note        Mysql calls have been updated for Mysqli:
 *              mysql extension was deprecated in PHP 5.5.0, and it was removed in PHP 7.0.0.
 * @version     1.6.1 30/08/2015
 */
class DAO{
    protected $db;

    function __construct() {
        global $db; // global database connection
        if (!isset($this->db) && isset($db))
            $this->db=$db;
        if (!isset($this->db)) {
            $this->db = new mysqli(DB_HOST.':'.DB_PORT, DB_USER, DB_PASSWORD);
            error_log('[QCHECKER] [DB_INFO] Connect '.DB_HOST.':'.DB_PORT);
            if ($this->db->connect_errno>0)
                die('Unable to connect to db.');
            if (!$this->db->select_db(DB_NAME))
                die('DB connection established, but database "'.DB_NAME.'" cannot be selected.');
            error_log('[QCHECKER] [DB_INFO] '.$this->db->stat());
            error_log('[QCHECKER] [DB_INFO] '.$this->db->get_client_info());
            $db=$this->db;
        }
    }

    /**
     * Execute SQL Statement
     * @access      public
     * @param       $sql SQL statement to be executed
     * @return      mixed return retrieved rows, true for non-select sql and false if fail
     * @author      Cindy Qi Li
     * @author      Joel Carvalho
     * @version     1.6.1 30/08/2015
     */
    public function execute($sql) {
        $sql = trim($sql);
        $result = $this->db->query($sql) or die($sql . "<br />" . $this->db->error);

        // Deal with "select" statement: return false if no row is returned, otherwise, return an array
        if ($result !== true && $result !== false) {
            $rows = false;

            while ($row = $result->fetch_assoc()) {
                if (!$rows) $rows = array();

                $rows[] = $row;
            }
            $result->free_result();
            return $rows;
        }
        return true;
    }

    public function getDB(){
        return $this->db;
    }
}
?>