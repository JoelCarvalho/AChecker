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
use QChecker\DAO\LangCodesDAO;

if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");
include_once(AC_INCLUDE_PATH . 'classes/DAO/LangCodesDAO.class.php');

define("DEFAULT_FONT_SIZE", 12);
define("DEFAULT_FONT_FORMAT", "pt");

/**
 * BasicChecks.class.php
 * Class for accessibility validate
 * This class contains basic functions called by BasicFunctions.class.php
 *
 * @access    public
 * @author    Cindy Qi Li
 * @package   checker
 */
class BasicChecks {

    /**
     * cut out language code from given $lang
     * return language code
     */
    public static function cutOutLangCode($lang)
    {
        $words = explode("-", $lang);
        return trim($words[0]);
    }

    /**
     * return array of all the 2-letter & 3-letter language codes with direction 'rtl'
     */
    public static function getRtlLangCodes()
    {
        $langCodesDAO = new LangCodesDAO();

        return $langCodesDAO->GetLangCodeByDirection('rtl');
    }

    /**
     * check if the text is in one of the search string defined in $search_strings
     * @param $text : text to check
     *        $search_strings: array of match string. The string could be %[string]% or %[string] or [string]%
     * @return true if in, otherwise, return false
     */
    public static function inSearchString($text, $search_strings)
    {
        foreach ($search_strings as $str) {
            $str = trim($str);
            $prefix = substr($str, 0, 1);
            $suffix = substr($str, -1);

            if ($prefix == '%' && $suffix == '%') {  // match '%match%' 
                if (stripos($text, substr($str, 1, -1)) > 0) return true;
            } else if ($prefix == '%') {  // match '%match'
                $match = substr($str, 1);
                if (substr($text, strlen($match) * (-1)) == $match) return true;
            } else if ($suffix == '%') {  // match 'match%'
                $match = substr($str, 0, -1);
                if (substr($text, 0, strlen($match)) == $match) return true;
            } else if ($text == $str) {
                return true;
            }
        }
        return false;
    }

    /**
     * check if the inner text is in one of the search string defined in checks.search_str
     * return true if in, otherwise, return false
     */
    public static function isTextInSearchString($text, $check_id, $e)
    {
        $text = strtolower(trim($text));

        $checksDAO = new ChecksDAO();
        $row = $checksDAO->getCheckByID($check_id);

        $search_strings = explode(',', strtolower(_AC($row['search_str'])));

        if (!is_array($search_strings)) return true;
        else return BasicChecks::inSearchString($text, $search_strings);
    }

    /**
     * Makes a guess about the table type.
     * Returns true if this should be a data table, false if layout table.
     */
    public static function isDataTable($e)
    {
        global $is_data_table;

        // "table" element containing <th> is considered a data table
        if ($is_data_table) return;

        foreach ($e->children() as $child) {
            if ($child->tag == "th")
                $is_data_table = true;
            else
                BasicChecks::isDataTable($child);
        }
    }

    /**
     * Check recursively to find if $global_e has a parent with tag $parent_tag
     * return true if found, otherwise, false
     */
    public static function hasParent($e, $parent_tag)
    {
        if ($e->parent() == NULL) return false;

        if ($e->parent()->tag == $parent_tag)
            return true;
        else
            return BasicChecks::hasParent($e->parent(), $parent_tag);
    }

    /**
     * Check recursively to find the number of children in $e with tag $child_tag
     * return number of qualified children
     */
    public static function getNumOfTagRecursiveInChildren($e, $tag)
    {
        $num = 0;

        foreach ($e->children() as $child)
            if ($child->tag == $tag) $num++;
            else $num += BasicChecks::getNumOfTagRecursiveInChildren($child, $tag);

        return $num;
    }

    /**
     * Check recursively if there are duplicate $attr defined in children of $e
     * set global var hasDuplicateAttribute to true if there is, otherwise, set it to false
     * @author Cindy Li
     * @author Joel Carvalho (Modified to solve a issue with the new version of simple_html_dom)
     * @version 1.0 27/04/2015
     */
    public static function hasDuplicateAttribute($e, $attr, &$id_array=array()){
        global $has_duplicate_attribute;

        if (empty($has_duplicate_attribute))
            $has_duplicate_attribute = false;

        foreach ($e->children() as $child) {
            $id_val="";
            if (array_key_exists($attr, $child->attr))
                $id_val = strtolower(trim($child->attr[$attr]));

            // A hack to swap out the element line number for the duplicate ID line number
            if ($id_val <> "" && in_array($id_val, $id_array)) {
                global $global_content_dom;
                $has_duplicate_attribute["linenumber"] = $child->linenumber;
                $has_duplicate_attribute["value"] = strtolower($child->id);
                $has_duplicate_attribute["attr"] = $attr;
                $has_duplicate_attribute["html"] = substr($global_content_dom->html, $e->tag_start);
                return $has_duplicate_attribute;
            } else {
                if ($id_val <> "") $id_array[]=$id_val;
                BasicChecks::hasDuplicateAttribute($child, $attr, $id_array);
            }
        }
    }

    /**
     * Get number of header rows and number of rows that have header column
     * return array of (num_of_header_rows, num_of_rows_with_header_col)
     */
    public static function getNumOfHeaderRowCol($e)
    {
        $num_of_header_rows = 0;
        $num_of_rows_with_header_col = 0;

        foreach ($e->find("tr") as $row) {
            $num_of_th = count($row->find("th"));

            if ($num_of_th > 1) $num_of_header_rows++;
            if ($num_of_th == 1) $num_of_rows_with_header_col++;
        }

        return array($num_of_header_rows, $num_of_rows_with_header_col);
    }

    /**
     * called by BasicFunctions::hasFieldsetOnMultiCheckbox()
     * Check if form has "fieldset" and "legend" to group multiple checkbox buttons.
     * @return true if has, otherwise, false
     */
    public static function hasFieldsetOnMultiCheckbox($e)
    {
        // find if there are radio buttons with same name
        $children = $e->children();
        $num_of_children = count($children);

        foreach ($children as $i => $child) {
            if (strtolower(trim($child->attr["type"])) == "checkbox") {
                $this_name = strtolower(trim($child->attr["name"]));

                for ($j = $i + 1; $j <= $num_of_children; $j++)
                    // if there are radio buttons with same name,
                    // check if they are contained in "fieldset" and "legend" elements
                    if (strtolower(trim($children[$j]->attr["name"])) == $this_name)
                        if (BasicChecks::hasParent($e, "fieldset"))
                            return BasicChecks::hasParent($e, "legend");
                        else
                            return false;
            } else
                return BasicChecks::hasFieldsetOnMultiCheckbox($child);
        }

        return true;
    }

    /**
     * check if value in the given attribute is a valid language code
     * return true if valid, otherwise, return false
     */
    public static function isValidLangCode($code)
    {
        // The allowed characters in a valid language code are letters, numbers or dash(-).
        if (!preg_match("/^[a-zA-Z0-9-]+$/", $code)) {
            return false;
        }

        $code = BasicChecks::cutOutLangCode($code);
        $langCodesDAO = new LangCodesDAO();

        if (strlen($code) == 2) {
            $rows = $langCodesDAO->GetLangCodeBy2LetterCode($code);
        } else if (strlen($code) == 3) {
            $rows = $langCodesDAO->GetLangCodeBy3LetterCode($code);
        } else {
            return false;
        }

        return (is_array($rows));
    }

    /**
     * Return file location based on base href or uri
     * return file itself if both base href and uri are empty.
     */
    public static function getFile($src_file, $base_href, $uri)
    {
        if (preg_match('/http.*(\:\/\/).*/', $src_file)) {
            $file = $src_file;
        } else {
            // URI that image relatively located to
            // Note: base_href is from <base href="...">
            if (isset($base_href) && $base_href <> '') {
                if (substr($base_href, -1) <> '/') $base_href .= '/';
            } else if (isset($uri) && $uri <> '') {
                preg_match('/^(.*\:\/\/.*\/).*/', $uri, $matches);
                if (!isset($matches[1])) $uri .= '/';
                else $uri = $matches[1];
            }

            if (substr($src_file, 0, 1) == '/')  //absolute path
            {
                if (isset($base_href) && $base_href <> '') {
                    $prefix_uri = $base_href;
                } else if (isset($uri) && $uri <> '') {
                    $prefix_uri = $uri;
                }

                if (isset($prefix_uri) && $prefix_uri <> '') {
                    preg_match('/^(.*\:\/\/)(.*)/', $uri, $matches);
                    $root_uri = $matches[1] . substr($matches[2], 0, strpos($matches[2], '/'));
                    $file = $root_uri . $src_file;
                }
            } else // relative path
            {
                if (isset($base_href) && $base_href <> '') {
                    $file = $base_href . $src_file;
                } else if (isset($uri) && $uri <> '') {
                    $file = $uri . $src_file;
                }
            }
        }

        if (!isset($file)) $file = $src_file;

        return $file;
    }

    /**
     * Check if the luminosity contrast ratio between $color1 and $color2 is at least 5:1
     * Input: color values to compare: $color1 & $color2. Color value can be one of: rgb(x,x,x), #xxxxxx, colorname
     * Return: true or false
     */
    public static function has_good_contrast_waiert($color1, $color2)
    {
        include_once(AC_INCLUDE_PATH . "classes/Validator/ColorValue.class.php");

        $color1 = new ColorValue($color1);
        $color2 = new ColorValue($color2);

        if (!$color1->isValid() || !$color2->isValid())
            return true;

        $colorR1 = $color1->getRed();
        $colorG1 = $color1->getGreen();
        $colorB1 = $color1->getBlue();

        $colorR2 = $color2->getRed();
        $colorG2 = $color2->getGreen();
        $colorB2 = $color2->getBlue();

        $brightness1 = (($colorR1 * 299) +
                ($colorG1 * 587) +
                ($colorB1 * 114)) / 1000;

        $brightness2 = (($colorR2 * 299) +
                ($colorG2 * 587) +
                ($colorB2 * 114)) / 1000;

        $difference = 0;
        if ($brightness1 > $brightness2) {
            $difference = $brightness1 - $brightness2;
        } else {
            $difference = $brightness2 - $brightness1;
        }

        if ($difference < 125) {
            return false;
        }

        // calculate the color difference
        $difference = 0;
        // red
        if ($colorR1 > $colorR2) {
            $difference = $colorR1 - $colorR2;
        } else {
            $difference = $colorR2 - $colorR1;
        }

        // green
        if ($colorG1 > $colorG2) {
            $difference += $colorG1 - $colorG2;
        } else {
            $difference += $colorG2 - $colorG1;
        }

        // blue
        if ($colorB1 > $colorB2) {
            $difference += $colorB1 - $colorB2;
        } else {
            $difference += $colorB2 - $colorB1;
        }

        return ($difference > 499);
    }

    /**
     * Check recursively to find if $e has a parent with tag $parent_tag
     * return true if found, otherwise, false
     */
    public static function has_parent($e, $parent_tag)
    {
        if ($e->parent() == NULL) return false;

        if ($e->parent()->tag == $parent_tag)
            return true;
        else
            return BasicChecks::has_parent($e->parent(), $parent_tag);
    }


    /**
     * cut out language code from given $lang
     * return language code
     */
    public static function cut_out_lang_code($lang)
    {
        $words = explode("-", $lang);
        return trim($words[0]);
    }

    /**
     * check if $code is a valid language code
     * return true if valid, otherwise, return false
     */
    public static function valid_lang_code($code)
    {
        global $db;

        $code = BasicChecks::cut_out_lang_code($code);

        $sql = "SELECT COUNT(*) cnt FROM " . TABLE_PREFIX . "lang_codes WHERE ";

        if (strlen($code) == 2) $sql .= "code_2letters = '" . $code . "'";
        else if (strlen($code) == 3) $sql .= "code_3letters = '" . $code . "'";
        else return false;

        $result = $db->query($sql) or die($db->error);
        $row = $result->fetch_assoc();

        return ($row["cnt"] > 0);
    }


    /**
     * find language code defined in html
     * return language code
     */
    public static function get_lang_code($content_dom)
    {
        // get html language
        $e_htmls = $content_dom->find("html");

        foreach ($e_htmls as $e_html) {
            if (isset($e_html->attr["xml:lang"])) {
                $lang = trim($e_html->attr["xml:lang"]);
                break;
            } else if (isset($e_html->attr["lang"])) {
                $lang = trim($e_html->attr["lang"]);
                break;
            }
        }

        return BasicChecks::cut_out_lang_code($lang);
    }

    /**
     * check if $e has associated label
     * return true if has, otherwise, return false
     */
    public static function has_associated_label($e, $content_dom)
    {
        // 1. The element $e is contained by a "label" element
        // 2. The element $e has a "title" attribute
        if ($e->parent()->tag == "label" || isset($e->attr["title"])) return true;

        // 3. The element $e has an "id" attribute value that matches the "for" attribute value of a "label" element
        $input_id = $e->attr["id"];

        if ($input_id == "") return false;  // attribute "id" must exist

        foreach ($content_dom->find("label") as $e_label)
            if (strtolower(trim($e_label->attr["for"])) == strtolower(trim($e->attr["id"])))
                return true;

        return false;
    }

    /**
     * Check radio button groups are marked using "fieldset" and "legend" elements
     * Return: use global variable $is_radio_buttons_grouped to return true (grouped properly) or false (not grouped)
     */
    public static function is_radio_buttons_grouped($e)
    {
        $radio_buttons = array();

        foreach ($e->find("input") as $e_input) {
            if (strtolower(trim($e_input->attr["type"])) == "radio")
                array_push($radio_buttons, $e_input);
        }

        for ($i = 0; $i < count($radio_buttons); $i++) {
            for ($j = 0; $j < count($radio_buttons); $j++) {
                if ($i <> $j && strtolower(trim($radio_buttons[$i]->attr["name"])) == strtolower(trim($radio_buttons[$j]->attr["name"]))
                    && !BasicChecks::has_parent($radio_buttons[$i], "fieldset") && !BasicChecks::has_parent($radio_buttons[$i], "legend")
                )
                    return false;
            }
        }

        return true;
    }

    /**
     * Makes a guess about the table type.
     * Returns true if this should be a data table, false if layout table.
     */
    public static function is_data_table($e)
    {
        global $is_data_table;

        // "table" element containing <th> is considered a data table
        if ($is_data_table) return;

        foreach ($e->children() as $child) {
            if ($child->tag == "th")
                $is_data_table = true;
            else
                BasicChecks::is_data_table($child);
        }
    }


    /**
     * check if associated label of $e has text
     * return true if has, otherwise, return false
     */
    public static function associated_label_has_text($e, $content_dom)
    {
        // 1. The element $e has a "title" attribute
        if (trim($e->attr["title"]) <> "") return true;

        // 2. The element $e is contained by a "label" element
        if ($e->parent()->tag == "label") {
            $pattern = "/(.*)" . preg_quote($e->outertext, '/') . "/";
            preg_match($pattern, $e->parent->innertext, $matches);
            if (strlen(trim($matches[1])) > 0) return true;
        }

        // 3. The element $e has an "id" attribute value that matches the "for" attribute value of a "label" element
        $input_id = $e->attr["id"];

        if ($input_id == "") return false;  // attribute "id" must exist

        foreach ($content_dom->find("label") as $e_label) {
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
     * get input and returns an item on the table
     * prende in input un elemento e restituisce la relativa table
     */
    public static function getTable($e)
    {

        while ($e->parent()->tag != "table" && $e->parent()->tag != null)
            $e = $e->parent();

        if ($e->parent()->tag == "html")
            return null;
        else
            return $e->parent();

    }

    /**
     * gets an array of id (headers attribute of a td element) and verifies that each id is associated with an th
     * prende un array di id (attributo headers di un elemento td) e verifica che ogni id sia associato a un th
     */
    public static function checkIdInTable($t, $ids)
    {

        $th = $t->find("th");
        $num = 0;
        $size_of_ids = sizeof($ids);

        //for($i = 0; $i < $size_of_ids; $i ++) {
        //for($j = 0; $j < sizeof ( $th ); $j ++) {				
        //if (isset ( $th [$j]->attr ['id'] ) && $th [$j]->attr ['id'] == $ids [$i]) {
        //$num ++;
        //break;
        //}d
        //}
        //}

        foreach ($ids as $one_id) {
            foreach ($th as $one_th) {
                if (isset ($one_th->attr ['id']) && $one_th->attr ['id'] == $one_id) {
                    $num++;
                    break;
                }
            }
        }

        if ($num == $size_of_ids) //ho trovato un id in un th per ogni id di un td
            return true;
        else
            return false;
    }

    /**
     * verify the existence of a row for an element td
     * verifica l'esistenza di un'intestazione di riga per un elemento td
     */
    public static function getRowHeader($e)
    {

        while ($e->prev_sibling() != null && $e->prev_sibling()->tag != "th") {

            $e = $e->prev_sibling();
        }

        if ($e->prev_sibling() == null)
            return null;
        else

            return $e->prev_sibling();
        /*
			if(isset($e->attr["scope"]) && $e->attr["scope"]=="row")
				return $e;
			else
				return null;
			*/

    }

    /**
     * checks for the existence of a column header for a td element
     * verifica l'esistenza di un'intestazione di colonna per un elemento td
     */
    public static function getColHeader($e)
    {

        $pos = 0;
        $e_count = $e;
        //find the position in the row of td
        //trovo la posizione nella riga di td
        while ($e_count->prev_sibling() != null) {
            $pos++;
            $e_count = $e_count->prev_sibling();
        }

        $t = BasicChecks::getTable($e);
        // there isn't a <table> tag
        //non c'è il tag <table>
        if ($t == null) {
            return true; //tabella mal composta
        }

        $tr = $t->find("tr");
        $size_of_tr = sizeof($tr);

        if ($tr == null || $size_of_tr == 0)
            return true; //tabella mal composta - table is not well formed


        for ($i = 0; $i < $size_of_tr - 1; $i++) {
            $th_next = $tr [$i + 1]->find("th");
            if ($th_next == null || sizeof($th_next) == 0)
                break; //l'i-esima tr contiene l'intestazione pi interna
        }

        $h = $tr [$i]->childNodes();
        // Verify that the header box in place $pos  is actually a header
        //verifico che la casella in posizione $pos della presunta riga di intestazione sia effettivamente un'intestazione 
        if (isset ($h [$pos]) && $h [$pos]->tag == "th" /*&& isset($h[$pos]->attr["scope"]) && $h[$pos]->attr["scope"]=="col"*/)
            return $h [$pos];
        else
            return null;

    }

    /**
     * VOID...
     */
    public static function rec_check_15005($e)
    {
        if ($e->tag == 'script' || $e->tag == 'object' || $e->tag == 'applet' || isset ($e->attr ['onload']) || isset ($e->attr ['onunload']) || isset ($e->attr ['onclick']) || isset ($e->attr ['ondblclick']) || isset ($e->attr ['onmousedown']) || isset ($e->attr ['onmouseup']) || isset ($e->attr ['onmouseover']) || isset ($e->attr ['onmousemove']) || isset ($e->attr ['onmouse']) || isset ($e->attr ['onblur']) || isset ($e->attr ['onkeypress']) || isset ($e->attr ['onkeydown']) || isset ($e->attr ['onkeyup']) || isset ($e->attr ['onsubmit']) || isset ($e->attr ['onreset']) || isset ($e->attr ['onselect']) || isset ($e->attr ['onchange']))
            return false;

        else
            $c = $e->children();
        $res = true;
        foreach ($c as $elem) {
            $res = BasicChecks::rec_check_15005($elem);
            if ($res == false)
                return $res;
        }
        return $res;

    }

}

?>