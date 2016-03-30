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

define('IS_ERROR', 1);
define('IS_WARNING', 2);
define('IS_INFO', 3);

/**
 * AccessibilityRpt.class.php
 * Base Class for accessibility validation report
 * @author      Cindy Qi Li
 * @author      Joel Carvalho
 * @package     checker
 * @version     1.6.0 27/05/2015
*/
class AccessibilityRpt {

    protected $errors;
    protected $user_link_id;
    protected $allow_set_decision;
    protected $from_referer;
    protected $show_source;
    protected $source_array;

    protected $num_of_errors;
    protected $num_of_likely_problems;
    protected $num_of_potential_problems;

    protected $num_of_likely_problems_fail;
    protected $num_of_potential_problems_fail;

    protected $num_of_no_decisions;
    protected $num_of_made_decisions;

    protected $num_of_oks;
    protected $num_of_skipped;

    protected $num_array;

    protected $rpt_errors;
    protected $rpt_likely_problems;
    protected $rpt_potential_problems;
    protected $rpt_ok;
    protected $rpt_skipped;
    protected $rpt_source;

    protected $aValidator;
	
	/**
     * AccessibilityRpt constructor
	 * @param AccessibilityValidator $aValidator
     * @param string $user_link_id
     * @author Cindy Qi Li
     * @author Joel Carvalho
     * @version 1.6.0 28/05/2015
	*/
	function __construct($aValidator, $user_link_id = '') {
        $this->aValidator=$aValidator;
		$this->errors = $aValidator->getValidationErrorRpt();
		$this->user_link_id = $user_link_id;
		$this->allow_set_decision = 'false';        // set default "show decision choices" to false
		$this->from_referer = 'false';              // set default "from referer" to false
		$this->show_source = 'false';               // set default "show source" to false
		$this->source_array = array();

		$this->num_array=Array();
        $this->num_array["ok"]=$aValidator->getNumSuccessFiltered();
        $this->num_array["nok"]=$aValidator->getNumErrors();
        $this->num_array["skipped"]=$aValidator->getNumSkippedFiltered();

		$this->num_of_errors = 0;
		$this->num_of_likely_problems = 0;
		$this->num_of_potential_problems = 0;
        $this->num_of_likely_problems_fail = 0;
        $this->num_of_potential_problems_fail = 0;
        $this->num_of_oks=count($this->num_array["ok"]);
        $this->num_of_skipped=count($this->num_array["skipped"]);

        $this->rpt_errors = "";
		$this->rpt_likely_problems = "";
		$this->rpt_potential_problems = "";
        $this->rpt_ok = "";
        $this->rpt_skipped = "";
	}
	
	/**
	* set flag "show decision"
	* @access public
	*/
	public function setAllowSetDecisions($allowSetDecisions) {
		// set default to 'false'
		if ($allowSetDecisions <> 'true' && $allowSetDecisions <> 'false')
			$allowSetDecisions = 'false';

		$this->allow_set_decision = $allowSetDecisions;
	}

	/**
	* set flag "from referer"
	* @access public
	*/
	public function setFromReferer($fromReferer) {
		// set default to 'false'
		if ($fromReferer <> 'true' && $fromReferer <> 'false')
			$fromReferer = 'false';

		$this->from_referer = $fromReferer;
	}

	/**
	* set flag "show source"
	* @access public
	*/
	public function setShowSource($showSource, $sourceArray) {
		// set default to 'false'
		if ($showSource <> 'true' && $showSource <> 'false')
			$showSource = 'false';

		$this->show_source = $showSource;
		$this->source_array = $sourceArray;
	}

    /**
     * return the output array of AccessibilityValidator -> getValidationErrorRpt
     * @access public
     * @return mixed
     */
    public function getErrors(){
        return $this->errors;
    }

    /**
    * return validation error report in html
    * @access public
    */
    public function getErrorRpt() {
        return $this->rpt_errors;
    }

    /**
     * return validation likely problem report in html
     * @access public
     */
    public function getLikelyProblemRpt(){
        return $this->rpt_likely_problems;
    }

    /**
     * return validation potential problem report in html
     * @access public
     */
    public function getPotentialProblemRpt(){
        return $this->rpt_potential_problems;
    }

    /**
     * return 'true' or 'false'. default to 'false'. show decision choices or not.
     * @access public
     * @return boolean
     */
    public function getAllowSetDecision(){
        return $this->allow_set_decision;
    }

    /**
     * return the array of source content. Each element corresponds to a line in the file
     * @access public
     * @return array
     */
    public function getSourceArray(){
        return $this->source_array;
    }

	/**
     * return user link id
     * @access public
     * @return int
     */
	public function getUserLinkID() {
		return $this->user_link_id;
	}

	/**
     * return flag "show decisions"
     * @access public
     * @return boolean
     */
	public function getAllowSetDecisions() {
		return $this->allow_set_decision;
	}

	/**
     * return 'true' or 'false'. default to 'false'. indicate the request is from referer or not.
     * if from referer and user_link_id is set but user is not login, only display the choice and not allow to make decision
     * @access public
     * @return mixed
     */
	public function getFromReferer() {
		return $this->from_referer;
	}

	/**
     * return 'true' or 'false'. default to 'false'. if 'true', wrap line number in <a> to jump to the source line.
     * @access public
     * @return boolean
     */
	public function getShowSource() {
		return $this->show_source;
	}


	/**
	* return the number of known errors (db: checks.confidence = "Known")
     * @access public
     * @return int
     */
	public function getNumOfErrors() {
		return $this->num_of_errors;
	}
	
	/**
     * return the number of known errors (db: checks.confidence = "Likely")
     * @access public
     * @return int
     */
	public function getNumOfLikelyProblems() {
		return $this->num_of_likely_problems;
	}
	
	/**
     * return the number of known errors (db: checks.confidence = "Potential")
     * @access public
     * @return int
     */
	public function getNumOfPotentialProblems() {
		return $this->num_of_potential_problems;
	}

    /**
     * return the number of likely errors that decisions have not been made
     * @access public
     * @return int
     */
    public function getNumOfLikelyProblemsFail(){
        return $this->num_of_likely_problems_fail;
    }

    /**
     * return the number of potential errors that decisions have not been made
     * @access public
     * @return int
     */
    public function getNumOfPotentialProblemsFail(){
        return $this->num_of_potential_problems_fail;
    }

    /**
     * return the number of likely/potential errors that decisions have not been made
     * @access public
     * @return int
     */
    public function getNumOfNoDecisions(){
        return $this->num_of_no_decisions;
    }

    /**
     * return the number of likely/potential errors that decisions have been made
     * @access  public
     * @return  int
     */
    public function getNumOfMadeDecisions(){
        return $this->num_of_made_decisions;
    }

    /**
     * return the number of likely errors that decision have not been made or have fail decision
     * @access  public
     * @return  int
     */
    public function getNumOfLikelyWithFailDecisions(){
        return $this->num_of_likely_problems_fail;
    }

    /**
     * return the number of potential errors that decision have not been made or have fail decision
     * @access  public
     * @return  int
     */
    public function getNumOfPotentialWithFailDecisions(){
        return $this->num_of_potential_problems_fail;
    }

    /**
     * return the number of checkpoints passed
     * @access  public
     * @return  int
     */
    public function getNumOfCheckOk(){
        return $this->num_of_oks;
    }

    /**
     * return the number of checkpoints skipped
     * @access  public
     * @return  int
     */
    public function getNumOfCheckSkipped(){
        return $this->num_of_skipped;
    }

    /**
     * return the array of all numbers
     * @access  public
     * @return  int
     */
    public function getNumArray(){
        return $this->num_array;
    }

    /**
     * return the <DIV> section of source code used for validation
     * @access public
     * @return string
     */
    public function getRptSource(){
        return $this->rpt_source;
    }

    /**
     * return the <DIV> section of errors (known problems)
     * @access public
     * @return string
     */
    public function getRptErrors(){
        return $this->rpt_errors;
    }

    /**
     * return the <DIV> section of likely problems
     * @access public
     * @return string
     */
    public function getRptLikelyProblems(){
        return $this->rpt_likely_problems;
    }

    /**
     * return the <DIV> section of potential problems
     * @access public
     * @return string
     */
    public function getRptPotentialProblems(){
        return $this->rpt_potential_problems;
    }

    /**
     * return the <DIV> section of checkpoints passed
     * @access public
     * @return string
     */
    public function getRptOk(){
        return $this->rpt_ok;
    }

    /**
     * return the <DIV> section of checkpoints skipped
     * @access public
     * @return string
     */
    public function getRptSkipped(){
        return $this->rpt_skipped;
    }

}

?>