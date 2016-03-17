;(function($, window, undefined)
{
  var main_min_height = mobile_callback = about_slider = product_header_fix = '';

  $(document).ready(function()
  {
    var nav = $('#js-main-nav');

    mobile_callback = function(w, el, func, alt)
    {
      if( $(window).width() <= w )
      {
        typeof func == 'function' && el[0] != undefined ? func(el) : '' ;
      }
      else
      {
        typeof alt == 'function' && el[0] != undefined ? alt(el) : '' ;
      }
    }

    // MOBILE CALLBACK FUNCTION CALL TO POSITION PRODUCT HEADER CORRECTLY, HACKY, I KNOW.
    mobile_callback( 1024, $('[data-mobile-media-gallery]'),
    function( el ){
      el.css('padding-top', $('[data-product-header]').height() + 25);
    },
    function( el ){
      el.attr('style','');
    });

    $('[data-body-wrap]').addClass('active');


    // RESIZABLE CATEGORY GRID
    if( $('#js-resizable-grid')[0] != null )
    {
      var first = $('#js-resizable-grid li:first-child'),
          width = first.width();

      $('#js-resizable-grid').find('a').each(function()
      {
        $(this).height(width*.85);
      })
    }



    // UTILITY SEARCH BOX
    $(document).on('click', '.header #js-utility-search', function(e)
    {
      e.preventDefault();
      $(this).addClass('disabled');
      $('#js-search-box').addClass('active').find('input').focus();
    })

    $(document).on('click', '#js-utility-mobile-search', function(e)
    {
      e.preventDefault();

      $(this).find('i').each(function()
      {
        if( $(this).hasClass('active') )
        {
          $(this).removeClass('active').addClass('inactive');
        } else {
          $(this).removeClass('inactive').addClass('active');
        }
      })

      $('[data-header]').toggleClass('add-search');
      $('#js-search-box').toggleClass('mobile-active');
    })

    $(document).on('focusout','#js-search-box',function()
    {
      $('#js-utility-search').removeClass('disabled');
      $('#js-search-box').removeClass('active');
    })


    $('[data-toggle-action]').click(function(e)
    {
      e.preventDefault();
      var target = $(this).data('toggle-id');

      if( $('#'+target)[0] != null )
      {
        $('#'+target).slideToggle();
      }

    })


    // ABOUT US SLIDERS
    if( $('[data-client-slider]')[0] != null )
    {
      var slickOptions = {
        dots: false,
        infinite: true,
        speed: 300,
        centerMode: false,
        variableWidth: true,
        arrows:true,
        lazyLoad:true,
        slidesToShow:1
      };

      $('[data-client-slider]').each(function()
      {
        imagesLoaded( $(this), function()
        {
          $('[data-client-slider]').slick(slickOptions)
        })
      })
    }


    // CATALOG CATEGORY FILTERS
    var filters = {
      filter_parent: '',
      filter_dropdown: '',
      exists:function()
      {
        var exists = false;

        if( this.filter_parent[0] != undefined && this.filter_dropdown[0] != undefined )
        {
          exists = true;
        }

        return exists;
      },
      set_data:function( el )
      {
        if( el[0] != undefined )
        {
          this.filter_parent = el.closest('[data-filter-parent]');
          this.filter_dropdown = el.closest('[data-filter-parent]').find('[data-filter-options]')
        }
      },
      show_filter_options:function()
      {
        if( !this.filter_parent.hasClass('active-filter') )
        {
          this.close_open_filters();
        }
        this.filter_parent.toggleClass('active-filter');
      },
      close_open_filters:function()
      {
        $('[data-filter-parent]').each(function()
        {
          $(this).removeClass('active-filter')
        })
      }
    }

    $('[data-filter-dropdown]').click( function(e)
    {
      e.preventDefault();
      filters.set_data( $(this) );

      if( filters.exists() )
      {
        filters.show_filter_options();
      }
    })

    // MOBILE CATALOG FILTERS & NAV MENUS
    $(document).on('click', '[data-list-toggle]', function(e)
    {
      e.preventDefault();
      $(this).toggleClass('active')
      $(this).closest('[data-filter-list]').find('ul').slideToggle(300, function()
      {
        $(this).toggleClass('active');
      })
    })

    $(document).on('click', '[data-toggle-sort]', function(e)
    {
      e.preventDefault();
      $(this).toggleClass('active');
      $(this).closest('li').find('.sort-by').slideToggle(300, function()
      {
        $(this).toggleClass('active')
      })
    })

    $(document).on('click', '[data-account-links]', function(e)
    {
      e.preventDefault();
      $(this).toggleClass('active');
      $(this).closest('nav').find('ul').slideToggle(300, function()
      {
        $(this).toggleClass('active')
      })
    })

    $(document).on('click', '[data-mobile-dropdown]', function(e)
    {
      e.preventDefault();
      $(this).toggleClass('active');
      $(this).closest('[data-mobile-dropdown-parent]').find('[data-mobile-toggle-panel]').slideToggle(200, function(e)
      {
        $(this).toggleClass('active');
      })
    })

    // FIRE NAV DROP-DOWN
  	nav.find("li.parent").each(function()
  	{
  		if ( $(this).find(".sub-menu").length > 0 )
  		{
  		  $(this).mouseenter(function()
  		  {
  			  $(this).find(" > .sub-menu").stop(true, true).show();
  		  });
  		  $(this).mouseleave(function()
  		  {
  			  $(this).find(" > .sub-menu").stop(true, true).hide();
  		  });
  		}

    });

    // MOBILE NAVIGATION TRIGGERS
    var nav_tools = {
      body:$('body'),
      body_wrap: $('[data-body-wrap]'),
      nav_wrap: $('[data-mobile-nav-wrap]'),
      trigger: $('#js-mobile-nav-trigger'),
      show_nav:function( func)
      {
        this.body_wrap.addClass('open');
        this.nav_wrap.addClass('open');
        typeof b == 'function' ? b() : '';
      },
      hide_nav:function(b)
      {
        this.body_wrap.removeClass('open');
        this.nav_wrap.removeClass('open');
        typeof b == 'function' ? b() : '';
      },
      switch_icon:function()
      {
        this.trigger.find('span').each(function()
        {
          if( $(this).hasClass('active') )
          {
            $(this).removeClass('active').addClass('inactive');
          } else {
            $(this).removeClass('inactive').addClass('active');
          }
        })
      },
      lock_body:function(b)
      {
        this.body.addClass('lock');
        typeof b == 'function' ? b() : '';
      },
      unlock_body:function(b)
      {
        this.body.removeClass('lock');
        typeof b == 'function' ? b() : '';
      },
      get_nav_html:function(a)
      {
        return a.html();
      },
      clear_html:function(a)
      {
        a.html('');
      },
      fill_nav:function(e, c)
      {
        $(e)[0] != null ? e.html('<nav>' + c + '</nav>') : '' ;

        nav_tools.nav_wrap.find("li.parent").each(function()
      	{
        	  $(this).removeClass('active');

        		if( $(this).find(".sub-menu ul").length > 0 )
        		{
        		  $(this).mouseenter(function()
        		  {
          		  $(this).addClass('active-parent');
        			  $(this).find(" > .sub-menu ul").stop(true, true).slideDown();
        		  });
        		  $(this).mouseleave(function()
        		  {
          		  $(this).removeClass('active-parent');
        			  $(this).find(" > .sub-menu ul").stop(true, true).delay(300).slideUp();
        		  });
        		}

        });
      }
    }

    $(nav_tools.trigger).click( function(e)
    {
      e.preventDefault();
      if( !nav_tools.body.hasClass('lock') )
      {
        nav_tools.lock_body(function(){
          nav_tools.fill_nav( nav_tools.nav_wrap, nav_tools.get_nav_html(nav) );
          nav_tools.switch_icon();
          nav_tools.show_nav();
        });
      }
      else
      {
        nav_tools.switch_icon();
        nav_tools.hide_nav(function(){
          nav_tools.unlock_body( function(){
            nav_tools.clear_html( nav_tools.nav_wrap );
          });
        });
      }
    })

    var newsletter = {
      form:'',
      method:'',
      url:'',
      has_data:false,
      set_data:function( el )
      {
        if( el[0] != null )
        {
          this.form = el;
          this.method = el.data('method');
          this.url = el.data('url');
          this.form_errors.error_box = el.closest('[data-popup]').find('[data-signup-error]')
          this.form_errors.error_input = el.find('input')

          this.has_data = true;
        }
      },
      get_form_data:function()
      {
        return this.form.serialize()
      },
      form_errors:{
        error_box:'',
        error_input:'',
        clear_error:function()
        {
          this.error_box.attr('class', '');
          this.error_box.html('');
        },
        fire_error:function( msg_class, callback )
        {
          this.error_box.addClass('active '+ msg_class);

          if( msg_class == 'error' )
          {
           this.error_box.html(' <small class="'+msg_class+'">There was an error. Please try again.</small>');
          }
          else
          {
            this.error_box.html(' <small class="'+msg_class+'">Success!</small>');
          }

          typeof callback == 'function' ? callback() : '';
        }
      }
    }

    $('[data-loader]')[0] != undefined ? $('[data-loader]').hide() : '';


    // NEWSLETTER AJAX SIGNUP
  	$('[data-newsletter-signup]').submit(function(e)
    {
      e.preventDefault();
      newsletter.set_data( $(this) );
      newsletter.form_errors.clear_error();

      if( newsletter.form.find('input[type="email"]').val().length > 0 )
      {
        $('[data-loader]').show();
        setTimeout(function()
        {
          $.ajax({
            url:newsletter.url,
            method: newsletter.method,
            data:newsletter.get_form_data(),
            dataType: "json",
            success:function( data )
            {
              if( data.status == 'error' )
              {
                newsletter.form_errors.fire_error( data.status )
              }
              else
              {
                newsletter.form_errors.fire_error( 'success', function()
                {
                  setTimeout( function()
                  {
                    $.cookie('newsletter-signup','visited');
                    popup.hide_popup();
                  }, 2000)
                })

              }
            },
            error:function( data )
            {
              if( data )
              {
                newsletter.form_errors.fire_error( data.status )
              }
            }
          })
        }, 400)

      }
      else
      {
        newsletter.form_errors.fire_error( 'error' )
      }

    });

  });


  $(window).on('resize orientationchange', function()
  {

    if( $('.fb-comments iframe')[0] != null )
    {
      resizeFacebookComments();
    }

    // RESIZABLE CATEGORY GRID - ON RESIZE
    var first = $('#js-resizable-grid li:first-child'),
        width = first.width();

    $('#js-resizable-grid').find('a').each(function()
    {
      $(this).height(width*.85);
    })

    // SET TIMEOUT TO ADD PADDING TO PRODUCT HEADER IN MOBILE
    setTimeout(function()
    {
      mobile_callback( 1024, $('[data-mobile-media-gallery]'),
      function( el ){
        el.css('padding-top', $('[data-product-header]').height() + 25);
      },
      function( el ){
        el.attr('style','');
      });

    }, 300)


    function resizeFacebookComments()
    {
      var src = $('.fb-comments iframe').attr('src').split('width='),
          width = $(".fb-comments").parent().width();

      $('.fb-comments iframe').attr('src', src[0] + 'width=' + width);
    }


  })




})($j, window)