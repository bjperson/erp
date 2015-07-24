<?php
namespace ERP\DAO;

use ERP\Domain\Erp;

class UtilsDAO extends DAO
{
    /**
     * Transform a WGS84 bbox into a Lambert93 bbox.
     *
     * @param string $bbox WGS84 ("southwest_lng,southwest_lat,northeast_lng,northeast_lat").
     * @return string $bbox Lambert93
     */
    public function getLambertBboxFromWGS84($bbox) {
        $b = explode(",", $bbox);
        $sql = 'SELECT ST_AsText(ST_Transform(ST_SetSRID(ST_MakeLine(ST_MakePoint('.$b[0].', '.$b[1].'), ST_MakePoint('.$b[2].', '.$b[3].')),4326),2154)) as bbox2154';
        $result = $this->getDb()->fetchColumn($sql);
        $result = substr($result, 11, -1);
        $result = str_replace(' ', ',', $result);
        return $result;
    }

}
