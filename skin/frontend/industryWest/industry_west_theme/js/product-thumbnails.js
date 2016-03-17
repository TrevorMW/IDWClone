;(function($, window, undefined)
{
  $(document).ready(function()
  {
    slick_options = {
      dots: false,
      lazyLoad: 'ondemand',
      infinite: true,
      speed: 300,
      slidesToShow: 5,
      slidesToScroll: 1,
      responsive: [
        {
          breakpoint: 2000,
          settings: {
            slidesToShow: 5,
          }
        },
        {
          breakpoint: 1280,
          settings: {
            slidesToShow: 5,
          }
        },
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 4,
          }
        },
        {
          breakpoint: 800,
          settings: {
            slidesToShow: 4,
          }
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 3,
          }
        }
      ]
    }

    if( $('[data-product-thumbs]')[0] != null )
    {
      slick = $('[data-product-thumbs]'),
      product_type = $('[data-product-thumbs]').data('product-type');

      imagesLoaded(slick, slick.slick(slick_options))

      if( product_type == 'configurable' )
      {
        slick.slick('slickFilter', '.configurable')
      }
    }

  })

})($j, window);