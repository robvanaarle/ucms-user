<?php

namespace ucms\user\forms;

class LocaleForm extends \ultimo\form\Form {
  protected function init() {
    $this->appendValidator('locale', 'InArray', array($this->getConfig('availableLocales')));
  }
}