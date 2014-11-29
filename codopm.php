<?php

/*
 * @CODOLICENSE
 */

function codopm_get_adapter() {
    
    //$myadapter = 'Joomla';
    $myadapter = 'Codoforum';

    return $myadapter;
}

$myadapter = codopm_get_adapter();

require 'adapters/' . $myadapter . '.php';
defined('_JEXEC') or die('No JOOMLA');

require 'arg.php';


$adapter = new Adapter();
$adapter->setup_tables();
codopm::$path = $adapter->get_abs_path();

if ($myadapter == 'Joomla') {

    $jversion = new JVersion();

    $ver = $jversion->RELEASE;
    $list = explode(".", $ver);

    $version = (int) $list[0];

    if ($version < 3) {

        $adapter->add_js("http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js");
    }

    $row_class = '';
    codopm::$req_path = codopm::$path . 'server/codopm.php?';
} else {

    $row_class = 'row';
    codopm::$db_prefix = PREFIX . 'codo_';
    codopm::$req_path = RURI . 'codopm&';
}

function codopm_add_assets() {

    $adapter = new Adapter();

    $adapter->add_css(codopm::$path . "client/css/app.css");
    $adapter->add_css(codopm::$path . "client/css/flick/jquery-ui-1.10.3.custom.min.css");
    $adapter->add_js(codopm::$path . "client/js/jquery.form.min.js");
    $adapter->add_js(codopm::$path . "client/js/jquery-ui-1.10.3.custom.min.js");
    $adapter->add_js(codopm::$path . "client/js/jquery.autosize.min.js");
}

function codopm_load() {

    $myadapter = codopm_get_adapter();
    
    $adapter = new Adapter();

    $user = $adapter->get_user();

    codopm::$xhash = md5($user->id . codopm::$secret);

    if ($user->id == 0) {

        if ($myadapter != 'Codoforum') {
            
            require "error.php";
        }
    } else {


        if (isset($_GET['to'])) {
            $to = $_GET['to'];
        } else {
            $to = '';
        }

        echo '
    <script>
    var codopm={};
    codopm.path="' . codopm::$path . '";
    codopm.req_path="' . codopm::$req_path . '";
    codopm.from="' . $user->id . '";
    codopm.xhash="' . codopm::$xhash . '";
    codopm.profile_id="' . codopm::$profile_id . '";    
    codopm.profile_name="' . codopm::$profile_name . '";
    </script>';

        require "start.php";
    }
}

$user = $adapter->get_user();

if ($myadapter == 'Codoforum') {

    \CODOF\Hook::add('before_profile_view', function($user) {

        codopm::$profile_id = $user->id;
        codopm::$profile_name = $user->username;
        codopm::$profile_path = RURI . 'user/profile';
    });

    \CODOF\Hook::add('block_user_recent_posts_after', 'codopm_load');
    \CODOF\Hook::add('tpl_before_user_profile_view', 'codopm_add_assets');

    require 'server/codopm.php';
} else {

    codopm::$profile_id = $user->id;
    codopm::$profile_path = '';
    codopm::$profile_name = '';
    codopm_load();
    codopm_add_assets();
}
