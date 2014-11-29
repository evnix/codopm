<?php

class Config {
    
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function get_config() {
        
        $sql  = 'SELECT option_name,option_value FROM codopm_config';
        $res  = $this->db->query($sql);
        $conf = $res->fetchAll();

        $info = array();
        foreach($conf as $c) {

            $info[$c['option_name']] = $c['option_value'];
        }
        
        return $info;        
    }
    
}