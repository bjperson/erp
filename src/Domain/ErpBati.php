<?php
namespace ERP\Domain;

class ErpBati
{
    private $id;
    private $ids_ign;
    private $source;
    private $cle;
    private $bati;
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
    public function getCle() {
        return $this->cle;
    }
    public function setCle($cle) {
        $this->cle = $cle;
    }
    public function getBati() {
        return $this->bati;
    }
    public function setBati($bati) {
        $this->bati = $bati;
    }
    public function getMd5() {
        return $this->md5;
    }
    public function setMd5($md5) {
        $this->md5 = $md5;
    }
}
