<?php

/*
 * @CODOLICENSE
 */

define('_JEXEC', 'JOOMLA_ADAPTER');

class Adapter {

    public function __construct() {
        
    }

    public function setup_tables() {

        codopm::$table['mail_column'] = 'mail';
        codopm::$upload_path = PLUGIN_DIR . "codopm/";
    }

    public function get_user() {

        return \CODOF\User\User::get();
    }

    public function add_js($js) {

        add_js($js, array('type' => 'defer'));
    }

    public function add_css($css) {

        add_css($css);
    }

    public function get_abs_path() {

        return PLUGIN_PATH . 'codopm/';
    }

}
