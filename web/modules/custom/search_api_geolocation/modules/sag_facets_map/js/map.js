/**
 * @file
 * Provides the map functionality.
 */
(function ($) {

    'use strict';

    Drupal.facets = Drupal.facets || {};
    Drupal.behaviors.facetsMapWidget = {
      attach: function (context, settings) {
        if (settings.facets !== 'undefined' && settings.facets.map !== 'undefined') {
          $('#' + settings.facets.map.facet_id).once('exposed-filter-map').each(function () {
            var makedmap = Drupal.facets.makeMap(context, settings);
            Drupal.facets.UpdateValuesMap(makedmap[0], makedmap[1], context, settings);
          });
        }
      }
    };

    Drupal.facets.makeMap = function (context, settings) {

        // Add basemap.
        var baseLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 15,
            minZoom: 1,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });
        // Create map and set center and zoom.
        var map = new L.Map('sag-facets-map-block', {
            // scrollWheelZoom: false,
            // minZoom: 3,
            // maxZoom: 15,
            gestureHandling: true
        });
        // Add baselayer to map.
        map.addLayer(baseLayer);

        //cluster
        var markers = L.markerClusterGroup({
          chunkedLoading: true,
          spiderfyOnMaxZoom: true,
          showCoverageOnHover: false,
          zoomToBoundsOnClick: true,
          maxClusterRadius: 50,
          iconCreateFunction: function(cluster) {
            var markers = cluster.getAllChildMarkers();
            var markerCount = 0;
            markers.forEach(function(m){markerCount = markerCount + m.count;});
            var digits = (markerCount+'').length;
            return new L.DivIcon({
              html: markerCount,
              className:'marker-cluster digits-'+digits,
              iconSize: null
            });
          }
        });
        //add points to map
        map.addLayer(markers);

        // // //load map
        // map.on('load', function(){
        //   //get lat/long
        //   Drupal.facets.send_facets_filters(map, context, settings);
        // });

        //Dragend event of map for update marker position
        var zoomend_counter = 0;
        map.on('zoomend dragend', function(e) {
          //first zoom from map.setView is a bug from leaflet library
          zoomend_counter = zoomend_counter+1;
          if(zoomend_counter > 1){
            setTimeout(function(){
              Drupal.facets.send_facets_filters(map, context, settings);
            }, 1000);
          }

        });

        return [map, markers];

    };

    Drupal.facets.get_original_LatLong = function (context, settings) {
        var lat = 0;
        var lng = 0;

        if(settings.facets.map.lat !== 'undefined'){
            lat = settings.facets.map.lat;
        }
        if(settings.facets.map.lng !== 'undefined'){
            lng = settings.facets.map.lng;
        }

        return [lat, lng];
    };

    Drupal.facets.get_original_zoom = function (context, settings) {
        var zoom = 0;
        if(settings.facets.map.zoom !== 'undefined'){
            zoom = settings.facets.map.zoom;
        }
        return zoom;
    };

    Drupal.facets.send_facets_filters = function (map, context, settings) {
        var facet_id = settings.facets.map.facet_id;

        var cnt = map.getCenter();
        var lat = Number(cnt['lat']).toFixed(5);
        var lng = Number(cnt['lng']).toFixed(5);

        var zoom = map.getZoom();

        var b = map.getBounds();

        var top_left_lat_limit = b.getNorthWest().wrap().lat;
        var top_left_lng_limit = b.getNorthWest().wrap().lng;
        var bottom_right_lat_limit = b.getSouthEast().wrap().lat;
        var bottom_right_lng_limit = b.getSouthEast().wrap().lng;

        var params = lat + '/' + lng + '/' + zoom + '/' + top_left_lat_limit + '/' + top_left_lng_limit + '/' + bottom_right_lat_limit + '/' + bottom_right_lng_limit;

        // send values to query
        // window.location.href = drupalSettings.facets.map.url.replace('__GEOM__', params);
        var facet_map_link = drupalSettings.facets.map.url.replace('__GEOM__', params);

        //remove old link
        $('#'+facet_id).find('a.facet-map-link').remove();
        //add link
        $('#'+facet_id).append('<a class="facet-map-link" href="'+facet_map_link+'" rel="nofollow" data-drupal-facet-item-id="'+facet_id+'" data-drupal-facet-item-value="'+params+'" tabindex="-1" ></a>');
        //send values
        $('#'+facet_id).find('a.facet-map-link')[0].click();
    };

    Drupal.facets.UpdateValuesMap = function (map, markers, context, settings) {

        //get lat/long
        get_geohash();

        function get_geohash(){
            var hits = settings.facets.map.geo_hash;
            //reset array
            settings.facets.map.geo_hash = [];
            //reset map (delete old points)
            markers.clearLayers();
            //makes the points as returned by the server.
            makePoints(hits);
        }

        /* This will add all the clusters as returned by the elastic server.*/
        function makePoints(aggs){
            var markerList = [];
            var lat_long_list = [];
            aggs.forEach(function(agg, index){
                var center = geohash.decode (agg.key);//elastic return a geohas so need to change it into lat/lon
                var digits = (agg.doc_count+'').length;
                var myIcon = new L.DivIcon({
                    html: agg.doc_count,
                    className:'marker-cluster digits-'+digits,
                    iconSize: null
                });
                var marker = L.marker(new L.LatLng(center.latitude, center.longitude),{icon:myIcon});
                lat_long_list.push([center.latitude, center.longitude]);
                marker.count = agg.doc_count;
                // marker.bindPopup(''+agg.doc_count);
                markerList.push(marker);
            });
            markers.addLayers(markerList);

            //set initial point
            var original_LatLong = Drupal.facets.get_original_LatLong(context, settings);
            var original_zoom = Drupal.facets.get_original_zoom(context, settings);
            if(original_LatLong[0] == 0 && original_LatLong[1] == 0){
              var bounds = new L.LatLngBounds(lat_long_list);
              map.fitBounds(bounds);
              //lat 39, lng -4 = center map in Spain
              // map.setView([37,-7], 5);
            }
            else{
              map.setView(original_LatLong, original_zoom);
            }
        }
    };

})(jQuery);
