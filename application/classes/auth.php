<?php

defined('SYSPATH') or die('No direct access allowed.');

abstract class Auth extends Kohana_Auth {

    public function get_user($default = NULL) {

        $user = parent::get_user($default);
       
        if (!$user) {
            return $default;
        } elseif($user instanceof	Model_Auth_User) {
            try{
												
            $user->ip = $_SERVER['REMOTE_ADDR'];
            $user->last_action = time();          
            $user->update();
            
            }catch(ORM_Validation_Exception $e){
               echo  Debug::vars($e->errors(I18n::$lang));
            }
            return $user;
        }
    }

    public function hash($str) {
        return hash($this->_config['hash_method'], $str);
    }



}