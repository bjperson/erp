// Geocodeur Etalab
API_URL = '//api-adresse.data.gouv.fr';

var searchPoints = L.geoJson(null, {
      onEachFeature: function (feature, layer) {
          layer.bindPopup(feature.properties.name);
      }
  });
var showSearchPoints = function (geojson) {
  searchPoints.clearLayers();
  searchPoints.addData(geojson);
};
var formatResult = function (feature, el) {
  var title = L.DomUtil.create('strong', '', el),
      detailsContainer = L.DomUtil.create('small', '', el),
      details = [];
  title.innerHTML = feature.properties.label || feature.properties.name;
  var types = {
      housenumber: 'numéro',
      street: 'rue',
      locality: 'lieu-dit',
      hamlet: 'hameau',
      village: 'village',
      city: 'ville',
      commune: 'commune'
  };
  if (types[feature.properties.type]) L.DomUtil.create('span', 'type', title).innerHTML = types[feature.properties.type];
  if (feature.properties.city && feature.properties.city !== feature.properties.name) {
      details.push(feature.properties.city);
  }
  if (feature.properties.context) details.push(feature.properties.context);
  detailsContainer.innerHTML = details.join(', ');
};


var SHORT_CITY_NAMES = ['y', 'ay', 'bu', 'by', 'eu', 'fa', 'gy', 'oo', 'oz', 'py', 'ri', 'ry', 'sy', 'ur', 'us', 'uz'];
var photonControlOptions = {
  resultsHandler: showSearchPoints,
  placeholder: 'Ex. 6 Avenue Émile Zola Paris…',
  position: 'topleft',
  url: API_URL + '/search/?',
  formatResult: formatResult,
  noResultLabel: 'Aucun résultat',
  feedbackLabel: 'Signaler',
  feedbackEmail: 'adresses@data.gouv.fr',
  minChar: function (val) {
      return SHORT_CITY_NAMES.indexOf(val) !== -1 || val.length >= 3;
  },
  submitDelay: 200
};
// Fin Geocodeur Etalab

layers = new Array('adresse', 'batiment', 'cimetiere', 'commune', 'pai_culture_et_loisirs', 'pai_equipement_administratif_ou_militaire', 'pai_espace_naturel', 'pai_gestion_des_eaux', 'pai_hydrographie', 'pai_industriel_et_commercial', 'pai_orographie', 'pai_religieux', 'pai_sante', 'pai_science_et_enseignement', 'pai_sport', 'pai_transport', 'pai_zone_habitation', 'piste_d_aerodrome', 'terrain_de_sport');

var geojson;

function fitContent() {
    boxh=$('#content').outerHeight()-$('#search').outerHeight()-$('#edit').outerHeight()-$('#layers').outerHeight()-15;
    $('#map_canvas').height(maph);
    $('.box').css('max-height', boxh);
}

$('.boxtitle').on('click', function(e) {
    $('.box').css('display', 'none');
    $('#box_'+$(this).attr('id')).css('display', 'block');

});

$('.searchbtn').on('click', function(e) {
    $('#start').val(0)
    getSearch($(this).attr('id'));
});

$("#getsearch").submit(function( event ) {
    $('#start').val(0);
    allLayers['search'][0].clearLayers();
    event.preventDefault();
    getSearch('simple');
});

$('#activite2 .typeahead').typeahead({
    highlight: true,
    minLength: 3,
},
{
    name: 'q',
    source: function(query, syncResults, asyncResults) {
        $.getJSON( "http://dev.erp-ign.fr/erp/v1/designations/", {q: query}, function(data) {
            asyncResults(data);
        });
    }
});

function getErpAt(e) {
    $.getJSON( "http://dev.erp-ign.fr/erp/v1/at/bati/", {lat: e.latlng.lat, lon: e.latlng.lng}, function(data) {
        if(Number(data.nb) !== 0) {
            layer = 'erpat';
            if (allLayers[layer] !== undefined) {
                if(map.hasLayer(allLayers[layer][0]) == true) {
                  map.removeLayer(allLayers[layer][0]);
                }
            }
            popup = '';
            colornum = 5;

            for (f in data.features[0].properties) {
                popup += '- '+data.features[0].properties[f].nom+' <a href="javascript:editERP('+data.features[0].properties[f].id+')">Editer</a></br >';
                if(data.features[0].properties[f].categorie !== null) {
                    if (data.features[0].properties[f].categorie < colornum) { colornum = data.features[0].properties[f].categorie; }
                }
            }

            allLayers[layer] = new Array(
                L.geoJson(data, {
                    style: function (feature) {
                        return {color: erpColors[colornum]};
                    },
                    onEachFeature: function (feature, flayer) {
                        flayer.bindPopup(popup);
                    },
                    name: 'Recherche dans bâtiment',
                    layername: 'erpat',
                    attribution: 'BD ERP'
                }),
                '',
                1
            );
            allLayers[layer][0].addTo(map);
        }
    });
}

function editERP(id) {
    $.getJSON( "http://dev.erp-ign.fr/erp/v1/item/"+id, function(data) {
        for (property in data.features[0].properties) {
            $('#'+property).val(data.features[0].properties[property]);
        }
        $('#edit').click();
    });
}

function getErpNear(e) {
    $('#loading').html('<img src="/img/ajax-loader.gif" />');
    $('#searchresults').html('');
    allLayers['search'][0].clearLayers();

    q = $('#q').val();
    fq = $('#fq').val();
    start = 0;

    opts = { q: q, limit: 100, fq: fq, start: start, lat: e.latlng.lat, lon: e.latlng.lng, d: 0.5 };

    getResults(opts, 'proximite');
}

function getSearch(type) {

    $('#loading').html('<img src="/img/ajax-loader.gif" />');
    $('.nextresults').remove();

    q = $('#q').val();
    fq = $('#fq').val();
    start = $('#start').val();

    opts = { q: q, limit: 100, fq: fq, start: start };

    if (type == 'carte') {
        bbox = map.getBounds(); bbox = bbox.toBBoxString();
        opts = { q: q, limit: 100, bbox: bbox, fq: fq, start: start };
        getResults(opts, type);
    } else if (type == 'autour') {
        var deferred = $.Deferred();

        var success = function (position) {
            // resolve the deferred with your object as the data
            deferred.resolve({
                longitude: position.coords.longitude,
                latitude: position.coords.latitude
            });
        };

        var fail = function (error) {
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    msg = "Vous n'avez pas accepté d'être géolocalisé.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    msg = "Aucune information n'est disponible pour vous géolocaliser.";
                    break;
                case error.TIMEOUT:
                    msg = "La demande pour obtenir votre emplacement a expiré.";
                    break;
                case error.UNKNOWN_ERROR:
                    msg = "Une erreur inconnue ne nous permet pas de vous géolocaliser.";
                    break;
            }
            deferred.reject(msg);
        };

        var getLocation = function () {

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(success, fail);
            } else {
                $('#summary h2').append('<span class="error">Erreur : La géolocalisation n\'est pas supportée par ce navigateur</span>');
            }

            navigator.geolocation.getCurrentPosition(success, fail);

            return deferred.promise(); // return a promise
        };

        // then you would use it like this:
        getLocation().then(
            function (location) {
                 opts = { q: q, limit: 100, lat: location.latitude, lon: location.longitude, fq: fq, start: start };
                 getResults(opts, type);
            },
            function (errorMessage) {
                $('#summary h2').append(' <span class="error">Erreur : '+errorMessage+'</span>');
                $('#loading').html('');
            }
        );
    } else {
        getResults(opts, type);
    }
}

function getResults(opts, type) {
    $.getJSON( "http://dev.erp-ign.fr/erp/v1/search/", opts, function(data) {
        if(Number(data.nb) !== 0) {
            layer = 'search';
            if (allLayers[layer] !== undefined && data.start == 0) {
                if(map.hasLayer(allLayers[layer][0]) == true) {
                  map.removeLayer(allLayers[layer][0]);
                }
            }

            if (data.start == 0) {
                $('#searchresults').html('');
            }

            $('#summary').html('<h2>'+data.nb+' résultats</h2>');

            allLayers[layer] = new Array(
                L.geoJson(data, {
                    style: function (feature) {
                        return {color: 'black'};
                    },
                    pointToLayer: function(feature, latlng) {
                        if(feature.properties.categorie !== null) {
                            icon = erpIcons[feature.properties.categorie]
                        } else {
                            icon = erpIcons[5];
                        }
                        return new L.marker(latlng, {icon: icon});
                    },
                    onEachFeature: function (feature, flayer) {
                        flayer.bindPopup(feature.properties.nom+'<br /><a href="javascript:editERP('+feature.properties.id+')">Editer</a>');
                        /*
                        flayer.on("mouseover", function (e) {
                            flayer.openPopup();
                        });
                        */
                        $('#searchresults').append(showResult(feature.properties));
                    },
                    name: 'Resultats de recherche',
                    layername: 'search',
                    attribution: 'BD ERP'
                }),
                '',
                1
            );
            allLayers[layer][0].addTo(map);

            if(data.nb > data.limit && data.start < (data.nb - data.limit)) {
                $('#start').val(Number($('#start').val())+Number(data.limit));
                $('#searchresults').append('<div class="nextresults" onclick="javascript:getSearch(\''+type+'\')" onmouseover="javascript:getSearch(\''+type+'\')" style="text-align: center;">Charger les résultats suivants</div>');
                $('#summary h2').append('<a class="linknext" href="javascript:getSearch(\''+type+'\')">>></a>');
            }



            if (type !== 'carte') {
                map.fitBounds(allLayers[layer][0].getBounds());
            }
        } else {
            $('#summary h2').html('<span class="error">Aucun résultats</span>');
            $('#searchresults').html('');
            allLayers['search'][0].clearLayers();
        }
        $('#loading').html('');
    });
}

function showResult(properties) {
    if(properties.categorie !== null) {
        color = erpColors[properties.categorie]
    } else {
        color = erpColors[5];
    }
    return '<div class="result" onclick="javascript:goTo('+properties['y']+','+properties['x']+')"><span class="nom">'+properties['nom']+'</span> à <span class="nom_com">'+properties['nom_com']+' ('+properties['dpt_num']+')</span> <span class="cat" style="background-color:'+color+'">'+properties['categorie']+' '+properties['type']+'</span> <span class="editresult"><a href="javascript:editERP('+properties['id']+')">Editer</a></span> </div>';
}

function goTo(lat, lon) {
    map.setView([lat, lon], 18, {animate: true});
}

function fitScreen() {
maph=$(window).height()-$('#header').outerHeight();
$('#map_canvas').height(maph);
$('#content').height(maph);
}

function getWFS(bbox) {
$('#features').append('<img src="/img/ajax-loader.gif" /> ');
if(bbox === undefined) { bbox = map.getBounds(); bbox = bbox.toBBoxString() }
layer = $('#layer').val();

if($.inArray(layer, layers) !== -1) {
  $.getJSON( "/erp/v1/bduni/", { layer: layer, bbox: bbox }, function(data) {
    if(map.hasLayer(allLayers[layer]) == true) {
      map.removeLayer(allLayers[layer]);
    }

    nb = data.features.length;
    $('#features').html('<strong>'+nb+' objets</strong>');

    allLayers[layer] = new L.featureGroup();
    allLayers[layer].addTo(map);

    geojson = L.geoJson(data, {
      style: style,
      onEachFeature: onEachFeature
    });

    allLayers[layer].addLayer(geojson);
  });
}
}

bati = [];
parcel = [];

function getOneFromBduni(e, name, vector) {

layer = 'batiment';

if($.inArray(layer, layers) !== -1) {
  circle = L.circle([e.latlng.lat, e.latlng.lng], 1);
  bbox = circle.getBounds();
  bbox = bbox.toBBoxString();
  $.getJSON( "http://dev.erp-ign.fr/erp/v1/bduni/", { layer: layer, bbox: bbox }, function(data) {
    gd = 0;
    $.each(data.features, function(i) {
      if(isInPolygon([e.latlng.lng, e.latlng.lat], data.features[i].geometry.coordinates[0][0])) { vector[data.features[i].properties.cleabs] = data.features[i]; gd++; }
    });
    nb = data.features.length;
    renderVectorLayer(name, vector);
  });
}
}

function getOneFromBdParcellaire(e, name, vector) {

circle = L.circle([e.latlng.lat, e.latlng.lng], 1);
bbox = circle.getBounds();
bbox = bbox.toBBoxString();
$.getJSON( "http://dev.erp-ign.fr/erp/v1/bdparcellaire/", { bbox: bbox }, function(data) {
    gd = 0;
    $.each(data.features, function(i) {
      if(isInPolygon([e.latlng.lng, e.latlng.lat], data.features[i].geometry.coordinates[0][0])) { vector[data.features[i].properties.cleabs] = data.features[i]; gd++; }
    });
    nb = data.features.length;
    renderVectorLayer(name, vector);
});
}

function renderVectorLayer(name, vector) {
tmpvector = [];
for (i in vector) {
  tmpvector.push(vector[i]);
};
data = {type: 'FeatureCollection', features: tmpvector}
if(map.hasLayer(allLayers[name]) == true) {
  map.removeLayer(allLayers[name]);
}
allLayers[name] = new L.featureGroup();
allLayers[name].addTo(map);

geojson = L.geoJson(data, {
  style: style,
  onEachFeature: onEachFeature
});

allLayers[name].addLayer(geojson);
}

function isInPolygon(point, polygon) {
var x = point[0], y = point[1];
var inside = false;
for (var i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
  var xi = polygon[i][0], yi = polygon[i][1];
  var xj = polygon[j][0], yj = polygon[j][1];
  var intersect = ((yi > y) != (yj > y))
  && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
  if (intersect) inside = !inside;
}
return inside;
};

props = new Array('geometrie','empreinte','_id');

function setBdUniPopup(prop) {
var table = L.DomUtil.create('table', 'popup-content');
$.each(prop, function(index, value) {
  if($.inArray(index, props) == -1) {
    $(table).append('<tr><th>'+index+'</th><td>'+value+'</td></tr>');
  }
});
return table;
}

colors = {'Indifférenciée' : '#2166ac','Sportive' : '#1b7837','Mairie' : '#d73027','Industrielle' : '#5E6166','Agricole' : '#8c510a','Commerciale' : '#542788','Religieuse' : '#f1a340'};

function style(feature) {
if(colors[feature.properties.fonction] !== 'undefined') { color = colors[feature.properties.fonction]; } else { color = '#2166ac'; }
return {
  weight: 2,
  opacity: 1,
  color: color,
  dashArray: '',
  fillOpacity: 0.7
};
}

function highlightFeature(e) {
var layer = e.target;
layer.setStyle({
  weight: 4,
  color: 'red',
  dashArray: ''
});
if (!L.Browser.ie && !L.Browser.opera) {
  layer.bringToFront();
}
}

function resetHighlight(e) {
geojson.resetStyle(e.target);
}

function onEachFeature(feature, layer) {
layer.bindPopup(setBdUniPopup(feature.properties));
layer.on({
  mouseover: highlightFeature,
  mouseout: resetHighlight
});
}

function onEachFeature2(feature, layer) {
layer.bindPopup(setBdUniPopup(feature.properties));
}

function loadFromHash() {
if(location.hash) {
  hashvars = new Array();
  hashes = location.hash.substring(1).split('&');
  for (vars in hashes) {
    v = hashes[vars].split('=');
    hashvars[v[0]] = v[1];
  }
  if(hashvars.hasOwnProperty('bbox')) {
    b = hashvars['bbox'].split(',');
    var bounds = [[b[1], b[0]], [b[3], b[2]]];
    map.fitBounds(bounds);
  }
  if(hashvars.hasOwnProperty('load')) {
    getWFS();
  }
}
}

function setHashLink() {
bbox = map.getBounds();
bbox = bbox.toBBoxString();
hashes = 'bbox='+bbox;
window.location.hash = hashes;
}

function showLayers() {
$('#layerlist').html('');
for (layer in allLayers) {
    if (map.hasLayer(allLayers[layer][0]) == true) { checked = 'checked'; } else { checked = ''; }
    $('#layerlist').append('<div><input type="checkbox" id="'+layer+'" class="layercontrol" '+checked+'/> <label for="'+layer+'">'+allLayers[layer][0]['options']['name']+'</label></div>');
}
}

function addLayer(layer) {
  allLayers[layer][0].addTo(map);
  showLayers();
}

function removeLayer(layer) {
  map.removeLayer(allLayers[layer][0]);
  showLayers();
}

function initmap() {

ignkey = '61hm1akqvb5grybsdhx8kc89';

map = L.map('map_canvas', {
  center: [47.06129129529406, 4.655869150706053],
  zoom: 6,
  zoomControl: false,
  attributionControl : true,
  photonControl: true,
  photonControlOptions: photonControlOptions
});

cscale = L.control.scale({"position": 'bottomright', "imperial": false, "updateWhenIdle": true}).addTo(map);
czoom = L.control.zoom({"position": 'bottomright'}).addTo(map);

allLayers['map'] = new Array(
  L.tileLayer("http://wxs.ign.fr/"+ignkey+"/geoportail/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=GEOGRAPHICALGRIDSYSTEMS.PLANIGN&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&FORMAT=image%2Fjpeg", {
    minZoom: "1",
    maxZoom: "18",
    name: 'Plan IGN',
    attribution: '© <a href="http://www.ign.fr">IGN</a>, Plan IGN'
  }), '', 1);

allLayers['cadastre'] = new Array(
  L.tileLayer("http://wxs.ign.fr/"+ignkey+"/geoportail/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=CADASTRALPARCELS.PARCELS&STYLE=bdparcellaire&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&FORMAT=image%2Fpng", {
    minZoom: "5",
    maxZoom: "20",
    name: 'Cadastre',
    attribution: 'Cadastre'
  }), '', 1);
allLayers['orthophotos_ign'] = new Array(
  L.tileLayer('http://gpp3-wxs.ign.fr/'+ignkey+'/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=ORTHOIMAGERY.ORTHOPHOTOS&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&FORMAT=image%2Fjpeg', {
    minZoom: 1,
    maxZoom: 19,
    name: 'Photographies aériennes',
    layername: 'ORTHOIMAGERY.ORTHOPHOTOS',
    attribution: '© <a href="http://www.geoportail.gouv.fr/">Géoportail</a>'
  }), '', 2);
/*
allLayers['relief_ign'] = new Array(
  L.tileLayer('http://gpp3-wxs.ign.fr/'+ignkey+'/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=ELEVATION.SLOPES&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&FORMAT=image%2Fjpeg', {
    minZoom: 1,
    maxZoom: 19,
    name: 'Carte du relief',
    layername: 'ELEVATION.SLOPES',
    attribution: '© <a href="http://www.geoportail.gouv.fr/">Géoportail</a>'
  }), '', 2);
*/
allLayers['erp32'] = new Array(
  L.tileLayer("http://www.ideeslibres.org/mapproxy/tiles/erp32_EPSG900913/{z}/{x}/{y}.png", {
//          L.tileLayer("http://erp.ign.fr/mapproxy/tiles/erp_EPSG900913/{z}/{x}/{y}.png", {
    tms: true,
    opacity: 0.6,
    maxZoom: 19,
    minZoom: 1,
    name: 'ERP du Gers (32)',
    layername: 'erp32',
    attribution: '<a href="http://catalogue.geo-ide.developpement-durable.gouv.fr/catalogue/apps/search/?uuid=fr-120066022-jdd-5fedeff4-9782-4d93-b0d4-063b2cc55c7d" title="Établissements recevant du public dans le Gers">DDT du Gers</a>'
  }), '', 20);

allLayers['rail_ign'] = new Array(
  L.tileLayer('http://gpp3-wxs.ign.fr/'+ignkey+'/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=TRANSPORTNETWORKS.RAILWAYS&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&FORMAT=image%2Fpng', {
    minZoom: 1,
    maxZoom: 19,
    opacity: 0.6,
    name: 'Réseaux de transports (Ferré)',
    layername: 'TRANSPORTNETWORKS.RAILWAYS',
    attribution: '© <a href="http://www.geoportail.gouv.fr/">Géoportail</a>'
  }), '', 2);

allLayers['routes_ign'] = new Array(
  L.tileLayer('http://gpp3-wxs.ign.fr/'+ignkey+'/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=TRANSPORTNETWORKS.ROADS&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&FORMAT=image%2Fpng', {
    minZoom: 1,
    maxZoom: 19,
    opacity: 0.6,
    name: 'Réseaux de transports (Routier)',
    layername: 'TRANSPORTNETWORKS.ROADS',
    attribution: '© <a href="http://www.geoportail.gouv.fr/">Géoportail</a>'
  }), '', 2);

allLayers['population'] = new Array(
  L.tileLayer("http://erp.ign.fr/mapproxy/tiles/carroyage_EPSG900913/{z}/{x}/{y}.png", {
    tms: true,
    opacity: 0.6,
    maxZoom: 16,
    minZoom: 1,
    name: 'Population (carroyage)',
    layername: 'population',
    attribution: '<a href="http://www.insee.fr/fr/themes/detail.asp?reg_id=0&ref_id=donnees-carroyees&page=donnees-detaillees/donnees-carroyees/donnees_carroyees_diffusion.htm" title="Population : Données carroyées INSEE">INSEE</a>'
  }), '', 20);

allLayers['risques32'] = new Array(
  L.tileLayer("http://www.ideeslibres.org/mapproxy/tiles/risques32_EPSG900913/{z}/{x}/{y}.png", {
    tms: true,
    opacity: 0.6,
    maxZoom: 18,
    minZoom: 1,
    name: 'Risques Gers (32)',
    layername: 'risques32',
    attribution: '<a href="http://cartorisque.prim.net/dpt/32/32_ip.html" title="Risques dans le Gers">Cartorisque</a>'
  }), '', 20);

allLayers['risques'] = new Array(
  L.tileLayer("http://erp.ign.fr/mapproxy/tiles/risques_EPSG900913/{z}/{x}/{y}.png", {
    tms: true,
    opacity: 0.6,
    maxZoom: 18,
    minZoom: 1,
    name: 'Risques',
    layername: 'risques',
    attribution: '<a href="http://cartorisque.prim.net/dpt/32/32_ip.html" title="Risques en France">Cartorisque</a>'
  }), '', 20);

allLayers['risques_temp'] = new Array(
  L.tileLayer("http://erp.ign.fr/mapproxy/tiles/risques_temp_EPSG900913/{z}/{x}/{y}.png", {
    tms: true,
    opacity: 0.6,
    maxZoom: 18,
    minZoom: 1,
    name: 'Risques temp',
    layername: 'risques_temp',
    attribution: '<a href="http://cartorisque.prim.net/dpt/32/32_ip.html" title="Risques en France">Cartorisque</a>'
  }), '', 20);


allLayers['noms_ign'] = new Array(
  L.tileLayer('http://gpp3-wxs.ign.fr/'+ignkey+'/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=GEOGRAPHICALNAMES.NAMES&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&FORMAT=image%2Fpng', {
    minZoom: 1,
    maxZoom: 19,
    opacity: 0.6,
    name: 'Dénominations géographiques',
    layername: 'GEOGRAPHICALNAMES.NAMES',
    attribution: '© <a href="http://www.geoportail.gouv.fr/">Géoportail</a>'
  }), '', 2);

allLayers['map'][0].addTo(map);

var activelayer = L.Control.extend({
  options: {
    position: 'topright'
  },
  onAdd: function (map) {
    var container = L.DomUtil.create('span', 'bduni');
    container.innerHTML = '\
      <select id="activelayer" title="Selection du calque actif au click">\
          <option value="" selected>Selectionner</option>\
          <option value="proximite">Proximité</option>\
          <option value="bderp">BD ERP</option>\
          <option value="bduni">BD UNI</option>\
          <option value="bdparcellaire">BD PARCELLAIRE</option>\
      </select>';
    return container;
  }
});

erpIcon1 = L.icon({
    iconUrl: '/js/images/marker-icon1.png',
    iconRetinaUrl: '/js/images/marker-icon1-2x.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowUrl: '/js/images/marker-shadow.png',
    shadowRetinaUrl: '/js/images/marker-shadow.png',
    shadowSize: [41, 41],
    shadowAnchor: [12, 41]
});

erpIcon2 = L.icon({
    iconUrl: '/js/images/marker-icon2.png',
    iconRetinaUrl: '/js/images/marker-icon2-2x.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowUrl: '/js/images/marker-shadow.png',
    shadowRetinaUrl: '/js/images/marker-shadow.png',
    shadowSize: [41, 41],
    shadowAnchor: [12, 41]
});

erpIcon3 = L.icon({
    iconUrl: '/js/images/marker-icon3.png',
    iconRetinaUrl: '/js/images/marker-icon3-2x.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowUrl: '/js/images/marker-shadow.png',
    shadowRetinaUrl: '/js/images/marker-shadow.png',
    shadowSize: [41, 41],
    shadowAnchor: [12, 41]
});

erpIcon4 = L.icon({
    iconUrl: '/js/images/marker-icon4.png',
    iconRetinaUrl: '/js/images/marker-icon4-2x.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowUrl: '/js/images/marker-shadow.png',
    shadowRetinaUrl: '/js/images/marker-shadow.png',
    shadowSize: [41, 41],
    shadowAnchor: [12, 41]
});

erpIcon5 = L.icon({
    iconUrl: '/js/images/marker-icon5.png',
    iconRetinaUrl: '/js/images/marker-icon5-2x.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowUrl: '/js/images/marker-shadow.png',
    shadowRetinaUrl: '/js/images/marker-shadow.png',
    shadowSize: [41, 41],
    shadowAnchor: [12, 41]
});

erpIcons = {
    '1': erpIcon1,
    '2': erpIcon2,
    '3': erpIcon4,
    '4': erpIcon3,
    '5': erpIcon5
};

erpColors = {
    '1': '#D63E2A',
    '2': '#A97A39',
    '3': '#FFC000',
    '4': '#94AF35',
    '5': '#8B8B8B'
};

map.addControl(new activelayer());

searchPoints.addTo(map);

map.on('moveend', function(e) {
  setHashLink();
  if($('#auto').prop('checked')) { getWFS(); }
});

$('#activelayer').on('change', function() {
    map.removeEventListener('click');

    if ($(this).val() == 'bderp') {
        map.on('click', function(e) { getErpAt(e) });
    }
    if ($(this).val() == 'bduni') {
        map.on('click', function(e) { getOneFromBduni(e, 'bati', bati) });
    }
    if ($(this).val() == 'bdparcellaire') {
        map.on('click', function(e) { getOneFromBdParcellaire(e, 'parcel', parcel) });
    }
    if ($(this).val() == 'proximite') {
        map.on('click', function(e) { getErpNear(e) });
    }
});

stopPropag();

loadFromHash();

$('#box_search').css('display', 'block');
}

function stopPropag() {
$.each($('.leaflet-control'), function() {
  L.DomEvent.disableClickPropagation(this);
  L.DomEvent.on(this, 'click', L.DomEvent.stopPropagation);
  L.DomEvent.on(this, 'mousewheel', L.DomEvent.stopPropagation);
  L.DomEvent.on(this, 'MozMousePixelScroll', L.DomEvent.stopPropagation);
});
}

allLayers = [];
erpIcons = {};
erpColors = {};
$(document).ready( function() {
initmap();
$(window).resize(function() {
  fitScreen();
  fitContent();
});
fitScreen();
fitContent();
showLayers();
map.invalidateSize(1);
$("#layerlist").on( "change", "input", function() {
  if (this.checked) {
    addLayer($(this).attr('id'));
  }
  else {
    removeLayer($(this).attr('id'));
  }
});
});
