<?php
namespace ERP;

use ERP\Domain\Erp;
use ERP\Domain\ErpBati;
use ERP\Domain\ErpEnceinte;
use ERP\Domain\ErpInfos;
use geoPHP;

$erp_props = Array(
    'id' => null,
    'id_adresse' => null,
    'numero' => null,
    'repetition' => null,
    'voie' => null,
    'complement' => null,
    'entree' => null,
    'lieu_dit' => null,
    'code_post' => null,
    'insee_com' => null,
    'nom_com' => null,
    'dpt_num' => null,
    'categorie' => null,
    'type' => null,
    'accessible' => null,
    'nom' => null,
    'activite' => null,
    'actif' => null,
    'public' => null,
    'capacite' => null,
    'hebergement' => null,
    'personnel' => null,
    'siret' => null,
    'code_ape' => null,
    'x' => null,
    'y' => null,
    'precision' => null,
    'id_parent' => null,
    'id_bati' => null,
    'id_encte' => null,
    'id_sdis' => null,
    'id_ddt' => null,
    'id_mairie' => null,
    'id_editeur' => null,
    'id_pai' => null,
    'id_iris' => null,
    'creation' => null,
    'modif' => null,
    'etat' => null,
    'ponctuel' => null,
    'bati' => null,
    'enceinte' => null,
    'infos' => null
);

$fl = implode(",", array_keys($erp_props));

$geom_props = Array(
    'id' => null,
    'bati' => null,
    'enceinte' => null,
    'ids_ign' => null,
    'source' => null,
    'md5' => null,
    'cle' => null
);

$info_props = Array(
    'id' => null,
    'id_erp' => null,
    'titre' => null,
    'texte' => null,
    'type' => null,
    'x' => null,
    'y' => null,
    'ponctuel' => null
);

class Actions
{
    // API ERP

    public function getSearch($query) {
        $qops = Array('OR', 'AND');
        $geoms = Array('ponctuel', 'bati', 'enceinte');
        global $fl, $erp_props;

        $q = str_replace(array('&','<','>'), ' ', trim($query['q']));
        if (trim($query['fq']) !== '') { $fq = str_replace(array('&','<','>'), ' ', trim($query['fq'])); }

        if (get_magic_quotes_gpc() == 1) { $q = stripslashes($q); if (isset($fq)) { $fq = stripslashes($fq); } }
        if (empty($q)) { $q = '*'; }

        if (!in_array($query['qop'], $qops)) { $qop = 'AND'; } else { $qop = $query['qop']; }
        if (!in_array($query['geom'], $geoms)) { $geom = 'ponctuel'; } else { $geom = $query['geom']; }
        if (intval($query['limit']) !== 0) { $limit = intval($query['limit']); } else { $limit = 100; }
        if ($limit > 500) { $limit = 500; }
        if (intval($query['start']) !== 0) { $start = intval($query['start']); } else { $start = 0; }
        if (is_numeric($query['lat'])) { $lat = $query['lat']; }
        if (is_numeric($query['lon'])) { $lon = $query['lon']; }
        if (is_numeric($query['d'])) { $d = $query['d']; }
        if (isset($query['fl'])) { $fl = $query['fl']; }
        if (isset($query['bbox'])) { $bbox = self::cleanBbox($query['bbox']); }

        $req = 'http://localhost:8983/solr/erp/select?q='.urlencode($q).'&start='.$start.'&rows='.$limit.'&wt=json&q.op='.$qop;

        if (isset($fq)) {
            $req .= '&fq='.$fq;
        }

        if (isset($bbox)) {
            $b = explode(',', $bbox);

            $req .= '&fq=w_ponctuel:['.$b[1].','.$b[0].'+TO+'.$b[3].','.$b[2].']';
        }
        elseif (isset($lat) && isset($lon)) {

          $req .= '&sfield=w_ponctuel&pt='.$lon.'+'.$lat.'&sort=geodist()+asc';

          $fl .= ',distance:geodist()';

          if (isset($d)) {
            $req .= '&fq={!geofilt+sfield=w_ponctuel}&pt='.$lon.'+'.$lat.'&d='.$d;
          }
        }

        $req .= '&fl='.$fl;

        $res = json_decode(file_get_contents($req), true);

        if($res['response']['numFound'] !== 0) {
          $featureCollection = Array(
            'query' => $res['responseHeader']['params']['q'],
            'nb' => $res['response']['numFound'],
            'start' => $res['response']['start'],
            'limit' => $res['responseHeader']['params']['rows'],
            'type' => 'FeatureCollection',
            'features' => Array()
          );

          foreach ($res['response']['docs'] as $key => $value) {
            $feature = Array(
              'properties' => array_merge($erp_props, (array) $res['response']['docs'][$key]),
              'geometry' => '',
              'type' => 'Feature'
            );

            foreach ($geoms as $g) {
              if (isset($feature['properties'][$g])) {
                if($g == $geom) { $feature['geometry'] = json_decode($feature['properties'][$g]); }
                unset($feature['properties'][$g]);
              }
            }

            unset($feature['properties']['infos']);

            array_push($featureCollection['features'], $feature);
          }

          return $featureCollection;
        } else {
          return array('nb' => 0);
        }
    }

    public function getGeom($query) {
      $geoms = Array('bati' => 'id_bati', 'enceinte' => 'id_encte');

      if (intval($query['id']) !== 0 && array_key_exists($query['geom'], $geoms)) {

        $q = $geoms[$query['geom']].':'.intval($query['id']);

        $req = 'http://localhost:8983/solr/erp/select?q='.$q.'&rows=1&wt=json&fl='.$geoms[$query['geom']].','.$query['geom'];

        $res = json_decode(file_get_contents($req), true);

        if($res['response']['numFound'] !== 0) {
          $featureCollection = Array(
            'query' => $res['responseHeader']['params']['q'],
            'type' => 'FeatureCollection',
            'features' => Array(
              Array(
                'properties' => $res['response']['docs'][0],
                'geometry' => json_decode($res['response']['docs'][0][$query['geom']]),
                'type' => 'Feature'
              )
            )
          );

          unset($featureCollection['features'][0]['properties'][$query['geom']]);

          return $featureCollection;
        } else {
          return array('nb' => 0);
        }
      }
    }

    public function getErpAt($query) {
      $geoms = Array('ponctuel', 'bati', 'enceinte');

      if (isset($query['lat']) && isset($query['lon']) && isset($query['geom'])) {

        if (is_numeric($query['lat']) && is_numeric($query['lon']) && in_array($query['geom'], $geoms)) {

          global $fl, $erp_props;

          $req = 'http://localhost:8983/solr/erp/select?q=*&fq=w_'.$query['geom'].':"Intersects('.$query['lon'].'+'.$query['lat'].')"&rows=1000&wt=json&fl='.$fl;

          $res = json_decode(file_get_contents($req), true);

          if($res['response']['numFound'] !== 0) {
            $featureCollection = Array(
              'nb' => $res['response']['numFound'],
              'type' => 'FeatureCollection',
              'features' => Array(
                Array(
                  'type' => 'Feature',
                  'properties' => '',
                  'geometry' => ''
                )
              )
            );

            global $erp_props;

            $props = Array();

            foreach ($res['response']['docs'] as $key => $value) {

              $prop = array_merge($erp_props, (array) $res['response']['docs'][$key]);

              foreach ($geoms as $g) {
                if (isset($prop[$g])) {
                  if($g == $query['geom']) { $featureCollection['features'][0]['geometry'] = json_decode($prop[$query['geom']]); }
                  unset($prop[$g]);
                }
              }

              array_push($props, $prop);
            }

            $featureCollection['features'][0]['properties'] = $props;

            return $featureCollection;
          } else {
                return array('nb' => 0);
            }
        }
      }
    }

    public function setErpFromGeojson($erp) {

        global $erp_props;

        $prop = array_merge($erp_props, $erp['features'][0]['properties']);

        if ($erp['features'][0]['geometry']['type'] == 'Point') {
            $prop['ponctuel'] = $erp['features'][0]['geometry']['coordinates'];
            $prop['x'] = (string) $prop['ponctuel'][0];
            $prop['y'] = (string) $prop['ponctuel'][1];
            $prop['ponctuel'] = 'POINT('.$prop['ponctuel'][0].' '.$prop['ponctuel'][1].')';
        } else {
            throw new \Exception("geometry type must be Point");
        }

        unset($prop['bati']);
        unset($prop['enceinte']);
        unset($prop['infos']);

        if (is_null($prop['id'])) { unset($prop['id']); }

        $prop = new Erp($prop);

        return $prop;
    }

    public function setGeomFromGeojson($geom, $type) {

        $geoms = Array('bati', 'enceinte');

        if (in_array($type, $geoms)) {

            global $geom_props;

            $prop = array_merge($geom_props, $geom['features'][0]['properties']);

            if (isset($prop['id_bati'])) {

                $prop['id'] = $prop['id_bati'];

                unset($prop['id_bati']);

            } elseif (isset($prop['id_encte'])) {

                $prop['id'] = $prop['id_encte'];

                unset($prop['id_encte']);

            }

            if ($geom['features'][0]['geometry']['type'] == 'MultiPolygon') {

                $prop[$type] = self::json_to_wkt(json_encode($geom));

            } else {

                throw new \Exception("geometry type must be MultiPolygon");

            }

            if (is_null($prop['id'])) {

                unset($prop['id']);

            }

            if ($type == 'bati') {

                unset($prop['enceinte']);

                $prop = new ErpBati($prop);

            } elseif ($type == 'enceinte') {

                unset($prop['bati']);
                unset($prop['cle']);

                $prop = new ErpEnceinte($prop);

            }

            return $prop;
        }
    }

    public function getInfos($id) {

        global $info_props;

      if (intval($id) !== 0) {

        $q = 'id:'.intval($id);

        $req = 'http://localhost:8983/solr/erp/select?q='.$q.'&rows=1&wt=json&fl=infos';

        $res = json_decode(file_get_contents($req), true);

        if($res['response']['numFound'] !== 0) {
          $featureCollection = Array(
            'query' => $res['responseHeader']['params']['q'],
            'type' => 'FeatureCollection',
            'features' => Array()
          );

          $infos = json_decode($res['response']['docs'][0]['infos'], true);

          foreach ($infos as $info) {
              $feature = Array(
                'type' => 'Feature'
              );
              $info = array_merge($info_props, $info);
              $feature['geometry'] = json_decode($info['ponctuel']);
              unset($info['ponctuel']);
              $feature['properties'] = $info;
              array_push($featureCollection['features'], $feature);
          }

          return $featureCollection;
        } else {
          return array('nb' => 0);
        }
      }
    }

    public function setInfosFromGeojson($infos) {

        global $info_props;

        $prop = array_merge($info_props, $infos['features'][0]['properties']);

        if ($infos['features'][0]['geometry']['type'] == 'Point') {
            $prop['ponctuel'] = $infos['features'][0]['geometry']['coordinates'];
            $prop['x'] = (string) $prop['ponctuel'][0];
            $prop['y'] = (string) $prop['ponctuel'][1];
            $prop['ponctuel'] = 'POINT('.$prop['ponctuel'][0].' '.$prop['ponctuel'][1].')';
        } else {
            throw new \Exception("geometry type must be Point");
        }

        if (is_null($prop['id'])) { unset($prop['id']); }

        $prop = new ErpInfos($prop);

        return $prop;
    }

    public function getBdUni($bbox, $layer, $url) {

        // Source is EPSG:2154 only at the moment so we convert to EPSG:4326
        if ($stream = file_get_contents($url.'&typeName='.$layer.'&bbox='.$bbox.'&filter={%22detruit%22%3Afalse}', 'r')) {
            $iname = tempnam("tmp", "i");
            $oname = $iname.'o';
            file_put_contents($iname, $stream);
            exec('ogr2ogr -f "GeoJSON" '.$oname.' '.$iname.' -s_srs "EPSG:2154" -t_srs "EPSG:4326"',$output,$error);
            $data = json_decode(file_get_contents($oname));
            unlink($iname);
            unlink($oname);
            return $data;
        } else {
            throw new \Exception("Service unavailable");
        }
    }

    public function getGeoportailWFS($bbox, $layer, $url, $referer) {

        $bbox = self::cleanBbox($bbox);

        $ch = curl_init($url.'&typeName='.$layer.'&bbox='.$bbox);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Referer: '.$referer));
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($data = curl_exec($ch)) {
            $data = json_decode($data, true);
            return $data;
        } else {
            throw new \Exception("Service unavailable");
        }
    }

    public function getDesignations($query) {
        $q = str_replace(array('&','<','>'), ' ', trim($query['q']));
        if (get_magic_quotes_gpc() == 1) { $q = stripslashes($q); }
        if (empty($q)) { $q = '*'; } else { $q = '*'.$q.'*'; }

        $req = 'http://localhost:8983/solr/designations/select?q='.urlencode($q).'&start=0&rows=10&wt=json&q.op=OR&fl=designation';

        $res = json_decode(file_get_contents($req), true);

        $data = array();

        foreach ($res['response']['docs'] as $doc) {
            array_push($data, $doc['designation']);
        }

        return $data;
    }

    // API POPULATION

    public function getPopulation($query) {

        $q = str_replace(array('&','<','>'), ' ', trim($query['q']));
        if (get_magic_quotes_gpc() == 1) { $q = stripslashes($q); }
        if (empty($q)) { $q = '*'; }

        if (intval($query['limit']) !== 0) { $limit = intval($query['limit']); } else { $limit = 100; }
        if (intval($query['start']) !== 0) { $start = intval($query['start']); } else { $start = 0; }
        if (is_numeric($query['lat'])) { $lat = $query['lat']; }
        if (is_numeric($query['lon'])) { $lon = $query['lon']; }
        if (is_numeric($query['d'])) { $d = $query['d']; }
        if (isset($query['bbox'])) { $bbox = self::cleanBbox($query['bbox']); }

        $req = 'http://localhost:8983/solr/population/select?q='.urlencode($q).'&start='.$start.'&rows='.$limit.'&wt=json&q.op=OR&fl=id,idinspire,ind_c,the_geom';

        if (isset($bbox)) {
            $b = explode(',', $bbox);

            $req .= '&fq=w_the_geom:['.$b[1].','.$b[0].'+TO+'.$b[3].','.$b[2].']';
        }
        elseif (isset($lat) && isset($lon)) {

          $req .= '&sfield=w_the_geom&pt='.$lon.'+'.$lat.'&sort=geodist()+asc';

          $fl .= ',distance:geodist()';

          if (isset($d)) {
            $req .= '&fq={!geofilt+sfield=w_the_geom}&pt='.$lon.'+'.$lat.'&d='.$d;
          }
        }

        $res = json_decode(file_get_contents($req), true);

        if($res['response']['numFound'] !== 0) {
          $featureCollection = Array(
            'query' => $res['responseHeader']['params']['q'],
            'nb' => $res['response']['numFound'],
            'start' => $res['response']['start'],
            'type' => 'FeatureCollection',
            'features' => Array()
          );

          foreach ($res['response']['docs'] as $key => $value) {
            $feature = Array(
              'properties' => $res['response']['docs'][$key],
              'type' => 'Feature'
            );

            $feature['geometry'] = json_decode($feature['properties']['the_geom'], true);

            unset($feature['properties']['the_geom']);

            array_push($featureCollection['features'], $feature);
          }

          return $featureCollection;
        } else {
          return array('nb' => 0);
        }
    }

    // API RISQUES

    public function getRisques($query) {

        $q = str_replace(array('&','<','>'), ' ', trim($query['q']));
        if (get_magic_quotes_gpc() == 1) { $q = stripslashes($q); }
        if (empty($q)) { $q = '*'; }

        if (intval($query['limit']) !== 0) { $limit = intval($query['limit']); } else { $limit = 100; }
        if (intval($query['start']) !== 0) { $start = intval($query['start']); } else { $start = 0; }
        if (is_numeric($query['lat'])) { $lat = $query['lat']; }
        if (is_numeric($query['lon'])) { $lon = $query['lon']; }
        if (is_numeric($query['d'])) { $d = $query['d']; }
        if (isset($query['bbox'])) { $bbox = self::cleanBbox($query['bbox']); }

        $req = 'http://localhost:8983/solr/risques/select?q='.urlencode($q).'&start='.$start.'&rows='.$limit.'&wt=json&q.op=AND&fl=id,wkb_geometry,ident,document,auteur,date_approbation,date_approbation_affichee,precision,risque,theme,code_degre,degre,label,lien_rapport,taille_rapport,lien_reglement,taille_reglement,fournisseur,date_import';

        if (isset($bbox)) {
            $b = explode(',', $bbox);

            $req .= '&fq=w_wkb_geometry:['.$b[1].','.$b[0].'+TO+'.$b[3].','.$b[2].']';
        }
        elseif (isset($lat) && isset($lon)) {

          $req .= '&sfield=w_wkb_geometry&pt='.$lon.'+'.$lat.'&sort=geodist()+asc';

          $fl .= ',distance:geodist()';

          if (isset($d)) {
            $req .= '&fq={!geofilt+sfield=w_wkb_geometry}&pt='.$lon.'+'.$lat.'&d='.$d;
          }
        }

        $res = json_decode(file_get_contents($req), true);

        if($res['response']['numFound'] !== 0) {
          $featureCollection = Array(
            'query' => $res['responseHeader']['params']['q'],
            'nb' => $res['response']['numFound'],
            'start' => $res['response']['start'],
            'type' => 'FeatureCollection',
            'features' => Array()
          );

          foreach ($res['response']['docs'] as $key => $value) {
            $feature = Array(
              'properties' => $res['response']['docs'][$key],
              'type' => 'Feature'
            );

            $feature['geometry'] = json_decode($feature['properties']['wkb_geometry'], true);

            unset($feature['properties']['wkb_geometry']);

            array_push($featureCollection['features'], $feature);
          }

          return $featureCollection;
        } else {
          return array('nb' => 0);
        }
    }

    // OTHER

    public function cleanBbox($bbox) {
        if(isset($bbox)) {
            $sanity = 0;

            $bbox1 = explode(",", $bbox);

            foreach ($bbox1 as $value) {
                if(!is_numeric($value)) { $sanity++; }
            }

            if ($sanity !== 0 || count($bbox1) !== 4) {
                throw new \Exception("bbox must be 'southwest_lng,southwest_lat,northeast_lng,northeast_lat' with numeric values");
            }

            return $bbox;
        }
    }

    private function json_to_wkt($json) {
        require __DIR__.'/../vendor/phayes/geophp/geoPHP.inc';
        $geom = geoPHP::load($json,'json');
        return $geom->out('wkt');
    }

}
