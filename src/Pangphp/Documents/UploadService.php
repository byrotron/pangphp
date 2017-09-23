<?php

namespace Pangphp\Documents;

use \FileUpload\Validator\SizeValidator;
use \FileUpload\Validator\MimeTypeValidator;
use \FileUpload\PathResolver\Simple as PathResolverSimple;
use \FileUpload\FileSystem\Simple as FileSystemSimple;
use \FileUpload\FileUploadFactory;
use \Pangphp\Exceptions\SktnUploaderException;

class UploadService {

  protected $_validators = [];
  protected $_file = [];
  protected $_folder;

  public $files;

  function __construct(DocumentService $doc) {
    $this->_document = $doc;
  }

  function setFile($file) {
    if(count($file)) {
      $this->_file = $file;
    } else {
      throw new SktnUploaderException("Files array empty");
    } 
  }

  function setFolder($folder) {
    if($this->_document->checkAndCreateDir($folder)) {
      $this->_folder = $folder;
    } else {
      throw new SktnUploaderException("This directory does not exist");
    }
  }

  function setValidFileSize($max, $min = 0) {
    array_push($this->_validators, new SizeValidator($max, $min));
  }

  function setValidMimes(array $mimes) {
    array_push($this->_validators, new MimeTypeValidator($mimes));
  }

  function upload() {

    if(!isset($this->_folder)) {
      throw new SktnUploaderException("Folder not set");
    }

    if(!isset($this->_file)) {
      throw new SktnUploaderException("Files not set");
    }

    $factory = new FileUploadFactory(
        new PathResolverSimple($this->_folder), 
        new FileSystemSimple(), 
        $this->_validators
    );

    $upload = $factory->create($this->_file, $_SERVER);
    $this->file = $upload->processAll();

  }

}