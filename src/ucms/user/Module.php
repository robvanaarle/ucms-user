<?php

namespace ucms\user;

class Module extends \ultimo\mvc\Module implements \ultimo\security\mvc\AuthorizedModule {
  protected function init() {
    $this->setAbstract(true);
  }
  
  public function getAcl() {
    $acl = new \ultimo\security\Acl();
    $acl->addRole('user.guest');
    $acl->addRole('user.member', array('user.guest'));
    $acl->addRole('user.admin', array('user.member'));
    
    $acl->allow('user.guest', array('user.login', 'user.logout', 'user.locale'));
    $acl->allow('user.member', array('member.update', 'member.index'));
    $acl->allow('user.admin');
    return $acl;
  }
}