;(function( $, window, undefined )
{
  $(document).ready(function()
  {
    $(document).on('click', '[data-scroll-to]', function( e )
    {
    	e.preventDefault();

    	var hash = '#' + $(this).data('scroll-to-anchor'),
    	    offset = $(this).data('scroll-offset') || 0

    	if( hash.length > 1 && $(hash)[0] != null )
    	{
        $('html, body').animate({
  	      scrollTop: $(hash).offset().top - offset
  	    }, 400, 'linear');
      }

    });

  });

})( $j , window);