<?php namespace QChecker\Utils;
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

use QChecker\DAO\GuidelinesDAO;
use QChecker\DAO\UserLinksDAO;
use QChecker\Validator\HTMLRpt;
use QChecker\Validator\AccessibilityValidator;

if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");

include_once(AC_INCLUDE_PATH . 'classes/Validator/HTMLRpt.class.php');
include_once(AC_INCLUDE_PATH . 'classes/DAO/GuidelinesDAO.class.php');
include_once(AC_INCLUDE_PATH . 'classes/DAO/UserLinksDAO.class.php');
include_once(AC_INCLUDE_PATH . 'classes/Validator/AccessibilityValidator.class.php');

/**
 * HTMLWebServiceOutput.class.php
 * This file defines all the html templates used to generate web service html output
 */
class HTMLWebServiceOutput {

    // all private
    var $css=QCHECKER_CSS;
    var $js=QCHECKER_JS;

    var $html_main =
        '<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html><head>
<title>QChecker Report</title>
<link rel="stylesheet" href="{CSS}/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="{CSS}/shuffle.css" type="text/css" />
<link rel="stylesheet" href="{CSS}/report.css" type="text/css" />

<script src="{JS}/lib/jquery-2.1.4.min.js" type="text/javascript"></script>
<script src="{JS}/lib/jquery-URLEncode.js" type="text/javascript"></script>
<script src="{JS}/lib/bootstrap-3.3.5.min.js" type="text/javascript"></script>
<script src="{JS}/lib/shuffle.modernizr.min.js" type="text/javascript"></script>
<script src="{JS}/AChecker.js" type="text/javascript"></script>
{SCRIPT}
</head>
<body>
<div class="main-report pull-right col-lg-5 col-md-12 col-xs-12">
    <div class="margintopnav">&nbsp;</div>
    <h2>{REVIEW_TITLE}</h2>
    <h3><i>{URL_CHECKED} on {CONTEXT}</i></h3>
    {GUIDELINE_SECTION}
    <strong>Result: </strong> {SUMMARY}
    <p>{WEBCRAWLER_INFO}</p>
    <form name="file_form" enctype="multipart/form-data" method="post">
        <input type="hidden" name="jsessionid" value="{JSESSION_ID}" />
        <input type="hidden" name="userid" value="{USER_ID}" />
        <input type="hidden" name="output" value="html" />
        <input type="hidden" name="server" value="{SERVER}" />
        <input type="hidden" name="uri" value="{URI}" />
    {DETAIL}
    {BUTTON_MAKE_DECISION}
    </form>
</div>
<div class="code-report col-lg-7 visible-lg">
    <pre><code id="code-editor">{ORIGINAL_HTML}</code></pre>
    <script src="{JS}/lib/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
</div>
<div id="render" class="render col-lg-7 visible-lg"></div>
<script src="{JS}/lib/pym.min.js" type="text/javascript" ></script>
<script>
    var editor = ace.edit("code-editor");
    editor.setTheme("ace/theme/monokai");
    editor.getSession().setMode("ace/mode/html");
    var pymParent = new pym.Parent("render", "{RENDER_HTML}", {});
    function wc_find(id){
        ace.edit("code-editor").find(\'wc_id="\'+id+\'"\');
        pymParent.sendMessage("scroll", id.toString());
    }
</script>
';

    var $html_summary =
        '<span style="color: {COLOR2}; background-color: {COLOR}; border: solid black; padding-right: 1em; padding-left: 1em">{SUMMARY}</span>&nbsp;&nbsp;
<span style="color:{COLOR}; background-color: {COLOR2}">{SUMMARY_DETAIL}</span>';

    var $html_a =
        '<a title="{TITLE}" target="_new" href="{HREF}">{TITLE}</a>';

    var $html_button_make_decision =
        '<p align="center">
<input value="Make Decisions" type="button" id="AC_btn_make_decision_" />
</p>';

    var $html_detail =
        '<div id="{DIV_ID}" style="margin-top:1em">
    <h4>{DETAIL_TITLE} <span class="review-qtinfo">(<span class="review-qt"></span>)</span></h4>
	{DETAIL}
</div>';

    var $aValidator;                  // from parameter. instance of AccessibilityValidator
    var $userLinkID;                  // from parameter. user_links.user_link_id
    var $guidelineIDs;                // from parameter. array of guideline IDs
    var $guidelineGroups;             // from parameter. array of guideline ID and Group/Subgroup
    var $htmlRpt;                     // instance of HTMLRpt. Generate error detail

    var $guidelineStr;                // used to replace $html_main.{GUIDELINE}. Generated by setGuidelineStr()
    var $summaryStr;                  // used to replace $html_main.{SUMMARY}. Generated by setSummaryStr()
    var $mainStr;                     // main output. Generated by setMainStr()

    /**
     * Constructor
     * @access  public
     * @param   AccessibilityValidator $aValidator Instance of AccessibilityValidator. Call $aValidator->validate(); before pass in the instance
     * @param   string $userLinkID
     * @param   array $guidelineIDs array of guideline IDs
     * @author  Cindy Qi Li
     * @author  Joel Carvalho
     * @version 1.6.3 20/09/2015
     */
    function __construct($aValidator, $userLinkID, $guidelineIDs, $guidelineGroups) {
        $this->aValidator = $aValidator;
        $this->guidelineIDs = $guidelineIDs;
        $this->userLinkID = $userLinkID;
        $this->guidelineGroups = $guidelineGroups;

        $this->htmlRpt = new HTMLRpt($aValidator, $userLinkID);
        $this->htmlRpt->setAllowSetDecisions('true');
        $this->htmlRpt->generateRpt();

        // setGuidelineStr() & setSummaryStr() must be called before setMainStr()
        $this->setGuidelineStr();       // set $this->guidelineStr
        $this->setSummaryStr();         // set $this->summaryStr
        $this->setMainStr();            // set $this->mainStr
    }

    /**
     * set guideline string used to replace $html_main.{GUIDELINE}
     * @access  private
     * @author  Cindy Qi Li
     */
    private function setGuidelineStr(){
        if (is_array($this->guidelineIDs)){
            $guidelinesDAO = new GuidelinesDAO();
            $rows = $guidelinesDAO->getGuidelineByIDs($this->guidelineIDs);

            $this->guidelineStr="<h3><strong>"._AC("guidelines").":</strong> ";
            if (is_array($rows)) {
                foreach ($rows as $id => $row) {
                    $this->guidelineStr .= str_replace(array('{TITLE}', '{HREF}'),
                            array($row['title'],
                                AC_BASE_HREF . 'guideline/view_guideline.php?id=' . $row['guideline_id']),
                            $this->html_a) . "&nbsp;&nbsp;";
                }
            }
            $this->guidelineStr.="</h3>";
        }

        if (count($this->guidelineGroups)==0)
            return "";
        $strOp="";
        foreach ($this->guidelineGroups as $op) {
            if ($strOp!=="") $strOp.=', ';
            $strOp.="<a href='".AC_BASE_HREF."guideline/view_guideline.php?id=".$op["guideline_id"]."' target='_blank'>".strtoupper($op["abbr"])."</a>";
        }
        if ($strOp!=="")
            $this->guidelineStr.="<h3><strong>Guideline Groups:</strong> ".$strOp."</h3>";
    }

    /**
     * set summary string used to replace $html_main.{SUMMARY}
     * @access  private
     * @author  Cindy Qi Li
     */
    private function setSummaryStr(){
        // generate $html_summary.{SUMMARY}
        if ($this->htmlRpt->getNumOfErrors() > 0) {
            $summary = _AC('fail');
            $color = 'red';
            $color2 = 'white';
        } else if ($this->htmlRpt->getNumOfLikelyProblemsFail()+$this->htmlRpt->getNumOfPotentialProblemsFail() > 0) {
            $summary = _AC('conditional_pass');
            $color = 'yellow';
            $color2 = 'black';
        } else {
            $summary = _AC('pass');
            $color = 'green';
            $color2 = 'white';
        }

        // generate $html_summary.{SUMMARY_DETAIL}
        $summary_detail = '<span style="font-weight: bold;"><span id="problems_found">'.
            $this->aValidator->getNumOfValidateError().'</span> '._AC("problems_found").' @ '.
            date("G:i d M Y", time()).'</span>';

        $this->summaryStr = str_replace(array('{COLOR}', '{COLOR2}', '{SUMMARY}', '{SUMMARY_DETAIL}'),
            array($color, $color2, $summary, $summary_detail),
            $this->html_summary);
    }

    /**
     * set main report
     * @access      private
     * @author      Cindy Qi Li
     * @author      Joel Carvalho
     * @version     1.6.4 03/11/2015
     */
    private function setMainStr(){
        global $context_combined_name;
        // get $html_main.{SESSIONID}
        $userLinksDAO = new UserLinksDAO();
        $row = $userLinksDAO->getByUserLinkID($this->userLinkID);
        $sessionID = $row['last_sessionID'];

        $detailsArray=array();
        $detailsArray["known"]= array(
            "qt" => $this->htmlRpt->getNumOfErrors(),
            "title" => _AC('known_problems'),
            "res" => '',
            "div" => 'known_problems_section',
            "report" => $this->htmlRpt->getRptErrors());
        $detailsArray["likely"]= array(
            "qt" => $this->htmlRpt->getNumOfLikelyProblems(),
            "title" => _AC('likely_problems'),
            "res" => '',
            "div" => 'likely_problems_section',
            "report" => $this->htmlRpt->getRptLikelyProblems());
        $detailsArray["potential"]= array(
            "qt" => $this->htmlRpt->getNumOfPotentialProblems(),
            "title" => _AC('potential_problems'),
            "res" => '',
            "div" => 'potential_problems_section',
            "report" => $this->htmlRpt->getRptPotentialProblems());
        $detailsArray["ok"]= array(
            "qt" => $this->htmlRpt->getNumOfCheckOk(),
            "title" => _AC('ok_report'),
            "res" => '',
            "div" => 'ok_review_section',
            "report" => $this->htmlRpt->getRptOk());
        $detailsArray["skipped"]= array(
            "qt" => $this->htmlRpt->getNumOfCheckSkipped(),
            "title" => _AC('skipped_report'),
            "res" => '',
            "div" => 'skipped_review_section',
            "report" => $this->htmlRpt->getRptSkipped());

        foreach($detailsArray as $i => $e){
            if ($e["qt"] > 0) {
                $detailsArray[$i]["res"] = str_replace(array('{DETAIL_TITLE}', '{DIV_ID}', '{DETAIL}'),
                    array($e["title"], $e["div"], $e["report"]),
                    $this->html_detail);
            }
        }

        // generate $html_main.{DETAIL}
        $detail = $this->htmlRpt->generateMenu();

        $detail .= '<div id="shuffle_grid">'.
            $detailsArray["known"]["res"].
            $detailsArray["likely"]["res"].
            $detailsArray["potential"]["res"].
            $detailsArray["ok"]["res"].
            $detailsArray["skipped"]["res"].
            '</div>';

        if(strrpos($_SERVER['REQUEST_URI'],'http')!=0)
          $_SERVER['REQUEST_URI']=preg_replace('/\/api\/check.php/',QCHECKER_SERVER.'/api/check.php',$_SERVER['REQUEST_URI'],1);

        $api_link="<a href='".$_SERVER['REQUEST_URI']."'>".$this->aValidator->getURL()."</a>";
        // set display of "make decision" button
        if ($this->htmlRpt->getNumOfNoDecisions() > 0)
            $button_make_decision = $this->html_button_make_decision;

        if (isset($this->aValidator->getWebcrawlerInfo()['html_ofile'])){
            $html_original=@file_get_contents($this->aValidator->getWebcrawlerInfo()['html_file']);
            $html_original=Utility::clearWCTags($html_original);
        }
        else
            $html_original=@file_get_contents($this->aValidator->getURL());

        $html_original=Utility::prettiffyHTML($html_original);
        $render_file=$this->aValidator->getWebcrawlerInfo();
        $render_file=$render_file['html_file'];

        // set main string
        $this->mainStr = str_replace(array('{SESSIONID}',
            '{SUMMARY}',
            '{WEBCRAWLER_INFO}',
            '{SCRIPT}',
            '{REVIEW_TITLE}',
            '{CONTEXT}',
            '{GUIDELINE_SECTION}',
            '{URL_CHECKED}',
            '{DETAIL}',
            '{BUTTON_MAKE_DECISION}',
            '{ORIGINAL_HTML}',
            '{RENDER_HTML}',
            '{CSS}',
            '{JS}',
            '{URI}',
            '{SERVER}',
            '{JSESSION_ID}',
            '{USER_ID}'),
            array($sessionID,
                $this->summaryStr,
                $this->htmlRpt->generateWebCrawlerInfo(),
                $this->setScript(),
                _AC("quality_review"),
                $context_combined_name,
                $this->guidelineStr,
                $api_link,
                $detail,
                $button_make_decision,
                $html_original,
                $render_file,
                $this->css,
                $this->js,
                $_GET['uri'],
                'http://'.$_SERVER[HTTP_HOST],
                $_POST["jsessionid"],
                $_SESSION["user_id"]),
            $this->html_main
        );
        $this->mainStr.='</body></html>';
    }

    /**
     * return main report
     * @access  public
     * @return  string html
     * @author  Cindy Qi Li
     */
    public function getWebServiceOutput(){
        return $this->mainStr;
    }

    function setScript(){
        $_custom_head = '	<script language="javascript" type="text/javascript">'."\n".
                         '	//<!--'."\n";

        ob_start();
        require_once(AC_INCLUDE_PATH.'../checker/js/checker_js.php');
        $_custom_head .= ob_get_contents();
        ob_end_clean();

        $_custom_head .= '	//-->'."\n".
            '	</script>'."\n".
            '	<script src="'.AC_BASE_HREF.'checker/js/checker.js" type="text/javascript"></script>'."\n";

        return $_custom_head;
    }
}

?>