<?php


/*
 * @CODOLICENSE
 */

//define('_JEXEC', 'JOOMLA_ADAPTER');

class Adapter {
    
    private $doc;
    
    public function __construct() {
        
        $this->doc = JFactory::getDocument();
        
    }
    
    public function setup_tables() {
        
        codopm::$table['mail_column'] = 'email';
    }
    
    public function get_user() {
        
        return JFactory::getUser();
    }
    
    public function add_js($js) {
        
        $this->doc->addScript($js);
    }
    
    public function add_css($css) {
        
        $this->doc->addStyleSheet($css);
    }
    
    public function get_abs_path() {

        if (@$_SERVER["HTTPS"] == "on") {
            $protocol = "https://";
        } else {
            $protocol = "http://";
        }

        $sn = $_SERVER['SCRIPT_NAME'];
        $sn = str_replace("index.php", "components/com_codopm/", $sn);

        return $protocol . $_SERVER['HTTP_HOST'] . $sn;
    }    
}
