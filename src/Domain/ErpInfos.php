<?php
namespace ERP\Domain;

class ErpInfos
{
    private $id;
    private $id_erp;
    private $titre;
    private $texte;
    private $type;
    private $x;
    private $y;
    private $ponctuel;
    private $source;

    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }
    public function getId_erp() {
        return $this->id_erp;
    }
    public function setId_erp($id_erp) {
        $this->id_erp = $id_erp;
    }
    public function getTitre() {
        return $this->titre;
    }
    public function setTitre($titre) {
        $this->titre = $titre;
    }
    public function getTexte() {
        return $this->texte;
    }
    public function setDesc($texte) {
        $this->texte = $texte;
    }
    public function getType() {
        return $this->type;
    }
    public function setType($type) {
        $this->type = $type;
    }
    public function getX() {
        return $this->x;
    }
    public function setX($x) {
        $this->x = $x;
    }
    public function getY() {
        return $this->y;
    }
    public function setY($y) {
        $this->y = $y;
    }
    public function getPonctuel() {
        return $this->ponctuel;
    }
    public function setPonctuel($ponctuel) {
        $this->ponctuel = $ponctuel;
    }
    public function getSource() {
        return $this->source;
    }
    public function setSource($source) {
        $this->source = $source;
    }
}
