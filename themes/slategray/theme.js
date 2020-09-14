$(document).ready(function () {    
	/** pre-employment action links **/
	$('#menu a').each(function (index, elem) {
		if ($(elem).attr('href') == module.get_value('base_url')) {
			$(elem).attr('href', window.location.href + '#');
		}
	});

	$('#menu ul.submenu li').has('ul li').children('a').addClass('icon-16-portlet-unfold');	
	
	

	var o_bg;
	
	//$('#menu ul.submenu li a').hover(
	//	function () {
	//		o_bg = $(this).parent('li').css('background');
	//		$(this).parent('li').css('background', '#cadded');
	//	},
	//	function () {
	//		$(this).parent('li').css('background', o_bg);
	//	}
	//);

	$('#menu ul.submenu li a').click(function () {
		
	});

	$(".pe-actions").hide();
	$(".rightpane-list li").hover(
		function(){
			$(this).find(".pe-actions").show();
		},
		function(){
			$(this).find(".pe-actions").hide();
		});
	
	$('.layout-switcher').live('click', function () {
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/detail',
			type: 'post',
			dataType: 'html',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Please wait....</div>'
				});
			},
			data: 'record_id=' + $('#record_id').val() + '&flag=' + $(this).attr('flag'),
			success: function (response) {
				$('.content-wrap').html(response);
				init_wizard_buttons();
                
				// init() is our onload functions.
				if (typeof (init) == typeof(Function)) {
					init();
				}
                
				$('#message-container').html(message_growl('success', 'View Changed'));
			}
		});
	});    
    
	$('.toggle-trig').click(function () {
		$('#' + $(this).attr('rel')).slideToggle(600);
	});
    
    
	$('.jqgrid-advanced-search').live('click', function () {
		$('#' + $(this).attr('container')).jqGrid('setGridParam', 
		{
			search: true,
			postData: {
				query: $(this).parents('form').serialize()
			}
		}).trigger("reloadGrid");
	});	
	
	$("#viewchatlist").click(function(e){
		e.stopPropagation();
	});
	
	$("#ChatWidget").click(function(e){
		e.stopPropagation();
	});
	
	$(document).click(function(){
		close_chatlist_window();
	});
});

/**
 * Save without validation.
 */
function save_partial(on_success) {
	ok_to_save = true;
	if( ok_to_save ){
		var data = $('#record-form').serialize();
		var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"
				
		$.ajax({
			url: saveUrl,
			type:"POST",
			data: data,
			dataType: "json",
			async: false,
			beforeSend: function(){
				if( $('.now-loading').length == 0) $.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>'
				});
			},
			success: function(data){
				if(on_success == "back") {
					go_to_previous_page( data.msg );
				} else if (on_success == "email") {
					// Ajax request to send email.
					$.ajax({
						url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
						data: 'record_id=' + data.record_id,
						type: 'post',
						success: function () {
							if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
							$.unblockUI({
								onUnblock: function() {
									message_growl(data.msg_type, data.msg)
								}
							});
						}
					});                                    
				} else if (typeof(on_success) == typeof(Function)) {
					on_success();
				} else{
					//check if new record, update record_id
					if($('#record_id').val() == -1 && data.record_id != ""){
						$('#record_id').val(data.record_id);
						$('#record_id').trigger('change');
						if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
					}
					$.unblockUI({
						onUnblock: function() {
							message_growl(data.msg_type, data.msg)
						}
					});
				}
			}
		}); 
	}
	else{
		return false;
	}
	return true;    
}

function add_layout_switcher(flag) {
	if (flag == 0) {
		text = 'Full View &raquo; ';
		lstclass = 'lst-full';
	} else {
		text = '&laquo; Compact View';
		lstclass = 'lst-compact';
	}

	$('div.layout-switch-toggle').
		addClass(lstclass).
		html('<a href="javascript:void(0);" class="layout-switcher" flag="' + flag + '">' + text + '</a>');
}

function activate_add_more(obj) {
	if (obj.find('.add-more-flag').val() != undefined) {
		$('.add-more-div').show();
		$('.add-more-div a.add-more').attr('rel', obj.find('.add-more-flag').val());
	} else {
		$('.add-more-div').hide();
		$('.add-more-div a.add-more').removeAttr('rel');
	}
}

function init_datepick() {
	// Add datepicker to new instances only.
	$('.date').not('.hasDatePicker').datepicker({
		changeMonth: true,
		changeYear: true,
		showOtherMonths: true,
		showButtonPanel: true,
		showAnim: 'slideDown',
		selectOtherMonths: true,
		showOn: "both",
		buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
		buttonImageOnly: true,
		buttonText: '',
		yearRange: 'c-90:c+10',
		beforeShow: function(input, inst) {
			// Fixes bug that changes calendar to month-year selection when .date and .month-year are on same page.
			inst.dpDiv.removeClass('monthonly');
			inst.dpDiv.removeClass('yearonly');
		}		
	});
    
	$('.month-year').not('.hasDatePicker').datepicker( {
		changeMonth: true,		
		changeYear: true,
		showOtherMonths: true,
		showButtonPanel: true,
		showAnim: 'slideDown',
		selectOtherMonths: true,
		showOn: "both",
		buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
		buttonImageOnly: true,
		buttonText: '',        
		dateFormat: 'MM yy',
		yearRange: 'c-90:c+10',
		onClose: function(dateText, inst) { 
			var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
			var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
			$(this).val($.datepicker.formatDate('MM yy', new Date(year, month, 1)));
			//$(this).datepicker('setDate', new Date(year, month, 1));
		},
		beforeShow: function(input, inst) {
			inst.dpDiv.addClass('monthonly');
			inst.dpDiv.removeClass('yearonly');
		}
	}).keyup(function(e) {
		if(e.keyCode == 8 || e.keyCode == 46) {
			$.datepicker._clearDate(this);
		}
	}); 

	$('.year-dtp').not('.hasDatePicker').datepicker( {
		changeMonth: true,		
		changeYear: true,
		showOtherMonths: true,
		showButtonPanel: true,
		showAnim: 'slideDown',
		selectOtherMonths: true,
		showOn: "both",
		buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
		buttonImageOnly: true,
		buttonText: '',        
		dateFormat: 'yy',
		yearRange: 'c-90:c+10',
		onClose: function(dateText, inst) { 
			var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
			$(this).datepicker('setDate', new Date(year, 1));
		},
		beforeShow: function(input, inst) {
			inst.dpDiv.addClass('yearonly');
		}
	});  	
}

function validate_mandatory_array(fieldname, fieldlabel)
{
	if($('input[name="'+fieldname+'"]').attr('type') == "checkbox"){
		var checked = 0;
		$('input[name="'+fieldname+'"]').each(function(){
			if($(this).attr('checked')) checked++;
		});

		if(checked == 0){
			add_error(fieldname, fieldlabel, "This field is mandatory, select at least 1.");
			return false;
		}
	}
	else{
		$('input[name="'+fieldname+'"]').each(function (index, element){
			if($(element).val() == ""){
				if( fieldname == "password" && $('.password-field-div').length > 0 ){
					if( $('.password-field-div').css('display') != "none"){
						add_error(fieldname, fieldlabel, "This field is mandatory.");
						return false;
					}
				}else{
					add_error(fieldname, fieldlabel, "This field is mandatory.");
					return false;
				}
			}
		});

	}
	return true;
}

function print()
{
	if( user.get_value('print_control') == 1 ){
		$('#record-form').attr("action", module.get_value('base_url') + module.get_value('module_link') +"/print_record");
		$('#record-form').submit();
	}
	else{
		$('#message-container').html(message_growl('attention', 'Insufficient Access grant!\nPlease contact the system administrator.'))
	}
}




/**
 * Declare toggleFieldGroupVisibility function if it does not exist.
 */
if (typeof (toggleFieldGroupVisibility) != typeof(Function)) {
	function toggleFieldGroupVisibility( obj )
	{
		if ( obj.parent().next().is( ":hidden" ) ){

			obj.parent().next().removeClass( "hidden" );
			obj.text("Hide");
		}
		else{

			obj.parent().next().addClass( "hidden" );
			obj.text("Show");
		}
	}
}

//global js var in edit view
var error = new Array();
var error_ctr = 0;
var related_module_boxy = new Array();
var related_module_boxy_count = 0;
var quickedit_boxy = "";
var module_id = 0;
if (typeof(getRelatedModule) != typeof(Function)) {
	function getRelatedModule(field_id, fieldname, other){
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + "/get_related_module",
			type:"POST",
			data: "field_id="+field_id+"&fieldname="+fieldname,
			dataType: "json",
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
				});  		
			},
			success: function(data){
				if(data.msg != ""){
					$.unblockUI();
					$('#message-container').html(message_growl(data.msg_type, data.msg));
				}
				if(data.link != "") showRelatedModule(data.link, data.short_name, fieldname, data.column, other);
			}
		}); 
	}    
}

if (typeof(showRelatedModule) != typeof(Function)) {

	function showRelatedModule(related_module_link, related_module, fieldname, column, other)
	{
		$.ajax({
			url: module.get_value('base_url') + related_module_link + "/show_related_module",
			type:"POST",
			data: "other="+other+"&fieldname="+fieldname+"&column="+column+'&module_link='+related_module_link+"&fmlinkctr="+related_module_boxy_count,
			dataType: "html",
			beforeSend: function(){
			},
			success: function(data){
				related_module_boxy[related_module_boxy_count] = new Boxy('<div id="related_module_boxy-'+related_module_boxy_count+'-container">'+ data +'</div>',
				{
					title: related_module,
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					show: false,
					afterShow: function(){
						$.unblockUI();
					},
					beforeUnload: function(){
						$('.tipsy').remove();
					}
				});
				boxyHeight(related_module_boxy[related_module_boxy_count], '#related_module_boxy-'+related_module_boxy_count+'-container');
				related_module_boxy_count++;
			}
		});
	}    
}

if (typeof(showQuickEditForm) != typeof(Function)) {

	function showQuickEditForm( module_url, data)
	{
		$.ajax({
			url: module_url,
			type:"POST",
			data: data,
			dataType: "json",
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
				});  		
			},
			success: function(data){
				$.unblockUI();
				if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
				if(data.quickedit_form != ""){
					var width = $(window).width()*.7;
					quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.quickedit_form +'</div>',
					{
						title: 'Quick Add/Edit',
						draggable: false,
						modal: true,
						center: true,
						unloadOnHide: true,
						beforeUnload: function (){
							$('.tipsy').remove();
						},
						afterShow: function () {
							if (typeof(init_quickedit_datepick) == typeof(Function)) {
								init_quickedit_datepick();                                
							}
							if (typeof(BindLoadEvents) == typeof(Function)) {
				               BindLoadEvents();
				            }
				            if (typeof(CustomBindLoadEvents) == typeof(Function)) {
				               CustomBindLoadEvents();
				            }
						}
					});
					boxyHeight(quickedit_boxy, '#boxyhtml');
				}
			}
		});
	}    
}

function chatListView() {
	userList = $("#ChatWrap .online-user-list");
	if ( $( userList ).hasClass("thumbsView") ) {
		$( userList ).removeClass("thumbsView");
		$( userList ).addClass("listView");
	}	
	return false;
}

function chatThumbsView() {
	userList = $("#ChatWrap .online-user-list");
	if ( $( userList ).hasClass("listView") ) {
		$( userList ).removeClass("listView");
		$( userList ).addClass("thumbsView");
	}	
	return false;
}

function toggle_chatlist_window(){
	var chatlist = $('.chatlist-window');
	if( chatlist.css('display') == 'none' )
		chatlist.show('slow');
	else
		chatlist.hide('slow');
}

function close_chatlist_window(){
	 $('.chatlist-window').hide('slow');
	 $('input#chatsearch').val('');
}

function create_filter_list(filters) {	
	$('#ul-filter').empty();
	$(filters[0]).each(function(index, filter) {
		if (filter == '<a href="http://localhost/firstbalfour/recruitment/candidates/filter/6"><span class="align-left aside-link">Hired</span><span class="bg-red ctr-inline align-right">0</span></a>'){
			li = '<li style="background:#A8ACB6">&nbsp;</li><li><h3>' + filter + '</h3></li>';
		}
		else{
			li = '<li><h3>' + filter + '</h3></li>';
		}

		$('#ul-filter').append(li);
	});
}

$(function () {
	$("#menu li li").hover(function() {
	    $(this).stop().animate({ backgroundColor: "#cadded"}, 400);
	    },function() {
	    $(this).stop().animate({ backgroundColor: "#F3F3F4"}, 200);
	    });
	$("#menu li li li").hover(function() {
	    $(this).stop().animate({ backgroundColor: "#ffffff"}, 400);
	    });
	});

$(document).ready(function() {
	$("#btn-panel").live('click',function() {
		if ($("aside").attr("style") == "margin-left: -265px;"){	
			toggleOff();
		}
		else{
			toggleOn();
		}
	});
});

function toggleOn(){		
	$("aside").animate({"margin-left": "-265"});
	setTimeout(function () {
		$("#body-content-wrap").css({"width": "98%", "padding-left": "20px"});
		if ($("#gbox_jqgridcontainer").length > 0){
			$(window).bind('resize', function() {
			$("#gbox_jqgridcontainer").setGridWidth($(window).width());
			}).trigger('resize');
		}
	}, 500);
	$("#btn-panel").removeClass("close-panel");
	$("#btn-panel").addClass("open-panel");
	if ($("#gbox_jqgridcontainer").length > 0){	
		$(window).bind('resize', function() {
		$("#gbox_jqgridcontainer").setGridWidth($(window).width());
		}).trigger('resize');
	}		
}

function toggleOff(){
	$("#btn-panel").removeClass("open-panel");
	$("#btn-panel").addClass("close-panel");	
	$("aside").animate({"margin-left": "17"});
	$("#body-content-wrap").css({"width": "80%"});
	$("#btn-panel").addClass("close-panel");
	$("#btn-panel").removeClass("open-panel");
	if ($("#gbox_jqgridcontainer").length > 0){
		$(window).bind('resize', function() {
		//alert("yo");
		$("#gbox_jqgridcontainer").setGridWidth($(window).width());
		}).trigger('resize');
	}	
}


