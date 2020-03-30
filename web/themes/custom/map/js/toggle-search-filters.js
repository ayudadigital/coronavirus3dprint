(function ($) {
    Drupal.behaviors.toggle_search_filters_sidebar = {
        attach: function (context, settings) {

          // $('.view-content.owl-carousel').once('toggle-search-filters-sidebar').each(function(){
          //
          //   var pathname = window.location.pathname.toString();
          //   var referrer = '';
          //
          //   if(!document.referrer){
          //     referrer = pathname;
          //   }
          //   else{
          //     referrer = new URL(document.referrer).pathname.toString();
          //   }
          //
          //   if(referrer !== pathname){
          //     //shown on first load
          //     $('#sidebar-first').addClass('active');
          //   }
          //
          // });


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
