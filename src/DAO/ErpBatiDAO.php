<?php
namespace ERP\DAO;

use ERP\Domain\ErpBati;

class ErpBatiDAO extends DAO
{
    /**
     * Saves an ErpBati into the database.
     *
     * @param \ERP\Domain\Erp $erp the ERP to save
     * @return \ERP\Domain\Erp
     */
    public function save(ErpBati $bati) {
        if ($bati->getId()) {
            // The ERP has already been saved : update it
            $this->getDb()->update('erp_bati', self::buildPropertiesArray($bati), array('id' => $bati->getId()));
        } else {
            // The ERP has never been saved : insert it
            $this->getDb()->insert('erp_bati', self::buildPropertiesArray($bati));
            // Workaround to get lastInsertId() wich is not working with pdo_pgsql...
            $id = $this->getDb()->fetchColumn("SELECT id FROM erp_bati order by id desc limit 1");
            //$id = $this->getDb()->lastInsertId();
            $bati->setId($id);
        }
        return $bati;
    }

    /**
     * Creates an ErpBati object based on a DB row.
     *
     * @param array $row The DB row containing ErpBati data.
     * @return \ERP\Domain\ErpBati
     */
    protected function buildDomainObject($row) {
        $bati = new ErpBati();
        $bati->setId($row['id']);
        $bati->setIds_ign($row['ids_ign']);
        $bati->setSource($row['source']);
        $bati->setCle($row['cle']);
        $bati->setBati($row['bati']);
        $bati->setMd5($row['md5']);
        return $bati;
    }

    /**
     * Creates an ErpBati array of properties based on a ERP object.
     *
     * @param \ERP\Domain\ErpBati $bati
     * @return array
     */
    protected function buildPropertiesArray(ErpBati $bati) {
        $arr = Array();
        $arr['id'] = $bati->getId();
        $arr['ids_ign'] = $bati->getIds_ign();
        $arr['source'] = $bati->getSource();
        $arr['cle'] = $bati->getCle();
        $arr['bati'] = $bati->getBati();
        $arr['md5'] = $bati->getMd5();

        if ($bati->getId()) {
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
