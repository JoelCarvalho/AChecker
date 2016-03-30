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
 * DAO for "context" table
 * @access    public
 * @author    Joel CArvalho
 * @package   DAO
 * @version   1.0 30/04/2015
 */
class ContextDAO extends DAO {

    /**
     * Create a new context
     * @access  public
     * @param   string $driver_name
     * @param   string $description
     * @param   string $service_url
     * @param   int $host_id
     * @param   int $browser_id
     * @return  mixed $context_id or false
     * @author  Joel Carvalho
     * @version 1.0 30/04/2015
     */
    public function Create($driver_name, $description, $service_url, $host_id, $browser_id) {
        global $msg;
        $host_id = intval($host_id);
        $browser_id = intval($browser_id);

        $sql = "INSERT INTO " . TABLE_PREFIX . "context
				('driver_name', 'host_id', 'browser_id')
				VALUES
				('".$driver_name."',".$host_id.",".$browser_id.")";

        if (!$this->execute($sql)) {
            $msg->addError('DB_NOT_UPDATED');
            return false;
        } else {
            $contextID = $this->db->insert_id;

            if ($description!=null){
                $description = trim($description);
                $this->updateDescription($contextID, $description);
            }
            if ($service_url!=null){
                $service_url = trim($service_url);
                $this->updateServiceURL($contextID, $service_url);
            }
            return $contextID;
        }
    }

    /**
     * Update a existing context
     * @access  public
     * @param   string $driver_name
     * @param   string $description
     * @param   string $service_url
     * @param   int $host_id
     * @param   int $browser_id
     * @return  boolean
     * @author  Joel Carvalho
     * @version 1.0 30/04/2015
     */
    public function Update($contextID, $driver_name, $description, $service_url, $host_id, $browser_id)  {
        global $msg;
        $host_id = intval($host_id);
        $browser_id = intval($browser_id);
        $contextID = intval($contextID);

        $sql = "UPDATE " . TABLE_PREFIX . "context
				   SET `driver_name`='".$driver_name."',
				       `host_id` = " . $host_id . ",
				       `browser_id` = " . $browser_id . "
				 WHERE context_id = " . $contextID;

        if (!$this->execute($sql)) {
            $msg->addError('DB_NOT_UPDATED');
            return false;
        } else {
            if ($description!=null){
                $description = trim($description);
                $this->updateDescription($contextID, $description);
            }
            if ($service_url!=null){
                $service_url = trim($service_url);
                $this->updateServiceURL($contextID, $service_url);
            }
            return true;
        }
    }

    /**
     * Delete a context by context ID
     * @access  public
     * @param   int $contextID
     * @return  boolean
     * @author  Joel Carvalho
     * @version 1.0 30/04/2015
     */
    function Delete($contextID) {
        $contextID = intval($contextID);

        $sql = "DELETE FROM " . TABLE_PREFIX . "context
            WHERE context_id=" . $contextID;
        return $this->execute($sql);
    }

    /**
     * Return all context
     * @access  public
     * @param   none
     * @return  mixed
     * @author  Joel Carvalho
     * @version 1.6.3 13/10/2015
     */
    function getAll() {
        $sql = "SELECT c.context_id as context_id, CONCAT(h.name,'.',c.driver_name) as combined_name,
            c.driver_name as driver_name, c.driver_type as driver_type, c.description as description,
            c.service_url as service_url, c.remote_host_id as remote_host_id, h.host_id as host_id,
            h.name as host_name, h.service_url as host_service_url, o.os_id as os_id, o.name as os_name,
            b.browser_id as browser_id, b.name as browser_name, LEAST(c.active, h.active) as active
            FROM ".TABLE_PREFIX."context as c, ".TABLE_PREFIX."host as h,".TABLE_PREFIX."browser as b, ".TABLE_PREFIX."operating_system as o
            WHERE c.browser_id=b.browser_id AND
                c.host_id=h.host_id AND
                o.os_id=h.os_id
            ORDER BY active DESC, h.name ASC, c.driver_name ASC";

        $res=$this->execute($sql);
        return $res;
    }

    /**
     * Return the combined name (host name + driver name) of a context
     * @access  public
     * @param   none
     * @return  mixed combined name or false
     * @author  Joel Carvalho
     * @version 1.1 08/05/2015
     */
    function getCombinedName($context_id) {
        $sql = "SELECT CONCAT(h.name,'.',c.driver_name) as name, c.remote_host_id FROM ".TABLE_PREFIX."context as c, ".TABLE_PREFIX."host as h
            WHERE c.host_id=h.host_id AND c.context_id=".$context_id;
        $res=$this->execute($sql);
        if (is_array($res[0])) {
            if ($res[0]["remote_host_id"]!=null){
                $rh=$this->getHostInfo($res[0]["remote_host_id"]);
                if (is_array($rh[0]))
                    return ($res[0]["name"])."-".$rh[0]["name"];
            }
            return ($res[0]["name"]);
        }
        else return false;
    }

    /**
     * Return service url from context or host given a context_id
     * @access  public
     * @param   int $context_id
     * @return  mixed service_url or false
     * @author  Joel Carvalho
     * @version 1.1 08/05/2015
     */
    function getServiceURL($context_id) {
        $context=$this->getContextByID($context_id);
        if (is_array($context) && array_key_exists("driver_name",$context))
            $driver_name=$context["driver_name"];
        else false;

        $sql = "SELECT service_url, remote_host_id FROM " . TABLE_PREFIX . "context
            WHERE context_id=".intval($context_id);
        $res=$this->execute($sql);
        if (is_array($res[0]) && $res[0]["remote_host_id"]==null && $res[0]["service_url"]!=null
                && count($res[0]["service_url"])>0)
            return $res[0]["service_url"]."/".$driver_name;

        $sql = "SELECT h.service_url as service_url FROM " . TABLE_PREFIX . "context as c, " . TABLE_PREFIX . "host as h
        WHERE c.host_id=h.host_id AND
            c.context_id=".intval($context_id);
        $res=$this->execute($sql);
        if (is_array($res[0]) && count($res[0]["service_url"])>0)
            return $res[0]["service_url"]."/".$driver_name;

        return false;
    }

    /**
     * Return Host info
     * @access  public
     * @param   int $host_id
     * @return  table rows
     * @author  Joel Carvalho
     * @version 1.0 30/04/2015
     */
    function getHostInfo($host_id) {
        $sql = "SELECT * FROM " . TABLE_PREFIX . "host
            WHERE host_id=".intval($host_id);
        return $this->execute($sql);
    }

    /**
     * Return Browser info
     * @access  public
     * @param   int browser_id
     * @return  table rows
     * @author  Joel Carvalho
     * @version 1.0 30/04/2015
     */
    function getBrowserInfo($browser_id) {
        $sql = "SELECT * FROM " . TABLE_PREFIX . "browser
            WHERE browser_id=".intval($browser_id);
        return $this->execute($sql);
    }

    /**
     * Return all context from a host
     * @access  public
     * @param   mixed
     * @return  table rows
     * @author  Joel Carvalho
     * @version 1.1 14/05/2015
     */
    function getAllContextByHost($host) {
        if (is_int($host))
            $sql = "SELECT * FROM " . TABLE_PREFIX . "context
                WHERE host_id=". intval($host);
        else
            $sql = "SELECT c.* FROM " . TABLE_PREFIX . "context as c, ".TABLE_PREFIX."host as h
                WHERE c.host_id=h.host_id AND
                    h.name LIKE '".$host."'";
        $sql.=" ORDER BY driver_name ASC";
        return $this->execute($sql);
    }

    /**
     * Return all context with some browser
     * @access  public
     * @param   mixed
     * @return  table rows
     * @author  Joel Carvalho
     * @version 1.0 30/04/2015
     */
    function getAllContextByBrowser($browser) {
        if (is_int($browser))
            $sql = "SELECT * FROM " . TABLE_PREFIX . "context
                WHERE browser_id=". intval($browser);
        else
            $sql = "SELECT c.* FROM " . TABLE_PREFIX . "context as c,".TABLE_PREFIX."browser as b
                WHERE c.browser_id=b.browser_id AND
                    b.name LIKE '".$browser."'";
        return $this->execute($sql);
    }

    /**
     * Return context info of given context id
     * @access  public
     * @param   int $contextID
     * @return  table rows
     * @author  Joel Carvalho
     * @version 1.0 30/04/2015
     */
    function getContextByID($contextID) {
        $sql = "SELECT c.context_id as context_id, CONCAT(h.name,'.',c.driver_name) as combined_name, c.driver_name as driver_name, c.description as description,
            c.service_url as service_url, h.host_id as host_id, h.name as host_name, h.service_url as host_service_url,
            o.os_id as os_id, o.name as os_name, b.browser_id as browser_id, b.name as browser_name
            FROM ".TABLE_PREFIX."context as c, ".TABLE_PREFIX."host as h,".TABLE_PREFIX."browser as b, ".TABLE_PREFIX."operating_system as o
            WHERE c.context_id=".intval($contextID)." AND
                c.browser_id=b.browser_id AND
                c.host_id=h.host_id AND
                o.os_id=h.os_id";

        $rows = $this->execute($sql);

        if (is_array($rows))
            return $rows[0];
        else
            return false;
    }

    /**
     * Update a existing context description
     * @access  public
     * @param   int $contextID
     * @param   string description
     * @return  boolean
     * @author  Joel Carvalho
     * @version 1.0 30/04/2015
     */
    function updateDescription($contextID, $description){
        global $msg;
        $sql = "UPDATE " . TABLE_PREFIX . "context
				   SET `description`='".$description."',
				 WHERE context_id = " . $contextID;

        if (!$this->execute($sql)) {
            $msg->addError('DB_NOT_UPDATED');
            return false;
        } else
            return true;
    }

    /**
     * Update a existing context service url
     * @access  public
     * @param   int $contextID
     * @param   string description
     * @return  boolean
     * @author  Joel Carvalho
     * @version 1.0 30/04/2015
     */
    function updateServiceURL($contextID, $service_url){
        global $msg;
        $sql = "UPDATE " . TABLE_PREFIX . "context
				   SET `description`='".$service_url."',
				 WHERE context_id = " . $contextID;

        if (!$this->execute($sql)) {
            $msg->addError('DB_NOT_UPDATED');
            return false;
        } else
            return true;
    }

    /**
     * Get context Id given the context combined name
     * @access  public
     * @param   string $cName
     * @param   string description
     * @return  boolean
     * @author  Joel Carvalho
     * @version 1.6.3 19/09/2015
     */
    function getContextIdByCombinedName($cName){
        list($host,$driver)=explode('.',$cName, 2);
        $sql = "SELECT context_id FROM " . TABLE_PREFIX . "context as c, ".TABLE_PREFIX."host as h
                WHERE c.host_id=h.host_id AND
                    h.name LIKE '".$host."' AND
                    c.driver_name LIKE '".$driver."'";
        $res=$this->execute($sql);
        return ($res[0]['context_id']);
    }
}

?>