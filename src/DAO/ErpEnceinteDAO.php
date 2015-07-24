<?php
namespace ERP\DAO;

use ERP\Domain\ErpEnceinte;

class ErpEnceinteDAO extends DAO
{
    /**
     * Return a list of all ERPEnceinte, sorted by id (most recent first).
     *
     * @return array A list of all ERPEnceinte.
     */
    public function findAll() {
        $sql = "select * from erp_enceinte order by id desc limit 10";
        $result = $this->getDb()->fetchAll($sql);

        // Convert query result to an array of domain objects
        $enceintes = array();
        foreach ($result as $row) {
            $enceintes[$row['id']] = $this->buildDomainObject($row);
        }
        return $enceintes;
    }

    /**
     * Saves an ErpEnceinte into the database.
     *
     * @param \ERP\Domain\ErpEnceinte $enceinte
     * @return \ERP\Domain\ErpEnceinte
     */
    public function save(ErpEnceinte $enceinte) {
        if ($enceinte->getId()) {
            // The ERP has already been saved : update it
            $this->getDb()->update('erp_enceinte', self::buildPropertiesArray($enceinte), array('id' => $enceinte->getId()));
        } else {
            // The ERP has never been saved : insert it
            $this->getDb()->insert('erp_enceinte', self::buildPropertiesArray($enceinte));
            // Need fix : Workaround to get lastInsertId() wich is not working with pdo_pgsql...
            $id = $this->getDb()->fetchColumn("SELECT id FROM erp_enceinte order by id desc limit 1");
            //$id = $this->getDb()->lastInsertId();
            $enceinte->setId($id);
        }
        return $enceinte;
    }

    /**
     * Creates an ErpEnceinte object based on a DB row.
     *
     * @param array $row The DB row containing ErpEnceinte data.
     * @return \ERP\Domain\ErpEnceinte
     */
    protected function buildDomainObject($row) {
        $enceinte = new ErpEnceinte();
        $enceinte->setId($row['id']);
        $enceinte->setEnceinte($row['enceinte']);
        $enceinte->setIds_ign($row['ids_ign']);
        $enceinte->setSource($row['source']);
        $enceinte->setMd5($row['md5']);
        return $enceinte;
    }

    /**
     * Creates an ERP array of properties based on a ERP object.
     *
     * @param \ERP\Domain\ErpEnceinte $enceinte
     * @return array
     */
    protected function buildPropertiesArray(ErpEnceinte $enceinte) {
        $arr = Array();
        $arr['id'] = $enceinte->getId();
        $arr['ids_ign'] = $enceinte->getIds_ign();
        $arr['source'] = $enceinte->getSource();
        $arr['enceinte'] = $enceinte->getEnceinte();
        $arr['md5'] = $enceinte->getMd5();

        if ($enceinte->getId()) {
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
