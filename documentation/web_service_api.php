<?php
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

/**
* @ignore
*/
define('AC_INCLUDE_PATH', '../include/');

include(AC_INCLUDE_PATH.'vitals.inc.php');
include(AC_INCLUDE_PATH.'header.inc.php');

$webservice_id='825bf3e5033d3ed1da82580dc5b701796364953d';

if(is_object($_current_user)){
    $row=$_current_user->getInfo();
    $webservice_id=$row[web_service_id];
}

$ws_op_1='uri=http://www.ubi.pt&guide=WCAG2-AA,UDEMO&context=QChecker.Chrome&resolution=1200x600';
$ws_url_1=AC_BASE_HREF.'api/check.php?id='.$webservice_id.'&'.$ws_op_1;
$ws_op_2='uri=http://www.ubi.pt&guide=WCAG2-AA.G11,WCAG2-AA.G13.133&context=QChecker.PhantomJS';
$ws_url_2=AC_BASE_HREF.'api/check.php?id='.$webservice_id.'&'.$ws_op_2;
$ws_op_3='uri=http://www.ubi.pt&check=ACR-1,ACR-16';
$ws_url_3=AC_BASE_HREF.'api/check.php?id='.$webservice_id.'&'.$ws_op_3;

?>
<div class="output-form" style="line-height:150%">

<h1>QChecker API v1.6</h1>
<p>This API was deeply improved to extend the initial functionality of <a href="http://achecker.ca/" target="_blank">AChecker</a>. The main goal of our work was to improve this API, checkpoints specification and the evaluation core.
  To achieve this goals we also refactored and updated some code from <a href="https://github.com/inclusive-design/AChecker" target="_blank">AChecker GitHub</a> since March of 2015.
  Since we removed diversity of reports generation but we added some significant features in the HTML report version and we made many changes in the core we decided to fork our work in this renamed and extended version.</p>

  <h3>Contributors</h3>
    <h4>RELEASE Group - University of Beira Interior (<a href="http://www.ubi.pt/en/" target="_blank">website</a>)</h4>
    <h4>PT Innovation (<a href="http://www.ptinovacao.pt/en/" target="_blank">website</a>)</h4>
<p></p>
  <h3>New Features</h3>
  <ul style="list-style:decimal;">
    <li>Improved Review with filters, search, collapsible elements and more;</li>
    <li>Extend Review information with Execution Times, current date, HTML and print screens saved;</li>
    <li>Context specification using Host, Driver, Configuration and Resolution references;</li>
    <li>Guideline groups and sub-groups reference for evaluation;</li>
    <li>Checkpoints reference for evaluation using abbreviations and/or ID's;</li>
    <li>New lists for Passed (OK) and Skipped Checkpoints;</li>
    <li>New abstraction of checkpoint specification using assertions;</li>
    <li>Dynamic repair information available in checkpoint specification;</li>
    <li>Dynamic checkpoint skip available in specification;</li>
  </ul>
  <p></p>

<h2 id="TableOfContents">Table of Contents</h2>
    <div id="toc">
      <ul>
        <li><a href="<?php echo AC_BASE_HREF.'documentation/web_service_api.php'; ?>#api_desc">QChecker API Description</a>
          <ul>
            <li><a href="<?php echo AC_BASE_HREF.'documentation/web_service_api.php'; ?>#requestformat_validation">API Request Format</a></li>
            <li><a href="<?php echo AC_BASE_HREF.'documentation/web_service_api.php'; ?>#api_request_ex">API Request Examples</a></li>
          </ul>
        </li>
        <li><a href="<?php echo AC_BASE_HREF.'documentation/web_service_api.php'; ?>#cucumber">Cucumber</a>
          <ul>
            <li><a href="<?php echo AC_BASE_HREF.'documentation/web_service_api.php'; ?>#scenario_spec">Scenario Specification</a></li>
            <li><a href="<?php echo AC_BASE_HREF.'documentation/web_service_api.php'; ?>#scenario_ex">Scenario Examples</a></li>
            <li><a href="<?php echo AC_BASE_HREF.'documentation/web_service_api.php'; ?>#data_spec">Data-Driven Scenario Specification</a></li>
            <li><a href="<?php echo AC_BASE_HREF.'documentation/web_service_api.php'; ?>#cucumber_exec">Cucumber Execution And Reports</a></li>
          </ul>
        </li>
      </ul>
    </div>
    
    <p id="skip"></p>

  <br/><br/>

<div id="api_desc">
<h2>QChecker API Description</h2><br/>
<h3 id="requestformat_validation">API Request Format</h3>

<p>Below is a table of parameters you can use to send a request to QChecker for URI Evaluation.</p>

<p>To use QChecker public server, configure request parameters with ones listed below in conjunction with the following base URI:<br />
<b><?php echo AC_BASE_HREF; ?>api/check.php</b> <small>(replace with the address of your own server if you want to call a private instance of QChecker)</small></p>

<table class="data" rules="all">
<tbody><tr>
<th>Parameter</th><th>Description</th><th>Default value</th>
</tr>

<tr>
  <th>uri</th>
  <td>Encoded URL of the responsive and interactive user interface to evaluate.</td>
  <td>None, must be given.</td>
</tr>

<tr>
  <th>id</th>
  <td>"Web Service ID" generated once successfully registering into QChecker.
    This ID is a 40 characters long string. It can always be retrieved from user's "Profile" page.
    e.g. <i>id=825bf3e5033d3ed1da82580dc5b701796364953d</i>
  </td>
  <td>None, must be given.</td>
</tr>

<tr>
  <th>guide</th>
  <td>Guidelines, groups and sub-groups to validate against. Separate each guideline abbreviation with comma (,).
    e.g. <i>guide=WCAG2-AA,508</i></td>
  <td>WCAG2-AA, more info: <a href="<?php echo AC_BASE_HREF; ?>guideline/index.php" target="_blank">Guidelines</a></td>
</tr>

<tr>
  <th>check</th>
  <td>Checkpoints to validate against. Separate each checkpoint abbreviation and/or ID with comma (,). QChecker only consider valid values and will ignore redundant data.
    e.g. <i>check=1,2,ACR-18,ACR-20</i>
  </td>
  <td>None, more info: <a href="<?php echo AC_BASE_HREF; ?>check/index.php" target="_blank">Checkpoints</a></td>
</tr>

<tr>
  <th>context</th>
  <td>Context to use in the evaluation. The provided URL will be visited in this context (HostName.DriverName) and some manipulations will be made to extract more data.
    After this process QChecker evaluate the modified HTML including new data from AJAX invocations, some Javascript manipulations and more.
    e.g. <i>context=QChecker.PhantomJS</i>
  </td>
  <td>None, more info: <a href="<?php echo AC_BASE_HREF; ?>api/contexts.php?all=true" target="_blank">Contexts</a></td>
</tr>

<tr>
  <th>resolution</th>
  <td>Resolution (WidthxHeight) used in specified context. Very useful for responsive interfaces. e.g. <i> resolution=400x700</i>
  </td>
  <td>None</td>
</tr>

<tr>
  <th>config</th>
  <td>Context can be extended using a javascript configuration, this value is a direct reference (without the extension) for a configuration file.
    e.g. <i>config=default</i>
  </td>
  <td>None</td>
</tr>

<tr>
  <th>port</th>
  <td>Port used in the WebCrawler of specified Context. When not specified the default port of WebCrawler is used.
    e.g. <i>port=8080</i>
  </td>
  <td>None</td>
</tr>

<tr>
  <th>offset</th>
  <td>The line offset to begin validation on the html output from URI.
    e.g. <i>offset=10</i>
  </td>
  <td>0</td>
</tr>
</tbody></table>
<br />

<h3 id="api_request_ex">API Request Examples</h3>
  <p><b>Parameters</b>: <a href="<?php echo $ws_url_1;?>" target="_blank"><?php echo $ws_op_1;?></a><br/>
  <b>Goal</b>: Evaluate URI <code>http://www.ubi.pt</code> against guidelines "WCAG 2.0 L2" and "Usability" using Chrome with 1200x600 resolution.</p>

  <p><b>Parameters</b>: <a href="<?php echo $ws_url_2;?>" target="_blank"><?php echo $ws_op_2;?></a><br/>
  <b>Goal</b>: Evaluate URI <code>http://www.ubi.pt</code> against Group "1.1 Text Alternatives" from guideline "WCAG 2.0 L2" and sub-group "Success Criteria 1.3.3" from guideline "WCAG 2.0 L2" using PhantomJS.</p>

  <p><b>Parameters</b>: <a href="<?php echo $ws_url_3;?>" target="_blank"><?php echo $ws_op_3;?></a><br/>
  <b>Goal</b>: Evaluate URI <code>http://www.ubi.pt</code> against Checkpoint ACR-1 and ACR-16 without any Browser.</p>


<br/>
  <h2 id="cucumber">Cucumber</h2>

  <p>For more information about BDD, Gherkin and Cucumber go to <a href="https://cucumber.io/" target="_blank">https://cucumber.io/</a>.</p>

  <h3 id="scenario_spec">Scenario Specification</h3>

  <p>This Gherkin implementation allow the specification of every QChecker API Parameter using a more Natural Language. This implementation also allows developers and testers to easily automate U&A Evaluation of their systems.</p>

  <table class="data" rules="all">
    <tbody><tr>
      <th width="350px">Given</th><th>Description</th>
    </tr>

    <tr>
      <th style="padding-left:20px;">I am on "URL"</th>
      <td>Where <b>URL</b> is the encoded URL of the responsive and interactive user interface to evaluate.</td>
    </tr>
    <tr>
      <th style="padding-left:20px;">My Context is "HOST.DRIVER"</th>
      <td>Where <b>HOST</b> is the Hostname specified in <i>AC_host</i> table and <b>DRIVER</b> the specified driver name in <i>AC_context</i> table. You can check combined_name for every combination available <a href="<?php echo AC_BASE_HREF; ?>api/contexts.php?all=true" target="_blank">there</a>.</td>
    </tr>
    <tr>
      <th style="padding-left:20px;">My Context ... With "CONFIG_NAME" Config</th>
      <td>Where <b>CONFIG_NAME</b> is the desired javascript configuration. This value is a direct reference (without the extension) for a configuration file.</td>
    </tr>
    <tr>
      <th style="padding-left:20px;">My Screen Resolution is "WIDTHxHEIGHT"</th>
      <td>Where <b>WIDTH</b> is the desired browser width in px and <b>HEIGTH</b> the desired browser height in px.</td>
    </tr>

  </tbody></table>

  <table class="data" rules="all" style="margin-top:5px;">
    <tbody><tr>
      <th width="350px">When</th><th></th>
    </tr>

    <tr>
      <th style="padding-left:20px;">I Evaluate U&A</th>
      <td>QChecker API invocation using Given Rules.</td>
    </tr>

    </tbody></table>

  <table class="data" rules="all"  style="margin-top:5px;">
    <tbody>
    <tr>
      <th width="350px">Then</th><th></th>
    </tr>
    <tr>
      <th style="padding-left:20px;">I Should Not Get TYPE Problems</th>
      <td>Where <b>TYPE</b> (<b>Know</b>, <b>Likely</b>, <b>Potential</b>) is the type of Problems we don't want to have.</td>
    </tr>
    <tr>
      <th style="padding-left:20px;">I Should Get Less Than N TYPE Problems</th>
      <td>Where <b>N</b> is the number (minus one) of allowed problems and <b>TYPE</b> the allowed type.</td>
    </tr>
    </tbody></table><br/>

  <h3 id="scenario_ex">Scenario Examples</h3>

  <p><b>Scenario</b>: Evaluate ubi.pt against WCAG2-AA Using Chrome with 1200x800px</p>
  <pre style="margin-left:20px;margin-right:20px;background-color:#F7F3ED;" >
  <b>Scenario: SC1</b>
  Given I am on "http://www.ubi.pt"
    And My Context is "QChecker.Chrome"
    And My Screen Resolution is "1200x800"
    And I Want Check Guideline "WCAG2-AA"
  When I Evaluate U&A
  Then I Should Not Get Known Problems</pre>

  <p><b>Scenario</b>: Evaluate ubi.pt against Group 1.1 of WCAG2-AA, Checkpoints ACR-116 And ACR-117 using ubi.js config file</p>
  <pre style="margin-left:20px;margin-right:20px;background-color:#F7F3ED;">
  <b>Scenario: SC2</b>
  Given I am on "http://www.ubi.pt"
    And My Context is "QChecker.PhantomJS" with "ubi" config
    And I Want Check Guideline "WCAG2-AA.G11"
    AND I Want Check Checkpoints "ACR-116,ACR-117"
  When I Evaluate U&A
  Then I Should Get Less Than 100 Potential Problems
    And I Should Not Get Known Problems</pre>


  <h3 id="data_spec">Data-Driven Scenario Specification</h3>
  <p>Since each scenario only evaluate one page using a specific Context we suggest the use of Outlines. This annotation allows the BDD specification to be mixed with Data-Driven concept. We can write some scenarios and feed them with data from one or more Cucumber tables.</p>

  <pre style="margin-left:20px;margin-right:20px;background-color:#F7F3ED;">
  <b>Scenario Outline: SCO</b>
  Given I am on &lt;url&gt;
    And My Context is &lt;context&gt; With &lt;config&gt; Config
    And I Want Check Guideline &lt;guideline>
  When I Evaluate U&A
  Then I Should Not Get Known Problems

  Examples:
      | url                                   | guideline      | context              | config     |
      | "http://www.ubi.pt/"                  | "WCAG2-AA"     | "QChecker.Chrome"    | "default"  |
      | "http://www.ubi.pt/Pagina/missao"     | "WCAG2-AA"     | "QChecker.PhantomJS" | "ubi"      |
      | "http://www.ubi.pt/Pagina/3os_ciclos" | "WCAG2-AA.G11" | "QChecker.Chrome"    | "ubi"      |</pre>


  <h3 id="cucumber_exec">Cucumber Execution And Reports</h3>

  <p>This Cucumber implementation generates additional console logs and save report files in <i>features/reports/execution_date_time/</i> besides the usual cucumber reports.
    This additional logs can be hidden using <b>DEBUG = false</b> in file <i>support/env.rb</i>, but if this change is made don't use the option -o in the command line.</p>

  <p>Recommended Execution</p>
  <pre style="margin-left:20px;margin-right:20px;background-color:#F7F3ED;">
    > cucumber --name SCO --expand -s -o cucumber-details.log</pre>

  <p>Console Output</p>
  <pre style="margin-left:20px;margin-right:20px;background-color:#F7F3ED;">
    [WEBCRAWLER] URL => http://www.ubi.pt/
    [WEBCRAWLER] Guidelines => WCAG2-AA
    [WEBCRAWLER] Context => QChecker.Chrome
    [WEBCRAWLER] Config => default
    [TESTC] Report Saved => www.ubi.pt__QChecker.Chrome_1445539145.html
    [ERROR] 43 Known Problems Found

    [WEBCRAWLER] URL => http://www.ubi.pt/Pagina/missao
    [WEBCRAWLER] Guidelines => WCAG2-AA
    [WEBCRAWLER] Context => QChecker.PhantomJS
    [WEBCRAWLER] Config => ubi
    [TESTC] Report Saved => www.ubi.pt_Pagina_missao_QChecker.PhantomJS_1445539152.html
    [ERROR] 22 Known Problems Found

    [WEBCRAWLER] URL => http://www.ubi.pt/Pagina/3os_ciclos
    [WEBCRAWLER] Guidelines => WCAG2-AA.G11
    [WEBCRAWLER] Context => QChecker.Chrome
    [WEBCRAWLER] Config => ubi
    [TESTC] Report Saved => www.ubi.pt_Pagina_3os_ciclos_QChecker.Chrome_1445539185.html
    [ERROR] 9 Known Problems Found
  </pre>

  <p>cucumber-details.log</p>
  <pre style="margin-left:20px;margin-right:20px;background-color:#F7F3ED;">
      Scenario: | "http://www.ubi.pt/" | "WCAG2-AA" | "QChecker.Chrome" | "default" |
        Given I am on "http://www.ubi.pt/"
        And My Context is "QChecker.Chrome" With "default" Config
        And I Want Check Guideline "WCAG2-AA"
        When I Evaluate U&A
        Then I Should Not Get Known Problems
      43 Known Problems Found (RuntimeError)
      ./features/support/start.rb:22:in `fail_with'
      ./features/support/validator.rb:122:in `checkNot'
      ./features/step_definitions/checker_steps.rb:37:in `/^I Should Not Get (Known|Likely|Potential) Problems$/'
      features/documentation_demo.feature:13:in `Then I Should Not Get Known Problems'
      features/documentation_demo.feature:9:in `Then I Should Not Get Known Problems'

      Scenario: | "http://www.ubi.pt/Pagina/missao" | "WCAG2-AA" | "QChecker.PhantomJS" | "ubi" |
        Given I am on "http://www.ubi.pt/Pagina/missao"
        And My Context is "QChecker.PhantomJS" With "ubi" Config
        And I Want Check Guideline "WCAG2-AA"
        When I Evaluate U&A
        Then I Should Not Get Known Problems
      22 Known Problems Found (RuntimeError)
      ./features/support/start.rb:22:in `fail_with'
      ./features/support/validator.rb:122:in `checkNot'
      ./features/step_definitions/checker_steps.rb:37:in `/^I Should Not Get (Known|Likely|Potential) Problems$/'
      features/documentation_demo.feature:14:in `Then I Should Not Get Known Problems'
      features/documentation_demo.feature:9:in `Then I Should Not Get Known Problems'

      Scenario: | "http://www.ubi.pt/Pagina/3os_ciclos" | "WCAG2-AA.G11" | "QChecker.Chrome" | "ubi" |
        Given I am on "http://www.ubi.pt/Pagina/3os_ciclos"
        And My Context is "QChecker.Chrome" With "ubi" Config
        And I Want Check Guideline "WCAG2-AA.G11"
        When I Evaluate U&A
        Then I Should Not Get Known Problems
      9 Known Problems Found (RuntimeError)
      ./features/support/start.rb:22:in `fail_with'
      ./features/support/validator.rb:122:in `checkNot'
      ./features/step_definitions/checker_steps.rb:37:in `/^I Should Not Get (Known|Likely|Potential) Problems$/'
      features/documentation_demo.feature:15:in `Then I Should Not Get Known Problems'
      features/documentation_demo.feature:9:in `Then I Should Not Get Known Problems'

      Failing Scenarios:
      cucumber features/documentation_demo.feature:13
      cucumber features/documentation_demo.feature:14
      cucumber features/documentation_demo.feature:15

      3 scenarios (3 failed)
      15 steps (3 failed, 12 passed)
      0m30.792s
  </pre>

</div>

</div>
<?php include(AC_INCLUDE_PATH.'footer.inc.php'); ?>
