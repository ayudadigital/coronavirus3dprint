(function ($, Drupal) {
  'use strict';

  Drupal.facets = Drupal.facets || {};

  Drupal.behaviors.search_map_results_sidebar = {
      attach: function (context, settings) {

          //load map once time
          $('.view-content.views-result-map-search', context).once('loadded-map-results').each(function(){

            if(settings.geohash !== undefined) {
              if (settings.geohash !== 0) {
                $(this).closest('#sidebar-map-results').addClass('active');
              }
              else {
                $(this).closest('#sidebar-map-results').removeClass('active');
              }
            }

            $(document).on("click", function(event){
              if(!$(event.target).closest('#sidebar-map-results').length){
                $('#sidebar-map-results').removeClass('active');
              }
            });

          });

      }
  };

})(jQuery, Drupal);
