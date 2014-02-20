<?php

namespace ucms\user\forms\user;

class ModifyForm extends \ultimo\form\mvc\Form {
  const UNCHANGED = '❤Ǳ♎Ƿ☀ʞ☆☂';
  
  public function init() {
    $manager = $this->module->getPlugin('uorm')->getManager();
    $user = $this->getConfig('user');
    
    $this->appendValidator('username', 'StringLength', array(1, 255));
    $this->appendValidator('username', 'Callback', array(function($value) use ($manager, $user) {
      // username must not exists
      $dupUser = $manager->User->byUsername($value)->first();
      
      if ($dupUser !== null && ($user === null || $dupUser->id != $user->id)) {
        return 'username.not_unique';
      }
      
      return true;
    }));
    
    $this->appendValidator('password', 'StringLength', array(4, 255));
  
    $form = $this;
    
    $this->appendValidator('password', 'Callback', array(function($value) use ($form, $user) {
      if ($user !== null && ($value == $form::UNCHANGED && $form['password_repeat'] == $form::UNCHANGED)) {
        return true;
      }
      
      // new passwords must match
      if ($value != $form['password_repeat']) {
        $form['password'] = $form['password_repeat'] = '';
        return 'password.no_match';
      }

      return true;
    }));
    
    $appAcl = $this->module->getApplication()->getPlugin('authorizer')->getAcl();
    $roles = $appAcl->getRoles();
    $this['roles'] = $roles;
    
    $this->appendValidator('role', 'InArray', array($roles));
  }
  
}