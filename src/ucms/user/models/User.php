<?php

namespace ucms\user\models;

class User extends \ultimo\orm\Model implements \ultimo\security\mvc\User {
  public $id;
  public $role;
  public $username;
  public $password;
  public $salt;
  public $last_login;
	
  static protected $fields = array('id', 'role', 'username', 'password', 'salt', 'last_login');
  static protected $primaryKey = array('id');
  static protected $autoIncrementField = 'id';
  static protected $scopes = array('byUsername', 'byPassword');
  static protected $plugins = array('Timestamps');
  
  static public function hashPassword($manager, $password, $salt) {
    $config = $manager->getModule()->getPlugin('config')->getConfig('general');
    
    $method = $config['password']['hash']['method'];
    if ($method == 'none') {
      return $password;
    }
    
    if (!in_array($method, hash_algos())) {
      throw new \Exception("Unknown hash method: {$method}");
    }
    
    $runs = $config['password']['hash']['runs'];
    while($runs--) {
      $password = hash($method, $config['password']['hash']['salt'] . $salt . $password);
    }
    
    return $password;
  }
  
  static public function login($manager, $username, $password) {
    $user = $manager->select('User')
                    ->where('@username = ?', array($username))
                    ->limit(0, 1)
                    ->first();
    
    if ($user === null || !$user->verifyPassword($password)) {
      return false;
    }
    
    $user->disableTimestamps();
    $user->last_login = date("Y-m-d H:i:s");
    $user->enableTimestamps();
    $user->save();
    
    $user->authorize();
    return true;
  }
  
  protected function generateSalt() {
    $this->salt = base64_encode(mcrypt_create_iv(64));
  }
  
  public function setPassword($password) {
    $this->generateSalt();
    $this->password = static::hashPassword($this->_manager, $password, $this->salt);
  }
  
  public function verifyPassword($password) {
    return strcmp($this->password, static::hashPassword($this->_manager, $password, $this->salt)) == 0;
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