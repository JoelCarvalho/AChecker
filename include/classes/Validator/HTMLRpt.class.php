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

if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");
include_once(AC_INCLUDE_PATH . 'classes/DAO/UserDecisionsDAO.class.php');
include_once(AC_INCLUDE_PATH . 'classes/Validator/AccessibilityRpt.class.php');
include_once(AC_INCLUDE_PATH . 'classes/DAO/GuidelinesDAO.class.php');

use QChecker\DAO\ChecksDAO;
use QChecker\DAO\UserDecisionsDAO;
use QChecker\Utils\Utility;

/**
 * HTMLRpt.class.php
 * Class to generate error report in html format
 * @author      Cindy Qi Li
 * @author      Joel Carvalho
 * @package     checker
 * @version     1.6.3 20/09/2015
 */
class HTMLRpt extends AccessibilityRpt {
    // all private
    var $html_problem_achecker = '
        <li class="{MSG_TYPE} shuffle-item"
            data-groups=\'["accessibility", "{IMG_SRC}"]\'
            data-tagname="{TAGNAME}"
            data-checkpoint="{CHECK_ID}"
            data-line="{LINE_NUMBER}"
            style="width:100%">
            <div class="error-info">
                <span class="err_type"><img src="{BASE_HREF}images/{IMG_SRC}" alt="{IMG_TYPE}" title="{IMG_TYPE}"/></span>
                <span class="msg check_selector">
                    <a href="{BASE_HREF}checker/suggestion.php?id={CHECK_ID}" target="_blank" class="check_abbr">{CHECK_ABBR}</a>: {ERROR}
                </span>
                <em>&nbsp;&nbsp;<i>({ELEMENT_ID}{LINE_INFO})</i></em>
                <a class="error-more" onclick="return false;">[+<span class="error-moreqt"></span>]</a>
            </div>
            <div class="extra-info">
                <pre class="code-editor-small"><code>{HTML_CODE}</code></pre>
                {IMAGE}
                <p class="helpwanted">
                {REPAIR}
                {DECISION}
                </p>
                <br/>
            </div>
            <!--<hr class="error-line"/>-->
        </li>';

    var $html_notproblem_achecker = '
        <li class="{MSG_TYPE} shuffle-item"
            data-groups=\'["accessibility", "{IMG_SRC}"]\'
            data-tagname="{TAGNAME}"
            data-checkpoint="{CHECK_ID}"
            style="width:100%">
            <span class="err_type"><img src="{BASE_HREF}images/{IMG_SRC}" alt="{IMG_TYPE}" title="{IMG_TYPE}" /></span>
            <span class="msg check_selector">
                <a href="{BASE_HREF}checker/suggestion.php?id={CHECK_ID}" target="_blank" class="check_abbr">{CHECK_ABBR}</a>: {ERROR}
            </span>
        </li>';

    var $html_image = '
        <img src="{SRC}" height="{HEIGHT}" border="1" {ALT} />';

    var $html_repair = '
            <span style="font-weight:bold">{REPAIR_LABEL}: </span>{REPAIR_DETAIL}';

    var $html_decision_not_made =
        '<table class="table-decision">
  <tr>
   <td class="question">
      {QUESTION}
   </td>
  </tr>
  <tr>
    <td>
      <input value="P" type="radio" name="d[{LINE_NUM}_{COL_NUM}_{CHECK_ID}]" id="pass{LINE_NUM}_{COL_NUM}_{CHECK_ID}" class="AC_childCheckBoxAPI" {PASS_CHECKED} />
      <label for="pass{LINE_NUM}_{COL_NUM}_{CHECK_ID}">{DECISION_PASS}</label>
   </td>
  </tr>
  <tr>
    <td>
	  <input value="F" type="radio" name="d[{LINE_NUM}_{COL_NUM}_{CHECK_ID}]" id="fail{LINE_NUM}_{COL_NUM}_{CHECK_ID}" class="AC_childCheckBoxAPI" {FAIL_CHECKED} />
      <label for="fail{LINE_NUM}_{COL_NUM}_{CHECK_ID}">{DECISION_FAIL}</label>
    </td>
  </tr>
  <tr>
    <td>
	  <input value="N" type="radio" name="d[{LINE_NUM}_{COL_NUM}_{CHECK_ID}]" id="nodecision{LINE_NUM}_{COL_NUM}_{CHECK_ID}" class="AC_childCheckBoxAPI" {NODECISION_CHECKED} />
      <label for="nodecision{LINE_NUM}_{COL_NUM}_{CHECK_ID}">{DECISION_NO}</label>
    </td>
  </tr>
</table>
';

    var $html_decision_made =
        '<table class="form-data">
  <tr>
    <th align="left">{LABEL_QUESTION}:</th>
    <td>{QUESTION}</td>
  </tr>
  <tr>
    <th align="left">{LABEL_DECISION}:</th>
    <td>{DECISION}</td>
  </tr>
  <tr>
    <th align="left">{LABEL_DATE}:</th>
    <td>{DATE}</td>
  </tr>
  {REVERSE_DECISION}
</table>
';

    var $html_reverse_decision =
        '  <tr>
    <td colspan="2">
	  <input value="{LABEL_REVERSE_DECISION}" type="submit" name="reverse[{LINE_NUM}_{COL_NUM}_{CHECK_ID}]" />
    </td>
  </tr>
';

    var $html_source =
        '	<ol class="source">
{SOURCE_CONTENT}
	</ol>
';

    var $html_source_line =
        '		<li id="line-{LINE_ID}">{LINE}</li>
';

    /**
     * public
     * $errors: an array, output of AccessibilityValidator -> getValidationErrorRpt
     * $type: html
     * @param AccessibilityValidator $aValidator
     * @param string $user_link_id
     */
    function __construct($aValidator, $user_link_id = ''){
        parent::__construct($aValidator, $user_link_id);
    }

    /**
     * public
     * main process to generate report in html format
     */
    public function generateRpt(){
        global $msg;

        // user_link_id must be given to show decision section
        if ((!isset($this->user_link_id) || $this->user_link_id == '') && $this->allow_set_decision == 'true') {
            $msg->addError('NONE_USER_LINK');
            return false;
        }

        // initialize each section
        $this->rpt_errors = "<ul>\n";
        $this->rpt_likely_problems = "<ul>\n";
        $this->rpt_potential_problems = "<ul>\n";
        $this->rpt_ok = "<ul>\n";
        $this->rpt_skipped = "<ul>\n";

        $checksDAO = new ChecksDAO();
        // generate section details
        foreach ($this->errors as $error) {
            $row = $checksDAO->getCheckByID($error["check_id"]);
            if ($row["confidence"] == KNOWN) { // no decision to make on known problems
                $this->num_of_errors++;

                $this->rpt_errors .= $this->generate_problem_section($error, _AC($row["err"]), _AC($row["how_to_repair"]), '', IS_ERROR);
            } else if ($row["confidence"] == LIKELY) {
                $this->num_of_likely_problems++;
                if ($this->allow_set_decision == 'false' && !($this->from_referer == 'true' && $this->user_link_id > 0)) {
                    $this->rpt_likely_problems .= $this->generate_problem_section($error, _AC($row["err"]), _AC($row["how_to_repair"]), '', IS_WARNING);
                    $this->num_of_likely_problems_fail++;
                } else {
                    $this->generate_cell_with_decision($row, $error["line_number"], $error["col_number"], $error["element_id"], $error["html_code"], $error['image'], $error["image_alt"], $error["error_msg"], IS_WARNING);
                }
            } else if ($row["confidence"] == POTENTIAL) {
                $this->num_of_potential_problems++;
                if ($this->allow_set_decision == 'false' && !($this->from_referer == 'true' && $this->user_link_id > 0)) {
                    $this->rpt_potential_problems .= $this->generate_problem_section($error, _AC($row["err"]), _AC($row["how_to_repair"]), '', IS_INFO);
                    $this->num_of_potential_problems_fail++;
                } else {
                    $this->generate_cell_with_decision($row, $error["line_number"], $error["col_number"], $error["element_id"], $error["html_code"], $error['image'], $error["image_alt"], $error["error_msg"], IS_INFO);
                }
            }
        }

        foreach($this->num_array["ok"] as $ok=>$qt){
            $row = $checksDAO->getCheckByID($ok);
            $this->rpt_ok .=$this->generate_notproblem_section($ok,_AC($row["name"]),IS_OK);
        }

        foreach($this->num_array["skipped"] as $skipped=>$qt){
            $row = $checksDAO->getCheckByID($skipped);
            $this->rpt_skipped .=$this->generate_notproblem_section($skipped,_AC($row["name"]),IS_SKIPPED);
        }

        if ($this->allow_set_decision == 'true' ||
            ($this->allow_set_decision == 'false' && $this->from_referer == 'true' && $this->user_link_id > 0)){
            $this->rpt_likely_problems .= $this->rpt_likely_decision_not_made . $this->rpt_likely_decision_made;
            $this->rpt_potential_problems .= $this->rpt_potential_decision_not_made . $this->rpt_potential_decision_made;
        }

        $this->rpt_errors .= "</ul>";
        $this->rpt_likely_problems .= "</ul>";
        $this->rpt_potential_problems .= "</ul>";
        $this->rpt_ok .= "</ul>";
        $this->rpt_skipped .= "</ul>";

        if ($this->show_source == 'true')
            $this->generateSourceRpt();
    }

    /**
     * private
     * generate html output with decision. In html output, the errors with no decision made are display at the top,
     * followed by errors that decisions have been made. This method also calculates number of errors based on made decisions.
     * If a decision is made as pass, the error is ignored without adding into number of errors.
     * parameters:
     * $check_row: table row of the check
     * $line_number: line number that the error happens
     * $col_number: column number that the error happens
     * $html_tag: html tag that the error happens
     * $error_type: IS_WARNING or IS_INFO
     */
    private function generate_cell_with_decision($check_row, $line_number, $col_number, $element_id, $html_code, $image, $image_alt, $error_msg, $error_type)
    {
        // generate decision section
        $userDecisionsDAO = new UserDecisionsDAO();
        $row = $userDecisionsDAO->getByUserLinkIDAndLineNumAndColNumAndCheckID($this->user_link_id, $line_number, $col_number, $check_row['check_id']);


        if (!$row || $row['decision'] == AC_DECISION_FAIL) { // no decision or decision of fail
            if ($error_type == IS_WARNING) $this->num_of_likely_problems_fail++;
            if ($error_type == IS_INFO) $this->num_of_potential_problems_fail++;
        }

        if (!$row) // no decision
        {
            if ($this->allow_set_decision == 'true') {
                $decision_section = str_replace(array("{LINE_NUM}",
                    "{COL_NUM}",
                    "{CHECK_ID}",
                    "{PASS_CHECKED}",
                    "{FAIL_CHECKED}",
                    "{NODECISION_CHECKED}",
                    "{QUESTION}",
                    "{DECISION_PASS}",
                    "{DECISION_FAIL}",
                    "{DECISION_NO}"),
                    array($line_number,
                        $col_number,
                        $check_row['check_id'],
                        "",
                        "",
                        'checked="checked"',
                        _AC($check_row['question']),
                        _AC($check_row['decision_pass']),
                        _AC($check_row['decision_fail']),
                        _AC('no_decision')),
                    $this->html_decision_not_made);
            }
            // generate problem section
            $_error['check_id']=$check_row['check_id'];
            $_error['line_number']=$line_number;
            $_error['col_number']=$col_number;
            $_error['element_id']=$element_id;
            $_error['html_code']=$html_code;
            $_error['image']=$image;
            $_error['image_alt']=$image_alt;
            $_error['error_msg']=$error_msg;
            $problem_section = $this->generate_problem_section($_error, _AC($check_row['err']), _AC($check_row['how_to_repair']), $decision_section, $error_type);
            if ($error_type == IS_WARNING) $this->rpt_likely_decision_not_made .= $problem_section;
            if ($error_type == IS_INFO) $this->rpt_potential_decision_not_made .= $problem_section;

            $this->num_of_no_decisions++;
        } else {
            if ($row['decision'] == AC_DECISION_PASS) $decision = $check_row['decision_pass'];
            if ($row['decision'] == AC_DECISION_FAIL) $decision = $check_row['decision_fail'];

            if ($this->allow_set_decision == 'true') {
                $reverse_decision = str_replace(array("{LABEL_REVERSE_DECISION}",
                    "{LINE_NUM}",
                    "{COL_NUM}",
                    "{CHECK_ID}"),
                    array(_AC('reverse_decision'),
                        $line_number,
                        $col_number,
                        $check_row['check_id']),
                    $this->html_reverse_decision);
            }

            $decision_section = str_replace(array("{LABEL_DECISION}",
                "{QUESTION}",
                "{DECISION}",
                "{LABEL_QUESTION}",
                "{LABEL_USER}",
                "{LABEL_DATE}",
                "{DATE}",
                "{REVERSE_DECISION}"),
                array(_AC('decision'),
                    _AC($check_row['question']),
                    _AC($decision),
                    _AC('question'),
                    _AC('user'),
                    _AC('date'),
                    $row['last_update'],
                    $reverse_decision),
                $this->html_decision_made);

            // generate problem section
            $_error['check_id']=$check_row['check_id'];
            $_error['line_number']=$line_number;
            $_error['col_number']=$col_number;
            $_error['html_code']=$html_code;
            $_error['image']=$image;
            $_error['image_alt']=$image_alt;
            $_error['element_id']=$check_row['element_id'];
            $_error['error_msg']=$error_msg;
            $problem_section = $this->generate_problem_section($_error, _AC($check_row['err']), _AC($check_row['how_to_repair']), $decision_section, $error_type);

            if ($error_type == IS_WARNING) $this->rpt_likely_decision_made .= $problem_section;
            if ($error_type == IS_INFO) $this->rpt_potential_decision_made .= $problem_section;

            $this->num_of_made_decisions++;
        }
    }

    /**
     * Generate html code for a problem section with specified parameters
     * @access private
     * @param mixed $error
     * @param string $error_text
     * @param string $error_type
     * @param string $decision
     * @param string $repair
     * @return string
     */
    private function generate_problem_section($error, $error_text, $repair, $decision, $error_type){
        $html_image = "";
        $html_repair = "";
        $checksDAO = new ChecksDAO();
        if ($this->show_source == 'true')
            $error["line_number"] = '<a href="checker/index.php#line-' . $error["line_number"] . '">' . $error["line_number"] . '</a>';

        if ($error_type == IS_ERROR) {
            $msg_type = "msg_err";
            $img_type = _AC('error');
            $img_src = "error.png";
        } else if ($error_type == IS_WARNING) {
            $msg_type = "msg_info";
            $img_type = _AC('warning');
            $img_src = "warning.png";
        } else if ($error_type == IS_INFO) {
            $msg_type = "msg_info";
            $img_type = _AC('manual_check');
            $img_src = "info.png";
        }

        // generate repair string
        if ($repair <> '') {
            $html_repair = str_replace(array('{REPAIR_LABEL}', '{REPAIR_DETAIL}'),
                array(_AC("repair"), $repair), $this->html_repair);
            if (strlen($error["error_msg"])>0)
                $html_repair.='<br/><b>Info:</b> <span class="extra-repair-info">'.$error["error_msg"]."</span>";
        }

        if ($error["image"] <> '') {
            // COMMENTTED OUT the way to determine the image display size by measuring the actual image size
            // since the fetch of the remote images slows down the process a lot.
//			$dimensions = getimagesize($image);
//			if ($dimensions[1] > DISPLAY_PREVIEW_IMAGE_HEIGHT) $height = DISPLAY_PREVIEW_IMAGE_HEIGHT;
//			else $height = $dimensions[1];

            $height = DISPLAY_PREVIEW_IMAGE_HEIGHT;

            if ($error["image_alt"] == '_NOT_DEFINED') $alt = '';
            else if ($error["image_alt"] == '_EMPTY') $alt = 'alt=""';
            else $alt = 'alt="' . $error["image_alt"] . '"';

            $html_image = str_replace(array("{SRC}", "{HEIGHT}", "{ALT}"), array($error["image"], $height, $alt), $this->html_image);
        }
        $check=$checksDAO->getCheckByID($error["check_id"]);

        $element_id='';
        if ($error['element_id']!=""){
            $element_id="#<span class='wc_id'>".$error['element_id']."</span>, ";
            $link="wc_find(".$error["element_id"].");";
        }
        else $link = "ace.edit('code-editor').gotoLine(".$error["line_number"].",true);";
        $line_link="<a onclick=\"".$link."\">"._AC('line')." ".$error["line_number"]."</a>";

        return str_replace(array("{MSG_TYPE}",
            "{IMG_SRC}",
            "{IMG_TYPE}",
            "{LINE_INFO}",
            "{LINE_NUMBER}",
            "{ELEMENT_ID}",
            "{TAGNAME}",
            "{HTML_CODE}",
            "{ERROR}",
            "{BASE_HREF}",
            "{CHECK_ABBR}",
            "{CHECK_ID}",
            "{TITLE}",
            "{IMAGE}",
            "{REPAIR}",
            "{DECISION}"),
            array($msg_type,
                $img_src,
                $img_type,
                $line_link,
                $error["line_number"],
                $element_id,
                $error["tag_name"],
                Utility::prettiffyHTML($error["html_code"]),
                $error_text,
                AC_BASE_HREF,
                $check["abbr"],
                $error["check_id"],
                _AC('suggest_improvements'),
                $html_image,
                $html_repair,
                $decision),
            $this->html_problem_achecker);
    }

    /**
     * Generate html code for a non problem section like OK and SKIPPED
     * @access private
     * @param int $check_id
     * @param string $error
     * @param string $error_type
     * @return  string
     * @author  Joel Carvalho
     * @version 1.6.0 28/05/2015
     */
    private function generate_notproblem_section($check_id, $error, $error_type){
        $tagname=$this->aValidator->getTagName($check_id);
        $checksDAO=new ChecksDAO();

        if ($error_type == IS_OK) {
            $msg_type = "msg_ok";
            $img_type = _AC('ok');
            $img_src = "ok.png";
        } else if ($error_type == IS_SKIPPED) {
            $msg_type = "msg_skipped";
            $img_type = _AC('skipped');
            $img_src = "skipped.png";
        }
        $check=$checksDAO->getCheckByID($check_id);
        return str_replace(array("{MSG_TYPE}",
            "{IMG_SRC}",
            "{IMG_TYPE}",
            "{TAGNAME}",
            "{BASE_HREF}",
            "{ERROR}",
            "{CHECK_ABBR}",
            "{CHECK_ID}"),
            array($msg_type,
                $img_src,
                $img_type,
                $tagname,
                AC_BASE_HREF,
                $error,
                $check["abbr"],
                $check_id
            ),
            $this->html_notproblem_achecker);
    }

    // generate $this->rpt_source
    public function generateSourceRpt()
    {
        $source_content="";
        if (count($this->source_array) == 0) return;

        $line_num = 1;
        foreach ($this->source_array as $line) {
            $source_content .= str_replace(array("{LINE_ID}", "{LINE}"),
                array($line_num, htmlspecialchars($line)),
                $this->html_source_line);
            $line_num++;
        }

        $this->rpt_source = str_replace("{SOURCE_CONTENT}", $source_content, $this->html_source);
    }

    /**
     * return the filter menu
     * @access  public
     * @return  string
     * @author  Joel Carvalho
     * @version 1.6.0 28/05/2015
     */
    public function generateMenu(){
        return '
        <div id="main-menu" class="row col-lg-5 col-md-12 col-xs-12">
            <div class="filter-group filter">
                <div class="main-menu-options">
                    <div class="filter-options btn-group">
                        <button class="btn btn--danger btn-xs" data-group="error.png">'.
                            _AC("known_problems_abv").' (<span id="known_problems">'.$this->getNumOfErrors().'</span>) </button>
                        <button class="btn btn--danger btn-xs" data-group="warning.png">'.
                            _AC("likely_problems_abv").' (<span id="likely_problems">'.$this->getNumOfLikelyProblemsFail().'</span>) </button>
                        <button class="btn btn--danger btn-xs" data-group="info.png">'.
                            _AC("potential_problems_abv").' (<span id="potential_problems">'.$this->getNumOfPotentialProblemsFail().'</span>) </button>
                        <button class="btn btn--go btn-xs" data-group="ok.png">'.
                            _AC("ok_report_abv").' (<span id="check_ok">'.$this->getNumOfCheckOk().'</span>) </button>
                        <button class="btn btn--go btn-xs" data-group="skipped.png">'.
                            _AC("skipped_report_abv").' (<span id="check_skipped">'.$this->getNumOfCheckSkipped().'</span>) </button>
                    </div>
                    <div class="pull-right">
                        <select class="sort-options btn btn-xs">
                            <option value="line">Line Number</option>
                            <option value="checkpoint">Checkpoint</option>
                            <option value="tagname">Tag Name</option>
                        </select>
                    </div>
                </div>
                <div class="main-menu-search row-fluid">
                    <input class="filter__search js-shuffle-search btn span12" type="search" placeholder="Search..."/>
                </div>
            </div>
        </div>
        ';
    }

    /**
     * return error report in html
     * parameters: $errors: errors array
     * author: Cindy Qi Li
     * @access public
     */
    public static function generateErrorRpt($errors)
    {
        $error_detail="";
        // html error template
        $html_error =
            '<div id="error">
	<h4>{ERROR_MSG_TITLE}</h4>
	{ERROR_DETAIL}
</div>';

        $html_error_detail =
            '		<ul>
			<li>{ERROR}</li>
		</ul>
';
        if (!is_array($errors)) return false;

        foreach ($errors as $err) {
            $error_detail .= str_replace("{ERROR}", _AC($err), $html_error_detail);
        }

        return str_replace(array('{ERROR_MSG_TITLE}', '{ERROR_DETAIL}'),
            array(_AC('the_follow_errors_occurred'), $error_detail),
            $html_error);
    }

    /**
     * public
     * return success in html
     * parameters: none
     * author: Cindy Qi Li
     */
    public static function generateSuccessRpt()
    {
        $html_success =
            '<div id="success">Success</div>';
        return $html_success;
    }

    /**
     * Generate and return webcrawler info if it exists, or only review execution time info
     * @access  public
     * @return  string
     * @author  Joel Carvalho
     * @version 1.6.3 29/09/2015
     */
    public function generateWebCrawlerInfo(){
        global $testExecutionTime;
        $report='';
        $testExecutionTime=number_format(microtime(true)-$testExecutionTime,3);
        $webcrawlerInfo=$this->aValidator->getWebcrawlerInfo();
        if (isset($webcrawlerInfo['page_loading_time'])){
            $checkerTime=number_format(($testExecutionTime*1000-$webcrawlerInfo['execution_time']*1000-$webcrawlerInfo['page_loading_time'])/1000,3);
            $report.='<br/><span><b>'._AC(TIMES).':</b> ';
            $report.='Page ('.$webcrawlerInfo['page_loading_time'].'ms) | ';
            $report.='WebCrawler ('.$webcrawlerInfo['execution_time'].'ms) | ';
            $report.='Checker ('.$checkerTime.'ms)</span>';
        }
        $report.='<br/><span><b>'._AC(REVIEW_EXECUTION_TIME).':</b> '.$testExecutionTime.'ms';
        $report=str_replace(array('(0.',' 0.','.'),array('(',' ', ' '),$report);
        if (isset($webcrawlerInfo['html_file'])){
            $report.=' (<a href="'.$webcrawlerInfo['html_file'].'" target="_blank">Html</a>, <a href="'.$webcrawlerInfo['img_file'].'" target="_blank">Img</a>, ';
            $report.='<a href="'.$webcrawlerInfo['html_ofile'].'" target="_blank">Original Html</a>, <a href="'.$webcrawlerInfo['img_ofile'].'" target="_blank">Original Img</a>)';
        }
        $report.='</span>';
        return $report;
    }
}

?>
