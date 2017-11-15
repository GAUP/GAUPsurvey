/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

gmaps = new Object;
osmaps = new Object;
zoom = [];
var hideResponses = [];
var MapJSONResponses = [];

$(document).ready(function(){
    $('.if-no-js').each(function(index, element) {
        $(element).css({"display":"none"});//hide();
    });

    $(".location").each(function(index,element){
        var survey      = $(element).attr('name');
        var coordinates = $(element).val();
        var latLng      = coordinates.split(" ");
        if ($("#mapservice" + survey ).val() == 100){
            //  Maps
            if (osmaps['' + survey] == undefined) {
                osmaps['' + survey] = OSGeoInitialize(survey,latLng);
            }
        }
    });
});
 
function isvalidCoord(val){
    if (!isNaN(parseFloat(val)) && (val>-180 && val<180)) {
        return true;
    } else {
        return false;
    }
}
 
// OSMap functions
function OSGeoInitialize(question,latLng){
        var tileServerURL = {
            OSM : "http://{s}.tile.openstreetmap.org/{z}/{x}/{y}",
            HUM : "http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}",
            CYC : "http://{s}.tile.thunderforest.com/cycle/{z}/{x}/{y}",
            TRA : "http://{s}.tile.thunderforest.com/transport/{z}/{x}/{y}"
        };
        var name = question;//.substr(0,question.length - 2);
        // tiles layers def
        // If not latLng is set the Map will center to Hamburg
        var MapOption=LSmaps[name];
        if(isNaN(MapOption.latitude) || MapOption.latitude==""){
            MapOption.latitude=53.582665;
        }
        if(isNaN(MapOption.longitude) || MapOption.longitude==""){
            MapOption.longitude=10.018924;
        }
        var mapOSM = L.tileLayer(tileServerURL.OSM+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
            attribution: 'Map data © <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
        });
        var mapCYC = L.tileLayer(tileServerURL.CYC+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
            attribution: 'Map data © <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
        });
        var mapHOT = L.layerGroup([L.tileLayer(tileServerURL.HUM+".png", {
            maxZoom: 20,
            subdomains: ["a", "b", "c"],
        }), L.tileLayer(tileServerURL+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
            attribution: 'Map data © <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
        })]);
        var mapTRA = L.layerGroup([L.tileLayer(tileServerURL.TRA+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
        }), L.tileLayer(tileServerURL+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
            attribution: 'Map data © <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
        })]);

        var baseLayers = {
            "Street Map": mapOSM,
            "Humanitarian": mapHOT,
            "Cyclemap": mapCYC,
            "Traffic Map" : mapTRA
        };
        var overlays = {
        };

    var homeButton = false;
    if ((typeof(MapOption.homeButton) != 'undefined')) {
    homeButton = MapOption.homeButton;
    }

    var geojson = '';
    if ((typeof(MapOption.geojson) != 'undefined')) {
    geojson = MapOption.geojson;
    }
    var invert_geojson = false;
    if ((typeof(MapOption.geojson_invert) != 'undefined')) {
    if (MapOption.geojson_invert == '1') {
        invert_geojson = true;
    }
    }

    var allow_polygon = false;
    var allow_polyline = false;
    var allow_rectangle = false;
    var allow_edit_features = false;
    var allow_marker = false;
    var allow_comment = false;

    if ((typeof(MapOption.allow_polygon) != 'undefined')) {
        allow_polygon = MapOption.allow_polygon;
    }

    if ((typeof(MapOption.allow_polyline) != 'undefined')) {
        allow_polyline = MapOption.allow_polyline;
    }

    if ((typeof(MapOption.allow_rectangle) != 'undefined')) {
        allow_rectangle = MapOption.allow_rectangle;
    }

    if ((typeof(MapOption.allow_edit_features) != 'undefined')) {
        allow_edit_features = MapOption.allow_edit_features;
    }

    if ((typeof(MapOption.allow_marker) != 'undefined')) {
        allow_marker = MapOption.allow_marker;
    }

    if ((typeof(MapOption.allow_comment) != 'undefined')) {
        allow_comment = MapOption.allow_comment;
    }

    var maxBounds0 = -90;
    var maxBounds1 = -180;
    var maxBounds2 = 90;
    var maxBounds3 = 180;
    if ((typeof(MapOption.maxBounds0) != 'undefined')) {
        maxBounds0 = MapOption.maxBounds0;
        maxBounds1 = MapOption.maxBounds1;
        maxBounds2 = MapOption.maxBounds2;
        maxBounds3 = MapOption.maxBounds3;
    }
    var map = L.map("map_"+name, {
        zoom: MapOption.zoomLevel,
        minZoom: MapOption.minZoomLevel,
        maxZoom: MapOption.maxZoomLevel,
        zoomControl: !homeButton,
        center: [MapOption.latitude, MapOption.longitude] ,
        maxBounds: ([[maxBounds0, maxBounds1],[maxBounds2, maxBounds3]]),
        layers: [mapOSM]
    });

    if (homeButton) {
        var zoomHome = L.Control.zoomHome({position: 'topleft'});
        zoomHome.addTo(map);
    }

    var polygongeojson;
    if (geojson != ''){
        geojson = JSON.parse(geojson);
        polygongeojson = L.geoJson(geojson, {
        // Add invert: true to invert the geometries in the GeoJSON file
        invert: invert_geojson,
        }).addTo(map);
    }

    var featureGroup = L.featureGroup().addTo(map);
    var drawtoolbaractions = [];
    if (allow_marker) {
        drawtoolbaractions.push(L.Draw.Marker);
    }
    if (allow_polygon) {
        drawtoolbaractions.push(L.Draw.Polygon);
    }
    if (allow_polyline) {
        drawtoolbaractions.push(L.Draw.Polyline);
    }
    if (allow_rectangle) {
        drawtoolbaractions.push(L.Draw.Rectangle);
    }
    if (allow_edit_features) {
        new L.EditToolbar.Control({
            position: 'topleft',
            className: 'leaflet-draw-toolbar'
        }).addTo(map, featureGroup);
    }

    var pt1 = latLng[0].split("@");
    var pt2 = latLng[1].split("@");

    if ((pt1.length == 2) && (pt2.length == 2)) { // is Rect
        var isRect = true;
        lat = "";
        lng = "";
        minLat = pt1[0];
        minLng = pt1[1];
        maxLat = pt2[0];
        maxLng = pt2[1];
        map.fitBounds([[minLat, minLng],[maxLat, maxLng]]);
        map.setMaxBounds([[minLat, minLng],[maxLat, maxLng]]);
        UI_update("","");
    } else { // is default marker position
        var isRect = false;
        lat = latLng[0];
        lng = latLng[1];
    }

    if (isNaN(parseFloat(lat)) || isNaN(parseFloat(lng))) {
        lat=-9999; lng=-9999;
    }

    // var marker = new L.marker([lat,lng], {title:'Current Location',id:1,draggable:'true'});
    // map.addLayer(marker);

    if (geojson != '') {
        overlays = {
            "AREA": polygongeojson
        };
    }

    var layerControl = L.control.layers(baseLayers, overlays, {
        collapsed: true
    }).addTo(map);

    map.on('draw:created', function(e) {
    var type = e.layerType,
    layer = e.layer;
    feature = layer.feature = layer.feature || {};
    feature.type = feature.type || "Feature";
    var props = feature.properties = feature.properties || {};
    props.desc = "";
    props.saved_id = MapOption.saved_id;
    props.question_code = MapOption.question_code;
    props.survey_id = MapOption.survey_id;
    e.layer.options.draggable = true;
    featureGroup.addLayer(layer);
    var data = featureGroup.toGeoJSON();
    $("#answer"+name).val(JSON.stringify(data));
        if (allow_comment == true) {
            addPopup(layer);
        }
    });

    function addPopup(layer) {
        var contiudo = document.createElement("div");
        contiudo.style = 'text-align: center;';
        var content = document.createElement("textarea");
        content.addEventListener("keyup", function () {
            layer.feature.properties.desc = content.value;
            var data = featureGroup.toGeoJSON();
            $("#answer"+name).val(JSON.stringify(data));
        });
        layer.on("popupopen", function () {
            content.value = layer.feature.properties.desc;
            content.focus();
        });
        var b1 = document.createElement('input');
        b1.type='button';
        b1.value='OK';
        b1.onclick= function () { map.closePopup(); };
        b1.style = 'width:125px';
        var nl = document.createElement('br');
        contiudo.appendChild(content);
        contiudo.appendChild(nl);
        contiudo.appendChild(b1);
        layer.bindPopup(contiudo).openPopup();
    }

    // Zoom to 11 when switching to Aerial or Hybrid views - bug 10589
    var layer2Name, layer3Name, layerIndex = 0;
    for (var key in baseLayers) {
        if (!baseLayers.hasOwnProperty(key)) {
            continue;
        }
        if(layerIndex == 1) {
            layer2Name = key;
        }
        else if(layerIndex == 2) {
            layer3Name = key;
        }
        layerIndex++;
    }
    
    map.on('baselayerchange', function(e) {
        if(e.name == layer2Name || e.name == layer3Name) {
            map.setZoom(11);
        }
    });

    (function ($) {
        $.each(['show', 'hide'], function (i, ev) {
            var el = $.fn[ev];
            $.fn[ev] = function () {
            this.trigger(ev);
            return el.apply(this, arguments);
            };
        });
    })(jQuery);

    for(const quest of Object.keys(MapJSONResponses)) {
        MapJSONResponses[quest]['geojson'] = L.geoJson(MapJSONResponses[quest], {
            style: {
                "color": MapJSONResponses[quest]['colorLine'],
                "weight": 5,
                "opacity": 0.65 
                    }
        });
        MapJSONResponses[quest]['geojson'].addTo(map);
    }
    //Define an array of Latlng objects (points along the line)
    return map;
}

/* Placeholder hack for IE */
if (navigator.appName == "Microsoft Internet Explorer") {
    $("input").each(function () {
        if ($(this).val() === "" && $(this).attr("placeholder") !== "") {
            $(this).val($(this).attr("placeholder"));
            $(this).focus(function () {
                if ($(this).val() === $(this).attr("placeholder")) $(this).val("");
            });
            $(this).blur(function () {
                if ($(this).val() === "") $(this).val($(this).attr("placeholder"));
            });
        }
    });
}

function showHideMapResponsesLayer(idResponse,iSurveyId,element) {
    var layer = MapJSONResponses[idResponse]['geojson'];
    var map = osmaps['' + iSurveyId];
    if(map.hasLayer(layer)) {
        layer.remove(map);
    } else {
        layer.addTo(map);
    }

    var backgroundColor = rgb2hex(element.style.background);
    var textColor = rgb2hex(window.getComputedStyle(element,null).getPropertyValue('color'));
    element.style.background = textColor;
    element.style.color = backgroundColor; 
}

function rgb2hex(rgb){
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? "#" +
     ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
     ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
     ("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : '';
}
