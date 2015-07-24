<?php
namespace ERP\DAO;

use ERP\Domain\ErpInfos;

class ErpInfosDAO extends DAO
{
    /**
     * Saves an ErpInfo into the database.
     *
     * @param \ERP\Domain\ErpInfo $erp the ERP to save
     * @return \ERP\Domain\ErpInfo
     */
    public function save(ErpInfos $infos) {
        if ($infos->getId()) {
            // The ErpInfo has already been saved : update it
            $this->getDb()->update('erp_infos', self::buildPropertiesArray($infos), array('id' => $infos->getId()));
        } else {
            // The ErpInfo has never been saved : insert it
            $this->getDb()->insert('erp_infos', self::buildPropertiesArray($infos));
            // Need fix : Workaround to get lastInsertId() wich is not working with pdo_pgsql...
            $id = $this->getDb()->fetchColumn("SELECT id FROM erp_infos order by id desc limit 1");
            //$id = $this->getDb()->lastInsertId();
            $infos->setId($id);
        }
        return $infos;
    }

    /**
     * Creates an ErpInfo object based on a DB row.
     *
     * @param array $row The DB row containing ErpInfo data.
     * @return \ERP\Domain\ErpInfo
     */
    protected function buildDomainObject($row) {
        $info = new ErpInfos();
        $info->setId($row['id']);
        $info->setId_erp($row['id_erp']);
        $info->setTitre($row['titre']);
        $info->setTexte($row['texte']);
        $info->setType($row['type']);
        $info->setX($row['x']);
        $info->setY($row['y']);
        $info->setPonctuel($row['ponctuel']);
        $info->setSource($row['source']);
        return $info;
    }

    /**
     * Creates an ErpInfo array of properties based on a ErpInfo object.
     *
     * @param \ERP\Domain\ErpInfo $infos
     * @return array
     */
    protected function buildPropertiesArray(ErpInfos $infos) {
        $arr = Array();
        $arr['id'] = $infos->getId();
        $arr['id_erp'] = $infos->getId_erp();
        $arr['titre'] = $infos->getTitre();
        $arr['texte'] = $infos->getTexte();
        $arr['type'] = $infos->getType();
        $arr['x'] = $infos->getX();
        $arr['y'] = $infos->getY();
        $arr['ponctuel'] = $infos->getPonctuel();
        $arr['source'] = $infos->getSource();

        if ($infos->getId()) {
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
