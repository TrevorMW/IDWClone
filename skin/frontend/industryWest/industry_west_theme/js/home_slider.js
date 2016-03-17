;(function( $, window, undefined)
{
  var home_slider = home_slider_options = '';

  $(document).ready(function()
  {
    home_slider_options = {
      mode:'horizontal',
      auto:true,
      pause:7000,
      pager:false,
      controls:true,
      captions:false,
    }

    if( $('#js-slideshow')[0] != null )
    {
      home_slider = $('#js-slideshow').bxSlider(home_slider_options)
    }

  })

  $(window).on('resize orientationchange', function()
  {

    if( home_slider != null )
    {
      home_slider.reloadSlider(home_slider_options);
    }

  })

})($j, window)