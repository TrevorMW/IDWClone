;(function($,window, undefined)
{
  var a = conditional_mobile = remove_padding = '';

  $(document).ready(function()
  {
    a = $('[data-timeline-container]');

    conditional_mobile = function(w, func)
    {
      setTimeout(function()
      {
        var result = w > 800;
        typeof func == 'function' ? func(result) : '' ;
      }, 200)
    }


    add_padding = function( r )
    {
      if( r )
      {
        a.find('[data-timeline-block]').each(function()
        {
          var b = $(this).find('[data-push-content]'),
              c = $(this).find('.timeline-block-intro h2');

          if( c.length > 0 && b != undefined )
          {
            b.css('padding-top', $(c).height() + 75 );
          }
        })
      }
      else
      {
        remove_padding();
      }
    }


    remove_padding = function()
    {
      a.find('[data-push-content]').each(function()
      {
        $(this).attr('style', '');
      });
    }


    conditional_mobile( $(window).width(), function( result )
    {
      add_padding(result);
    })

  });


  $(window).resize(function()
  {
    conditional_mobile( $(window).width(), function( result )
    {
      add_padding(result);
    })
  })


})($j, window);