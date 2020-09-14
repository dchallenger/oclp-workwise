$(document).ready(function(){
	 
	//expire session on browser close
    //handle if your mouse is over the document body,
    //if not your mouse is outside the document and if you click close button return message or do what you want
 //    var out = false;
 //    var is_click = false;
 //    $("body").mouseover(function(){
 //      out=false;
 //    }).mouseout(function(){
 //      out=true;
 //    });

	// $("body").mousedown(function(event) {
	// 	is_click=true;
	// });
	
	// $(document).bind('keypress', function(e) {
	// 	if (e.keyCode == 116){
	// 	  is_click = true;
	// 	}
	// });

	// // Attach the event click for all links in the page
	// $("a").bind("click", function() {
	// 	is_click = true;
	// });
	
	// $(window).bind('beforeunload', function(e){
	//     if(out == true && is_click == false)
	//         {
	//         	simpleAjax( module.get_value('base_url') + module.get_value('module_link') + '/close_browser', 'state=collapse' );
	//         }
 //    });
    // end of expire session on browser close

	if(module.get_value('module_link') != "login"){
		$.idleTimer( parseInt( user.get_value('idle_time') * 1000 ) );			
	
		$(document).bind("idle.idleTimer", function(){
			if( !redirect_interval ) redirect_interval = setInterval( 'redirect_timer(\'logout\', \'Session Timeout\')', 1000);
		});
	}
	
	$(document).ajaxError(function(e,req){
		if( req.status == 403 ){
			if( !redirect_interval ) redirect_interval = setInterval( 'redirect_timer(\'login\', \'403 FOrbidden\')', 1000);
		}
	});

	$(document).ajaxSuccess(function(e, xhr, settings){
		if(module.get_value('module_link') != "login"){
			if( module.get_value('base_url') + 'chat' != settings.url ){
				if( redirect_interval ){
					clearInterval(redirect_interval);
					redirect_interval = false;
				}
				
				$.idleTimer( parseInt( user.get_value('idle_time') * 1000 ) );			

				$(document).bind("idle.idleTimer", function(){
					if( !redirect_interval ) redirect_interval = setInterval( 'redirect_timer(\'logout\', \'Session Timeout\')', 1000);
				});
			}
		}
	});

	$(document).ajaxSend(function(e, xhr, settings){
		if(module.get_value('module_link') != "login"){
			if( module.get_value('base_url') + 'chat' != settings.url ){
				$.idleTimer('destroy');
			}
		}
	});

	//determine which parent root is active
	if(typeof $('li.current-child').attr('depth') === "undefined" ){
		$('li.current').addClass('current-root');	
	}
	else{
		var depth_from_parent = $('li.current-child').attr('depth');
		var active_parent = $('li.current-child');
		var falsifier = true;
		var asd = 0;
		while( falsifier ){
			if( typeof active_parent.attr('depth') !== "undefined" ){
				if( active_parent.attr('depth') == 1 ){
					falsifier = false;					
				}
				else{
					active_parent = active_parent.parent();	
				}
			}
			else{
				active_parent = active_parent.parent();	
			}
		}
		active_parent.addClass('current-root');	
	}
	
	$("input.hover-fade").hover(
		function(){
			$(this).css('opacity', .8);
		}, 
		function(){ 
			$(this).css('opacity', 1);
		}
	);
	
	$('ul.sf-menu').superfish({
		animation:   {opacity:'show',height:'show'},
		easing:      'easeOutQuad',
		speed:       'fast',
		dropShadows: true
	});
  
	/*  Aside Menu Toggle
	/******************************************************/
	
	$(".slidetoggle").click(function(){
		$(this).find(".icon-16-portlet-fold").toggleClass("icon-16-portlet-unfold");
		$(this).parent().next().slideToggle("slow");
  });
	//$(".slidetoggle").trigger('click');
	$(".aside-pane ul li:last-child").addClass("nav-last");
	 
	/*  On click dropdown
	/******************************************************/
	$("#page-header").focus( function(){ $(this).attr("hideFocus", "hidefocus"); });
	$(".account-link h4").click(function(){
		$(this).addClass("account-link-hover");

		var dd = $(".account-drop");
		if(dd.css("display") == "none"){
			dd.show();
		}
		else{
			dd.hide();
			$(".account-link h4").removeClass( "account-link-hover");
		}
	});
		
	$(".account-link").click(function(e){
		e.stopPropagation();
	});
	
	$(document).click(function(){
		$(".account-drop").hide();
		$(".account-link h4").removeClass( "account-link-hover");
	});
	
	/*  Wizard last step counter
	/******************************************************/ 
	$(".wizard-leftcol li:last-child").addClass("last");

	/*  portlet accordion
	/******************************************************/ 
	$( "#portlet-accordion" ).accordion({
			collapsible: true
	});


	
	
	
	/*  Sliding Panels
	/******************************************************/
   	
    $("div.hide-options").css({'display': 'none'});
    $("div.ph-options").css('margin-top', '-60px');
    $("div.hide-options").click(function(){
				$(this).hide(); 
				$("div.ph-options").animate({marginTop: '-60px'}, "slow"); 
				$("div.show-options").css({'display': 'inline'});
		});
		
		$("div.show-options").click(function(){
				$(this).hide(); 
			 	$("div.ph-options").animate({marginTop: '0px'}, "slow"); 
			  $("div.hide-options").show(); 
		});
		
		 
		 
		 
		
		$("div.hide-footer").click(function(){
	   $("div.pf-expand").animate({
		 	marginTop: '100px'
		 }, "slow");
		 
	   $("div.pf-collapse").animate({
		   marginTop: '-135px'
	   }, "slow");
		 
		 simpleAjax( module.get_value('base_url') + module.get_value('module_link') + '/set_footer_widget_state', 'state=collapse' );
   });
   
   $("div.show-footer").click(function() {
    	$("div.pf-collapse").animate({
			marginTop: '100px'
		}, "slow");
		
		$("div.pf-expand").animate({
		   marginTop: '0px'
	   }, "slow");
		 
		 simpleAjax( module.get_value('base_url') + module.get_value('module_link') + '/set_footer_widget_state', 'state=expand' );
   });
   
	$(".setting").live("click", function(){
		var recUrl=$(this).attr("base")+"users/settings/"+$(this).attr("reference");   
		settingFormLoad(recUrl);
	});
	
	$( '.enlarge-image' ).live( 'click', function(){
		var img = new Image();
		
		img.src = $( this ).attr( "img_target" )
		var viewport_h = $(window).height();
		var viewport_w = $(window).width();
		
		if( viewport_h > ( img.height + 71 ))
			var boxy_h = ( img.height + 90 );
		else
			var boxy_h = viewport_h - 71;
		
		
		if( viewport_w > ( img.width + 40 ) )
			var img_w = ( img.width + 60 );
		else
			var img_w = viewport_w - 71;
		
		basicInfo = new Boxy( '<div id="boxyhtml" style="height:'+ boxy_h +'px; overflow: auto;"><img src="'+img.src+'" width="'+ img_w +'"/></div>',
		{
			center: true,
			title: "Enlarged Image",
			unloadOnHide: true,
			beforeUnload: function(){ $('.tipsy').remove(); }
		});
		
		basicInfo.resize(img_w, boxy_h);
		basicInfo.center();
		
	});
	
	$( "a[rel='action-back']" ).live( 'click', function(){
		go_to_previous_page('');
	});
	
	// Show/Hide Comment Controls
	    
	$( "li.comment" ).live( 'mouseover mouseout', function(event) {
	    if ( event.type == 'mouseover' ) {
	        $( this ).children( ".comment-controls" ).show();
	    } 
		else{
	        $( this ).children( ".comment-controls" ).hide();	        
	    }
	});
	
	// Tooltips
	$( "a.tipsy-autowe" ).tipsy({
        title: 'tooltip',
        gravity: $.fn.tipsy.autoWE,
        opacity: 0.85,
        live: true,
		html: true,
		trigger: 'hover',
		delayIn: 500
    });
	
	// Tooltips
	$( "a[tooltip]" ).tipsy({
        title: 'tooltip',
        gravity: $.fn.tipsy.autoNS,
        opacity: 0.85,
        live: true,
		html: true,
		trigger: 'hover',
		delayIn: 700
    });
	
	$( "a[tooltip]").live('click', function () {
		$(this).tipsy('hide');
	});

	$('a[rel="back-to-list"]').live('click', function (){
		//fetch root/list page data from session
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/back_to_list',
			type:"POST",
			data: '',
			dataType: "json",
			beforeSend: function(){
				$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Redirecting to the record list page....</div>' });
			},
			success: function(data){
				var root_page = data.root_page;
				if(root_page != null){
					if( root_page.url != "" ){
						if( typeof root_page.post_data != 'undefined' )
						{
							 $( '#record_id' ).val( root_page.post_data.record_id );	
							 $( 'input[name="prev_search_str"]' ).val( root_page.post_data.prev_search_str );
							 $( 'input[name="prev_search_field"]' ).val( root_page.post_data.prev_search_field );
							 $( 'input[name="prev_search_option"]' ).val( root_page.post_data.prev_search_option );	
						}
						
						$( '#record-form' ).attr( "action", root_page.url );
						$( '#record-form' ).submit();	
						return false; //break script do not execute anything below
					}	
				}
				window.location = module.get_value('base_url') + module.get_value('module_link');	
			}
		});	
	});	

	// clear out plugin default styling 
	$.blockUI.defaults.overlayCSS = {}; 
	//by Ruid [fix jQuery UI datepicker positioning issue]
	//$.extend($.datepicker,{_checkOffset:function(inst,offset,isFixed){return offset}});	
});

/******************************************************/
/*  Alert Messages 
/******************************************************/
function message_box( type, msg)
{
    var icon;
	var txt;
	switch(type){
        case 'attention':
            icon = module.get_value('base_url')+user.get_value('user_theme')+'/icons/exclamation.png';
            text = 'Attention! ';
            break;
        case 'error':
            icon = module.get_value('base_url')+user.get_value('user_theme')+'/icons/cross-circle.png';
            text = 'Error! ';        
            break;     
        case 'info':
            icon = module.get_value('base_url')+user.get_value('user_theme')+'/icons/information.png';
            text = 'Tip: ';
            break;    
        case 'success':
            icon = module.get_value('base_url')+user.get_value('user_theme')+'/icons/tick-circle.png';
            text = 'Success! ';
            break;
    }
    
	var msg_div = '<div id="message_box" class="'+type+'"><img src="'+icon+'" alt="" ><p class="text-left"><strong>'+text+'</strong>'+msg+'</p></div>';	
    $( '#message-container' ).stop().slideDown().html( msg_div );
    $( '#message_box' ).stop().delay( 5000 ).fadeTo( "normal", 0.05 ).slideUp( 300 );
}

function message_growl( type, msg) 
{
    var icon;
	var txt;
	var div_class;
	switch( type ){
        case 'attention':
            icon = module.get_value('base_url')+user.get_value('user_theme')+'/icons/exclamation.png';
            text = 'Attention! ';
			div_class = 'attention';
            break;
        case 'error':
            icon = module.get_value('base_url')+user.get_value('user_theme')+'/icons/cross-circle.png';
            text = 'Error! ';  
			div_class = 'error';      
            break;     
        case 'info':
            icon = module.get_value('base_url')+user.get_value('user_theme')+'/icons/information.png';
            text = 'Tip: ';
			div_class = 'info';
            break;    
        case 'success':
            icon = module.get_value('base_url')+user.get_value('user_theme')+'/icons/tick-circle.png';
            text = 'Success! ';
			div_class = 'success';
            break;
        default:
        	return true;           

    }
    
	var msg_div = '<div id="message_box" class="'+type+'"><img src="'+icon+'" alt="" ><p class="text-left"><strong>'+text+'</strong>'+msg+'</p></div>';	
    $.growlUI( '<img src="'+icon+'" alt="" ><p class="text-left"><strong>'+text+'</strong></p>', '<p class="text-left">'+msg+'</p>', 5000, function(){}, div_class);
}

function simpleAjax( ajax_url, data, callback )
{
	$.ajax({
		url: ajax_url,
		type:"POST",
		async: false,
		data: data,
		dataType: "json",
		success: function ( data ) {
			if( data.msg != "" ) $( '#message-container' ).html( message_growl( data.msg_type, data.msg ) );
			
			if (typeof(callback) == typeof(Function)) 
				callback();
		}
	});	
}

function get_config( config ){
	var config_val = false;
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_config',
		type:"POST",
		async: false,
		data: 'config='+config,
		dataType: "json",
		success: function ( data ) {
			config_val = data.config;
		}
	});

	return config_val;
}

function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while ( rgx.test(x1) ){
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function remove_commas( nstr ){
	return nstr.replace(/\,/g,'');
}

function grid_resize( container )
{
	$( "#" + container ).jqGrid("setGridWidth", $( "#body-content-wrap" ).width() );
}

function go_to_previous_page( message )
{	
	//fetch previous page data from session
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/previous_page',
		type:"POST",
		data: '',
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />'+message+' Redirecting to the previous page...</div>' });
		},
		success: function(data){
			var previous_page = data.previous_page;
			if(previous_page != "false")
				{
				if( previous_page.url != "" ){
					if( typeof previous_page.post_data != 'undefined' ){
						 $( '#record_id' ).val( previous_page.post_data.record_id );	
						 $( 'input[name="prev_search_str"]' ).val( previous_page.post_data.prev_search_str );
						 $( 'input[name="prev_search_field"]' ).val( previous_page.post_data.prev_search_field );
						 $( 'input[name="prev_search_option"]' ).val( previous_page.post_data.prev_search_option );	
					}
					
					$( '#record-form' ).attr( "action", previous_page.url );
					$( '#record-form' ).submit();	
				}
				else{
					window.location = module.get_value('base_url') + module.get_value('module_link');												 	
				}	
			}
			else{
				window.location = module.get_value('base_url') + module.get_value('module_link');												 	
			}	
		}
	});

}

function boxyHeight(boxy, boxy_container)
{
	//viewport
	var viewport_width = $(window).width();
	var viewport_height = $(window).height();
	var content_height = $( boxy_container ).css('height');
	// 89 is constant top and bottom border of boxy + an allowance of 15px top and bottom
	var overall_height = parseFloat(content_height) + 89;
	if( overall_height > viewport_height){
		 $(boxy_container).css('overflow-y', 'scroll');
		 content_height = viewport_height - 89;
		 $(boxy_container).css('height', content_height);
	}
	boxy.center();
}

function footer_widget_state( state ){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/set_footer_widget_state',
		type:"POST",
		data: '',
		dataType: "json",
		beforeSend: function(){},
		success: function(data){}
	});
}

function comments_box(type, identifier, callback) {
    $.ajax({
        url: 'comments/get_comments',
        type:"POST",
        data: 'type=' + type + '&identifier=' + identifier + '&boxy=1' + '&callback='+callback,
        dataType: "json",
        beforeSend: function(){
            $.blockUI({
                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
            });  		
        },
        success: function(data){
            $.unblockUI();
            if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
            if(data.comment_box != ""){
                var width = $(window).width()*.7;
                quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.comment_box +'</div>',
                {
                    title: 'Comments',
                    draggable: false,
                    modal: true,
                    center: true,
                    unloadOnHide: true,
                    beforeUnload: function (){
                        $('.tipsy').remove();
                    }
                });
                boxyHeight(quickedit_boxy, '#boxyhtml');
            }
        }
    });  
}

/**
 * Used in boxy comments.
 * @return {[type]}
 */
function save_comment( callback ){
	ok_to_save = true;
	if( ok_to_save ) {		
		var data = $('#comment-form').serialize();
		var saveUrl = module.get_value('base_url')+"comments/ajax_save"		

		$.ajax({
			url: saveUrl,
			type:"POST",
			data: data,
			dataType: "json",
			/**async: false, // Removed because loading box is not displayed when set to false **/
			beforeSend: function(){
			    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
			},
			success: function(data){
				$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });

				comment_block = '<li>' +
					'<hr />' +
					'<div>' +
						'<span class="comment-name"><strong>' + data.name + '</strong></span>' +
						'&nbsp;<span class="comment-comment">' + data.comment + '</span>' +
					'</div>' +
					'<div class="comment-date">' + data.created_date + '</div>' +
					'</li>';

				$('#comments-list').prepend(comment_block);
				
				if( callback != undefined && callback != '' ) eval( callback )
			}
		});
	}
	else{
		return false;
	}
	return true;
}	

function show_saving_blockui(){
	$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
}

function page_refresh(){
	$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
	window.location.href = window.location.href;
}

/**
 * Checks wether provided variable is a Integer 
 * @param  {mixed}  n
 * @return {Boolean}
 */
function is_integer( n ) {
   return typeof n === 'number' && parseFloat(n) == parseInt(n) && !isNaN(n);
}

function detect_flash()
{
	var playerVersion = swfobject.getFlashPlayerVersion();
	if( playerVersion.major == 0 ){
		return false;
	}
	else{
		return true;
	}
}

var redirect_boxy = false;
var redirect_second = 15;
var redirect_interval = false;
function redirect_timer(url, reason){
	if( redirect_second == 0 ){
		window.location = module.get_value('base_url') + url;
		clearInterval(redirect_interval);
		redirect_interval = false;
	}else{
		if(redirect_second < 10){
			$.unblockUI();
			if( !redirect_boxy ){
				redirect_boxy = new Boxy('<div id="boxyhtml" style="width:200px">Redirecting in '+redirect_second+'sec</div>',
		        {
		            title: reason,
		            draggable: false,
		            modal: true,
		            center: true,
		            unloadOnHide: true,
		            beforeUnload: function (){
		                redirect_boxy = false;
		            }
		        });
			}
			else{
				redirect_boxy.setContent('<div id="boxyhtml" style="width:200px">Redirecting in '+redirect_second+'sec</div>')
			}
		}

		redirect_second--;
	}
}

(function($) {
	$.fn.disable = function() {
		this.each(function() {
			$(this).attr('disabled', true);
		});
		return this;
	};

	$.fn.enable = function() {
		this.each(function() {
			$(this).attr('disabled', false);
		});
		return this;
	};
})(jQuery);

var decodeEntities = (function() {
  // this prevents any overhead from creating the object each time
  var element = document.createElement('div');

  function decodeHTMLEntities (str) {
    if(str && typeof str === 'string') {
      // strip script/html tags
      str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
      str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
      element.innerHTML = str;
      str = element.textContent;
      element.textContent = '';
    }

    return str;
  }

  return decodeHTMLEntities;
})();

/* by Ruid */
$(window).load(function(){
	$('#ui-datepicker-div').draggable();
	$('#ui-datepicker-div').css('cursor','move');
});
