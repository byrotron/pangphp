<?php

namespace Pangphp\Documents;

use \Pangphp\Strings\StringService;
use \Pangphp\Exceptions\SktnDocumentException;

class DocumentService {

  /**
   * @var integer
   * The linux based permission number
   */
  protected $_permission = 0770;

  /**
   * @var string
   * This is the temp directory for public files
   */
  protected $_tmp_dir = '/dist/tmp';

  /**
   * @var integer
   * This will determine how long a temporary document and folder will persist in the public folder
   */
  protected $_tmp_time_limit = 1;

  function __construct(StringService $string) {
    $this->string = $string;
  }

  function setPermission($permission) {
    $this->_permission = $permission;
  }

  function setTempDir($dir) {
    $this->_tmp_dir = $dir;
  }

  function setTmpTimeLimit($limit) {
    $this->_tmp_time_limit = $limit;
  }

  function checkAndCreateDir($folder) {

    if(!is_dir($folder)) {
      if(!mkdir($folder, $this->_permission, true)) {
        throw new SktnDocumentException("We were unable to create the requested directory/s");
      }
    }
    return true;
    
  }

  function setSecureFolderName() {
    $date = new \DateTime(date("Y-m-d H:i"));
    $date->add(new \DateInterval('PT'.$this->_tmp_time_limit.'M'));
    return $folder = $date->format("Y-m-dH-i") . $this->string->randomStringGenerator(32);
  }

  function createSecurePublicDir() {

    $name = $this->setSecureFolderName();
    $folder = $this->_tmp_dir . '/' . $name;

    $this->checkAndCreateDir(getcwd() . $folder);
    
    return $folder;

  }

  function separateDateFromFolderName($str) {
    return substr($str, 0, 10);
  }

  function separateTimeFromFolderName($str) {
    return str_replace("-", ":", substr($str, 10, 5));
  }

  function folderInvalid($folder) {

    $date = $this->separateDateFromFolderName($folder);
    $time = $this->separateTimeFromFolderName($folder);

    $folder_time = new \DateTime($date . " " . $time);
    $now = new \DateTime("now");

    return $now > $folder_time;

  }

  function deleteExpiredTempFiles() {
    
    if(is_dir($this->_tmp_dir)) {
      $folders = scandir($this->_tmp_dir);
      
      if(count($folders) > 0) {
        foreach($folders as $folder) {

          if($this->folderInvalid($folder)) {
            
            array_map("unlink", glob("$this->_tmp_dir/$folder/*.*"));
            rmdir("$this->_tmp_dir/$folder");

          }
        }
      } else {
        rmdir($this->_tmp_dir);
      }
    }
  }

}