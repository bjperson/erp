<?php
namespace ERP\DAO;

use ERP\Domain\Erp;

class ErpDAO extends DAO
{
    /**
     * Saves an ERP into the database.
     *
     * @param \ERP\Domain\Erp $erp the ERP to save
     * @return \ERP\Domain\Erp
     */
    public function save(Erp $erp) {
        if ($erp->getId()) {
            // The ERP has already been saved : update it
            $this->getDb()->update('erp', self::buildPropertiesArray($erp), array('id' => $erp->getId()));
        } else {
            // The ERP has never been saved : insert it
            $this->getDb()->insert('erp', self::buildPropertiesArray($erp));
            // Need fix : Workaround to get lastInsertId() wich is not working with pdo_pgsql...
            $id = $this->getDb()->fetchColumn("SELECT id FROM erp order by id desc limit 1");
            //$id = $this->getDb()->lastInsertId();
            $erp->setId($id);
        }
        return $erp;
    }

    /**
     * Creates an ERP object based on a DB row.
     *
     * @param array $row The DB row containing Erp data.
     * @return \ERP\Domain\Erp
     */
    protected function buildDomainObject($row) {
        $erp = new Erp();
        $erp->setId($row['id']);
        $erp->setId_adresse($row['id_adresse']);
        $erp->setNumero($row['numero']);
        $erp->setRepetition($row['repetition']);
        $erp->setVoie($row['voie']);
        $erp->setComplement($row['complement']);
        $erp->setEntree($row['entree']);
        $erp->setLieu_dit($row['lieu_dit']);
        $erp->setCode_post($row['code_post']);
        $erp->setInsee_com($row['insee_com']);
        $erp->setNom_com($row['nom_com']);
        $erp->setDpt_num($row['dpt_num']);
        $erp->setCategorie($row['categorie']);
        $erp->setType($row['type']);
        $erp->setAccessible($row['accessible']);
        $erp->setNom($row['nom']);
        $erp->setActivite($row['activite']);
        $erp->setActif($row['actif']);
        $erp->setPublic($row['public']);
        $erp->setCapacite($row['capacite']);
        $erp->setHebergement($row['hebergement']);
        $erp->setPersonnel($row['personnel']);
        $erp->setSiret($row['siret']);
        $erp->setCode_ape($row['code_ape']);
        $erp->setX($row['x']);
        $erp->setY($row['y']);
        $erp->setPrecision($row['precision']);
        $erp->setId_parent($row['id_parent']);
        $erp->setId_bati($row['id_bati']);
        $erp->setId_encte($row['id_encte']);
        $erp->setId_pai($row['id_pai']);
        $erp->setId_sdis($row['id_sdis']);
        $erp->setId_ddt($row['id_ddt']);
        $erp->setId_mairie($row['id_mairie']);
        $erp->setId_editeur($row['id_editeur']);
        $erp->setCreation($row['creation']);
        $erp->setModif($row['modif']);
        $erp->setEtat($row['etat']);
        $erp->setPonctuel($row['ponctuel']);
        return $erp;
    }

    /**
     * Creates an ERP array of properties based on a ERP object.
     *
     * @param \ERP\Domain\Erp $erp
     * @return array
     */
    protected function buildPropertiesArray(Erp $erp) {
        $arr = Array();
        $arr['id'] = $erp->getId();
        $arr['id_adresse'] = $erp->getId_adresse();
        $arr['numero'] = $erp->getNumero();
        $arr['repetition'] = $erp->getRepetition();
        $arr['voie'] = $erp->getVoie();
        $arr['complement'] = $erp->getComplement();
        $arr['entree'] = $erp->getEntree();
        $arr['lieu_dit'] = $erp->getLieu_dit();
        $arr['code_post'] = $erp->getCode_post();
        $arr['insee_com'] = $erp->getInsee_com();
        $arr['nom_com'] = $erp->getNom_com();
        $arr['dpt_num'] = $erp->getDpt_num();
        $arr['categorie'] = $erp->getCategorie();
        $arr['type'] = $erp->getType();
        $arr['accessible'] = $erp->getAccessible();
        $arr['nom'] = $erp->getNom();
        $arr['activite'] = $erp->getActivite();
        $arr['actif'] = $erp->getActif();
        $arr['public'] = $erp->getPublic();
        $arr['capacite'] = $erp->getCapacite();
        $arr['hebergement'] = $erp->getHebergement();
        $arr['personnel'] = $erp->getPersonnel();
        $arr['siret'] = $erp->getSiret();
        $arr['code_ape'] = $erp->getCode_ape();
        $arr['x'] = $erp->getX();
        $arr['y'] = $erp->getY();
        $arr['precision'] = $erp->getPrecision();
        $arr['id_parent'] = $erp->getId_parent();
        $arr['id_bati'] = $erp->getId_bati();
        $arr['id_encte'] = $erp->getId_encte();
        $arr['id_pai'] = $erp->getId_pai();
        $arr['id_sdis'] = $erp->getId_sdis();
        $arr['id_ddt'] = $erp->getId_ddt();
        $arr['id_mairie'] = $erp->getId_mairie();
        $arr['id_editeur'] = $erp->getId_editeur();
        $arr['creation'] = $erp->getCreation();
        $arr['modif'] = $erp->getModif();
        $arr['etat'] = $erp->getEtat();
        $arr['ponctuel'] = $erp->getPonctuel();

        if ($erp->getId()) {
            foreach ($arr as $key => $value) {
                if (is_null($value)) {
                    unset($arr[$key]);
                }
            }
        } else {
            unset($arr['id']);
        }

        return $arr;
    }
}
