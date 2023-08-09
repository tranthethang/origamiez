jQuery(document).ready(function($){

function rtaJS() {};

rtaJS.prototype = {
  //offset: 0,
  process: false,
  is_interrupted_process: false, // was the process killed by reload earlier?
  in_process: false, // processing, in general. can be set via server
  in_ajax: false,  // currently waiting for an ajax response.
  is_stopped: false, // is_stopped: paused, when stopped permanently it also removes server-queue.
  is_saved: true,
  is_debug: false,  // use sparingly
  status: []
}


rtaJS.prototype.init = function()
{
  if (rta_data.is_debug == 1)
  {
      this.is_debug= true;
      console.log(rta_data); // let's go.
  }

  this.setStatusCodes();
  this.initProcess();
  this.checkSubmitReady();


  $('.select, .deselect').on('click', $.proxy(this.selectAll, this));
  $(document).on('change','input, select', $.proxy(this.checkSubmitReady, this));

  // the start of it all.
  //$(document).on("click", '.rta_regenerate', $.proxy(this.processInit, this));
  $('#rtaform_process').on('submit', $.proxy(this.startProcess, this));

  // save image sizes when updated
  $(document).on('change', '.table.imagesizes input, .table.imagesizes select', $.proxy(this.image_size_changed, this));
  $(document).on('click', 'button[name="save_settings"]', $.proxy(this.image_size_changed, this));
  $(document).on('click', '.table.imagesizes .btn_remove_row', $.proxy(this.remove_image_size_row, this));
  $(document).on('click', '#btn_add_image_size', $.proxy(this.add_image_size_row, this));
  $(document).on('click', '.stop-process', $.proxy(this.stopProcess,this));
  $(document).on('click', '.pause-process', $.proxy(this.pauseProcess,this));
  $(document).on('click', '.rta_success_box .modal-close', $.proxy(function(e){
          this.togglePanel('success', false);
  }, this));

  // Warnings, errors and such.
  $(document).on('change', 'input[name="del_associated_thumbs"]', $.proxy(this.toggleDeleteItems,this));
  $(document).on('change', 'input[name^="regenerate_sizes"]', $.proxy(this.toggleDeleteItems,this));
  this.toggleDeleteItems();

  $(document).on('change', '.rta-settings-wrap input, .rta-settings-wrap select', $.proxy(this.show_save_indicator, this) );
  $(document).on('change', 'input[name^="regenerate_sizes"]', $.proxy(this.checkOptionsVisible, this));

  $('.toggle-window').on('click', $.proxy(this.toggleWindow, this));

}

/** Status codes need to sync with Ajax Controller */
rtaJS.prototype.setStatusCodes = function()
{
  //  this.status[
}

// function to check if admin screen can start a new job.
rtaJS.prototype.checkSubmitReady = function()
{
  processReady = true;

  inputs = $('input[name^="regenerate_sizes"]:checked');
/*  if (inputs.length == 0)
    processReady = false; */

  if (this.in_process || ! this.is_saved)
    processReady = false;

  if (processReady)
  {
    $('button.rta_regenerate').removeClass('disabled');
    $('button.rta_regenerate').prop('disabled', false);
  }
  else {
    $('button.rta_regenerate').addClass('disabled');
    $('button.rta_regenerate').prop('disabled', true);
  }

  if (this.is_saved)
  {
    $('button[name="save_settings"]').prop('disabled', true);
    $('button[name="save_settings"]').addClass('disabled');
    $('.save_note').addClass('rta_hidden');

  }
  else {
    $('button[name="save_settings"]').prop('disabled', false);
    $('button[name="save_settings"]').removeClass('disabled');
    $('.save_note').removeClass('rta_hidden');
  }


}

// Function to check if there was a interrupted process via rta_data.
rtaJS.prototype.initProcess = function()
{
  process = rta_data.process;
  if (this.is_debug)
    console.log(process);

  this.process = process;

  if (process.running)
    this.in_process = process.running;

  /*if (process.current)
      this.offset = process.current;

  if (process.total)
    this.total = process.total;
 */

   if (process.running || process.preparing)
   {
      this.updateProgress();
      this.resumeProcess();
  }

}

rtaJS.prototype.selectAll = function(e)
{
   var action = $(e.target).data('action');
   var target = $(e.target).data('target');

   if (action == 'select')
      checked = true;
   else {
     checked = false;
   }

   $('input[name^="' + target + '"]').prop('checked', checked).trigger('change');
}

// starts the process.
rtaJS.prototype.startProcess = function (e)
{
  e.preventDefault();

  this.resetPanels();
  this.togglePanel('main', true);
  this.togglePanel('loading', true);

  $([document.documentElement, document.body]).animate({
       scrollTop: $("section.regenerate").offset().top
   }, 1000);


  var status = new Object;
  status.id = -1;
  status.message = rta_data.strings.status_start;
  status.error = true;
  this.add_status([status]);

  this.in_process = true;
  this.is_stopped = false;
  this.checkSubmitReady();

  var self = this;
  var form = $('#rtaform_process');

  $.ajax({
      type: 'POST',
      dataType: 'json',
      url: rta_data.ajaxurl,
      data: {
              nonce: rta_data.nonce_generate,
              action: 'rta_start_process',
              genform: form.serialize(),
       },
      success: function (response) {

            if (response.status)
            {
              self.add_status(response.status);
            }
            self.process = response;
            self.updateProgress();
            self.doProcess();
      },
      error: function(xhr, text, error)
      {
        var status = new Object;
        if (this.is_debug)
          console.log(xhr); // log response on error.

        status.id = -1;
        status.message = rta_data.strings.status_fatal;
        status.error = true;
        self.add_status([status]);
        self.finishProcess();
      }
  });
}

// function was interrupted, but will continue now; draw panels.
rtaJS.prototype.resumeProcess = function()
{
  this.togglePanel('main', true);
  //this.resetPanels();
  //this.togglePanel('main', true);
  this.togglePanel('loading', true);
  this.togglePanel('progress', true);

  var status = new Object;
  status.id = -1;
  status.message = rta_data.strings.status_resume;
  status.error = true;
  this.add_status([status]);

  $([document.documentElement, document.body]).animate({
       scrollTop: $("section.regenerate").offset().top
   }, 1000);

  this.processStoppable();
  this.togglePanel('loading', false);
  this.pauseProcess();
//  this.doProcess();

}

// function for getting the next image in line.
rtaJS.prototype.doProcess = function()
{

    if (this.is_stopped)
      return; // escape if process has been stopped.
    //offset = this.offset;
    //total = this.total;

    this.in_process = true;
    this.in_ajax = true;
    this.checkSubmitReady();

    this.togglePanel('progress', true);
    this.processStoppable();

    var self = this;

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: rta_data.ajaxurl,
        data: {
                nonce: rta_data.nonce_doprocess,
                action: 'rta_do_process',
                type: 'submit',
            //    offset:offset,
                //genform: JSON.stringify(form),
        },
        success: function (response) {
            self.in_ajax = false;
            self.togglePanel('loading', false);

            if (typeof response.items !== 'undefined') // return is a process var..
            {
              self.process = response;
              self.updateProgress();
            }

            if (response.status)
            {
              self.add_status(response.status);
            }
            if( response.running || response.preparing ) {

                if (! self.is_stopped)
                {
                //  self.offset = response.current;
                  setTimeout(function(){ self.doProcess(); },500);
                }
                else
                {
                    self.in_process = false;
                    self.togglePanel('paused', true);
                    self.togglePanel('pausing', false);
                    $('.button.pause-process').prop('disabled', false);
                }
            }else{
                self.finishProcess(); // done, or so.
            }

        },
        error: function (response) {

          self.togglePanel('loading', false);
          var status = new Object;
          status.id = -1;
          status.message = response.status + ' ' + response.statusText + ' :: ';
          status.error = true;
          self.add_status([status]);

          setTimeout(function(){ self.doProcess(); },1000);

        },
    });


        //this.show_buttons();
    //    this.finishProcess();


}

// check if progress is stoppable  and activate stop process button, or not.
rtaJS.prototype.processStoppable = function()
{
   var stoppable = false;

    if (this.in_process)
        stoppable = true;

    if (stoppable)
    {
      $('.stop-process').prop('disabled', false);
      $('.pause-process').prop('disabled', false);
    }
    else
    {
      $('.stop-process').prop('disabled', true);
      $('.pause-process').prop('disabled', true);
    }

}

  rtaJS.prototype.finishProcess = function()
  {
    this.in_process = false;
    this.is_interrupted_process = false;

    this.togglePanel('success', true);
    this.togglePanel('paused', false);
    this.togglePanel('pausing', false);
    this.processStoppable();
    //this.toggleShortPixelNotice(true);
  //  $('.stop-process').addClass('rta_hidden');
    var status = new Object;
    status.id = -1;
    status.message = rta_data.strings.status_finish;
    status.error = true;
    this.add_status([status]);

    this.checkSubmitReady();
  }

  // This functions as a toggle
  rtaJS.prototype.pauseProcess = function(e)
  {
      // Disable button pending action.
      $('.button.pause-process').prop('disabled', true);

      if (this.is_stopped == false)
      {
        this.is_stopped = true;
        $('.pause-process .pause').css('display', 'none');
        $('.pause-process .resume').css('display', 'inline');

        if (this.in_ajax == false)
        {
          this.togglePanel('paused', true);
          $('.button.pause-process').prop('disabled', false);
        }
        else
        {
          this.togglePanel('pausing', true);
        }

      }
      else if (this.is_stopped == true)
      {
        this.is_stopped = false;
        $('.pause-process .pause').css('display', 'inline');
        $('.pause-process .resume').css('display', 'none');
        var self = this;
        this.togglePanel('pausing', false);
        this.togglePanel('paused', false);
        this.togglePanel('loading', true);

        setTimeout(function(){
            $('.button.pause-process').prop('disabled', false);
            self.doProcess();
        },500);
      }

  }

  rtaJS.prototype.stopProcess = function()
  {
    if (window.confirm(rta_data.strings.confirm_stop))
    {
      this.is_stopped = true;

      this.togglePanel('loading', true);
      var self = this;

      $.ajax({
          type: 'POST',
          dataType: 'json',
          url: rta_data.ajaxurl,
          data: {
                  nonce: rta_data.nonce_generate,
                  action: 'rta_stop_process',
                  type: 'submit',
          },
          success: function (response) {
              if (response.status)
              {
                self.add_status(response.status);
              }
              self.process = false;
              self.finishProcess();
              self.togglePanel('loading', false);

          }

      });

    }
  }

    rtaJS.prototype.updateProgress = function() {

        if (! this.process)
          return;

        var items = parseInt(this.process.items);
        var done = parseInt(this.process.done);
        var total = (items + done);
        var errors = this.process.errors;
        //var offset = parseInt(this.offset);
        //var total = parseInt(this.total);

        if (done == 0 && total > 0)
          percentage_done = 0;
        else if (total > 0)
          percentage_done = Math.round( (done/total) * 100);
        else
          percentage_done = 100;

        var total_circle = 289.027;
        if(percentage_done>0) {
            total_circle = Math.round(total_circle-(total_circle*percentage_done/100));
        }
        $(".CircularProgressbar-path").css("stroke-dashoffset",total_circle+"px");
        $(".CircularProgressbar-text").html(percentage_done+"%");

        $('.progress-count .current').text(done);
        $('.progress-count .total').text(total);

    }

    rtaJS.prototype.togglePanel = function(name, show)
    {
      var panel;

      switch(name)
      {
        case 'main':
          panel = 'section.regenerate';
        break;
        case 'loading':
          panel = ".rta_wait_loader";
        break;
        case 'paused':
          panel = ".rta_wait_paused";
        break;
        case 'pausing':
         panel = '.rta_wait_pausing';
        break
        case 'progress':
          panel = '.rta_progress_view';
        break;
        case 'thumbnail':
          panel = '.rta_thumbnail_view';
        break;
        case 'success':
          panel = '.rta_success_box';
        break;
        case 'notices':
          panel = '.rta_notices';
        break;
      }

    /*  var is_visible = $(panel).is(':visible');
      if (is_visible)
      {
        // zero opacity is considered visible by Jquery.
        if ($(panel).css('opacity') == 0)
          is_visible = false;
      } */

      if (show)
      {
        if ($(panel).hasClass('rta_hidden'))
        {
          $(panel).slideDown();
        }
        else {
          $(panel).css('opacity', 1);
        }

        $(panel).removeClass('rta_panel_off');

      }
      else if (! show)
      {
        if ($(panel).hasClass('rta_hidden'))
          $(panel).hide();
        else
          $(panel).css('opacity', 0);

        $(panel).addClass('rta_panel_off');
      }

    }

    rtaJS.prototype.resetPanels = function()
    {
      this.togglePanel('loading', false);
      this.togglePanel('paused', false);
      this.togglePanel('pausing', false);
      this.togglePanel('progress', false);
      this.togglePanel('thumbnail', false);
      this.togglePanel('success', false);
      this.togglePanel('notices', false);

      $('.rta_notices .statuslist li').remove(); // empty previous statuses

      // Flick back the pause / resume thing.
      $('.pause-process .pause').css('display', 'inline');
      $('.pause-process .resume').css('display', 'none');

    }

    rtaJS.prototype.add_status = function(status) {
      //  var $ = jQuery;
        this.togglePanel('notices', true);

        if(status!="") {
            var html = '';

            for(var i=0;i < status.length;i++) {
                var item = status[i];
                var item_class = '';
                if (item.error)
                  item_class = 'error';
                else
                  item_class = '';

                  // @todo Move these to named constants.
                if(item.status == 1) // status 1 is successfully regenerated  thumbnail with URL in message.
                {
                  this.showThumb(item.message);
                  continue;
                }


                html = html+'<li class="list-group-item ' + item_class + '">'+ item.message +'</li>';
            }
            $(".rta_status_box ul.statuslist").append(html);

        }
    }

    rtaJS.prototype.showThumb = function(imgUrl)
    {
      this.togglePanel('thumbnail', true);
      $(".rta_progress .images img").attr("src",imgUrl);
    }


  /*  rtaJS.prototype.hide_progress = function()  {
        var $ = jQuery;
        var total_circle = 289.027;
        $(".rta_progress .images img").attr("src","");
        $(".CircularProgressbar-path").css("stroke-dashoffset",total_circle+"px");
        $(".rta_progress .images").css('opacity', 0);
        $(".rta_progress").slideUp();
        $(".CircularProgressbar-text").html("0%");
    }
 */

    rtaJS.prototype.add_image_size_row = function() {

        var $ = jQuery;
        var container = $('.table.imagesizes'); // $("#rta_add_image_size_container");
        var uniqueId = Math.random().toString(36).substring(2) + (new Date()).getTime().toString(36);

        var row = $('.row.proto').clone();
        $(row).attr('id', uniqueId);
        $(row).removeClass('proto');
        container.append(row); // row.css('display', 'flex')

        container.find('.header').removeClass('rta_hidden');
    }

    rtaJS.prototype.image_size_changed = function(e) {
        e.preventDefault();
        var rowid = $(e.target).parents('.row').attr('id');
        this.update_thumb_name(rowid);
        this.save_image_sizes();
    }

    rtaJS.prototype.update_thumb_name = function(rowid) {
        if($("#"+rowid).length) {
            var old_name = $("#"+rowid+" .image_sizes_name").val();
            var name = "rta_thumb";
            var width = $("#"+rowid+" .image_sizes_width").val();
            var height = $("#"+rowid+" .image_sizes_height").val();
            var cropping = $("#"+rowid+" .image_sizes_cropping").val();
            var pname = $("#"+rowid+" .image_sizes_pname").val();

            if (width <= 0) width = '';  // don't include zero values here.
            if (height <= 0) height = '';
            var slug = (name+" "+cropping+" "+width+"x"+height).toLowerCase().replace(/ /g, '_');

            // update the image size selection so it keeps checked indexes.
            $('input[name^="regenerate_sizes"][value="' + old_name + '"]').val(slug);
            if (pname.length <= 0)
            {
              $('input[name^="regenerate_sizes"][value="' + old_name + '"]').text(slug);
            }
            $('input[name="keep_' + old_name + '"]').attr('name', 'keep_' + slug);



            $("#"+rowid+" .image_sizes_name").val(slug);
        }
    }

    rtaJS.prototype.save_image_sizes = function() {
        this.settings_doingsave_indicator(true);
        var action = 'rta_save_image_sizes';
        var the_nonce = rta_data.nonce_savesizes;

        var self = this;
        // proper request
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: rta_data.ajaxurl,
            data: {
                  action: action,
                  save_nonce: the_nonce,
                  saveform: $('#rta_settings_form').serialize(),
            },
            success: function (response) {
                if (! response.error)
                {
                  if (response.new_image_sizes)
                  {
                    $('.thumbnail_select .checkbox-list').fadeOut(80).html(response.new_image_sizes).fadeIn(80);
                    self.checkOptionsVisible();
                  }
                }
                self.is_saved = true;
                self.settings_doingsave_indicator(false);
                self.checkSubmitReady();
                self.toggleDeleteItems();
            }
        });
    }

    rtaJS.prototype.settings_doingsave_indicator = function (show)
    {
        if (show)
        {
            $('.form_controls .save_indicator').fadeIn(20);
        }
        else {
            $('.form_controls .save_indicator').fadeOut(100);
        }
    }

    rtaJS.prototype.show_save_indicator = function()
    {
        this.is_saved = false;
        this.checkSubmitReady();
    }

    rtaJS.prototype.remove_image_size_row = function(e) {
        var rowid = $(e.target).parents('.row').attr('id');

        if(confirm( rta_data.strings.confirm_delete )) {
            var intName = $('#' + rowid).find('.image_sizes_name').val();
            $('input[name^="regenerate_sizes"][value="' + intName + '"]').remove(); // remove the checkbox as well, otherwise this will remain saved.

            $("#"+rowid).remove();

            this.save_image_sizes();
        }
    }

    rtaJS.prototype.checkOptionsVisible = function()
    {
        $('input[name^="regenerate_sizes"]').each(function ()
        {
           if ($(this).is(':checked'))
           {
             $(this).parents('.item').find('.options').removeClass('hidden');
             var input = $(this).parents('.item').find('input[type="checkbox"]');

             if (typeof $(input).data('setbyuser') == 'undefined')
             {
                $(input).prop('checked', true);
                $(input).data('setbyuser', true);
              }
           }
           else {
             $(this).parents('.item').find('.options').addClass('hidden');
           }
        });
    }

    rtaJS.prototype.toggleDeleteItems = function()
    {
      $('.checkbox-list label').removeClass('warning-removal');
      $('.checkbox-list .icon-warning').remove();

      // remove elements added by this func.
      var target = $('input[name="del_associated_thumbs"]');
      if ($(target).is(':checked'))
      {
        var has_items = false;
        $('input[name^="regenerate_sizes"]').not(':checked').each(function()
        {
            //$(this).addClass('rta_hidden');
            $(this).parent('label').addClass('warning-removal');
            $(this).parent('label').find('input').before("<span class='dashicons dashicons-no icon-warning'></span>");
            has_items = true;
        });

        if (has_items)
        {
          $('#warn-delete-items').removeClass('rta_hidden');
        }
      }
      else {
          $('#warn-delete-items').addClass('rta_hidden');
      }
      //

    }

    rtaJS.prototype.toggleWindow = function(e)
    {
        var $target = $(e.target);
        if (! $target.hasClass('toggle-window'))
          $target = $(e.target).parents('.toggle-window');

        var $window = $('#' + $target.data('window'));
        if ($window.hasClass('window-up'))
        {
          $window.removeClass('window-up').addClass('window-down');
          $target.find('span.dashicons').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');

        }
        else {
          $window.removeClass('window-down').addClass('window-up');
          $target.find('span.dashicons').addClass('dashicons-arrow-down').removeClass('dashicons-arrow-up');
        }
    }

    window.rtaJS = new rtaJS();
    window.rtaJS.init();

}); // Jquery
