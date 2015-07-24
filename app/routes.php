<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use ERP\Actions;

// Client

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig');
});


// API ERP

# Enable CORS
$app->after(function (Request $request, Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
});

# /search/
$app->get('/erp/v1/search/', function (Request $request) use ($app) {
    $query = array(
      'q' => $request->get('q'),
      'fq' => $request->get('fq'),
      'qop'  => $request->get('qop'),
      'limit'  => $request->get('limit'),
      'start'  => $request->get('start'),
      'lat'  => $request->get('lat'),
      'lon'  => $request->get('lon'),
      'd'  => $request->get('d'),
      'bbox'  => $request->get('bbox'),
      'geom'  => $request->get('geom')
    );
    $data = Actions::getSearch($query);
    return $app->json($data, 200);
});

# /at/
$app->get('/erp/v1/at/{geom}/', function (Request $request) use ($app) {
    $query = array(
      'lat' => $request->get('lat'),
      'lon'  => $request->get('lon'),
      'geom'  => $request->get('geom')
    );
    $data = Actions::getErpAt($query);
    return $app->json($data, 200);
});

# /item/
$app->get('/erp/v1/item/{id}', function ($id) use ($app) {
    $query = array(
      'q' => 'id:'.$id,
      'fq' => '',
      'qop'  => 'AND',
      'limit'  => 1,
      'start'  => 0,
      'lat'  => '',
      'lon'  => '',
      'd'  => '',
      'geom'  => 'ponctuel'
    );
    $data = Actions::getSearch($query);
    return $app->json($data, 200);
});

$app->match('/erp/v1/item/', function (Request $request) use ($app) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = Actions::setErpFromGeojson(json_decode($request->getContent(), true));
        $json = $app['dao.erp']->save($data);
        if ($data->getId()) {
            return $app->json($json, 200);
        } else {
            return $app->json($json, 201);
        }
    } else {
        throw new \Exception("This require a valid Geojson with 'Content-Type' : 'application/json'");
    }
})
->method('POST|PUT|PATCH');

# /geom/
$app->get('/erp/v1/geom/{geom}/{id}', function (Request $request) use ($app) {
    $query = array(
      'id' => $request->get('id'),
      'geom'  => $request->get('geom')
    );
    $data = Actions::getGeom($query);
    return $app->json($data, 200);
});

$app->match('/erp/v1/geom/{geom}/', function (Request $request) use ($app) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = Actions::setGeomFromGeojson(json_decode($request->getContent(), true), $request->get('geom'));
        if ($data->getId()) { $code = 200; } else { $code = 201; }
        if ($request->get('geom') == 'bati') {
            $data = $app['dao.bati']->save($data);
        } elseif ($request->get('geom') == 'enceinte') {
            $data = $app['dao.enceinte']->save($data);
        }
        return $app->json(Array('id' => $data->getId()), $code);
    } else {
        throw new \Exception("This require a valid Geojson with 'Content-Type' : 'application/json'");
    }
})
->method('POST|PUT|PATCH');

# /infos/
$app->get('/erp/v1/infos/{id}', function ($id) use ($app) {
    $data = Actions::getInfos($id);
    return $app->json($data, 200);
});

$app->match('/erp/v1/infos/', function (Request $request) use ($app) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = Actions::setInfosFromGeojson(json_decode($request->getContent(), true));
        if ($data->getId()) { $code = 200; } else { $code = 201; }
        $data = $app['dao.infos']->save($data);
        return $app->json(Array('id' => $data->getId()), $code);
    } else {
        throw new \Exception("This require a valid Geojson with 'Content-Type' : 'application/json'");
    }
})
->method('POST|PUT|PATCH');

# /designations/
$app->get('/erp/v1/designations/', function (Request $request) use ($app) {
    $query = array(
      'q' => $request->get('q')
    );
    $data = Actions::getDesignations($query);
    return $app->json($data, 200);
});

# /bduni/
$app->get('/erp/v1/bduni/', function (Request $request) use ($app) {
    if (in_array($request->get('layer'), $app['bduni']['layers'])) {
        $bbox = Actions::cleanBbox($request->get('bbox'));
        $bbox = $app['dao.utils']->getLambertBboxFromWGS84($bbox);
        $data = Actions::getBdUni($bbox, $request->get('layer'), $app['bduni']['url']);
        return $app->json($data, 200);
    } else {
        throw new \Exception("Unknow layer");
    }
});

# /parcellaire/
$app->get('/erp/v1/bdparcellaire/', function (Request $request) use ($app) {
    $data = Actions::getGeoportailWFS($request->get('bbox'), 'BDPARCELLAIRE-VECTEUR_WLD_BDD_WGS84G:parcelle', $app['geoportail']['url'], $app['geoportail']['referer']);
    return $app->json($data, 200);
});

// API POPULATION

# /population/
$app->get('/population/v1/search/', function (Request $request) use ($app) {
    $query = array(
      'q' => $request->get('q'),
      'limit'  => $request->get('limit'),
      'start'  => $request->get('start'),
      'lat'  => $request->get('lat'),
      'lon'  => $request->get('lon'),
      'd'  => $request->get('d'),
      'bbox'  => $request->get('bbox')
    );
    $data = Actions::getPopulation($query);
    return $app->json($data, 200);
});

// API RISQUES

# /risques/
$app->get('/risques/v1/search/', function (Request $request) use ($app) {
    $query = array(
      'q' => $request->get('q'),
      'limit'  => $request->get('limit'),
      'start'  => $request->get('start'),
      'lat'  => $request->get('lat'),
      'lon'  => $request->get('lon'),
      'd'  => $request->get('d'),
      'bbox'  => $request->get('bbox')
    );
    $data = Actions::getRisques($query);
    return $app->json($data, 200);
});
