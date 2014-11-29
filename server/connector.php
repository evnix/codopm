<?php

/**
 * @package Component codoPM for Joomla! 3.0
 * @author codologic
 * @copyright (C) 2013 - codologic
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('_JEXEC') or die;

require "../arg.php";

require "../../../configuration.php";

$jconfig = new JConfig();
codopm::$db_prefix = $jconfig->dbprefix;

try {
    codopm::$db = new PDO('mysql:host=' . $jconfig->host . ';dbname=' . $jconfig->db, $jconfig->user, $jconfig->password);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die("CODO_PM SAYS: Error Connecting Through PDO");
}
codopm::$db->query('SET CHARACTER SET utf8');
codopm::$db->query("SET NAMES utf8");
