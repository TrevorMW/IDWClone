;(function($, window, undefined)
{
  /*var owl = options = '';

  $(document).ready(function()
  {
    options = {
      items: 4,
      itemsDesktop: [1000,3],
      itemsDesktopSmall: [800,2],
      itemsTablet:[600,1],
      navigation:true,
      pagination:false,
      lazyLoad : true,
      slideSpeed:150,
      paginationSpeed:150,
      navigationText:	["",""]
    }

    if( $('.featured-product-list')[0] != null )
    {
      owl = $('.featured-product-list');

      owl.owlCarousel(options);
    }


  })*/

})($j, window);



;(function($, window, undefined)
{
  $(document).ready(function()
  {
    slick_options = {
      dots: false,
      lazyLoad: 'ondemand',
      infinite: true,
      speed: 300,
      slidesToShow: 4,
      slidesToScroll: 1,
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 3,
          }
        },
        {
          breakpoint: 800,
          settings: {
            slidesToShow: 2,
          }
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 1,
          }
        }
      ]
    }

    if( $('[data-featured-product-list]')[0] != null )
    {
      slick = $('[data-featured-product-list]');

      imagesLoaded(slick, slick.slick(slick_options))
    }

  })

})($j, window);