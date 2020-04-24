(function ($, Drupal) {
  'use strict';

  Drupal.facets = Drupal.facets || {};

  Drupal.behaviors.search_map_results_sidebar = {
      attach: function (context, settings) {

          //show/hide sidebar of map results
          $('.view-content.views-result-map-search', context).once('sidebar-loadded-map-results').each(function(){
            if(settings.geohash !== undefined) {
              if (settings.geohash !== 0) {
                $(this).closest('#sidebar-map-results').addClass('active');
              }
              else {
                $(this).closest('#sidebar-map-results').removeClass('active');
              }
            }
          });

        //reload map once time
        $('body', context).once('loadded-map-results').each(function(){
          $(document, context).on("click", function(event){

            //out of sidebar
            if(!$(event.target).closest('#sidebar-map-results').length){
              if($('#sidebar-map-results').hasClass('active')){
                $('#sidebar-map-results').removeClass('active');
                reload_map();
              }
            }

            //close button
            if($(event.target).closest('.sidebar-btn.map-results').length){
              reload_map();
            }

          });
        });


        function reload_map(){

          let $ul = $('ul#'+settings.facets.map.facet_id);

          // Add correct CSS selector for the widget. The Facets JS API will
          // register handlers on that element.
          $ul.addClass('js-facets-widget');

          // We have to trigger attaching of behaviours, so that Facets JS API can
          // register handlers on link widgets.
          Drupal.attachBehaviors(context, Drupal.settings);

          //get current url path
          let pathname = window.location.pathname.toString();
          let search = window.location.search.toString();

          //remove geohash value to disable sidebar
          settings.geohash = 0;
          search = search.replace(/\(geom:(.+?)\//, '(geom:0/');

          //send values
          $ul.trigger('facets_filter', [ pathname + search ]);
        }

      }
  };

})(jQuery, Drupal);
