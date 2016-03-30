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

use QChecker\Language\Language;

if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");

/**
 * Utility.class.php
 * Utility functions
 * @access    public
 * @author    Cindy Qi Li
 */
class Utility {

    /**
     * return a unique session id based on timestamp
     * @access  public
     * @param   none
     * @return  language code
     * @author  Cindy Qi Li
     */
    public static function getSessionID()
    {
        return sha1(mt_rand() . microtime(TRUE));
    }

    /**
     * Return the valid format of given $uri. Otherwise, return FALSE
     * Return $uri itself if it has valid content,
     * otherwise, return the first listed uri that has valid content:
     * "http://".$uri
     * "https://".$uri
     * "http://www.".$uri
     * "https://www.".$uri
     * If none of above has valid content, return FALSE
     * @access  public
     * @param   string $uri The uri address
     * @return  true: if valid; false: if invalid
     * @author  Cindy Qi Li
     */
    public static function getValidURI($uri)
    {
        $uri_prefixes = array('http://', 'https://', 'http://www.', 'https://www.');
        $already_a_uri = false;

        $uri = trim($uri);

        // Check whether the URI prefixes are already in place
        foreach ($uri_prefixes as $prefix) {
            if (substr($uri, 0, strlen($prefix)) == $prefix) {
                $already_a_uri = true;
                break;
            }
        }
        if (!$already_a_uri) {
            // try adding uri prefixes in front of given uri
            foreach ($uri_prefixes as $prefix) {
                if (substr($uri, 0, strlen($prefix)) <> $prefix) {
                    $prefixed_uri = $prefix . $uri;
                    $connection = @file_get_contents($prefixed_uri);

                    if (!$connection) {
                        continue;
                    } else {
                        return $prefixed_uri;
                    }
                }
            }
        } else {
            $connection = @file_get_contents($uri);

            if ($connection) return $uri;
            else return $uri;
        }

        // no matching valid uri
        return false;
    }

    /**
     * convert text new lines to html tag <br/>
     * @access  public
     * @param   string
     * @return  converted string
     * @author  Cindy Qi Li
     */
    public static function convertHTMLNewLine($str)
    {
        $new_line_array = array("\n", "\r", "\n\r", "\r\n");

        $found_match = false;

        if (strlen(trim($str)) == 0) return "";

        foreach ($new_line_array as $new_line)
            if (preg_match('/' . preg_quote($new_line) . '/', $str) > 0) {
                $search_new_line = $new_line;
                $found_match = true;
            }

        if ($found_match)
            return preg_replace('/' . preg_quote($search_new_line) . '/', "<br />", $str);
        else
            return $str;
    }

    /**
     * Return array of seals to display
     * Some guidelines are in the same group. This is defined in guidelines.subset.
     * The format of guidelines.subset is [group_name]-[priority].
     * When the guidelines in the same group are validated, only the seal for the guideline
     * with the highest [priority] number is displayed.
     * @access  public
     * @param   $guidelines : array of guideline table rows
     * @return  converted string
     * @author  Cindy Qi Li
     */
    public static function getSeals($guidelines)
    {
        foreach ($guidelines as $guideline) {
            if ($guideline['subset'] == '0') {
                $seals[] = array('title' => $guideline['title'],
                    'guideline' => $guideline['abbr'],
                    'seal_icon_name' => $guideline['seal_icon_name']);
            } else {
                list($group, $priority) = explode('-', $guideline['subset']);

                if (!isset($highest_priority[$group]['priority']) || $highest_priority[$group]['priority'] < $priority) {
                    $highest_priority[$group]['priority'] = $priority;
                    $highest_priority[$group]['guideline'] = $guideline;
                }
            }// end of outer if
        } // end of foreach

        if (is_array($highest_priority)) {
            foreach ($highest_priority as $group => $guideline_to_display)
                $seals[] = array('title' => $guideline_to_display['guideline']['title'],
                    'guideline' => $guideline_to_display['guideline']['abbr'],
                    'seal_icon_name' => $guideline_to_display['guideline']['seal_icon_name']);
        }

        return $seals;
    }

    /**
     * Check if the free memory is big enough to process the given file size
     * @access  public
     * @param   $filesize : file size
     * @return  true if enough, otherwise, return false
     * @author  Cindy Qi Li
     */
    public static function hasEnoughMemory($filesize)
    {
        $memory_limit = ini_get('memory_limit');
        if ($memory_limit != '') {
            switch ($memory_limit{strlen($memory_limit) - 1}) {
                case 'G':
                    $memory_limit *= 1024;
                case 'M':
                    $memory_limit *= 1024;
                case 'K':
                    $memory_limit *= 1024;
            }
        } else
            return true;

        $used_memory = memory_get_usage();

        if (($filesize * 160) > ($memory_limit - $used_memory))
            return false;
        else
            return true;
    }

    /**
     * Sort $inArray in the order of the number presented in the field with name $fieldName
     * @access  public
     * @param   $inArray : input array
     *          $fieldName : the name of the field to sort by
     * @return  sorted array
     * @author  Cindy Qi Li
     */
    public static function sortArrayByNumInField($inArray, $fieldName)
    {
        if (is_array($inArray)) {
            foreach ($inArray as $num => $element) {
                preg_match('/[^\d]*(\d*(\.)*(\d)*(\.)*(\d)*)[^\d]*/', $element[$fieldName], $matches);
                if ($matches[1] <> '') {
                    $outArray[$matches[1]] = $element;
                } else
                    $outArray[$num] = $element;
            }
            ksort($outArray);
            return $outArray;
        } else
            return $inArray;
    }

    /**
     * This function deletes $dir recrusively without deleting $dir itself.
     * @access  public
     * @param   string $charsets_array The name of the directory where all files and folders under needs to be deleted
     * @author  Cindy Qi Li
     */
    public static function clearDir($dir)
    {
        if (!$opendir = @opendir($dir)) {
            return false;
        }

        while (($readdir = readdir($opendir)) !== false) {
            if (($readdir !== '..') && ($readdir !== '.')) {
                $readdir = trim($readdir);

                clearstatcache(); /* especially needed for Windows machines: */

                if (is_file($dir . '/' . $readdir)) {
                    if (!@unlink($dir . '/' . $readdir)) {
                        return false;
                    }
                } else if (is_dir($dir . '/' . $readdir)) {
                    /* calls lib function to clear subdirectories recrusively */
                    if (!Utility::clrDir($dir . '/' . $readdir)) {
                        return false;
                    }
                }
            }
        } /* end while */

        @closedir($opendir);

        return true;
    }

    /**
     * Enables deletion of directory if not empty
     * @access  public
     * @param   string $dir the directory to delete
     * @return  boolean            whether the deletion was successful
     * @author  Joel Kronenberg
     */
    public static function clrDir($dir)
    {
        if (!$opendir = @opendir($dir)) {
            return false;
        }

        while (($readdir = readdir($opendir)) !== false) {
            if (($readdir !== '..') && ($readdir !== '.')) {
                $readdir = trim($readdir);

                clearstatcache(); /* especially needed for Windows machines: */

                if (is_file($dir . '/' . $readdir)) {
                    if (!@unlink($dir . '/' . $readdir)) {
                        return false;
                    }
                } else if (is_dir($dir . '/' . $readdir)) {
                    /* calls itself to clear subdirectories */
                    if (!Utility::clrDir($dir . '/' . $readdir)) {
                        return false;
                    }
                }
            }
        } /* end while */

        @closedir($opendir);

        if (!@rmdir($dir)) {
            return false;
        }
        return true;
    }

    /**
     * This function accepts an array that is supposed to only have integer values.
     * The function returns a sanitized array by ensuring all the array values are integers.
     * To pervent the SQL injection.
     * @access  public
     * @param   $int_array : an array
     * @return  $sanitized_int_array : an array that all the values are sanitized to integer
     * @author  Cindy Qi Li
     */
    public static function sanitizeIntArray($int_array)
    {
        if (!is_array($int_array)) return false;

        $sanitized_array = array();
        foreach ($int_array as $i => $value) {
            $sanitized_array[$i] = intval($value);
        }
        return $sanitized_array;
    }

    /**
     * Return http fail status & message. Used to return error message on ajax call.
     * @access  public
     * @param   $errString : error message
     * @author  Cindy Qi Li
     */
    public static function returnError($errString)
    {
        header("HTTP/1.0 400 Bad Request");
        header("Status: 400");
        echo $errString;
    }

    /**
     * Return http success status & message. Used to return success message on ajax call.
     * @access  public
     * @param   $errString : error message
     * @author  Cindy Qi Li
     */
    public static function returnSuccess($successString)
    {
        header("HTTP/1.0 200 OK");
        header("Status: 200");
        echo $successString;
    }

    /**
     * Return true or false to indicate if the extension of the given file name is in the list.
     * @access  public
     * @param   a string of a file name
     * @param   an array of all file extensions
     * @return  true or false
     * @author  Cindy Qi Li
     */
    public static function is_extension_in_list($filename, $extension_list)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array($ext, $extension_list)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Merge two Arrays eventually not set and remove duplicate values
     * @author Joel Carvalho
     * @version 1.0 02/04/2015
     * @access public
     */
    public static function mergeRows($a1,$a2){
        if (!is_array($a1)) $a1=array();
        if (!is_array($a2)) $a2=array();

        return array_unique(array_merge($a1,$a2), SORT_REGULAR);
    }

    /**
     * Get the specified image height and width.
     * @param string $image image url
     * @return mixed is_exist with true or false and width and height if image exist
     * @author Joel Carvalho
     * @version 1.0 07/04/2015
     * @access public
     */
    public static function getImageWidthAndHeight($image){
        $dimensions = @getimagesize($image);

        if (is_array($dimensions))
            return array(
                "is_exist" => true,
                "width" => $dimensions[0],
                "height" => $dimensions[1]);

        return array(
                "is_exist" => false,
                "width" => NULL,
                "height" => NULL);
    }

    /**
     * LINE CHANGE: A function to convert color
     * MODIFICA FILO: Funzione per la conversione del colore
     */
    public static function convert_color_to_hex($f_color)
    {
        /* Se il colore e' indicato in esadecimale lo restituisco cos com' */
        $a = strpos($f_color, "#");

        //MBif($a!=0) {
        if ($a !== false) {
            $f_color = substr($f_color, $a + 1);
            return $f_color;
        } /* Se  in formato RGB lo converto in esadecimale poi lo restituisco */
        elseif (preg_match('/rgb/i', $f_color)) {
            if (preg_match('/\(([^,]+),/i', $f_color, $red)) {
                $red = dechex($red [1]);
            }
            if (preg_match('/,([^,]+),/i', $f_color, $green)) {
                $green = dechex($green [1]);
            }
            if (preg_match('/,([^\)]+)\)/i', $f_color, $blue)) {
                $blue = dechex($blue [1]);
            }
            $f_color = $red . $green . $blue;
            return $f_color;
        } /* La stessa cosa faccio se  indicato con il proprio nome */
        else {
            switch ($f_color) {

                case 'black' :
                    return '000000';
                case 'silver' :
                    return 'c0c0c0';
                case 'gray' :
                    return '808080';
                case 'white' :
                    return 'ffffff';
                case 'maroon' :
                    return '800000';
                case 'red' :
                    return 'ff0000';
                case 'purple' :
                    return '800080';
                case 'fuchsia' :
                    return 'ff00ff';
                case 'green' :
                    return '008800';
                case 'lime' :
                    return '00ff00';
                case 'olive' :
                    return '808000';
                case 'yellow' :
                    return 'ffff00';
                case 'navy' :
                    return '000080';
                case 'blue' :
                    return '0000ff';
                case 'teal' :
                    return '008080';
                case 'aqua' :
                    return '00ffff';
                case 'gold' :
                    return 'ffd700';
                case 'navy' :
                    return '000080';
            }
        }
    }

    /**
     * Convert color hex to rgb/rgba color string
     * @param string $color
     * @param mixed $opacity
     * @return string rgb/rgba color
     * @author Joel Carvalho
     * @version 1.6 02/06/2015
     */
    public static function hex2rgba($color, $opacity = false) {
        $default = 'rgb(0,0,0)';

        //Return default if no color provided
        if(empty($color))
            return $default;

        //Sanitize $color if "#" is provided
        if ($color[0] == '#' ) {
            $color = substr( $color, 1 );
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
            $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
            return $default;
        }

        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if($opacity){
            if(abs($opacity) > 1)
                $opacity = 1.0;
            $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
            $output = 'rgb('.implode(",",$rgb).')';
        }

        //Return rgb(a) color string
        return $output;
    }

    /**
     * Convert color string to rgba color string
     * @param string $value color to convert, can be hex, rgb or rgba
     * @return string rgba color
     * @author Joel Carvalho
     * @version 1.6 09/06/2015
     */
    public static function rgbaConvert($value, $opacity=null){
        if (strpos($value,'#')!==false)
            $value=trim(Utility::hex2rgba($value));
        if (strpos($value,'rgba')!==false)
            $color=str_replace(' ','',$value);
        else
            $color=str_replace(array('rgb(',')', ' '), array('rgba(',',1)', ''), $value);
        if (isset($opacity))
            $color=(preg_replace('/,[^,]+\)$/',','.$opacity.')',$color));
        $color=str_replace(' ','',$color);
        return $color;
    }

    /**
     * Clear all WebCrawler Tags from specified text
     * @param string $text html string
     * @return string
     * @author Joel Carvalho
     * @version 1.6.1 11/08/2015
     */
    public static function clearWCTags($text){
        $text=str_replace(' wc_id=',' tmp_id=', $text);
        $text=preg_replace('/wc\_([^=])+=((\"[^\"]+\")|(\"\"))/','',$text);
        $text=preg_replace('/\s+>/', '>', $text);
        $text=preg_replace('/\s+\/>/', ' />', $text);
        $text=str_replace(array(' >','> <','tmp_id='),array('>','><','wc_id='),$text);
        return $text;
    }

    /**
     * Clear and convert html to utf8
     * @param string $html html string
     * @return string
     * @author Joel Carvalho
     * @version 1.6.4 04/11/2015
     */
    public static function prettiffyHTML($html){
        $html=htmlspecialchars($html);
        $html=utf8_decode(trim($html));
        return $html;
    }

    /**
     * Stop php execution when the specified URL is not accessible
     * @param string $uri url to check
     * @return string
     * @author Joel Carvalho
     * @version 1.6.4 03/11/2015
     */
    public static function stop404($uri){
        $uri=str_replace("{sharp}","#",$uri);
        /*try{
            $headers=get_headers($uri) or die();
            if(substr($headers[0], 9, 3)==403 || substr($headers[0], 9, 3)==404)
                throw new \Exception('Page Not Found.');
        }catch(\Exception $e){
            die('Exception: '.$e->getMessage());
        }*/
    }
}

?>