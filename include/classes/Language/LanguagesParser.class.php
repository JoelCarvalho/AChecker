<?php namespace QChecker\Language;
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

require_once(dirname(__FILE__) . '/LanguageParser.class.php');

/**
 * LanguagesParser
 * Class for parsing XML languages info and returning a Language Objects
 * @access    public
 * @author    Joel Kronenberg
 * @package    Language
 */
class LanguagesParser extends LanguageParser {

    // private
    function startElement($parser, $name, $attributes)
    {
        if ($name == 'languages') {
            // strip off the initial 'languages'
            $this->element_path = array();
        } else {
            parent::startElement($this->parser, $name, $attributes);
        }
    }

}

?>