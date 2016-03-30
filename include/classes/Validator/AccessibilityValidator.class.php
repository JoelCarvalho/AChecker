<?php namespace QChecker\Validator;
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

use QChecker\DAO\ChecksDAO;
use QChecker\Utils\Utility;
use QChecker\DAO\ContextDAO;

if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");

include(AC_INCLUDE_PATH . "lib/simple_html_dom.php");
include(AC_INCLUDE_PATH . "lib/htmLawed.php");

include_once(AC_INCLUDE_PATH . "classes/Validator/BasicChecks.class.php");
include_once(AC_INCLUDE_PATH . "classes/Validator/BasicFunctions.class.php");
include_once(AC_INCLUDE_PATH . "classes/Validator/CheckFuncUtility.class.php");
include_once(AC_INCLUDE_PATH . "classes/DAO/ChecksDAO.class.php");
include_once(AC_INCLUDE_PATH . "classes/DAO/ContextDAO.class.php");

define("SUCCESS_RESULT", "success");
define("FAIL_RESULT", "fail");
define("SKIP_RESULT", "skip");
define("DISPLAY_PREVIEW_HTML_LENGTH", 1000);

// Hack for "easy" functions invocation
function filterElement($a){return BasicFunctions::filterElement($a);}
function setErrorMsg($a){return BasicFunctions::setErrorMsg($a);}
function skipCheck($a){return BasicFunctions::skipCheck($a);}

function assertAttributeValue($a){return BasicFunctions::assertAttributeValue($a);}
function assertAttributeConsistency($a){return BasicFunctions::assertAttributeConsistency($a);}
function assertTrue($a){return BasicFunctions::assertTrue($a);}
function assertEquals($a,$b){return BasicFunctions::assertEquals($a,$b);}
function assertNotEquals($a,$b){return BasicFunctions::assertNotEquals($a,$b);}
function assertContrast($a,$b,$c=null){return BasicFunctions::assertContrast($a,$b,$c);}

function getAttributeValue($a,$b=null){return BasicFunctions::getAttributeValue($a,$b);}
function getAttributeLength($a){return BasicFunctions::getAttributeLength($a,null);}

/**
 * AccessibilityValidator.class.php
 * Class for accessibility validate
 * This class checks the accessibility of the given html based on requested guidelines.
 * @access    public
 * @author    Cindy Qi Li
 * @author    Joel Carvalho
 * @version   1.6 27/05/2015
 * @package   checker
 */
class AccessibilityValidator {
    /** Array Number of success */
    protected $num_success = array();
    /** Array Number of skipped */
    protected $num_skipped = array();
    /** Array Number of errors */
    protected $num_errors = array();
    /** Array Number of checkpoints used */
    protected $num_checked = array();
    /** Array of Checkpoints specified by guidelines and checks array */
    protected $num_checks = array();

    /** Number of errors @var int */
    protected $num_of_errors = 0;
    /** HTML content to check */
    protected $validate_content;
    /** Guidelines Array to check on */
    protected $guidelines;
    /** Specified Checks Array to check on */
    protected $checks;
    /** URI that $validate_content is from, used in check image size in BasicFunctions */
    protected $uri;

    /**
     * Array Result with all check results, including success ones and failed ones
     * Object Structure: line_number, check_id, result (success, fail)
     */
    protected $result = array();

    /** Array of the to-be-checked check_ids */
    protected $check_for_all_elements_array = array();
    /** Array of the to-be-checked check_ids */
    protected $check_for_tag_array = array();
    /** Array of prerequisite check_ids of the to-be-checked check_ids */
    protected $prerequisite_check_array = array();
    /** Array of all the check functions */
    protected $check_func_array = array();

    /** Dom of $validate_content */
    protected $content_dom;
    /**
     * 1. ignore the problems on the lines before the line of $line_offset
     * 2. report line_number = real_line_number - $line_offset
     */
    protected $line_offset;
    /**
     * The number of characters that are added internally at the first line to deal with the
     * partial html. Fully private, cannot be set or get from outside
     */
    protected $col_offset;

    // UAChecker
    protected $context; // Context By Joel Carvalho
    protected $webcrawlerInfo;

    /**
     * @access public
     * @param array $guidelines guidelines to check on
     * @param array $checks specified checks to check on
     * @param string $uri uri for images validation
     * @param int $context context_id to check
     */
    function __construct($guidelines, $checks, $uri = '', $context=null) {
        if (!is_array($guidelines)) $this->guidelines=array();
        else $this->guidelines = $guidelines;
        if (!is_array($checks)) $this->checks=array();
        else $this->checks = $checks;
        $this->line_offset = 0;
        $this->col_offset = 0;
        $this->uri = $uri;
        $this->setContext($context); // Context By Joel Carvalho
        $this->preValidate();
    }

    /**
     * Guideline and Checkpoints Preparation and Validation
     * @access public
     */
    public function validate() {
        $this->validate_element($this->content_dom->find('html'));
        $this->finalize();
    }

    /**
     * Pre Validation Instructions (Preparation)
     * @author Joel Carvalho
     * @version 1.0 02/04/2015
     * @access private
     */
    private function preValidate() {
        // dom of the content to be validated
        $this->content_dom = $this->get_simple_html_dom($this->validate_content);

        // prepare gobal vars used in BasicFunctions.class.php to fasten the validation
        $this->prepare_global_vars();

        // set arrays of check_id, prerequisite check_id, next check_id
        $this->prepare_check_arrays();

        $this->prepare_webcrawler_vars();
    }

    /**
     * Convert webcrawler global vars into class vars
     * @author Joel Carvalho
     * @version 1.6 11/06/2015
     * @access private
     */
    private function prepare_webcrawler_vars(){
        $body = $this->content_dom->find('body', 0);
        if ($body!=null && $body->getAttribute(WC.'id')>0){
            $body=$this->content_dom->find('body',0);
            $cString=$body->getAttribute(WC.'system-colors');
            $cArray=explode(';',$cString);
            foreach($cArray as $c){
                $tuple=explode(':',$c);
                if (count($tuple)==2)
                    $this->system_colors[$tuple[0]]=Utility::rgbaConvert($tuple[1]);
            }
            $this->webcrawlerInfo['page_loading_time']=$body->getAttribute(WC.'loading-time');
            $this->webcrawlerInfo['execution_time']=
                number_format(($body->getAttribute(WC.'execution-time')-$body->getAttribute(WC.'loading-time'))/1000,3);
            $this->webcrawlerInfo['request_time']=$body->getAttribute(WC.'request-time');
            $this->webcrawlerInfo['html_file']=$body->getAttribute(WC.'html-file');
            $this->webcrawlerInfo['img_file']=$body->getAttribute(WC.'img-file');
            $this->webcrawlerInfo['html_ofile']=$body->getAttribute(WC.'html-file:original');
            $this->webcrawlerInfo['img_ofile']=$body->getAttribute(WC.'img-file:original');
            $this->webcrawlerInfo['session_id']=$body->getAttribute(WC.'session-id');
        }
    }

    /**
     * set global vars used in Checks.class.php and BasicFunctions.class.php
     * to fasten the validation process.
     * return nothing.
     * @access private
     */
    private function prepare_global_vars(){
        global $header_array, $base_href;

        // find all header tags which are used in BasicFunctions.class.php
        $header_array = $this->content_dom->find("h1, h2, h3, h4, h5, h6, h7");

        // find base href, used to check image size
        $all_base_elements = $this->content_dom->find("base");

        if (is_array($all_base_elements)) {
            foreach ($all_base_elements as $base) {
                if (isset($base->attr['href'])) {
                    $base_href = $base->attr['href'];
                    break;
                }
            }
        }

        // set all check functions
        $checksDAO = new ChecksDAO();
        $rows = $checksDAO->getAllOpenChecks();
        if (is_array($rows)) {
            foreach ($rows as $row)
                $this->check_func_array[$row['check_id']] = CheckFuncUtility::convertCode($row['func']);
        }
    }

    /**
     * Set the Context of AChecker Validator
     * @author Joel Carvalho
     * @version 1.6.3 17/09/2015
     * @access public
     */
    private function setContext($context_id) {
        error_log('[QCHECKER] [CONTEXT] Set => '.$context_id);
        $context = new ContextDAO();
        $this->context=$context->getContextByID($context_id);
        if (!empty($this->context['context_id'])){
            $service=$context->getServiceURL($context_id);
            if (isset($_REQUEST['port'])) {
                $service = preg_replace(array('/(.*)(:[0-9]+\/api)(.*)/', '/(.*[^0-9])(\/api)(.*)/'), array('${1}:' . $_REQUEST['port'] . '/api${3}', '${1}:' . $_REQUEST['port'] . '/api${3}'), $service);
                $service_api=preg_replace('/\/[^\/]+$/','',$service);
                Utility::stop404($service_api.'/version');
            }
            if (isset($_REQUEST['resolution'])){
                $service.='+'.$_REQUEST['resolution'];
            }
            if (isset($_REQUEST['config']))
                $service.="/".$_REQUEST['config'];
            error_log("[WEBCRAWLER] ".$service);
            ini_set('default_socket_timeout', 240);
            $uri = str_replace("#","{sharp}",$this->uri);
            $this->validate_content = @file_get_contents($service."/".$uri);
            if (empty($this->validate_content))
                throw new \Exception('Page Not Found.');
        } else {
            $this->context=null;
            $this->validate_content = @file_get_contents($this->uri);
        }
    }

    /**
     * return a simple_html_dom on the given content.
     * Because accessibility check is based on the root html element <html>,
     * check if dom has html tag <html>, if no, add it and the end tag to the content
     * and return the dom on modified content.
     * @access private
     */
    private function get_simple_html_dom($content){
        global $msg;

        $dom = str_get_dom($content);

        if (count($dom->find('html')) == 0) {
            $complete_html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' .
                '<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">' .
                $content .
                '</html>';
            $this->col_offset = 175;  // The number of extra characters that are added onto the first line.

            $dom = str_get_dom($complete_html);
        }
        return $dom;
    }

    /**
     * generate arrays of check ids, prerequisite check ids, next check ids
     * array structure:
     * check_array
     * (
     * [html_tag] => Array
     * (
     * [0] => check_id 1
     * [1] => check_id 2
     * ...
     * )
     * ...
     * )
     *
     * prerequisite_check_array
     * (
     * [check_id] => Array
     * (
     * [0] => prerequisite_check_id 1
     * [1] => prerequisite_check_id 2
     * ...
     * )
     * ...
     * )
     *
     * //     next_check_array
     * //     (
     * //     [check_id] => Array
     * //     (
     * //     [0] => next_check_id 1
     * //     [1] => next_check_id 2
     * //     ...
     * //     )
     * ...
     * )
     * @access private
     */
    private function prepare_check_arrays() {
        if (empty($this->guidelines) && empty($this->checks))
            return false;
        else {
            // validation process
            $checksDAO = new ChecksDAO();
            // generate array of "all element"
            $rows = $checksDAO->getOpenChecksForAllByGuidelineIDs($this->guidelines);
            $rows2 = $checksDAO->getOpenChecksForAllByCheckIDs($this->checks);
            $rows=Utility::mergeRows($rows,$rows2);

            $count = 0;
            foreach ($rows as $id => $row) {
                $this->check_for_all_elements_array[$count++] = $row["check_id"];
                $this->addNum($this->num_checks, $row["check_id"]);
            }

            // generate array of check_id
            $rows = $checksDAO->getOpenChecksNotForAllByGuidelineIDs($this->guidelines);
            $rows2 = $checksDAO->getOpenChecksNotForAllByCheckIDs($this->checks);
            $rows=Utility::mergeRows($rows,$rows2);

            $prev_html_tag=null;
            foreach ($rows as $id => $row) {
                if ($row["html_tag"] <> $prev_html_tag && $prev_html_tag <> "") $count = 0;

                $this->check_for_tag_array[$row["html_tag"]][$count++] = $row["check_id"];
                $this->addNum($this->num_checks, $row["check_id"]);
                $prev_html_tag = $row["html_tag"];
            }

            // generate array of prerequisite check_ids
            $rows = $checksDAO->getOpenPreChecksByGuidelineIDs($this->guidelines);
            $rows2 = $checksDAO->getOpenPreChecksByCheckIDs($this->checks);
            $rows=Utility::mergeRows($rows,$rows2);

            $prev_check_id=null;
            foreach ($rows as $id => $row) {
                if ($row["check_id"] <> $prev_check_id) $prerequisite_check_array[$row["check_id"]] = array();

                array_push($prerequisite_check_array[$row["check_id"]], $row["prerequisite_check_id"]);
                $this->addNum($this->num_checks, $row["prerequisite_check_id"]);
                $prev_check_id = $row["check_id"];
            }
            $this->prerequisite_check_array=(isset($prerequisite_check_array))?$prerequisite_check_array:array();

            return true;
        }
    }

    /**
     * Recursive function to validate html elements
     * @access private
     */
    private function validate_element($element_array){
        foreach ($element_array as $e) {
            // generate array of checks for the html tag of this element
            if (array_key_exists($e->tag,$this->check_for_tag_array))
                $check_array[$e->tag] = array_merge($this->check_for_tag_array[$e->tag], $this->check_for_all_elements_array);
            else
                $check_array[$e->tag] = $this->check_for_all_elements_array;

            foreach ($check_array[$e->tag] as $check_id) {
                // check prerequisite ids first, if fails, report failure and don't need to proceed with $check_id
                $prerequisite_failed = false;

                if (array_key_exists($check_id,$this->prerequisite_check_array)) {
                    foreach ($this->prerequisite_check_array[$check_id] as $prerequisite_check_id) {
                        $check_result = $this->check($e, $prerequisite_check_id);

                        if ($check_result == FAIL_RESULT) {
                            $prerequisite_failed = true;
                            break;
                        }
                    }
                }

                // if prerequisite check passes, proceed with current check_id
                if (!$prerequisite_failed) {
                    $check_result = $this->check($e, $check_id);
                }
            }

            $this->validate_element($e->children());
        }
    }

    /**
     * Execute Tests on the method hasDuplicateAttribute
     * @Author Joel Carvalho
     * @version 1.6 - 27/05/2015
     * @access private
     */
    private function addNum(&$aNum ,$id){
        if (isset($aNum[$id])) $aNum[$id]++;
        else $aNum[$id]=1;
    }

    /**
     * Check given html dom node for given check_id, save result into $this->result
     * @param \simple_html_dom_node $e
     * @param int $check_id check id
     * @return mixed "success", "fail" or skip
     * @access private
     * @author AChecker
     * @author Joel Carvalho
     * @version 1.6.3 22/09/2015
     */
    private function check($e, $check_id){
        $this->addNum($this->num_checked, $check_id);
        global $msg, $base_href, $tag_size, $has_duplicate_attribute;
        // don't check the lines before $line_offset
        if ($e->linenumber <= $this->line_offset) return;

        if ($e->linenumber == 1 && $this->col_offset > 0) {
            $col_number = $e->colnumber - $this->col_offset;
        } else {
            $col_number = $e->colnumber;
        }
        $line_number = $e->linenumber - $this->line_offset;

        $result = $this->get_check_result($e->tag_start, $check_id);

        // has not been checked
        if (!$result) {
            try {
                $check_result = eval($this->check_func_array[$check_id]);
            }catch(\Exception $ex){
                if ($ex->getCode()==100){
                    $error_msg=$ex->getMessage();
                    $check_result=false;
                }
                else // Skip
                    $check_result=null;
            }

            $checksDAO = new ChecksDAO();
            $row = $checksDAO->getCheckByID($check_id);

            if (!is_bool($check_result)) {
                // when $check_result is not true/false, something must be wrong with the check function
                // or the check was skipped for some reason (mainly lack of information)
                // $msg->addError(array('CHECK_FUNC', $row['html_tag'] . ': ' . _AC($row['name'])));
                $result = SKIP_RESULT;
                $this->addNum($this->num_skipped,$check_id);
            } else if ($check_result === true)  {
                $result = SUCCESS_RESULT;
                $this->addNum($this->num_success,$check_id);
            } else {
                $result = FAIL_RESULT;
                $this->addNum($this->num_errors,$check_id);

                $image=null; $image_alt=null;
                $preview_html=Utility::clearWCTags($e->outertext());
                $preview_html=hl_tidy($preview_html,'','span');
                if (strlen($preview_html)>DISPLAY_PREVIEW_HTML_LENGTH)
                    $html_code = substr($preview_html, 0, DISPLAY_PREVIEW_HTML_LENGTH) . " ...";
                else
                    $html_code = $preview_html;

                // find out preview images for validation on <img>
                if (strtolower(trim($row['html_tag'])) == 'img') {
                    $image = BasicChecks::getFile($e->attr['src'], $base_href, $this->uri);

                    // find out image alt text for preview image
                    if (!isset($e->attr['alt'])) $image_alt = '_NOT_DEFINED';
                    else if ($e->attr['alt'] == '') $image_alt = '_EMPTY';
                    else $image_alt = $e->attr['alt'];
                }
                // If its a duplicate ID, switch the line number from the element line (body)
                // to the line where the duplicate ID appears.
                // '(new ChecksDAO())->hasDuplicateAttCheck($check_id)' - Modified By Joel Carvalho to solve a issue when ID's are duplicated
                if (is_array($has_duplicate_attribute) && (new ChecksDAO())->hasDuplicateAttCheck($check_id)) {
                    $line_number = $has_duplicate_attribute["linenumber"];
                    $html_code = "Found duplicated ".$has_duplicate_attribute["attr"]." => " .
                        $has_duplicate_attribute["value"] . "\n\n".substr(($has_duplicate_attribute["html"]),0,100);
                }
                $this->save_result($line_number, $col_number, $e->tag, $html_code, $check_id, $e->attr[WC."id"],$result, $image, $image_alt, $error_msg, $e->tag_start);
            }
        }
        return $result;
    }

    /**
     * get number of success checkpoints
     * @access public
     * @return mixed
     */
    public function getNumSuccess(){
        return $this->num_success;
    }

    /**
     * Get number of success checkpoints without fails
     * @access      public
     * @return      mixed
     * @author      Joel Carvalho
     * @version     1.6 30/05/2015
     */
    public function getNumSuccessFiltered(){
        $filtered_success=array();
        foreach($this->num_success as $id=>$value) {
            if (!array_key_exists($id, $this->num_errors))
                $this->addNum($filtered_success, $id);
        }
        return $filtered_success;
    }

    /**
     * get number of skipped checkpoints without fails
     * @access      public
     * @return      mixed
     * @author      Joel Carvalho
     * @version     1.6 02/06/2015
     */
    public function getNumSkippedFiltered(){
        $filtered_skipped=array();
        foreach($this->num_skipped as $id=>$value) {
            if (!array_key_exists($id, $this->num_errors) && !array_key_exists($id, $this->num_success))
                $this->addNum($filtered_skipped, $id);
        }
        return $filtered_skipped;
    }

    /**
     * get number of skipped checkpoints
     * @access public
     * @return mixed
     */
    public function getNumSkipped(){
        return $this->num_skipped;
    }

    /**
     * get number of errors per checkpoint
     * @access public
     * @return mixed
     */
    public function getNumErrors(){
        return $this->num_errors;
    }

    /**
     * get number of checkpoints checked
     * @access public
     * @return mixed
     */
    public function getNumChecked(){
        return $this->num_checked;
    }

    /**
     * get all checkpoints specified by guidelines and checks array
     * @access public
     * @return mixed
     */
    public function getNumChecks(){
        return $this->num_checks;
    }

    /**
     * get check result from $result. Return false if the result is not found.
     * Parameters:
     * $line_number: line number in the content for this check
     * $check_id: check id
     * @access private
     * @author Joel Carvalho
     * @version 1.6.3 27/09/2015
     */
    private function get_check_result($tag_start, $check_id){
        foreach ($this->result as $one_result) {
            if ($one_result["tag_start"] == $tag_start && $one_result["check_id"] == $check_id)
                return $one_result["result"];
        }
        return false;
    }

    /**
     * save each check result
     * Parameters:
     * $line_number: line number in the content for this check
     * $check_id: check id
     * $result: result to save
     * @access private
     * @author AChecker
     * @author Joel Carvalho
     * @version 1.6.3 22/09/2015
     */
    private function save_result($line_number, $col_number, $tag_name, $html_code, $check_id, $element_id,$result, $image, $image_alt, $error_msg, $tag_start){
        array_push($this->result, array(
            "line_number"   => $line_number,
            "col_number"    => $col_number,
            "tag_name"      => $tag_name,
            "html_code"     => $html_code,
            "check_id"      => $check_id,
            "element_id"    => $element_id,
            "result"        => $result,
            "image"         => $image,
            "image_alt"     => $image_alt,
            "error_msg"     => $error_msg,
            "tag_start"     => $tag_start));
        return true;
    }

    /**
     * convert the given array to a string of the array elements separated by the given delimiter.
     * For example:
     * array ([0] => 7, [1] => 8)
     * delimiter: ,
     * is converted to string "7, 8"
     * @access private
     */
    private function convert_array_to_string($in_array, $delimiter){
        $count = 0;
        $str='';

        if (is_array($in_array)) {
            foreach ($in_array as $element) {
                if ($count == 0) $str = $element;
                else $str .= $delimiter . $element;

                $count++;
            }
            return $str;
        } else
            return false;
    }

    /**
     * generate class value: array of error results, number of errors
     * @access private
     */
    private function finalize(){
        $this->num_of_errors = count($this->result);

        ksort($this->num_success);
        ksort($this->num_errors);
        ksort($this->num_checked);
        ksort($this->num_checks);
        foreach($this->num_checks as $id=>$value) {
            if (!array_key_exists($id, $this->num_checked))
                $this->addNum($this->num_skipped, $id);
        }
        ksort($this->num_skipped);
    }

    /**
     * set line offset
     * @access public
     */
    public function setLineOffset($lineOffset){
        $this->line_offset = $lineOffset;
    }

    /**
     * return line offset
     * @access public
     */
    public function getLineOffset(){
        return $this->line_offset;
    }

    /**
     * return array of all checks that have been done, including successful and failed ones
     * @access public
     */
    public function getValidationErrorRpt(){
        return $this->result;
    }

    /**
     * return number of errors
     * @access public
     */
    public function getNumOfValidateError(){
        return $this->num_of_errors;
    }

    /**
     * return array of all checks that have been done by check id, including successful and failed ones
     * @access public
     */
    public function getResultsByCheckID($check_id){
        $rtn = array();
        foreach ($this->result as $oneResult)
            if ($oneResult["check_id"] == $check_id)
                array_push($rtn, array("line_number" => $oneResult["line_number"], "col_number" => $oneResult["col_number"], "check_id" => $oneResult["check_id"], "result" => $oneResult["result"]));

        return $rtn;
    }

    /**
     * return array of all checks that have been done by line number, including successful and failed ones
     * @access public
     */
    public function getResultsByLine($line_number){
        $rtn = array();
        foreach ($this->result as $oneResult)
            if ($oneResult["line_number"] == $line_number)
                array_push($rtn, array("line_number" => $oneResult["line_number"], "col_number" => $oneResult["col_number"], "check_id" => $oneResult["check_id"], "result" => $oneResult["result"]));

        return $rtn;
    }

    /**
     * get the uri checked
     * @access public
     * @return string
     */
    public function getURL(){
        $url = str_replace("{sharp}","#",$this->uri);
        return $url;
    }

    /**
     * get tagname of specified checkpoint
     * @access public
     * @param int $check_id
     * @return string
     */
    public function getTagName($check_id){
        foreach($this->check_for_tag_array as $tag=>$ids){
            if (in_array($check_id,$ids))
                return $tag;
        }
        return 'all';
    }

    /**
     * Get the actual context
     * @return mixed
     * @access private
     * @author Joel Carvalho
     * @version 1.6 11/06/2015
     */
    public function getContext(){
        return $this->context;
    }

    /**
     * Get the actual Context Combined Name
     * @return string
     * @access private
     * @author Joel Carvalho
     * @version 1.6 11/06/2015
     */
    public function getContextCombinedName(){
        if (is_array($this->context) && array_key_exists('combined_name', $this->context))
            return $this->context['combined_name'];
        return QCHECKER_NAME;
    }

    /**
     * Get WebCrawler info
     * @return string
     * @access private
     * @author Joel Carvalho
     * @version 1.6 11/06/2015
     */
    public function getWebcrawlerInfo(){
        return $this->webcrawlerInfo;
    }
}

?>
