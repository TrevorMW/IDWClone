;(function( $, window, undefined )
{

  $(document).ready(function()
  {
    var tabs = {
      tab_container: $('[data-tab-container]'),
      tab_handles: $('[data-tab-handles]'),
      tab_contents: $('[data-tab-contents]'),
      set_active:function( link ) { link.addClass('active'); },
      hide_tabs:function(func) {
        this.tab_contents.find('li').each( function() { $(this).removeClass('active'); });
        typeof func == 'function' ? func() : '';
      },
      show_tab:function(a) { $('[data-tab-content='+ a + ']').addClass('active') },
      remove_active:function() { this.tab_container.find('.active').removeClass('active'); },
    }

    var accordion = {
      accordion_container: $('[data-tab-container]'),
      show_content:function( el ){
        el.addClass('active');
        var id = el.data('accordion');
        var html = $('[data-tab-content='+ id +'] [data-tab-html]');
        html.is(':visible') ? '' : html.slideDown() ;
      },
      reset_drawers:function(){
        $('[data-accordion].active').removeClass('active');
        this.accordion_container.find('[data-tab-html]').each(function()
        {
          $(this).slideUp();
        })
      }
    }


    // TABINATION ON CLICK
    $('[data-tab-handle]').click( function(e)
    {
      e.preventDefault();
      tabs.hide_tabs( tabs.remove_active() );
      tabs.set_active( $(this) );
      tabs.show_tab( $(this).data('tab-handle') )
    });


    // ACCORDI-YONS ON CLICK
    $('[data-accordion]').click( function(e)
    {
      e.preventDefault();
      accordion.reset_drawers();
      accordion.show_content( $(this) )
    });

  });

})( $j, window);