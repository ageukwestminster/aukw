<?php

namespace Models;


/**
 * Simple data class to store parameters of a File
 * 
 * @category Model
 */
class File{
  protected $fileName;
  protected $contentType;

  protected function __construct(){
  }

  /**
   * Static constructor / factory
   */
  public static function getInstance() {
    return new self();
  }

  public function getFileName(){
      return $this->fileName;
  }

  public function getContentType(){
      return $this->contentType;
  }

  public function setFileName($fileName){
      $this->fileName = $fileName;
      return $this;
  }

  public function setContentType($contentType){
      $this->contentType = $contentType;
      return $this;
  }
  public static function fromArray($data){
      return (new self())
        ->setFileName($data["FileName"])
        ->setContentType($data["ContentType"]);      
  }

  public function toArray(){
      return [
          "FileName"=>$this->fileName,
          "ContentType"=>$this->contentType
      ];
  }
}