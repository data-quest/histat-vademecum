<?php defined('SYSPATH') or die('No direct access allowed.');

abstract class Auth extends Kohana_Auth {
    public function get_user($default = NULL) {
        
        $user = parent::get_user($default);
        
        if(!$user){
            return $default;
        }else{
            $user->ip = $_SERVER['REMOTE_ADDR'];
            $user->last_action = time();
            $user->update();
            return $user;
        }
    }
    public function hash($str){
        return hash($this->_config['hash_method'], $str);
    }
}