;(function( $, window, undefined )
{

  $(document).ready(function()
  {
    var img_swap = {
      switchable_img:$('[data-switchable-img]'),
      swap_image:function(a)
      {
        var url = a.data('img-to-swap');

        if( url.length > 7 )
        {
          this.switchable_img.find('img').attr('src', url);
        }
      }
    }

    $(document).on('click','[data-switch-trigger]',function(e)
    {
      e.preventDefault();
      img_swap.swap_image( $(this) );
    });

  });

})( $j, window);