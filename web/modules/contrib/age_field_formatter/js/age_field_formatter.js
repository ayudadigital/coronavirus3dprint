(function (Drupal, $) {

  "use strict";

  Drupal.behaviors.ageFieldFormatter = {
    attach: function (context) {
      
      // On form change, show or hide the Date/time format field.
      $('.age-format-select').change(function () {

        // Conditionally show the Date/time format field.
        if ($(this).val() === 'age_only') {
          $(context).find('.date-format').parent().hide();
        }
        else {
          $(context).find('.date-format').parent().show();
        }
      });

      // Hide the Date/time format field on page load if we do not display
      // the date.
        if ($('.age-format-select option:selected').val() === 'age_only') {
          $(context).find('.date-format').parent().hide();
        }
    }
  };

})(Drupal, jQuery);
