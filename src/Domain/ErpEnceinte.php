<?php
namespace ERP\Domain;

class ErpEnceinte
{
    private $id;
    private $enceinte;
    private $ids_ign;
    private $source;
    private $md5;


    public function __construct(Array $properties=array()){
        foreach($properties as $key => $value){
            $this->{$key} = $value;
        }
    }

    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }
    public function getEnceinte() {
        return $this->enceinte;
    }
    public function setEnceinte($enceinte) {
        $this->enceinte = $enceinte;
    }
    public function getIds_ign() {
        return $this->ids_ign;
    }
    public function setIds_ign($ids_ign) {
        $this->ids_ign = $ids_ign;
    }
    public function getSource() {
        return $this->source;
    }
    public function setSource($source) {
        $this->source = $source;
    }
    public function getMd5() {
        return $this->md5;
    }
    public function setMd5($md5) {
        $this->md5 = $md5;
    }
}
