<?php

/*
 * @CODOLICENSE
 */

defined('_JEXEC') or die;

class codopm {

    public static $secret = "DEd56g3@34azFfGv";
    public static $path = "";
    public static $xhash = "";
    public static $db_prefix = "";
    public static $db = null;
    public static $config;
    public static $language = "english";
    public static $sef_q = "";
    public static $req_path;
    public static $table = array();
    public static $upload_path;
    public static $profile_id;
    public static $profile_path;
    public static $profile_name;
    
    protected static $trans = false;

    public static function get_lang() {

        //$codopm_trans is declared in all language files
        //For backward compatibility purposes always include english.php
        require 'lang/english.php';

        if (self::$language != "english") {

            //Overwrite english with the new language
            require 'lang/' . self::$language . '.php';
        }

        return $codopm_trans;
    }

    public static function t($index) {

        if (!self::$trans) {

            self::$trans = self::get_lang();
        }

        if (isset(self::$trans[$index])) {

            echo self::$trans[$index];
        } else {

            echo $index; //echo passed if not found
        }
    }

}
