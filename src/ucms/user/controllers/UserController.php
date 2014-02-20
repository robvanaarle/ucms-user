<?php

namespace ucms\user\controllers;

class UserController extends \ultimo\mvc\Controller {
  
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
    $this->view->users = $this->manager->User->orderById()->all();
  }
  
  public function actionRead() {
    $user = $this->manager->User->getById($this->request->getParam('id'));
    if ($user === null) {
      throw new \ultimo\mvc\exceptions\DispatchException("User with id '{$id}' does not exist.", 404);
    }
    $this->view->user = $user;
  }
  
  public function actionCreate() {
    
    $form = $this->module->getPlugin('formBroker')->createForm(
      'user\CreateForm',
      $this->request->getParam('form', array())
    );
    
    if ($this->request->isPost()) {
      if ($form->validate()) {

        $user = $this->manager->User->create();
        $user->username = $form['username'];
        $user->setPassword($form['password']);
        $user->role = $form['role'];
        $user->save();
      
        return $this->getPlugin('redirector')->redirect(array('action' => 'read', 'id' => $user->id));
      }
    }
    
    $this->view->form = $form;
  }
    
  public function actionUpdate() {
    $id = $this->request->getParam('id');

    $user = $this->manager->User->getById($id);
    
    if ($user === null) {
      throw new \ultimo\mvc\exceptions\DispatchException("User with id '{$id}' does not exist.", 404);
    }
    
    $form = $this->module->getPlugin('formBroker')->createForm(
      'user\UpdateForm', $this->request->getParam('form', array()),
      array('user' => $user)
    );

    if ($this->request->isPost()) {
      if ($form->validate()) {
        $user->username = $form['username'];
        if (isset($form['password'])) {
          $user->setPassword($form['password']);
        }
        $user->role = $form['role'];
        $user->save();
      
        return $this->getPlugin('redirector')->redirect(array('action' => 'read', 'id' => $user->id));
      }
    } else {
      $values = $user->toArray();
      unset($values['password']);
      $form->fromArray($values);
    }
    
    $this->view->id = $id;
    $this->view->form = $form;
  }
  
  public function actionDelete() {
    $id = $this->request->getParam('id');
    $user = $this->manager->User->getById($id);
    
    if ($user !== null) {
      $user->delete();
    }
    
    return $this->getPlugin('redirector')->redirect(array('action' => 'index'));
  }
  
  public function actionLogin() {
    
    $form = $this->module->getPlugin('formBroker')->createForm(
      'LoginForm',
      $this->request->getParam('form', array())
    );
    
    if ($this->request->isPost()) {
      

      if ($this->manager->User->login($form['username'], $form['password'])) {

        if (!empty($form['returnUrl'])) {
          $this->getPlugin('redirector')->setRedirectUrl($form['returnUrl']);
        } else {
          $this->getPlugin('redirector')->redirect($this->config['login_redirect']);
        }
      }
    }
    
    $this->view->form = $form;
  }
  
  public function actionLogout() {
    $this->application->getPlugin('authorizer')->setUser(null);
    $this->getPlugin('redirector')->redirect(array('action' => 'login'));
  }
  
  public function actionLocale() {
    $translator = $this->module->getPlugin('translator');
    
    $form = $this->module->getPlugin('formBroker')->createForm(
      'LocaleForm',
     $this->request->getParam('form', array()),
      array(
        'availableLocales' => $translator->getAvailableLocales()
      )
    );
    
    if ($this->request->isPost()) {
      if ($form->validate()) {
        $translator->setLocale($form['locale'], true);
        $this->getPlugin('redirector')->redirect(array('action' => 'locale'));
      }
    } else {
      $form['locale'] = $translator->getLocale();
    }
    
    $this->view->form = $form;
  }
}