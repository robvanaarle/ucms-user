<?php

namespace ucms\user\forms\user;

class UpdateForm extends ModifyForm {
  
  public function init() {
    parent::init();
    $this['password'] = static::UNCHANGED;
    $this['password_repeat'] = static::UNCHANGED;
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