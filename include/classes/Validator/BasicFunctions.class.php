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

use QChecker\Utils\Utility;

if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");

include_once(AC_INCLUDE_PATH . 'classes/Validator/BasicChecks.class.php');
include_once(AC_INCLUDE_PATH . 'classes/Validator/HTMLValidator.class.php');
include_once(AC_INCLUDE_PATH . 'classes/DAO/LangCodesDAO.class.php');
include_once(AC_INCLUDE_PATH . 'classes/DAO/ChecksDAO.class.php');
include_once(AC_INCLUDE_PATH . 'classes/Utility.class.php');

/**
 * BasicFunctions.class.php
 * Class for basic functions provided to users in writing check functions
 *
 * @access    public
 * @author    Cindy Qi Li
 * @author    Joel Carvalho
 * @package   checker
 * @version   1.3
 * @license   http://opensource.org/licenses/GPL-2.0 GNU General Public License, Version 2
 */
class BasicFunctions {

    /**
     * Check if associated label has text in the given html.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=204" target="_blank">204</a><br/>
     * <b>References</b>: 8<br/>
     * <b>Check:</b> All <code>input</code> elements, <code>type</code> of "radio", have a <code>label</code> containing text.<br/>
     * <b>Function:</b> <code>return !(BasicFunctions::getAttributeValueInLowerCase("type")=="radio" && !<u>BasicFunctions::associatedLabelHasText()</u>);</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function associatedLabelHasText() {
        global $global_e, $global_content_dom;

        // 1. The element $global_e has a "title" attribute
        if (trim($global_e->attr["title"]) <> "") return true;

        // 2. The element $global_e is contained by a "label" element
        if ($global_e->parent()->tag == "label") {
            $pattern = "/(.*)" . preg_quote($global_e->outertext, '/') . "/";
            preg_match($pattern, $global_e->parent->innertext, $matches);
            if (strlen(trim($matches[1])) > 0) return true;
        }

        // 3. The element $global_e has an "id" attribute value that matches the "for" attribute value of a "label" element
        $input_id = $global_e->attr["id"];

        if ($input_id == "") return false;  // attribute "id" must exist

        foreach ($global_content_dom->find("label") as $e_label) {
            if ($e_label->attr["for"] == $input_id) {
                // label contains text
                if (trim($e_label->plaintext) <> "") return true;

                // label contains an image with alt text
                foreach ($e_label->children as $e_label_child)
                    if ($e_label_child->tag == "img" && strlen(trim($e_label_child->attr["alt"])) > 0)
                        return true;
            }
        }

        return false;
    }

    /**
     * Get the length of the trimed value of specified attribute.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=3" target="_blank">3</a><br/>
     * <b>References</b>: 13<br/>
     * <b>Check:</b> <code>img</code> <code>alt</code> text is short.<br/>
     * <b>Function:</b> <code>return (<u>BasicFunctions::getAttributeTrimedValueLength('alt') <= 100</u>);</code>
     * </samp>
     * @param string $attr attribute name
     * @return int trimed attribute value length
     * @access public
     */
    public static function getAttributeTrimedValueLength($attr) {
        global $global_e;

        return strlen(trim($global_e->attr[$attr]));
    }

    /**
     * Get the value of specified attribute as a number.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=4" target="_blank">4</a><br/>
     * <b>References</b>: 5<br/>
     * <b>Check:</b> Non-Decorative <code>img</code> must have <code>alt</code> text.<br/>
     * <b>Function:</b> <code>return !(<u>BasicFunctions::getAttributeValueAsNumber('width') > 25</u> && ... && BasicFunctions::getAttributeValue('alt') == "");</code>
     * </samp>
     * @param string $attr attribute name
     * @return int attribute value
     * @access public
     */
    public static function getAttributeValueAsNumber($attr) {
        global $global_e;

        return intval(trim($global_e->attr[$attr]));
    }

    /**
     * Get the value of the specified attribute in lower case.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=204" target="_blank">204</a><br/>
     * <b>References</b>: 35<br/>
     * <b>Check:</b> All <code>input</code> elements, <code>type</code> of "radio", have a <code>label</code> containing text.<br/>
     * <b>Function:</b> <code>return !(<u>BasicFunctions::getAttributeValueInLowerCase("type")=="radio"</u> && !BasicFunctions::associatedLabelHasText());</code>
     * </samp>
     * @param string $attr attribute name
     * @return string attribute value
     * @access public
     */
    public static function getAttributeValueInLowerCase($attr) {
        global $global_e;

        return @strtolower(trim($global_e->attr[$attr]));
    }

    /**
     * Get the length of the value of specified attribute.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=114" target="_blank">114</a><br/>
     * <b>References</b>: 3<br/>
     * <b>Check:</b> All layout <code>table</code> have an empty <code>summary</code> attribute or no <code>summary</code> attribute.<br/>
     * <b>Function:</b> <code>return !(!BasicFunctions::isDataTable() && <u>BasicFunctions::getAttributeValueLength('summary') > 0</u>);</code>
     * </samp>
     * @param string $attr attribute name
     * @return int attribute value length
     * @access public
     */
    public static function getAttributeValueLength($attr) {
        global $global_e;

        return strlen($global_e->attr[$attr]);
    }

    /**
     * Get the html tag of the first child.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=115" target="_blank">115</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> All layout <code>table</code> do not contain <code>caption</code> elements.<br/>
     * <b>Function:</b> <code>... if (<u>BasicFunctions::getFirstChildTag() == "caption"</u>) return false; ...</code>
     * </samp>
     * @return string html tag
     * @access public
     */
    public static function getFirstChildTag() {
        global $global_e;

        $children = $global_e->children();
        return $children[0]->tag;
    }

    /**
     * Get the trimed value of inner text in the given html.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=80" target="_blank">80</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> All <code>objects</code> contain a text equivalent of the <code>object</code>.<br/>
     * <b>Function:</b> <code>return (<u>BasicFunctions::getInnerText() <> ''</u>);</code>
     * </samp>
     * @return string trimed inner text
     * @access public
     */
    public static function getInnerText() {
        global $global_e;

        return trim($global_e->innertext);
    }

    /**
     * Get the length of the trimed inner text in the given html.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=51" target="_blank">51</a><br/>
     * <b>References</b>: 3<br/>
     * <b>Check:</b> <code>title</code> contains text.<br/>
     * <b>Function:</b> <code>return (<u>BasicFunctions::getInnerTextLength() > 0</u>);</code>
     * </samp>
     * @return int trimed inner text length
     * @access public
     */
    public static function getInnerTextLength() {
        global $global_e;

        return strlen(trim($global_e->innertext));
    }

    /**
     * Get language code defined in the given html.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=60" target="_blank">60</a><br/>
     * <b>References</b>: 2<br/>
     * <b>Check:</b> <code>alt</code> text for all <code>input</code> elements with a <code>type</code> attribute value of "image"
     * is less than 100 characters (English) or the user has confirmed that the <code>alt</code> text is as short as possible.<br/>
     * <b>Function:</b> <code><u>$lang_code = BasicFunctions::getLangCode()</u>; if ($lang_code == "ger" || $lang_code == "de") ...</code>
     * </samp>
     * @access public
     * @return string language code
     */
    public static function getLangCode() {
        global $global_content_dom;

        // get html language
        $e_htmls = $global_content_dom->find("html");

        foreach ($e_htmls as $e_html) {
            if (isset($e_html->attr["xml:lang"])) {
                $lang = trim($e_html->attr["xml:lang"]);
                break;
            } else if (isset($e_html->attr["lang"])) {
                $lang = trim($e_html->attr["lang"]);
                break;
            }
        }

        return BasicChecks::cutOutLangCode($lang);
    }

    /**
     * Get the last four characters of specified attribute, usually used to get file extension.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=17" target="_blank">17</a><br/>
     * <b>References</b>: 6<br/>
     * <b>Check:</b> Sound file must have a text transcript.<br/>
     * <b>Function:</b> <code><u>$ext = BasicFunctions::getLast4CharsFromAttributeValue('href')</u>; return !($ext == ".wav" || $ext == ".snd" || ...);</code>
     * </samp>
     * @param string $attr attribute name
     * @return string partial attribute value
     * @access public
     */
    public static function getLast4CharsFromAttributeValue($attr) {
        global $global_e;

        return substr(trim($global_e->attr[$attr]), -4);
    }

    /**
     * Scan all children's of specified attribute and get the
     * attribute value length of the first children with the specified html tag.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=174" target="_blank">174</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Each source anchor contains text.<br/>
     * <b>Function:</b> <code>return (... || <u>BasicFunctions::getLengthOfAttributeValueWithGivenTagInChildren('img', 'alt') > 0)</u> ...);</code>
     * </samp>
     * @param string $tag html tag
     * @param string $attr attribute name
     * @return int attribute value length
     * @access public
     */
    public static function getLengthOfAttributeValueWithGivenTagInChildren($tag, $attr) {
        global $global_e;

        $len = 0;

        foreach ($global_e->children() as $child)
            if ($child->tag == $tag) $len = strlen(trim($child->attr[$attr]));

        return $len;
    }

    /**
     * Scan all children's and get the attribute value of
     * the first children with the specified html tag.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=175" target="_blank">175</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> <code>alt</code> text for all <code>img</code> elements used as source anchors is different from the link text.<br/>
     * <b>Function:</b> <code><u>$alt_value = BasicFunctions::getLowerCaseAttributeValueWithGivenTagInChildren('img', 'alt')</u>; ...</code>
     * </samp>
     * @param string $tag html tag
     * @param string $attr attribute name
     * @return string attribute value
     * @access public
     */
    public static function getLowerCaseAttributeValueWithGivenTagInChildren($tag, $attr) {
        global $global_e;

        foreach ($global_e->children() as $child)
            if ($child->tag == $tag) $value = strtolower(trim($child->attr[$attr]));

        return $value;
    }

    /**
     * Scan all children's and return the plain text of
     * the first children with the specified html tag.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=243" target="_blank">243</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Table summaries do not duplicate the table captions.<br/>
     * <b>Function:</b> <code>... <u>$caption = BasicFunctions::getLowerCasePlainTextWithGivenTagInChildren('caption')</u>; ...</code>
     * </samp>
     * @param string $tag html tag
     * @return string plain text
     * @access public
     */
    public static function getLowerCasePlainTextWithGivenTagInChildren($tag) {
        global $global_e;

        foreach ($global_e->children() as $child)
            if ($child->tag == $tag) $value = strtolower(trim($child->plaintext));

        return $value;
    }

    /**
     * Get the specified attribute value of the next sibling.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=236" target="_blank">236</a><br/>
     * <b>References</b>: 2<br/>
     * <b>Check:</b> There are no adjacent text and image links having the same destination.<br/>
     * <b>Function:</b> <code>return !(... BasicFunctions::getAttributeValueInLowerCase("href") == <u>BasicFunctions::getNextSiblingAttributeValueInLowerCase("href")</u>);</code>
     * </samp>
     * @param string $attr attribute name
     * @return string attribute value
     * @access public
     */
    public static function getNextSiblingAttributeValueInLowerCase($attr) {
        global $global_e;

        return strtolower(trim($global_e->next_sibling()->attr[$attr]));
    }

    /**
     * Get the inner text of the next sibling.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=9" target="_blank">9</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> All <code>img</code> elements that have a <code>longdesc</code> attribute also have an associated 'd-link'.<br/>
     * <b>Function:</b> <code>... return (BasicFunctions::getNextSiblingTag() == "a" && <u>BasicFunctions::getNextSiblingInnerText() == "[d]"</u>);</code>
     * </samp>
     * @return string inner text
     * @access public
     */
    public static function getNextSiblingInnerText() {
        global $global_e;

        return $global_e->next_sibling()->innertext;
    }

    /**
     * Get the html tag of the next sibling.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=9" target="_blank">9</a><br/>
     * <b>References</b>: 4<br/>
     * <b>Check:</b> All <code>img</code> elements that have a <code>longdesc</code> attribute also have an associated 'd-link'.<br/>
     * <b>Function:</b> <code>... return (<u>BasicFunctions::getNextSiblingTag() == "a"</u> && BasicFunctions::getNextSiblingInnerText() == "[d]");</code>
     * </samp>
     * @return string html tag
     * @access public
     */
    public static function getNextSiblingTag() {
        global $global_e;

        return trim($global_e->next_sibling()->tag);
    }

    /**
     * Scan children's and get the number of times that the specified html tag appears in all children.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=50" target="_blank">50</a><br/>
     * <b>References</b>: 5<br/>
     * <b>Check:</b> Document contains a <code>title</code> element.<br/>
     * <b>Function:</b> <code>return (<u>BasicFunctions::getNumOfTagInChildren('title') > 0</u>);</code>
     * </samp>
     * @param string $tag html tag
     * @return int number of occurrences
     * @access public
     */
    public static function getNumOfTagInChildren($tag) {
        global $global_e;
        $num = 0;

        foreach ($global_e->children() as $child)
            if ($child->tag == $tag) $num++;

        return $num;
    }

    /**
     * Scan children's recursively and get the number of times that the specified html tag appears in all children.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=183" target="_blank">183</a><br/>
     * <b>References</b>: 5<br/>
     * <b>Check:</b> Use the <code>embed</code> element within the <code>object</code> element.<br/>
     * <b>Function:</b> <code>return (<u>BasicFunctions::getNumOfTagRecursiveInChildren('embed') > 0</u>);</code>
     * </samp>
     * @param string $tag html tag
     * @return int number of occurrences
     * @access public
     */
    public static function getNumOfTagRecursiveInChildren($tag) {
        global $global_e;
        $num = 0;

        foreach ($global_e->children() as $child)
            if ($child->tag == $tag) $num++;
            else $num += BasicChecks::getNumOfTagRecursiveInChildren($child, $tag);

        return $num;
    }

    /**
     * Scan children's and get the number of times that the specified html tag appears and its inner text has content.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=70" target="_blank">70</a><br/>
     * <b>References</b>: 2<br/>
     * <b>Check:</b> <code>menu</code> items should not be used to format text.<br/>
     * <b>Function:</b> <code>return (<u>BasicFunctions::getNumOfTagInChildrenWithInnerText('li') <> 1</u>);</code>
     * </samp>
     * @param string $tag html tag
     * @return int number of occurrences
     * @access public
     */
    public static function getNumOfTagInChildrenWithInnerText($tag) {
        global $global_e;
        $num = 0;

        foreach ($global_e->children() as $child)
            if ($child->tag == $tag && strlen(trim($child->innertext)) > 0) $num++;

        return $num;
    }

    /**
     * Get the number of times that the specified html tag appears in the content.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=29" target="_blank">29</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> <code>html</code> content has a valid <code>doctype</code> declaration.<br/>
     * <b>Function:</b> <code>return (<u>BasicFunctions::getNumOfTagInWholeContent('doctype') > 0</u>);</code>
     * </samp>
     * @param string $tag html tag
     * @return int number of occurrences
     * @access public
     */
    public static function getNumOfTagInWholeContent($tag) {
        global $global_content_dom;

        return count($global_content_dom->find($tag));
    }

    /**
     * Get the html tag of the parent html tag.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=7" target="_blank">7</a><br/>
     * <b>References</b>: 3<br/>
     * <b>Check:</b> <code>alt</code> text for all <code>img</code> elements used as source anchors is not empty when there is no other text in the anchor.<br/>
     * <b>Function:</b> <code>return !(<u>BasicFunctions::getParentHTMLTag() == "a"</u> && BasicFunctions::getAttributeValue('alt') == "");</code>
     * </samp>
     * @return string html tag
     * @access public
     */
    public static function getParentHTMLTag() {
        global $global_e;

        return $global_e->parent()->tag;
    }

    /**
     * Get the trimed plain text.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=175" target="_blank">175</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> <code>alt</code> text for all <code>img</code> elements used as source anchors is different from the link text.<br/>
     * <b>Function:</b> <code>... return !($alt_value <> "" && <u>$alt_value == BasicFunctions::getPlainTextInLowerCase()</u>);</code>
     * </samp>
     * @return string plain text
     * @access public
     */
    public static function getPlainTextInLowerCase() {
        global $global_e;

        return strtolower(trim($global_e->plaintext));
    }

    /**
     * Get the length of the trimed plain text.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=199" target="_blank">199</a><br/>
     * <b>References</b>: 16<br/>
     * <b>Check:</b> <code>legend</code> text is not empty or whitespace.<br/>
     * <b>Function:</b> <code>return (<u>BasicFunctions::getPlainTextLength() > 0</u>);</code>
     * </samp>
     * @return string plain text
     * @access public
     */
    public static function getPlainTextLength() {
        global $global_e;

        return strlen(trim($global_e->plaintext));
    }

    /**
     * Get the portion of string specified by the start and length parameters.
     * Wrapper of php function substr for security reasons.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=175" target="_blank">175</a><br/>
     * <b>References</b>: 2<br/>
     * <b>Check:</b> Anchor must not use Javascript URL protocol.<br/>
     * <b>Function:</b> <code>return (<u>BasicFunctions::getSubstring(BasicFunctions::getAttributeValueInLowerCase("href"), 0, 11) <> "javascript:"</u>);</code>
     * </samp>
     * @param string $string input string
     * @param int $start starting position
     * @param int $length length of substring
     * @return string|false Extracted part of string; or FALSE on failure, or an empty string.
     * @link http://php.net/substr
     * @access public
     */
    public static function getSubstring($string, $start, $length) {
        return substr($string, $start, $length);
    }

    /**
     * Check if current element has associated label.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=91" target="_blank">91</a><br/>
     * <b>References</b>: 7<br/>
     * <b>Check:</b> All <code>select</code> elements have an explicitly associated <code>label</code>.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::hasAssociatedLabel()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function hasAssociatedLabel() {
        global $global_e, $global_content_dom;

        // 1. The element $global_e is contained by a "label" element
        // 2. The element $global_e has a "title" attribute
        if ($global_e->parent()->tag == "label" || isset($global_e->attr["title"])) return true;

        // 3. The element $global_e has an "id" attribute value that matches the "for" attribute value of a "label" element
        $input_id = $global_e->attr["id"];

        if ($input_id == "") return false;  // attribute "id" must exist

        foreach ($global_content_dom->find("label") as $global_e_label)
            if (strtolower(trim($global_e_label->attr["for"])) == strtolower(trim($global_e->attr["id"])))
                return true;

        return false;
    }

    /**
     * Check if the element has specified attribute.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=1" target="_blank">1</a><br/>
     * <b>References</b>: 57<br/>
     * <b>Check:</b> All <code>img</code> elements have an <code>alt</code> attribute.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::hasAttribute('alt')</u>;</code>
     * </samp>
     * @param string $attr attribute name
     * @return bool
     * @access public
     */
    public static function hasAttribute($attr) {
        global $global_e;

        return isset($global_e->attr[$attr]);
    }

    /**
     * Check recursively if there are duplicate attribute in children of $global_e
     * set global var hasDuplicateAttribute to true if there is, otherwise, set it to false.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=185" target="_blank">185</a><br/>
     * <b>References</b>: 2<br/>
     * <b>Check:</b> <code>id</code> attributes must be unique.<br/>
     * <b>Function:</b> <code>return !<u>BasicFunctions::hasDuplicateAttribute('id')</u>;</code>
     * </samp>
     * @param string $attr attribute name
     * @return bool
     * @access public
     * @version 1.0 10/04/2015
     */
    public static function hasDuplicateAttribute($attr) {
        global $has_duplicate_attribute, $global_e;

        BasicChecks::hasDuplicateAttribute($global_e, $attr);

        return $has_duplicate_attribute;
    }

    /**
     * Check if form has "fieldset" and "legend" to group multiple checkbox buttons.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=185" target="_blank">185</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> All checkbox groups are marked using <code>fieldset</code> and <code>legend</code> elements.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::hasFieldsetOnMultiCheckbox()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function hasFieldsetOnMultiCheckbox() {
        global $global_e;

        // find if there are radio buttons with same name
        $children = $global_e->children();
        $num_of_children = count($children);

        foreach ($children as $i => $child) {
            if (strtolower(trim($child->attr["type"])) == "checkbox") {
                $this_name = strtolower(trim($child->attr["name"]));

                for ($j = $i + 1; $j <= $num_of_children; $j++)
                    // if there are radio buttons with same name,
                    // check if they are contained in "fieldset" and "legend" elements
                    if (strtolower(trim($children[$j]->attr["name"])) == $this_name)
                        if (BasicChecks::hasParent($global_e, "fieldset"))
                            return BasicChecks::hasParent($global_e, "legend");
                        else
                            return false;
            } else
                return BasicChecks::hasFieldsetOnMultiCheckbox($child);
        }

        return true;
    }

    /**
     * Check if the table contains more than one row or either row or column headers.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=245" target="_blank">245</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Data <code>tables</code> that contain more than one row/column of headers use the <code>id</code>
     * and <code>headers</code> attributes to identify cells.<br/>
     * <b>Function:</b> <code>... return <u>BasicFunctions::hasIdHeaders()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function hasIdHeaders() {
        global $global_e;

        // check if the table contains both row and column headers
        list($num_of_header_rows, $num_of_header_cols) = BasicChecks::getNumOfHeaderRowCol($global_e);

        // if table has more than 1 header rows or has both header row and header column,
        // check if all "th" has "id" attribute defined and all "td" has "headers" defined
        if ($num_of_header_rows > 1 || ($num_of_header_rows > 0 && $num_of_header_cols > 0)) {
            foreach ($global_e->find("th") as $th)
                if (!isset($th->attr["id"])) return false;

            foreach ($global_e->find("td") as $td)
                if (!isset($td->attr["headers"])) return false;
        }

        return true;
    }

    /**
     * Check if a link to anchor have some text
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=28" target="_blank">28</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> A "skip to content" link appears on all pages with blocks of material prior to the main document.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::hasLinkChildWithText(array("%jump%","%go to%","%skip%","%navigation%","%content%"))</u>;</code>
     * </samp>
     * @param string $searchStrArray array of match string. The string could be %[string]% or %[string] or [string]%
     * @return bool
     * @access public
     */
    public static function hasLinkChildWithText($linkStrArray,$searchStrArray) {
        global $global_e;

        foreach ($global_e->children() as $child) {
            if ($child->tag == 'a'){
                if (BasicChecks::inSearchString($child->attr['href'], $linkStrArray)
                && BasicChecks::inSearchString(implode($child->children), $searchStrArray))
                    return true;
            }
        }

        return false;
    }

    /**
     * Check if exists a parent with specified html tag.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=90" target="_blank">90</a><br/>
     * <b>References</b>: 2<br/>
     * <b>Check:</b> <code>script</code> must have a <code>noscript</code> section.<br/>
     * <b>Function:</b> <code>return !(<u>BasicFunctions::hasParent("body")</u> && BasicFunctions::getNextSiblingTag() <> "noscript");</code>
     * </samp>
     * @param string $parent_tag html tag
     * @return bool
     * @access public
     */
    public static function hasParent($parent_tag) {
        global $global_e;

        if ($global_e->parent() == NULL) return false;

        if ($global_e->parent()->tag == $parent_tag)
            return true;
        else
            return BasicChecks::hasParent($global_e->parent(), $parent_tag);
    }

    /**
     * Check if the table contains both row and column headers.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=244" target="_blank">244</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Data tables that contain both row and column headers use the <code>scope</code> attribute to identify cells.<br/>
     * <b>Function:</b> <code>... return <u>BasicFunctions::hasScope()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function hasScope() {
        global $global_e;

        // check if the table contains both row and column headers
        list($num_of_header_rows, $num_of_header_cols) = BasicChecks::getNumOfHeaderRowCol($global_e);

        if ($num_of_header_rows > 0 && $num_of_header_cols > 0) {
            foreach ($global_e->find("th") as $th)
                if (!isset($th->attr["scope"])) return false;
        }

        return true;
    }

    /**
     * Check if the tag plain text contains a line that is separated by more than one tab or vertical line.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=241" target="_blank">241</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Table markup is used for all tabular information.<br/>
     * <b>Function:</b> <code>return (BasicFunctions::getPlainTextLength() < 21 || !<u>BasicFunctions::hasTabularInfo()</u>);</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function hasTabularInfo() {
        global $global_e;

        $text = $global_e->plaintext;

        return (preg_match("/.*\t.+\t.*/", $text) || preg_match("/.*\|.+\|.*/", $text));
    }

    /**
     * Check if there's given tag in children.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=143" target="_blank">143</a><br/>
     * <b>References</b>: 5<br/>
     * <b>Check:</b> Table markup is used for all tabular information.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::hasTagInChildren('address')</u>;</code>
     * </samp>
     * @param string $tag html tag
     * @return bool
     * @access public
     */
    public static function hasTagInChildren($tag) {
        global $global_e;

        $tags = $global_e->find($tag);

        return (count($tags) > 0);
    }

    /**
     * Check if there's text between <a> elements.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=134" target="_blank">134</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Include non-link, printable characters (surrounded by spaces) between adjacent links.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::hasTextInBtw()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function hasTextInBtw() {
        global $global_e;

        $next_sibling = $global_e->next_sibling();

        if ($next_sibling->tag <> "a") return true;

        // check if there's other text in between $global_e and its next sibling
        $pattern = "/" . preg_quote($global_e->outertext, '/') . "(.*)" . preg_quote($next_sibling->outertext, '/') . "/";
        preg_match($pattern, $global_e->parent->innertext, $matches);

        return (strlen(trim($matches[1])) > 0);
    }

    /**
     * Check if there's child with tag named $childTag, in which the value of attribute $childAttribute equals one of the
     * values in given $valueArray.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=147" target="_blank">147</a><br/>
     * <b>References</b>: 2<br/>
     * <b>Check:</b> Document uses <code>link</code> element to describe navigation if it is within a collection.<br/>
     * <b>Function:</b> <code>... return !<u>BasicFunctions::hasTextInChild('link', 'rel', array('stylesheet', 'alternate'))</u>;</code>
     * </samp>
     * @param string $childTag html tag
     * @param string $childAttribute attribute name
     * @param string $valueArray attribute value array
     * @return bool
     * @access public
     */
    public static function hasTextInChild($childTag, $childAttribute, $valueArray) {
        global $global_e;

        // if no <link> element is defined or "rel" in all <link> elements are not "alternate" or href is not defined, return false
        foreach ($global_e->children() as $child) {
            if ($child->tag == $childTag) {
                $rel_val = strtolower(trim($child->attr[$childAttribute]));

                if (in_array($rel_val, $valueArray))
                    return true;
            }
        }

        return false;
    }

    /**
     * This function for now is solely used for attribute "usemap", check id 13.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=13" target="_blank">13</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> All links in all client side image-maps are duplicated within the document.<br/>
     * <b>Function:</b> <code>... return <u>BasicFunctions::hasTextLinkEquivalents('usemap')</u>;</code>
     * </samp>
     * @param string $attr attribute name
     * @return bool
     * @access public
     */
    public static function hasTextLinkEquivalents($attr) {
        global $global_e, $global_content_dom;

        $map_name = substr($global_e->attr[$attr], 1);  // remove heading #

        // find definition of <map> with $map_name
        $map_found = false;
        foreach ($global_content_dom->find("map") as $map) {
            if ($map->attr["name"] == $map_name) {
                $map_found = true;
                $area_hrefs = array();

                foreach ($map->children() as $map_child) {
                    if ($map_child->tag == "area")
                        array_push($area_hrefs, array("href" => trim($map_child->attr["href"]), "found" => false));
                }

                break;  // stop at finding <map> with $map_name
            }
        }

        // return false <map> with $map_name is not defined
        if (!$map_found) return false;

        foreach ($global_content_dom->find("a") as $a) {
            foreach ($area_hrefs as $i => $area_href)
                if ($a->attr["href"] == $area_href["href"]) {
                    $area_hrefs[$i]["found"] = true;
                    break;
                }
        }

        $all_href_found = true;
        foreach ($area_hrefs as $area_href)
            if (!$area_href["found"]) {
                $all_href_found = false;
                break;
            }

        // return false when not all area href are defined
        if (!$all_href_found) return false;

        return true;
    }

    /**
     * Check if "window.onload" is contained in tag "script".
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=275" target="_blank">275</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Loading the web page does not cause a new window to open.<br/>
     * <b>Function:</b> <code>return (!BasicFunctions::hasAttribute("onload") && !<u>BasicFunctions::hasWindowOpenInScript()</u>);</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function hasWindowOpenInScript() {
        global $global_content_dom;

        $tags = $global_content_dom->find('script');
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                if (stristr($tag->innertext, 'window.onload')) return true;
            }
        }
        return false;
    }

    /**
     * Check if the html document is validated.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=232" target="_blank">232</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Document validates to specification.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::htmlValidated()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     * @author Cindy Li
     * @author Joel Carvalho
     * @version 1.0 28/04/2015
     */
    public static function htmlValidated() {
        global $htmlValidator, $global_e;
        return false;
/*
        if (!isset($htmlValidator))
            $htmlValidator = new HTMLValidator("fragment", $global_e);

        return ($htmlValidator->getNumOfValidateError() == 0 && !$htmlValidator->containErrors());
*/
    }

    /**
     * Check if the inner text is in one of the search string defined in "checks.search_str".
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=202" target="_blank">202</a><br/>
     * <b>References</b>: 3<br/>
     * <b>Check:</b> All <code>frame</code> <code>titles</code> do not contain placeholder text.<br/>
     * <b>Function:</b> <code>return !<u>BasicFunctions::isAttributeValueInSearchString('title')</u>;</code>
     * </samp>
     * @param string $attr attribute name
     * @return bool
     * @access public
     * @version 1.0 10/04/2015
     */
    public static function isAttributeValueInSearchString($attr,$inParent=false) {
        global $global_e, $global_check_id;

        if (!$inParent) $text=trim($global_e->attr[$attr]);
        else $text=trim($global_e->parent->attr[$attr]);

        return BasicChecks::isTextInSearchString($text, $global_check_id, $global_e);
    }

    /**
     * Makes a guess about the table type.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=114" target="_blank">114</a><br/>
     * <b>References</b>: 14<br/>
     * <b>Check:</b> All layout <code>table</code> have an empty <code>summary</code> attribute or no <code>summary</code> attribute.<br/>
     * <b>Function:</b> <code>return !(!<u>BasicFunctions::isDataTable()</u> && BasicFunctions::getAttributeValueLength('summary') > 0);</code>
     * </samp>
     * @return bool return true if this should be a data table, false if layout table.
     * @access public
     */
    public static function isDataTable() {
        global $is_data_table, $global_e;

        $is_data_table = false;
        BasicChecks::isDataTable($global_e);

        return $is_data_table;
    }

    /**
     * Check if the inner text is in one of the search string defined in checks.search_str.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=53" target="_blank">53</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> <code>title</code> is not placeholder text.<br/>
     * <b>Function:</b> <code>return !<u>BasicFunctions::isInnerTextInSearchString()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function isInnerTextInSearchString() {
        global $global_e, $global_check_id;

        return BasicChecks::isTextInSearchString($global_e->innertext, $global_check_id, $global_e);
    }

    /**
     * Check if the next tag, is not in the given array.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=37" target="_blank">37</a><br/>
     * <b>References</b>: 5<br/>
     * <b>Check:</b> The header following an <code>h1</code> is <code>h1</code> or <code>h2</code>.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::isNextTagNotIn(array("h1", "h2"))</u>;</code>
     * </samp>
     * @param string[] $notInArray html tag array
     * @return bool
     * @access public
     */
    public static function isNextTagNotIn($notInArray) {
        global $header_array, $global_e;

        if (!is_array($header_array)) return true;

        // find the next header after $global_e->linenumber, $global_e->colnumber
        foreach ($header_array as $e) {
            if ($e->linenumber > $global_e->linenumber || ($e->linenumber == $global_e->linenumber && $e->colnumber > $global_e->colnumber)) {
                if (!isset($next_header))
                    $next_header = $e;
                else if ($e->linenumber < $next_header->line_number || ($e->linenumber == $next_header->line_number && $e->colnumber > $next_header->col_number))
                    $next_header = $e;
            }
        }

        if (isset($next_header) && !in_array($next_header->tag, $notInArray))
            return false;
        return true;
    }

    /**
     * check if the plain text is in one of the search string defined in checks.search_str.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=173" target="_blank">173</a><br/>
     * <b>References</b>: 2<br/>
     * <b>Check:</b> Suspicious link text.<br/>
     * <b>Function:</b> <code>return !<u>BasicFunctions::isPlainTextInSearchString()</u>;</code>
     * </samp>
     * @return true if in, otherwise, return false
     * @access public
     */
    public static function isPlainTextInSearchString() {
        global $global_e, $global_check_id;

        return BasicChecks::isTextInSearchString($global_e->plaintext, $global_check_id, $global_e);
    }

    /**
     * Check radio button groups are marked using "fieldset" and "legend" elements.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=168" target="_blank">168</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> All radio button groups are marked using <code>fieldset</code> and <code>legend</code> elements.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::isRadioButtonsGrouped()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function isRadioButtonsGrouped() {
        global $global_e;

        $radio_buttons = array();

        foreach ($global_e->find("input") as $e_input) {
            if (strtolower(trim($e_input->attr["type"])) == "radio")
                array_push($radio_buttons, $e_input);
        }

        for ($i = 0; $i < count($radio_buttons); $i++) {
            for ($j = 0; $j < count($radio_buttons); $j++) {
                if ($i <> $j && strtolower(trim($radio_buttons[$i]->attr["name"])) == strtolower(trim($radio_buttons[$j]->attr["name"]))
                    && !BasicChecks::hasParent($radio_buttons[$i], "fieldset") && !BasicChecks::hasParent($radio_buttons[$i], "legend")
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if the labels for all the submit buttons on the form are different.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=237" target="_blank">237</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> The labels for <code>form</code> submit buttons are unique for all buttons that lead to different results.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::isSubmitLabelDifferent()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function isSubmitLabelDifferent() {
        global $global_e;

        $submit_labels = array();

        foreach ($global_e->find("form") as $form) {
            foreach ($form->find("input") as $button) {
                $button_type = strtolower(trim($button->attr["type"]));

                if ($button_type == "submit" || $button_type == "image") {
                    if ($button_type == "submit")
                        $button_value = strtolower(trim($button->attr["value"]));

                    if ($button_type == "image")
                        $button_value = strtolower(trim($button->attr["alt"]));

                    if (in_array($button_value, $submit_labels)) return false;
                    else array_push($submit_labels, $button_value);
                }
            }
        }

        return true;
    }

    /**
     * Check if the element content is marked with the html tags given in $htmlTagArray.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=82" target="_blank">82</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> All <code>p</code> elements are not used as headers.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::isTextMarked(array('b', 'i', 'u', 'strong', 'font', 'em'))</u>;</code>
     * </samp>
     * @param string[] $htmlTagArray html tag array
     * @return bool
     * @access public
     */
    public static function isTextMarked($htmlTagArray) {
        global $global_e;

        $children = $global_e->children();

        if (count($children) == 1) {
            $child = $children[0];

            $tag = $child->tag;

            if (in_array($tag, $htmlTagArray) && $child->plaintext == $global_e->plaintext)
                return false;
        }
        return true;
    }

    /**
     * Check if value in the given attribute is a valid language code.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=49" target="_blank">49</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Document has valid language code.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::isValidLangCode()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function isValidLangCode() {
        global $global_e, $global_content_dom;

        $is_text_content = false;
        $is_application_content = false;

        $metas = $global_content_dom->find("meta");
        if (is_array($metas)) {
            foreach ($metas as $meta) {
                if (!array_key_exists('content',$meta->attr)) continue;
                if (stristr($meta->attr['content'], 'text/html')) $is_text_content = true;
                if (stristr($meta->attr['content'], 'application/xhtml+xml')) $is_application_content = true;
            }
        }
        $doctypes = $global_content_dom->find("doctype");

        if (count($doctypes) == 0) return false;

        foreach ($doctypes as $doctype) {
            foreach ($doctype->attr as $doctype_content => $garbage) {
                // If the content is HTML, check the value of the html element's lang attribute
                if (stristr($doctype_content, "HTML") && !stristr($doctype_content, "XHTML")) {
                    return BasicChecks::isValidLangCode(trim($global_e->attr['lang']));
                }

                // If the content is XHTML 1.0, or any version of XHTML served as "text/html",
                // check the values of both the html element's lang attribute and xml:lang attribute.
                // Note: both lang attributes must be set to the same value.
                if (stristr($doctype_content, "XHTML 1.0") || (stristr($doctype_content, " XHTML ") && $is_text_content)) {
                    return (BasicChecks::isValidLangCode(trim($global_e->attr['lang'])) &&
                        BasicChecks::isValidLangCode(trim($global_e->attr['xml:lang'])) &&
                        trim($global_e->attr['lang']) == trim($global_e->attr['xml:lang']));
                } else if (stristr($doctype_content, " XHTML ") && $is_application_content) {
                    return BasicChecks::isValidLangCode(trim($global_e->attr['xml:lang']));
                }
            }
        }
        return true;
    }

    /**
     * Validate if the <code>dir</code> attribute's value is "rtl" for languages
     * that are read left-to-right or "ltr" for languages that are read right-to-left.
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=273" target="_blank">273</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Reading order direction is marked using the html element's <code>dir</code> attribute if the document's primary language is read right to left.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::isValidRTL()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function isValidRTL() {
        global $global_e;

        if (isset($global_e->attr["lang"]))
            $lang_code = trim($global_e->attr["lang"]);
        else
            $lang_code = trim($global_e->attr["xml:lang"]);

        // return no error if language code is not specified
        if (!BasicChecks::isValidLangCode($lang_code)) return true;

        $rtl_lang_codes = BasicChecks::getRtlLangCodes();

        if (in_array($lang_code, $rtl_lang_codes))
            // When these 2 languages, "dir" attribute must be set and set to "rtl"
            return (strtolower(trim($global_e->attr["dir"])) == "rtl");
        else
            return (!isset($global_e->attr["dir"]) || strtolower(trim($global_e->attr["dir"])) == "ltr");
    }

    /**
     * This function validates html "doctype".
     * <samp>
     * <b>Check ID:</b> <a href="http://admin.mydev:9090/check/check_function_edit.php?id=225" target="_blank">225</a><br/>
     * <b>References</b>: 1<br/>
     * <b>Check:</b> Strict doctype is declared.<br/>
     * <b>Function:</b> <code>return <u>BasicFunctions::validateDoctype()</u>;</code>
     * </samp>
     * @return bool
     * @access public
     */
    public static function validateDoctype() {
        global $global_content_dom;

        $doctypes = $global_content_dom->find("doctype");

        if (count($doctypes) == 0) return false;

        foreach ($doctypes as $doctype) {
            foreach ($doctype->attr as $doctype_content => $garbage)
                if (stristr($doctype_content, "-//W3C//DTD HTML 4.01//EN") ||
                    stristr($doctype_content, "-//W3C//DTD HTML 4.0//EN") ||
                    stristr($doctype_content, "-//W3C//DTD XHTML 1.0 Strict//EN")
                )
                    return true;
        }
        return false;
    }

    /**
     * Wrapper for is_numeric php function
     * @param mixed $val
     * @return bool
     * @author Joel Carvalho
     * @version 1.0 10/04/2015
     */
    public static function isNumeric($val) {
        return is_numeric($val);
    }

    /**
     * Return element with specified filters
     * @param mixed $filter
     * @param simple_html_dom_node $e
     * @return bool
     * @author Joel Carvalho
     * @version 1.6.3 22/09/2015
     */
    private static function getElement($filter, $e){
        global $global_content_dom, $parents, $elements, $childs, $global_check_id;
        if (!isset($parents)) $parents = array();
        if (!isset($elements)) $elements = array();
        if (!isset($childs)) $childs = array();

        if (empty($filter)) return $e;
        // filter some elements
        if ($e->tag==='html' || $e->tag==='head') return null;
        if ($e->tag!=='body' && ($e->parent()->tag==='html' || $e->parent()->tag==='head')) return null;

        if (is_array($filter)){
            $parent=(isset($filter['parent']))?$filter['parent']:'*';
            $element=(isset($filter['element']))?$filter['element']:'*';
            $child=(isset($filter['child']))?$filter['child']:null;
            $text=(isset($filter['text']))?$filter['text']:null;

            if (!isset($elements[$global_check_id])){
                $list=$global_content_dom->find($element);
                if ($parent==='*'){
                    $elements[$global_check_id]=array();
                    foreach($list as $se)
                        $elements[$global_check_id][$se->tag_start]=true;
                }
            }

            if ($parent!=='*' && !isset($parents[$parent]))
                $parents[$parent]=$global_content_dom->find($parent);

            if ($element==='*' && !isset($elements[$global_check_id]))
                $elements[$global_check_id]=$parents[$parent];
            else if (!isset($elements[$global_check_id])){
                $elements[$global_check_id]=array();
                foreach($parents[$parent] as $p){
                    foreach($list as $se){
                        if (self::isParent($se,$p))
                            $elements[$global_check_id][$se->tag_start]=true;
                    }
                }
            }

            if ($child!=null && !isset($childs[$global_check_id])){
                $list=$global_content_dom->find($child);
                $childs[$global_check_id]=array();
                foreach($list as $se){
                    $childs[$global_check_id][$se->parent()->tag_start]=true;
                }
            }

            if ($element==='*'){
                if ($child!=null && $element==='*' && $parent==='*' && $childs[$global_check_id][$e->tag_start])
                    return self::checkElement($e, $text);
                foreach($elements[$global_check_id] as $p){
                    if ($child==null && self::isParent($e,$p))
                        return self::checkElement($e,$text);
                    else if($child!=null && self::isParent($e,$p) && $childs[$global_check_id][$e->tag_start])
                        return self::checkElement($e,$text);
                }
            }
            else if($elements[$global_check_id][$e->tag_start] && $child==null)
                return self::checkElement($e, $text);
            else if ($child!=null) {
                if ($childs[$global_check_id][$e->tag_start] && $elements[$global_check_id][$e->tag_start])
                    return self::checkElement($e, $text);
            }
        }
        return null;
    }

    /**
     * Check if a element have the specified parent
     * @param \simple_html_dom_node $e html element
     * @param \simple_html_dom_node $parent expected parent
     * @return bool
     * @access public
     * @author Joel Carvalho
     * @version 1.6.1 07/07/2015
     */
    private static function isParent($e,$parent){
        return (strpos(','.$e->attr[WC.'parents'].',',','.$parent->attr[WC.'id'].',')!==false);
    }

    /**
     * Check if a element have the expected plaintext
     * @param simple_html_dom_node $e html element
     * @param string $text expected plaintext expression
     * @return mixed return element if true and null if not
     * @access public
     * @author Joel Carvalho
     * @version 1.6.1 02/07/2015
     */
    private static function checkElement($e, $text){
        if (!isset($text)) return $e;
        else if (isset($text) && trim($e->plaintext)===$text)
            return $e;
        else if (isset($text) && (@preg_match($text, trim($e->plaintext))===1))
            return $e;
        return null;
    }

    /**
     * Assert expected attribute values
     * @param mixed $attributes
     * @return bool
     * @throws \Exception
     * @author Joel Carvalho
     * @version 1.6.4.1 04/11/2015
     */
    public static function assertAttributeValue($attributes){
        global $aValidator, $assert, $global_e;

        foreach($attributes as $attr=>$value){
            if ($attr==='text'){
                $aValue=$global_e->plaintext;
                $eValue=$value;
            }
            else if (strpos($attr,'color')!==false){
                $opacity=1;
                // set attribute value related with colors
                if (is_array($global_e->attr) && isset($global_e->attr[WC.$attr]))
                    $aValue=Utility::rgbaConvert($global_e->attr[WC.$attr]);
                else {$assert[]=null; break;};
                // evaluate color value and opacity when defined like => 'SystemColor1:0.75'
                $complex_color=explode(":",$value);
                if (count($complex_color)>1){
                    $opacity=$complex_color[1];
                    $value=$complex_color[0];
                }

                if (is_array($aValidator->system_colors) && isset($aValidator->system_colors[$value]))
                    $value=$aValidator->system_colors[$value];
                if ($opacity==='x'){
                  $opacity=1;
                  $aValue=Utility::rgbaConvert($aValue, $opacity);
                }
                $eValue=Utility::rgbaConvert($value, $opacity);
            }else{
                //set attribute value related with anything else
                $aValue=self::getAttribute($attr,$global_e);
                if ($aValue===false) {
                    $assert[] = false;
                    break;
                }
                $eValue=$value;
            }

            // Check Values!
            if (!isset($aValue)) {
                $assert[] = null;
                break;
            }
            else if (@preg_match($eValue, null) === false)
                $assert[]=($aValue===$eValue);
            else
                $assert[]=(preg_match($eValue, $aValue)===1);
        }
        return (self::assertTrue($assert));
    }

    /**
     * Assert consistency attribute values
     * @param mixed $attributes
     * @return bool
     * @throws \Exception
     * @author Joel Carvalho
     * @version 1.6.4.1 04/11/2015
     */
    public static function assertAttributeConsistency($attributes){
        global $global_e, $assert, $consistency, $global_check_id;
        if (!isset($consistency)) $consistency = array();
        if (!isset($consistency[$global_check_id]))
            $consistency[$global_check_id] = array();

        foreach($attributes as $pos=>$attr){
            if ($attr==='text')
                $aValue=$global_e->plaintext;
            else if (strpos($attr,'color')!==false){
                // set attribute value related with colors
                if (isset($global_e->attr[WC.$attr]))
                    $aValue=Utility::rgbaConvert($global_e->attr[WC.$attr]);
                else return ($assert[]=null);
            }else{
                //set attribute value related with anything else
                $aValue=self::getAttribute($attr,$global_e);
                if ($aValue===false)
                    throw new \Exception('NotFound');
            }

            if (!isset($consistency[$global_check_id][$attr]))
                $consistency[$global_check_id][$attr] = $aValue;
            $eValue=$consistency[$global_check_id][$attr];

            // Check Values!
            $assert[]=($aValue===$eValue);
        }
        return (self::assertTrue($assert));
    }
    
    /**
     * Assert true of a value or an array of values
     * @param string $v
     * @return bool
     * @throws \Exception
     * @author Joel Carvalho
     * @version 1.6 03/06/2015
     */
    public static function assertTrue($v){
        global $assert, $error_msg;
        $error=$error_msg;
        $vars=array();
        if (!is_array($v)) $vars[]=$v;
        else $vars=$v;
        $vars=Utility::mergeRows($assert,$vars);
        foreach ($vars as $var){
            if (!is_bool($var)) return($assert[]=null);
            if (!$var) throw new \Exception($error, 100);
        }
        $error_msg=""; // Error message reset for every execution
        return($assert[]=true);
    }

    /**
     * Assert not equals of specified values
     * @param mixed $va
     * @param mixed $vb
     * @return bool
     * @author Joel Carvalho
     * @version 1.6 18/06/2015
     */
    public static function assertNotEquals($va,$vb){
        global $assert;
        if (is_string($va) && is_string($vb)){
            if (strcmp($va,$vb)==0) return($assert[]=false);
            return($assert[]=true);
        }
        return($assert[]=($va!==$vb));
    }

    /**
     * Assert Equals of specified values
     * @param mixed $va
     * @param mixed $vb
     * @return bool
     * @author Joel Carvalho
     * @version 1.6 18/06/2015
     */
    public static function assertEquals($va,$vb){
        global $assert;
        if (is_string($va) && is_string($vb)){
            if (strcmp($va,$vb)!=0) return($assert[]=false);
            return($assert[]=true);
        }
        return($assert[]=($va===$vb));
    }

    /**
     * Assert if expected value is equal or greater than specified contrast attribute ($var:$option)
     * @param string $var
     * @param float $value
     * @param string $option
     * @return bool
     * @author Joel Carvalho
     * @version 1.6 19/06/2015
     */
    public static function assertContrast($var, $value, $option=null){
        global $assert;

        if (strcmp($var,'LuminosityContrastRatio')===0)
            $contrast=WC."color-contrast-rl";
        elseif (strcmp($var,'BrightnessContrast')===0)
            $contrast=WC."color-bdif";
        elseif (strcmp($var,'ColorContrast')===0)
            $contrast=WC."color-contrast";

        if ($option!==null)
            $contrast.=':'.$option;

        $ratio=self::getAttribute($contrast);
        if ($ratio===false)
            return $assert[]=null;
        return ($assert[]=($ratio>=$value));
    }

    /**
     * Set Error MSG for REPAIR extra info
     * @param string $msg
     * @author Joel Carvalho
     * @version 1.6.3 23/09/2015
     */
    public static function setErrorMsg($msg){
        global $error_msg;
        $error_msg=$msg;
    }

    /**
     * Send ForceSkip Exception to Validator for skiping the checkpoint validation
     * @param bool $skip
     * @throws \Exception
     * @author Joel Carvalho
     * @version 1.6.3 24/09/2015
     */
    public static function skipCheck($skip){
        if ($skip===true)
            throw new \Exception('ForceSkip');
    }

    /**
     * Get the length of specified attribute or false
     * @param string $attr attribute name
     * @param simple_html_dom_node $e html element
     * @return mixed attribute value or false
     * @access public
     * @author Joel Carvalho
     * @version 1.6 18/06/2015
     */
    public static function getAttributeLength($attr, $e=null) {
        $value=self::getAttribute($attr,$e);
        if ($value===false) return false;
        return strlen($value);
    }

    /**
     * Set Element to check from specified filters
     * @param mixed $filter
     *  $filter["parent"]   => selector for parent elements
     *  $filter["element"]  => selector for element to check
     *  $filter["child"]    => selector for child elements
     *  $filter["text"]     => Regex or value for plaintext
     * @throws \Exception
     * @author Joel Carvalho
     * @version 1.6.3 23/09/2015
     */
    public static function filterElement($filter){
        global $global_e;
        $e=self::getElement($filter, $global_e);
        if (!isset($e) || $e->nodetype==2 || $global_e->nodetype==2) throw new \Exception('NotFound');
        $global_e=$e;
    }

    /**
     * Return childs from specified filter
     * @param mixed $filter
     *  $filter["child"]    => selector for child elements
     *  $filter["pos"]      => "first" | "last"
     *  $filter["strict"]   => true | false
     * @param \simple_html_dom_node $e
     * @return mixed
     * @author Joel Carvalho
     * @version 1.6.3 23/09/2015
     */
    private static function getChilds($e, $filter=null){
        $res=null;
        $child="*";
        if (is_array($filter)){
            $child=(isset($filter['child']))?$filter['child']:'*';
            if (isset($filter['pos'])) {
                if ($filter['pos']==='last') $pos=-1;
                else if($filter['pos']==='first') $pos=0;
            }
        }
        if (isset($pos)) $res=$e->find($child,$pos);
        else $res=$e->find($child);

        if ($filter["strict"]){
            foreach($res as $i=>$sel){
                if ($sel->parent()!==$e)
                    unset($res[$i]);
            }
        }

        return $res;
    }

    /**
     * Get attribute value of specified filtered element
     * or attribute value of actual element if none specified
     * @param string $attr
     *  $attr => "count"            | count($e)
     *  $attr => "count_childs"     | count($e->find('*'))
     *  $attr => "text"             | $e->plaintext()
     *  $attr => "tag"              | $e->tag()
     *  $attr => "..."              | $e->attr[PREFIX.$attr] or $e->attr[$attr]
     * @param \simple_html_dom_node $e
     * @return mixed return attribute value or false if $attr|$e not found
     * @author Joel Carvalho
     * @version 1.6.3 22/09/2015
     */
    private static function getAttribute($attr, $e=null){
        $v=null;

        if (($attr==='count' || $attr==='count_childs') && is_array($e)) $v=count($e);
        else if ($attr==='count_childs' && $e!=null) $v=count($e->children());
        else if ($attr==='count' || $attr==='count_childs') $v=0;
        if ($v!==null) return intval($v);

        if (is_array($e) && count($e)>=1) $e=$e[0];
        if ($e===null || is_array($e)) return false;

        if ($attr==='text')
            $v=trim($e->plaintext);
        else if($attr==='tag')
            $v=$e->tag;
        else if (is_array($e->attr) && isset($e->attr[WC.$attr]))
            $v=trim($e->attr[WC.$attr]);
        else if (is_array($e->attr) && isset($e->attr[$attr]))
            $v=trim($e->attr[$attr]);

        if ($v===null || $v===false || $v==='false')
            return false;
        if ($v===true || $v==='true')
            return true;
        if ($v==="")
            return $v;
        if ($v===(string)floatval($v))
            return floatval($v);
        if ($v===(string)intval($v))
            return intval($v);
        return $v;
    }

    /**
     * Get the value of specified attribute or false if not found
     * @param string $attr attribute name
     * @param mixed $filter
     *  $filter["parent"]   => true | selector
     *  $filter["child"]    => selector for child elements
     *  $filter["pos"]      => "first" | "last"
     * @return mixed attribute value or false
     * @access public
     * @author Joel Carvalho
     * @version 1.6.3 24/09/2015
     */
    public static function getAttributeValue($attr, $filter=null) {
        global $global_e, $global_content_dom;
        if ($filter["parent"]===true)
          $e=$global_e->parent();
        else if ($filter["parent"]){
          $ep=$global_content_dom->find($filter["parent"]);
          foreach($ep as $p){
            if (self::isParent($global_e,$p))
              $e=$p;
              break;
            }
        }
        else $e=$global_e;

        if (isset($filter["child"])) $e=self::getChilds($e, $filter);

        return self::getAttribute($attr, $e);
    }
}

?>