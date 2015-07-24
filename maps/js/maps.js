/* 
 * codice per il caricamento mappe runtime
 *  attacca il codice di openlayers alle form Drupal
 */

Drupal.behaviors.maps = function(context) {
    /***********************************************************************************
     * leaflet map implementation
     ***********************************************************************************/
    $('div.map').each(function() {
        var url = Drupal.settings.basePath + $(this).attr('mapFile');
        var idMe = $(this).attr('id');
        var file = $(this).attr('imgPosition');
        var h = $(this).height(), w = $(this).width();
        var bounds = [[46.07677, 13.22634], [46.08061, 13.23059]];
        var map = L.map(idMe).fitBounds(bounds, 2);
        var drawnItems = new L.FeatureGroup();
        var baseMaps = {
            "Toolserver maps": L.tileLayer('http://{s}.www.toolserver.org/tiles/bw-mapnik/{z}/{x}/{y}.png',
                    {maxZoom: 19, minZoom: 15}).addTo(map),
            "OSM maps": L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', // openStreetMap 
                    {maxZoom: 19, minZoom: 15}).addTo(map)
        };
        var drawings = {
            "images": L.imageOverlay(url, //[[46.07659, 13.22776],[46.0777, 13.2288 ]],
                    JSON.parse(file), // recuperal le coordinate dall'array 
                    {maxZoom: 23}).addTo(map),
            "Shapes": drawnItems
        };
        //carica le geometrie inviate dal DB
        L.geoJson(Drupal.settings.features, {
            onEachFeature: function(feature, layer) {
                drawnItems.addLayer(layer);
                storeObject(layer);
            }
        });

        map.addLayer(drawnItems); //aggiunge le forme all'insieme dei layer
        L.control.layers(baseMaps, drawings).addTo(map); //assegna i gruppi di layer al layer switcher
        // Set the title to show on the polygon button
        L.drawLocal.draw.toolbar.buttons.polygon = 'Draw a sexy polygon!';
        L.Icon({
            iconUrl: 'leaflet/images/marker-icon.png',
            iconRetinaUrl: 'leaflet/images/marker-icon@2x.png',
            iconSize: [38, 95],
            iconAnchor: [22, 94],
            popupAnchor: [-3, -76],
            shadowUrl: 'leaflet/images/marker-icon-shadow.png',
            shadowRetinaUrl: 'leaflet/images/marker-icon-shadow.png',
            shadowSize: [68, 95],
            shadowAnchor: [22, 94]
        });
        var drawControl = new L.Control.Draw({
            position: 'topleft',
            draw: {
                polyline: {
                    metric: true
                },
//                                polyline:false,
                polygon: {
                    allowIntersection: false,
                    showArea: true,
                    drawError: {
                        color: '#b00b00',
                        timeout: 1000
                    },
                    shapeOptions: {
                        color: '#bada55',
                        weight: 1
                    }
                },
                circle: {
                    shapeOptions: {
                        color: '#662d91',
                        weight: 1
                    }
                },
//				marker: { icon: marker }
            },
            edit: {
                featureGroup: drawings.Shapes,
                remove: true
            }
        });
        map.addControl(drawControl);
// evento di creazione della geometria      
        map.on('draw:created', function(e) {
            var type = e.layerType,
                    layer = e.layer;
//                if (type === 'marker') {
//                        layer.bindPopup(layer._latlng.toString());
//                }
//            console.log(JSON.stringify(layer.toGeoJSON()));
            drawings.Shapes.addLayer(layer);
            storeObject(layer);
        });
// evento di modifica della geometria
        map.on('draw:edited', function(e) {
            var layers = e.layers;
            var countOfEditedLayers = 0;
            layers.eachLayer(function(layer) {
                countOfEditedLayers++;
//                $('#ctrl-'+ idMe).html(JSON.stringify(layer.toGeoJSON()));   
//                console.log(JSON.stringify(layer.toGeoJSON()));
                storeObject(layer);
            });

//            console.log("Edited " + countOfEditedLayers + " layers");
        });
        map.on('draw:deleted', function(e) {
            var layers = e.layers;
            var countOfEditedLayers = 0;
            var empty = function() {
                return {}
            };
            layers.eachLayer(function(layer) {
                storeObject({
                    _leaflet_id: layer._leaflet_id,
                    toGeoJSON: empty,
                });
            });
        });

        addCoordinats(map); // visualizza le coordinate del puntatore

//    addZoomFS(map);
//		L.DomUtil.get('changeColor').onclick = function () {
//			drawControl.setDrawingOptions({ rectangle: { shapeOptions: { color: '#004a80' } } });
//                }         

    });
    /**
     * funzione per registrare l'oggetto GEO nei campi nascosti ed attivare l'evendo
     * di trasmissione al server
     */
    function storeObject(layer) {
        $('#edit-layer-tool-GeoJSON').val(JSON.stringify(layer.toGeoJSON())); // salva l'oggetto nel campo nascosto
        $('#edit-layer-tool-GeoJSON-ID').val(layer._leaflet_id.toString());//salva il riferimento per l'edit ajax
        $('#edit-layer-tool-GeoJSON-command').trigger('change'); // genera l'evento che attiva il trasferimento  

    }

    /**
     * aggiunge il pulsante per il full screen
     */
    function addZoomFS(map) {
        // create custom zoom control with fullscreen button
        var zoomFS = new L.Control.ZoomFS();

        // add custom zoom control
        map.addControl(zoomFS);

        // you can bind to 2 events: enterFullscreen and exitFullscreen
        // note that these events are on the map object, not the zoomfs object...
        map.on('enterFullscreen', function() {
            if (window.console)
                window.console.log('enterFullscreen');
        });
        map.on('exitFullscreen', function() {
            if (window.console)
                window.console.log('exitFullscreen');
        });
    }

    /**
     * adding tooltip with coordinates 
     */
    function addCoordinats(map) {
        L.control.coordinates(
//     {
//	position:"bottomleft", //optional default "bootomright"
//	decimals:2, //optional default 4
//	decimalSeperator:".", //optional default "."
//	labelTemplateLat:"Latitude: {y}", //optional default "Lat: {y}"
//	labelTemplateLng:"Longitude: {x}", //optional default "Lng: {x}"
//	enableUserInput:true, //optional default true
//	useDMS:false, //optional default false
//	useLatLngOrder: true //ordering of labels, default false-> lng-lat
//    }
                ).addTo(map);
    }

    /****************************************************************************************************************/


    /**
     * openlayers connections
     */
    /**************************************************************************************
     * 
     
     var map, vectors, controls;
     $('div.map').each(function(){
     map = new OpenLayers.Map($(this).attr('id')); //selezione del contenitore
     var size = new OpenLayers.Size(824,505); //dimensioni del contenitore
     var file_url = location.protocol + '//' + location.hostname +Drupal.settings.basePath + $(this).attr('mapFile');
     var bounds=new OpenLayers.Bounds(142321.16494,5792614.12202,1472428.65451,5792674.43562); //calcolo dei limiti
     //         inizializza il layer di base
     var img = new OpenLayers.Layer.Image(
     $(this).attr('name'),
     file_url,
     bounds,size,{transparent: true,isBaseLayer: false}
     );
     //        var bounds=new OpenLayers.Bounds(46.07677, 13.22634,46.08061, 13.23059);
     var wmsLayer = new OpenLayers.Layer.OSM( 'real map'
     //        ,
     //                    "http://{s}.tile.osm.org/{z}/{x}/{y}.png", {isBaseLayer:true}
     );
     //        wmsLayer.setIsBaseLayer();
     vectors= new OpenLayers.Layer.Vector('Drawings');              
     
     map.addLayers([wmsLayer,vectors,img]);
     map.setCenter(new OpenLayers.LonLat(1472503.44934, 5792918.72557), 17);
     map.zoomToMaxExtent;
     map.addControl(new OpenLayers.Control.MousePosition()); //show mouse position
     map.addControl(new OpenLayers.Control.LayerSwitcher());
     //        map.addControl(new OpenLayers.Control.Measure());
     map.addControl(new OpenLayers.Control.Scale());
     var elem=document.getElementById('ctrl-' + $(this).attr('id'));
     map.addControl(new OpenLayers.Control.EditingToolbar(vectors));
     map.addControl(new OpenLayers.Control.PinchZoom());
     
     
     
     //        controls = [
     //            new OpenLayers.Control.DragFeature(vectors),
     //            new  OpenLayers.Control.DrawFeature(vectors, OpenLayers.Handler.Point),
     //            new OpenLayers.Control.DrawFeature(vectors, OpenLayers.Handler.Polygon)
     //        ];
     });
     
     //    $('.map-ctrl [type="radio"]').click(function(){
     //        //var check = $(this).attr('checked');
     //        var idx = $(this).val();
     //        for (i in controls) {
     //            if (i==idx) {
     //                controls[idx].activate();
     //            }
     //            else {
     //                controls[idx].deactivate();
     //            }
     //
     //        }
     //    });
     *************************************************************************************/
};// end Drupal.behaviours...

