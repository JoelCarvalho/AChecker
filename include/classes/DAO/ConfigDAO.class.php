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
 * @package   DAO
 */
class ConfigDAO extends DAO {

    /**
     * Return all config' information
     * @access  public
     * @param   none
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getAll() {
        $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'config ORDER BY name';
        return $this->execute($sql);
    }
}

?>