

// @todo: find a way to abstractise all these ajax requests...
jQuery(document).ready(function($){

  var pulsate = function(e, color){
        var finalColor = e.css('background-color').match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/),
            hex = function(x){ return ('0' + parseInt(x).toString(16)).slice(-2); };

        finalColor = '#' + hex(finalColor[1]) + hex(finalColor[2]) + hex(finalColor[3]);

        if(finalColor === '#000000')
          finalColor = '#fff';

        e.css('background-color', color).animate({backgroundColor: finalColor}, 700);
      },

      setupDependencies = function(e){

        // auto-grow text area
        $('textarea', e).css('overflow', 'hidden').bind('input focus blur keyup', function(){
          this.rows = this.value.split('\n').length;
        }).blur();

        // quick dependency processing (@todo: use my Form Dependecy script here...)
        $('[name="page_visibility"], [name="location"]', e).change(function(){

          var page = $('[name="page_visibility"]').val(),
              location = $('[name="location"]').val(),
              target = function(name, show){
                var e = $('[name="' + name + '"]', e).parents('.row-add');
                show ? e.removeClass('hidden').find('input:first').focus() : e.addClass('hidden');
              },

              posts = (location.indexOf('post') !== -1 || location.indexOf('teaser') !== -1) && page.indexOf('singular:') === -1 && (location.indexOf(':index') !== -1),
              comments = (location.indexOf('comment') !== -1) && page.indexOf('singular:') === 0 && (location.indexOf(':index') !== -1);

          target('index', posts || comments);
          target('action', (location === 'action'));
          target('shortcode', (location === 'shortcode'));

        }).change();

      };



  $('#new-ad').click(function(event){
    event.preventDefault();

    if(this.disabled)
      return;

    var control = $(this),
        table = control.parents('table'),
        loader = $('<div class="loader"></div>');

    $.ajax({
      url: ajaxurl,
      type: 'GET',
      data: ({
        action: 'ad_form',
        _ajax_nonce: $('#ad_form').val()
      }),

      beforeSend: function(){
        control.attr('disabled','disabled').removeClass('error').addClass('loading');
        control.after(loader);
      },
      error: function(){
        control.addClass('error');
        loader.remove();
      },

      success: function(response){

        var form = $(response).hide();

        $('tbody', table).append('<tr class="ad-editor" valign="top"><td scope="row" colspan="5"></td></tr>');
        $('tbody tr:last td', table).append(form);

        form.animate({
          opacity: 'show',
          height: 'show',
          marginTop: 'show',
          marginBottom: 'show',
          paddingTop: 'show',
          paddingBottom: 'show'
        }, 150,  function(){
          loader.remove();
          control.removeAttr('disabled').removeClass('loading');
          setupDependencies(form);
        });
      }
    });

  });


  // save/cancel/remove actions
  $('#ads')
    .delegate('.show-log', 'click', function(){

       $.ajax({
          url: ajaxurl,
          type: 'GET',
          context: this,
          data: ({
            action: 'get_ad_stats',
            id: $(this).data('id')
          }),

          error: function(){
            alert('An error occured while processing your request. Try again')
          },

          success: function(response){
            if(response){
              response = $(response).hide();
              $('#ad-stats').remove();

              var overlay = $('<div id="ad-stats-overlay"></div>').css('opacity', 0);


              overlay.appendTo('body').after(response).animate({'opacity': 0.5}, 150);

              response
                .css('left', (($(window).width() - response.outerWidth()) / 2) + $(window).scrollLeft() + 'px')
                .css('top', (($(window).height() - response.outerHeight()) / 2) + $(window).scrollTop() + 'px')
                .animate({
                  opacity: 'show',
                  top: '-=100'
                }, 300)
                .find('a.close').click(function(){
                  response.animate({
                    opacity: 'hide',
                    top: '+=100'
                  }, 300, function(){
                    response.remove();
                    overlay.fadeOut(150);
                  });

                });
            }
          }
        });

    })
    .delegate('textarea[name="html"]', 'change', function(){
      var auto_scan = parseInt($(this).parents('form').find('input[name="auto_scan"]').val());

      if((auto_scan !== 1) || $(this).parents('.type-options').find('p.notice').length)
        return;

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        context: this,
        data: ({
          action: 'scan_type_html',
          html: $(this).val(),
          type: $(this).parents('form').find('[name="type"]').val()
        }),

        error: function(){
          //control.addClass('error');
          //controls.show();
          //loader.remove();
        },

        success: function(response){
          if(response){
            response = $(response);
            $(this).parents('.type-options').after(response);
          }
        }
      });

    })
    .delegate('a.auto-change-type', 'click', function(event){
      event.preventDefault();
      var new_type = $(this).data('type');
      $(this).parents('form').find('select[name="type"]').val(new_type).change();
      $(this).parents('p').remove();
    })
    .delegate('a.ignore-type', 'click', function(event){
      event.preventDefault();
      $(this).parents('form').find('input[name="auto_scan"]').val('0');
      $(this).parents('p').remove();
    })
    .delegate('select[name="type"]', 'change', function(){
      var form = $(this).parents('form'),
          type_options = form.find('.type-options');

      $.ajax({
        url: ajaxurl,
        type: 'GET',
        context: this,
        data: ({
          action: 'change_ad_type',
          data: form.serialize(),
          type: $(this).val(),
          id: form.data('id')
        }),

        beforeSend: function(){
          //control.attr('disabled','disabled').removeClass('error').addClass('loading');
          //controls.hide();
          //controls.after(loader);
        },
        error: function(){
          //control.addClass('error');
          //controls.show();
          //loader.remove();
        },

        success: function(response){
          type_options.html($(response));
          setupDependencies(type_options);
        }
      });

    })
    .delegate('.quick-edit', 'click', function(){

      var loader = $('<div class="loader"></div>'),
          controls = $(this).parents('.controls');

      $.ajax({
        url: ajaxurl,
        type: 'GET',
        context: this,
        data: ({
          action: 'ad_form',
          id: $(this).data('id')
        }),

        beforeSend: function(){
          //control.attr('disabled','disabled').removeClass('error').addClass('loading');
          controls.hide();
          controls.after(loader);
        },
        error: function(){
          //control.addClass('error');
          //controls.show();
          loader.remove();
        },

        success: function(response){

          var form = $(response).hide(),
              old_html = $(this).parents('tr').clone().wrap('<p>').parent().html(),
              list = $('<tr class="ad-editor" valign="top"><td scope="row" colspan="5"></td></tr>').replaceAll($(this).parents('tr'));

          list.find('td').append(form);
          form.parents('tr').data('original_item', old_html);

          form.animate({
            opacity: 'show',
            height: 'show',
            marginTop: 'show',
            marginBottom: 'show',
            paddingTop: 'show',
            paddingBottom: 'show'
          }, 150, function(){

            setupDependencies(form);
          });
        }
      });
    })
    .delegate('.quick-enable, .quick-disable', 'click', function(){

      var loader = $('<div class="loader"></div>'),
          controls = $(this).parents('.controls');

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        context: this,
        data: ({
          action: 'process_ad',
          data: 'change_status',
          id: $(this).data('id')
        }),

        beforeSend: function(){
          controls.hide();
          controls.after(loader);
        },
        error: function(){
          loader.remove();
        },

        success: function(response){
          $(this).parents('tr').replaceWith(response);
        }
      });
    })
    .delegate('#cancel-edit', 'click', function(event){
      event.preventDefault();
      $(this).parents('tr').animate({
        opacity: 'hide',
        height: 'hide',
        marginTop: 'hide',
        marginBottom: 'hide',
        paddingTop: 'hide',
        paddingBottom: 'hide'
      }, 150, function(){

        var old_html = $(this).data('original_item');

        // restore old html if this was an edit
        if(old_html){
          old_html = $(old_html);
          old_html.find('.loader').remove();
          old_html.find('.controls').show();
          $(this).replaceWith(old_html);
          return;
        }

        $(this).remove();
      });
    })
    .delegate('#remove-ad, .quick-remove', 'click', function(event){
      event.preventDefault();

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        context: this,
        dataType: 'json',
        data: ({
          action: 'process_ad',
          data: 'remove',
          id: $(this).data('id')
        }),

        beforeSend: function(){
          $(this).parents('tr').addClass('to-be-removed');

          if($(this).is('input'))
            $(this).attr('disabled','disabled');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
          //console.log(XMLHttpRequest);
          //control.addClass('error');
        },

        success: function(response){
          if(response.removed){
            $(this).parents('tr').animate({
              opacity: 'hide',
              height: 'hide',
              marginTop: 'hide',
              marginBottom: 'hide',
              paddingTop: 'hide',
              paddingBottom: 'hide'
            }, 150, function(){
              $(this).remove();

            });
          }
        }
      });


    })
    .delegate('.quick-clone', 'click', function(event){
      event.preventDefault();

      var loader = $('<div class="loader"></div>'),
          controls = $(this).parents('.controls');

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        context: this,
        context: this,
        data: ({
          action: 'process_ad',
          data: 'clone',
          id: $(this).data('id')
        }),

        beforeSend: function(){
          controls.hide();
          controls.after(loader);
        },
        error: function(){
          loader.remove();
        },

        success: function(response){
          var new_row = $(response),
              last_row = $(this).parents('tbody').find('tr:last');

          if(!last_row.hasClass('alternate'))
            new_row.addClass('alternate');

          last_row.after(new_row);

          loader.remove();
          controls.removeAttr('style');
          pulsate(new_row, '#00CC00');
        }
      });

    })
    .delegate('#save-ad', 'click', function(event){
      event.preventDefault();

      var form = $(this).parents('form'),
          control = $(this);

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        context: this,
        dataType: 'json',
        data: ({
          action: 'process_ad',
          data: form.serialize(),
          _ajax_nonce: $('input.nonce', form).val()
        }),

        beforeSend: function(){
          control.attr('disabled','disabled');
          form.find('p.error').remove();
        },

        error: function(XMLHttpRequest, textStatus, errorThrown){
          console.log(XMLHttpRequest);
        },

        success: function(response){
          var html = $(response.html);

          if(response.error){
            form.find('.type-options').after(html);
            control.removeAttr('disabled');
            return;
          }

          form.parents('tr').replaceWith(html);
        }
      });

    });

});
