<?php
namespace ERP\Domain;

class Erp
{
    private $id;
    private $id_adresse;
    private $numero;
    private $repetition;
    private $voie;
    private $complement;
    private $entree;
    private $lieu_dit;
    private $code_post;
    private $insee_com;
    private $nom_com;
    private $dpt_num;
    private $categorie;
    private $type;
    private $accessible;
    private $nom;
    private $activite;
    private $actif;
    private $public;
    private $capacite;
    private $hebergement;
    private $personnel;
    private $siret;
    private $code_ape;
    private $x;
    private $y;
    private $precision;
    private $id_parent;
    private $id_bati;
    private $id_encte;
    private $id_pai;
    private $id_sdis;
    private $id_ddt;
    private $id_mairie;
    private $id_editeur;
    private $creation;
    private $modif;
    private $etat;
    private $ponctuel;

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
    public function getId_adresse() {
        return $this->id_adresse;
    }
    public function setId_adresse($id_adresse) {
        $this->id_adresse = $id_adresse;
    }
    public function getNumero() {
        return $this->numero;
    }
    public function setNumero($numero) {
        $this->numero = $numero;
    }
    public function getRepetition() {
        return $this->repetition;
    }
    public function setRepetition($repetition) {
        $this->repetition = $repetition;
    }
    public function getVoie() {
        return $this->voie;
    }
    public function setVoie($voie) {
        $this->voie = $voie;
    }
    public function getComplement() {
        return $this->complement;
    }
    public function setComplement($complement) {
        $this->complement = $complement;
    }
    public function getEntree() {
        return $this->entree;
    }
    public function setEntree($entree) {
        $this->entree = $entree;
    }
    public function getLieu_dit() {
        return $this->lieu_dit;
    }
    public function setLieu_dit($lieu_dit) {
        $this->lieu_dit = $lieu_dit;
    }
    public function getCode_post() {
        return $this->code_post;
    }
    public function setCode_post($code_post) {
        $this->code_post = $code_post;
    }
    public function getInsee_com() {
        return $this->insee_com;
    }
    public function setInsee_com($insee_com) {
        $this->insee_com = $insee_com;
    }
    public function getNom_com() {
        return $this->nom_com;
    }
    public function setNom_com($nom_com) {
        $this->nom_com = $nom_com;
    }
    public function getDpt_num() {
        return $this->dpt_num;
    }
    public function setDpt_num($dpt_num) {
        $this->dpt_num = $dpt_num;
    }
    public function getCategorie() {
        return $this->categorie;
    }
    public function setCategorie($categorie) {
        $this->categorie = $categorie;
    }
    public function getType() {
        return $this->type;
    }
    public function setType($type) {
        $this->type = $type;
    }
    public function getAccessible() {
        return $this->accessible;
    }
    public function setAccessible($accessible) {
        $this->accessible = $accessible;
    }
    public function getNom() {
        return $this->nom;
    }
    public function setNom($nom) {
        $this->nom = $nom;
    }
    public function getActivite() {
        return $this->activite;
    }
    public function setActivite($activite) {
        $this->activite = $activite;
    }
    public function getActif() {
        return $this->actif;
    }
    public function setActif($actif) {
        $this->actif = $actif;
    }
    public function getPublic() {
        return $this->public;
    }
    public function setPublic($public) {
        $this->public = $public;
    }
    public function getCapacite() {
        return $this->capacite;
    }
    public function setCapacite($capacite) {
        $this->capacite = $capacite;
    }
    public function getHebergement() {
        return $this->hebergement;
    }
    public function setHebergement($hebergement) {
        $this->hebergement = $hebergement;
    }
    public function getPersonnel() {
        return $this->personnel;
    }
    public function setPersonnel($personnel) {
        $this->personnel = $personnel;
    }
    public function getSiret() {
        return $this->siret;
    }
    public function setSiret($siret) {
        $this->siret = $siret;
    }
    public function getCode_ape() {
        return $this->code_ape;
    }
    public function setCode_ape($code_ape) {
        $this->code_ape = $code_ape;
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
    public function getPrecision() {
        return $this->precision;
    }
    public function setPrecision($precision) {
        $this->precision = $precision;
    }
    public function getId_parent() {
        return $this->id_parent;
    }
    public function setId_parent($id_parent) {
        $this->id_parent = $id_parent;
    }
    public function getId_bati() {
        return $this->id_bati;
    }
    public function setId_bati($id_bati) {
        $this->id_bati = $id_bati;
    }
    public function getId_encte() {
        return $this->id_encte;
    }
    public function setId_encte($id_encte) {
        $this->id_encte = $id_encte;
    }
    public function getId_pai() {
        return $this->id_pai;
    }
    public function setId_pai($id_pai) {
        $this->id_pai = $id_pai;
    }
    public function getId_sdis() {
        return $this->id_sdis;
    }
    public function setId_sdis($id_sdis) {
        $this->id_sdis = $id_sdis;
    }
    public function getId_ddt() {
        return $this->id_ddt;
    }
    public function setId_ddt($id_ddt) {
        $this->id_ddt = $id_ddt;
    }
    public function getId_mairie() {
        return $this->id_mairie;
    }
    public function setId_mairie($id_mairie) {
        $this->id_mairie = $id_mairie;
    }
    public function getId_editeur() {
        return $this->id_editeur;
    }
    public function setId_editeur($id_editeur) {
        $this->id_editeur = $id_editeur;
    }
    public function getCreation() {
        return $this->creation;
    }
    public function setCreation($creation) {
        $this->creation = $creation;
    }
    public function getModif() {
        return $this->modif;
    }
    public function setModif($modif) {
        $this->modif = $modif;
    }
    public function getEtat() {
        return $this->etat;
    }
    public function setEtat($etat) {
        $this->etat = $etat;
    }
    public function getPonctuel() {
        return $this->ponctuel;
    }
    public function setPonctuel($ponctuel) {
        $this->ponctuel = $ponctuel;
    }
}
