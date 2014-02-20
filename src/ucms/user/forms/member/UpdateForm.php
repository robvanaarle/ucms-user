<?php

namespace ucms\user\forms\member;

class UpdateForm extends \ultimo\form\mvc\Form {
  const UNCHANGED = '❤Ǳ♎Ƿ☀ʞ☆☂';
  
  protected function init() {
    $this['password'] = self::UNCHANGED;
    $this['password_repeat'] = self::UNCHANGED;
    
    $manager = $this->module->getPlugin('uorm')->getManager();
    $form = $this;
    $currentUser = $this->module->getApplication()->getPlugin('authorizer')->getUser();
    
    $this->appendValidator('username', 'StringLength', array(1, 255));
    
    $this->appendValidator('username', 'Callback', array(function($value) use ($manager, $currentUser) {
      // username must not exists, unless it's the current username
      $user = $manager->User->byUsername($value)->first();
      
      if ($user !== null && $user->id != $currentUser->id) {
        return 'username.not_unique';
      }
      
      return true;
    }));
    
    $this->appendValidator('password', 'StringLength', array(4, 255));
    $this->appendValidator('password', 'Callback', array(function($value) use ($form) {
      if ($value == $form::UNCHANGED && $form['password_repeat'] == $form::UNCHANGED) {
        return true;
      }
      
      // new passwords must match
      if ($value != $form['password_repeat']) {
        $form['password'] = $form['password_repeat'] = '';
        return 'password.no_match';
      }

      return true;
    }));
    
    
    $this->appendValidator('password_current', 'Callback', array(function($value) use ($form, $manager, $currentUser) {
      // current password must match of that in db
      $user = $manager->User->byId($currentUser->id)->byPassword($value)->first();
      if ($user === null) {
        $form['password_current'] = $form['password_current_repeat'] = '';
        return 'password_current.invalid';
      }
      
      return true;
    }));
  }
  
  public function validate() {
    $result = parent::validate();
    
    if ($result) {
      if ($this['password'] == static::UNCHANGED) {
        unset($this['password']);
        unset($this['password_repeat']);
      }
    }
    
    return $result;
  }
  

}