<?php

namespace ucms\user\models;

class User extends \ultimo\orm\Model implements \ultimo\security\mvc\User {
  public $id;
  public $role;
  public $username;
  public $password;
  public $last_login;
	
  static protected $fields = array('id', 'role', 'username', 'password', 'last_login');
  static protected $primaryKey = array('id');
  static protected $autoIncrementField = 'id';
  static protected $scopes = array('byUsername', 'byPassword');
  static protected $plugins = array('Timestamps');
  
  static public function hashPassword($manager, $password) {
    $config = $manager->getModule()->getPlugin('config')->getConfig('general');
    
    switch ($config['password']['hash']['method']) {
      case 'md5':
        return md5($config['password']['hash']['salt'] . $password);
      case 'sha1':
        return md5($config['password']['hash']['salt'] . $password);
      case 'crc32':
        return crc32($config['password']['hash']['salt'] . $password);
      case 'none':
        return $password;
      default:
  	throw new Exception("Unknown hash method: '{$config['password']['hash']['method']}'");
    }
    
    return $password;
  }
  
  static public function login($manager, $username, $password) {
    $user = $manager->select('User')
                    ->where('@username = ?', array($username))
                    ->where('@password = ?', array(static::hashPassword($manager, $password)))
                    ->limit(0, 1)
                    ->first();
    
    if ($user === null) {
      return false;
    }
    
    $user->disableTimestamps();
    $user->last_login = date("Y-m-d H:i:s");
    $user->enableTimestamps();
    $user->save();
    
    $user->authorize();
    return true;
  }
  
  public function setPassword($password) {
    $this->password = static::hashPassword($this->_manager, $password);
  }
  
  public function authorize() {
    $this->_manager->getModule()->getApplication()->getPlugin('authorizer')->setUser($this);
    
    // Remove manager, as this User will be stored in session. Managers contain a PDOConnection that cannot be serialized.
    $this->setManager(null);
  }
  
  static public function byUsername($username) {
    return function ($q) use ($username) {
      $q->where('@username = ?', array($username));
    }; 
  }
  
  static public function byPassword($password) {
    $class = get_called_class();
    return function ($q) use ($password, $class) {
      $q->where('@password = ?', array($class::hashPassword($q->getManager(), $password)));
    }; 
  }
    
  public function getId() {
    return $this->id;
  }
  
  public function getRole() {
    return $this->role;
  }
}