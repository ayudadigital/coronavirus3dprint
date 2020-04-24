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
        });
        // Add baselayer to map.
        map.addLayer(baseLayer);

        //cluster
        var markers = L.markerClusterGroup({
          chunkedLoading: true,
          spiderfyOnMaxZoom: true,
          showCoverageOnHover: false,
          zoomToBoundsOnClick: true,
          maxClusterRadius: 35,
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

        markers.on('click', function (a) {
          //send new geohash param
          Drupal.facets.send_facets_filters(map, context, settings, a.layer.geohash);
        });

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

    Drupal.facets.send_facets_filters = function (map, context, settings, geohash=0) {
      let facet_id = settings.facets.map.facet_id;

      let zoom = map.getZoom();

      let b = map.getBounds();

      let top_left_lat_limit = b.getNorthWest().wrap().lat;
      let top_left_lng_limit = b.getNorthWest().wrap().lng;
      let bottom_right_lat_limit = b.getSouthEast().wrap().lat;
      let bottom_right_lng_limit = b.getSouthEast().wrap().lng;

      //build new params
      let geoparams = '(geom:' + geohash + '/' + zoom + '/' + top_left_lat_limit + '/' + top_left_lng_limit + '/' + bottom_right_lat_limit + '/' + bottom_right_lng_limit +')';

      //add to js variables to detect from other js scripts
      settings.geohash = geohash;

      //get parameter base name
      let facets_url_name = settings.facets.map.facet_url_name;

      //get and alter old params
      let exist_geo_params = false;
      let facets_index_num = 0;
      let new_params = {};
      let old_params = get_query_params();
      $.each(old_params, function(key,val) {
        //is facets??
        if(key.includes('f[')){
          if(val !== ':null') {
            facets_index_num += 1;
            let new_facets_key = 'f[' + facets_index_num + ']';

            //check if first time to this filter
            if (val.includes(facets_url_name + ':')) {
              exist_geo_params = true;
              //refactor current value
              new_params[new_facets_key] = facets_url_name + ':' + geoparams;
            }
            else {
              new_params[new_facets_key] = val;
            }
          }

        }
        else{
          new_params[key] = val;
        }
      });

      //check if first time to this filter
      if(!exist_geo_params){
        facets_index_num += 1;
        new_params['f[' + facets_index_num + ']'] = facets_url_name + ':' + geoparams;
      }

      //build new url params
      let new_params_array = [];
      $.each(new_params, function(key,val) {
        let str = key + "=" + val;
        new_params_array.push(str);
      });

      let new_params_string = new_params_array.join("&");

      var $ul = $('ul#'+facet_id);

      // Add correct CSS selector for the widget. The Facets JS API will
      // register handlers on that element.
      $ul.addClass('js-facets-widget');

      // We have to trigger attaching of behaviours, so that Facets JS API can
      // register handlers on link widgets.
      Drupal.attachBehaviors(context, Drupal.settings);

      //get current url path
      var pathname = window.location.pathname.toString();

      //send values
      $ul.trigger('facets_filter', [ pathname + '?' + new_params_string ]);


      function get_query_params() {
        // initialize an empty object
        let result = {};
        // get URL query string
        let params = window.location.search;
        params = decodeURIComponent(params);
        // remove the '?' character
        params = params.substr(1);
        // split the query parameters
        let queryParamArray = params.split('&');
        // iterate over parameter array
        queryParamArray.forEach(function (queryParam) {
          // split the query parameter over '='
          let item = queryParam.split('=');
          result[item[0]] = item[1];
        });

        return result;
      }

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

                lat_long_list.push([center.latitude, center.longitude]);

                var marker = L.marker(new L.LatLng(center.latitude, center.longitude),{icon:myIcon});
                marker.geohash = agg[0];
                marker.count = agg[1];
                // marker.bindPopup(''+agg.doc_count);
                markerList.push(marker);
            });
            markers.addLayers(markerList);

            //only set default position in first load
            $('#sag-facets-map-block').once('set-first-position').each(function () {
              //set initial point
              var bounds = new L.LatLngBounds(lat_long_list);
              map.fitBounds(bounds);
            });

        }
    };

})(jQuery, Drupal);
