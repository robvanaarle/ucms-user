<?php

namespace ucms\user\controllers;

class MemberController extends \ultimo\mvc\Controller {
  
  /**
   * @var ucms\user\managers\UserManager
   */
  protected $manager;
  protected $config;
  
  protected function init() {
    $this->config = $this->module->getPlugin('config')->getConfig('general');
    $this->manager = $this->module->getPlugin('uorm')->getManager();
  }
    
  public function actionIndex() {
    
  }
  
  public function actionUpdate() {
    $form = $this->module->getPlugin('formBroker')->createForm(
      'member\UpdateForm',
      $this->request->getParam('form', array())
    );
    
    $currentUser = $this->getApplication()->getPlugin('authorizer')->getUser();
    
    if ($this->request->isPost()) {
      if ($form->validate()) {

        $user = $this->manager->User->get($currentUser->getId());
        $user->username = $form['username'];
        if (isset($form['password'])) {
          $user->setPassword($form['password']);
        }
        
        $user->save();
        $user->authorize();
      
        return $this->getPlugin('redirector')->redirect(array('action' => 'index'));
      }
    } else {
      $values = $currentUser->toArray();
      unset($values['password']);
      $form->fromArray($values);
    }
    
    $this->view->form = $form;
  }
 
}