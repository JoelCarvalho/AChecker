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
 * DAO for "color_mapping" table
 * @access    public
 * @author    Cindy Qi Li
 * @package   DAO
 */
class ColorMappingDAO extends DAO{

    /**
     * Return lang code info of the given 2 letters code
     * @access  public
     * @param   $code : 2 letters code
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function GetByColorName($colorName) {


        $sql = "SELECT * FROM " . TABLE_PREFIX . "color_mapping WHERE color_name='" . $this->db->real_escape_string($colorName) . "'";
        return $this->execute($sql);
    }

}

?>