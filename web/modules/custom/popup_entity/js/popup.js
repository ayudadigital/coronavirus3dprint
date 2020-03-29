(function ($, Drupal) {
  Drupal.behaviors.popup_entity = {
    attach: function (context, settings) {

      $('body', context).once('popup-entity-loaded').each(function () {

        var popups = $('.popup-entity-popup');

        //close popups
        $(".popup-close").on( "click", function() {
          var popup = $(this).closest('.popup-entity-popup');
          if(popup.length > 0){
            popup.addClass('popup-entity-hide');
            popup.addClass('popup-entity-closed');
            update_cookie(popup);
          }
          //hide overlay
          if($('.popup-entity-popup:visible').length <= 0){
            $('body').removeClass('popup-entity-open')
          }

        });

        //only in first load page
        show_all_popups(popups);

        $(window).resize(function() {
          show_all_popups(popups);
        });

      });

      function show_all_popups(popups){
        //hide all
        popups.addClass('popup-entity-hide');
        //recalculate show
        if(show_popups(popups)){
          $('body').addClass('popup-entity-open')
        }
      }

      function is_visible(popup){
        var visible = false;
        if(!popup.hasClass('popup-entity-closed')) {
          var breakpoints = popup.attr('breakpoints');
          if(breakpoints) {
            breakpoints = breakpoints.split("|");
            $.each(breakpoints, function (index, value) {
              if (window.matchMedia(value).matches) {
                visible = true;
              }
            });
          }
          else{
            visible = true;
          }
        }
        return visible;
      }

      function show_popups(popups){
        var show = false;
        popups.each(function() {
          var popup_element = $(this);
          if(is_visible(popup_element)) {
            var popup_id = Number(popup_element.attr('popup_id'));
            var popup_open_delay = Number(popup_element.attr('popup_open_delay'));
            var popup_times_to_show = Number(popup_element.attr('popup_times_to_show'));

            // Get the old visit count, or 0 if this is his first time
            var popup_views_count = $.cookie(popup_id + "_popup_count") || 0;
            popup_views_count = Number(popup_views_count);

            if (popup_views_count >= popup_times_to_show && popup_times_to_show !== 0) {
              //not show popup
              return true;
            }

            //show
            setTimeout(function () {
              popup_element.removeClass('popup-entity-hide');
            }, popup_open_delay);

            show = true;
          }

        });

        return show;
      }

      function update_cookie(popup_element){
        var popup_id = Number(popup_element.attr('popup_id'));
        var popup_cookies_expiration_minutes = Number(popup_element.attr('popup_cookies_expiration'));

        // Get the old visit count, or 0 if this is his first time
        var popup_views_count = $.cookie(popup_id + "_popup_count") || 0;
        popup_views_count = Number(popup_views_count);
        popup_views_count++;

        // Save the updated cookie
        var date = new Date();
        date.setTime(date.getTime() + (popup_cookies_expiration_minutes * 60 * 1000));
        popup_views_count = popup_views_count + 1;
        $.cookie(popup_id + "_popup_count", popup_views_count, {expires: date});
      }

    }
  };
})(jQuery, Drupal, drupalSettings);
