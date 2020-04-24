(function ($) {
    Drupal.behaviors.toggle_search_filters_sidebar = {
        attach: function (context, settings) {

          $('body').once('toggle-search-filters-sidebar').each(function(){
            //shown on first load
            $('#sidebar-first').addClass('active');
          });


          //show/hidde sidebar
          $('.sidebar-btn', context).once().bind('click', function() {
            $(this).closest(".sidebar").toggleClass('active');
          });

        }
    };
})(jQuery);
