/**
 * @file
 * Provides the map functionality.
 */
(function ($, Drupal) {

    'use strict';

    Drupal.facets = Drupal.facets || {};

    Drupal.behaviors.facetsMapWidget = {
      attach: function (context, settings) {
        if (settings.facets !== 'undefined' && settings.facets.map !== 'undefined') {

          $('#sag-facets-map-block').once('exposed-filter-map').each(function () {
            Drupal.facets.makeMap(context, settings);
          });

          $('#' + settings.facets.map.facet_id, context).once('exposed-filter-map').each(function () {
            Drupal.facets.UpdateValuesMap(settings['facets']['rendered_map'][0], settings['facets']['rendered_map'][1], context, settings);
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
            Drupal.facets.send_facets_filters(map, context, settings);
          }
        });

        //set map values
        settings['facets']['rendered_map'] = [map, markers];

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

      var params = '(geom:' + lat + '/' + lng + '/' + zoom + '/' + top_left_lat_limit + '/' + top_left_lng_limit + '/' + bottom_right_lat_limit + '/' + bottom_right_lng_limit + ')';


      var $ul = $('ul#'+facet_id);

      //get current url params
      var pathname = window.location.pathname.toString();
      var search = window.location.search.toString();
      var current_url = pathname + search;
      current_url = decodeURIComponent(current_url);

      var facets_url_name = '=' + settings.facets.map.facet_url_name + ':';

      //add ? to acept first param filter
      if(!current_url.includes('?')){
        current_url = current_url + '?'
      }

      //set default url
      var facet_map_link = current_url + '&f[0]' + facets_url_name + params;

      //check if first time to this filter
      if(current_url.includes(facets_url_name)){
        //refactor current url
        facet_map_link = current_url.replace(/\(geom:(.+?)\)/, params);
      }

      // Add correct CSS selector for the widget. The Facets JS API will
      // register handlers on that element.
      $ul.addClass('js-facets-widget');

      //send values
      $ul.trigger('facets_filter', [ facet_map_link ]);

      // We have to trigger attaching of behaviours, so that Facets JS API can
      // register handlers on link widgets.
      Drupal.attachBehaviors(context, Drupal.settings);
    };

    Drupal.facets.getValuesMap = function (map, markers, context, settings) {
      var facet_id = settings.facets.map.facet_id;

      var hits = [];

      var $results = $('ul#'+facet_id).find('li a');

      $results.each(function () {
        var geohash = $(this).find('.facet-item__value').text().toString();
        var geohash_count = $(this).find('.facet-item__count').text().toString();
        //remove non numeric characters
        geohash_count = geohash_count.replace(/\D/g,'').replace(/[_\W]+/g, '');
        geohash_count = parseInt(geohash_count);

        hits.push([geohash, geohash_count]);
      });

      return hits;
    };

    Drupal.facets.UpdateValuesMap = function (map, markers, context, settings) {

        //get lat/long
        get_geohash();

        function get_geohash(){
          var hits = Drupal.facets.getValuesMap(map, markers, context, settings);
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
                var center = geohash.decode (agg[0]);//elastic return a geohas so need to change it into lat/lon
                var digits = (agg[1]+'').length;
                var myIcon = new L.DivIcon({
                    html: agg[1],
                    className:'marker-cluster digits-'+digits,
                    iconSize: null
                });
                var marker = L.marker(new L.LatLng(center.latitude, center.longitude),{icon:myIcon});
                lat_long_list.push([center.latitude, center.longitude]);
                marker.count = agg[1];
                // marker.bindPopup(''+agg.doc_count);
                markerList.push(marker);
            });
            markers.addLayers(markerList);


            //only set default position in first load
            $('#sag-facets-map-block').once('set-first-position').each(function () {
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
            });

        }
    };

})(jQuery, Drupal);
