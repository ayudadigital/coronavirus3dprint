(function ($) {
    Drupal.behaviors.toggle_search_filters_sidebar = {
        attach: function (context, settings) {

          $('.view-content.owl-carousel').once('toggle-search-filters-sidebar').each(function(){

            var referrer = new URL(document.referrer).pathname.toString();
            var pathname = window.location.pathname.toString();

            if(referrer !== pathname){
              //shown on first load
              $('#sidebar-first').addClass('active');
            }

          });


          //show/hidde sidebar
          $('.sidebar-btn', context).once().bind('click', function() {
            $(this).closest(".sidebar").toggleClass('active');
            return false;
          });

          //hidde sidebar
          // $('#searchbar-map-wrapper .form-actions button', context).once().bind('click', function() {
          //     $('#searchbar-map-wrapper', context).removeClass('active');
          // });

        }
    };
})(jQuery);
