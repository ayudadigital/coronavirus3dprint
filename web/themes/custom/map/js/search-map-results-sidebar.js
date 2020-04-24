(function ($) {
    Drupal.behaviors.search_map_results_sidebar = {
        attach: function (context, settings) {

            //load map once time
            $('.view-content.views-result-map-search', context).once('loadded-map-results').each(function(){
              console.log('ghhghg');
              //todo exist param in url
              $(this).closest('#sidebar-map-results').addClass('active');
            });

        }
    };
})(jQuery);
