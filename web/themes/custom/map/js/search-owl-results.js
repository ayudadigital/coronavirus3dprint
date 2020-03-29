(function ($) {
    Drupal.behaviors.search_map = {
        attach: function (context, settings) {

            //load map once time
            $('.view-content.owl-carousel').once('loadded-owl').each(function(){
                load_carousel();
            });

            function load_carousel(){
                var owl = $('.view-content.owl-carousel .views-infinite-scroll-content-wrapper');
                //owl settings
                owl.owlCarousel({
                    // center: true,
                    items:2,
                    loop:false,
                    responsiveClass:true,
                    nav:true,
                    responsive:{
                        0:{
                            items:1
                        },
                        480:{
                            items:2
                        },
                        768:{
                            items:3
                        },
                        992:{
                            items:4
                        },
                        1200:{
                            items:5
                        }
                    }
                });

                //mousewheel support
                owl.on('mousewheel', '.owl-stage', function (e) {
                    if (e.deltaY>0) {
                        owl.trigger('next.owl');
                    } else {
                        owl.trigger('prev.owl');
                    }
                    e.preventDefault();
                });

                owl.on('initialized.owl.carousel changed.owl.carousel refreshed.owl.carousel', function (e) {
                    if (!e.namespace) return;
                    var carousel = e.relatedTarget,
                        current = carousel.current();
                    if (current === carousel.maximum()) {
                        //get next page
                        $('.view-content.owl-carousel').closest('.view').find('.pager__item a.button').click();
                    }
                });
            }

            //update list carousel
            var $views_rows = $('.view-content.owl-carousel .views-infinite-scroll-content-wrapper > .views-row');
            var owl = $('.view-content.owl-carousel .views-infinite-scroll-content-wrapper');
            $views_rows.each(function( index ) {
                // appends an item to the end
                owl.owlCarousel('add', $( this )).owlCarousel('update');
                owl.trigger('refresh.owl.carousel');
            });
        }
    };
})(jQuery);
