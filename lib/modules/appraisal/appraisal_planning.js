$(document).ready(function() {

	init_datepick();


    $('.key_weight').keydown(numeric_only);

    $('.target').keydown(numeric_only);

    $('.target').keypress(not_percentage);

    $('.key_weight').keypress(not_percentage);

    $('.weight').keypress(not_percentage);

    $('.description_competency').click(function () {
    	var content = $(this).next('label').html();
    	var width   = $(window).width()*.7;

        $.ajax({
            url: module.get_value('base_url') + "appraisal/appraisal_planning/get_competencies",
            type:"POST",    
            data: "master_id="+$(this).attr('master-id'),
            dataType: "json",
            beforeSend: function(){
                $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
            },
            success: function( data ){

                $.unblockUI();
                template_form = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.contents +'</div>',
                                {
                                        title: "Description",
                                        draggable: false,
                                        modal: true,
                                        center: true,
                                        unloadOnHide: true,
                                        beforeUnload: function (){
                                            template_form = false;
                                        }
                                    });
                                    boxyHeight(template_form, '#boxyhtml');
            }
        });   
    });	

    $('.weight').keydown( numeric_only ); 

	$('.show_hide').toggle(function() {
	    $(this).closest('table').children('tbody').slideUp();
	    $(this).children('span').text('Show');
	}, function () {
	    $(this).closest('table').children('tbody').slideDown();
	    $(this).children('span').text('Hide');
	});	

    $('.icon-16-document-view').live('click', function(){
        $.ajax({
            url: module.get_value('base_url') + "appraisal/appraisal_planning/get_jd_html",
            type:"POST",    
            data: "record_id="+$(this).attr('pid'),
            dataType: "json",
            beforeSend: function(){
                $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
            },
            success: function( data ){
                $.unblockUI();
                var width = $(window).width()*.7;
                new Boxy('<div id="boxyhtml" style="width:'+width+'px; overflow-y:auto">' + data.jd_items +'</div>', 
                    {
                        title:"Job Description",
                        modal: true
                    }
                    );
            }
        });         
    }); 

	if (module.get_value('view') == "edit"){

        $('.target').live('keyup',function(e){
            fieldval = parseFloat($(this).val());
            weight_val = $(this).closest('tr').find('.weight').val();

            var valid = /^[-+]?\d+(\.\d+)?$/.test( fieldval );
            if( fieldval.toString() != "" && valid){
                if( fieldval <= 0 && weight_val > 0){
                    $(this).focus();                    
                    $('#message-container').html(message_growl('error', 'This field should be greater than 0'))
                    return false;
                }
            }
        });

        $('#main .weight').live('keyup',function() {
            var perspective = $(this).attr('perspectiveid');
            var total_weight_average = 0;
            $('#main .weight[perspectiveid='+perspective+']').each(function (index, element) {
                if ($(element).val() != ' ') {
                    total_weight_average += parseFloat($(element).val());
                }   
            })
            $('.key_weight[perspectiveid='+perspective+']').val(total_weight_average);  

            $('.key_weight').trigger('keyup');
        });

        $('.actual,.target').live('keyup',function(){
            var parent = $(this).closest('tr');
            var actual = $(parent).find('.actual').val();
            var target = $(parent).find('.target').val();
            var weight = $(parent).find('.weight').val();
            var achieved = (actual / target) * 100;
            var weight_average = (achieved * weight) / 100
            $(parent).find('.achieved').val(parseInt(achieved)+'%');
            $(parent).find('.weight_average').val(parseInt(weight_average)+'%');
        });

        $('.key_weight').live('keyup',function(){

            var perspective_id = $(this).attr('perspectiveid');

            $('.perspective_weight_'+perspective_id).html(parseFloat($(this).val())+' %');


        });

        $('.add_strength_areas_improvement_row').live('click',function(){

            var type = $(this).attr('rel');
            var html = $('.strength_areas_improvement tr.'+type).clone();

            $(this).parents('table.'+type).find('tbody.'+type).append(html);

        });

        $('.delete_strength_areas_improvement_row').live('click',function(){

            $(this).parent().parent().remove();

        });

        var item_ctr = 1;
        $('.item_name').each(function(){
            item_ctr = 1;
            var this_name = $(this).attr('name');
            var question = $(this).attr('question');

            $('input[name="'+this_name+'"]').each(function(){
                var value = question + " "  + item_ctr;
                if( $(this).val() == '' ){
                    $(this).val(value);
                    item_ctr++;
                }
            });
        });
    	
		$('.add_row').live('click',function() {
			var elem = $(this);
			var id = $(elem).attr("columnid");
			var q_id = $(elem).attr("question");
			var html = '<tr class="additional">' + $('#'+id+'').html() + '</tr>';
			$('#'+q_id+'').before(html);
            $('.weight').keydown( numeric_only ); 
			$('tr.additional').hover(
				function(){		
					$(this).find('span.del-button').show();
				},
				function(){
					$(this).find('span.del-button').hide();
				}
			);

            var item_key = q_id.replace("q", "")

            var ghost_elem = 0;
            $('input[name="item_name['+item_key+'][]"]').each(function(){
                ghost_elem++;
            });

            ghost_elem = ghost_elem - 1;

            $('input[name="item_name['+item_key+'][]"]').each(function(){
                var question = $(this).attr('question');
                var value = question + " "  + ghost_elem;
                if( $(this).val() == '' ){
                    $(this).val(value);
                }
            });
		});

		
		$('.delete_row').live('click',function(){
			$(this).closest('tr').remove();
		});

		$('tr.additional').hover(
			function(){		
				$(this).find('span.del-button').show();
			},
			function(){
				$(this).find('span.del-button').hide();
			}
		);

		$('.add_row_competencies').live('click',function() {
			var elem = $(this);
			var id = $(elem).attr("core-value");
			var html = '<tr class="additional competency_value">' + $('#corevalue'+id+'').html() + '</tr>';
			$('#'+id+'').before(html);

			$('tr.additional').hover(
				function(){		
					$(this).find('span.del-button').show();
				},
				function(){
					$(this).find('span.del-button').hide();
				}
			);			
		});

        $('.competency_picklist').live('change',function(){

            var element = $(this);

            $.ajax({
                url: module.get_value('base_url') + "appraisal/appraisal_planning/get_competency_level",
                type:"POST",    
                data: "competency_id="+element.val(),
                dataType: "html",
                beforeSend: function(){
                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
                },
                success: function( response ){
                    $.unblockUI();

                    element.parents('tr.competency_value').find('.competency_level_picklist').html(response);
                    element.parents('tr.competency_value').find('.competency_level_picklist').parent().find('small').remove();

                }
            });  

        });

         $('.competency_level_picklist').live('change',function(){

                if( $(this).val() != "" ){
                    $(this).parent().find('small').remove();
                    $(this).parent().append('<div style="width:150px;"><small>'+$(this).find('option:selected').attr('description')+'</small></div>');
                }
                else{
                    $(this).parent().find('small').remove();
                }

            }); 

	}

});

function edit_detail(){
	var user_id = $('#appraisee_id').val();
	var period_id = $('#period_id').val();
	window.location = module.get_value('base_url') + "appraisal/appraisal_planning/edit/" + user_id + "/" + period_id;	
}

function insert_period_id(period_id) 
{	
	if ($('#period_id').size() > 0) {
		$('#period_id').val(period_id);
	} else {		
		$('#record-form').append($('<input type="hidden" id="period_id" name="period_id" />').val(period_id));
	}

	$('.icon-appraisal').each(function(index, elem) {
		$(this).attr('href', $(this).attr('href')+ '/' + period_id);
	});	

	$('.icon-view-log-access').each(function(index, elem) {
		$(this).attr('period_id', period_id);
	});		
}

function comment_box(criteria_id, question_id) {

    var appraisee = $("#appraisee_id").val();
    var period_id = $("#period_id").val();
    var appraisal_year = $('#appraisal_year').val();
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') +'/get_comments',
        type:"POST",
        data: 'appraisee=' + appraisee + '&criteria_id=' + criteria_id + '&boxy=1' + '&period_id='+period_id + '&appraisal_year='+appraisal_year + '&question_id='+question_id+ '&view='+module.get_value('view'),
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

function save_comments( ){
    ok_to_save = true;
    if( ok_to_save ) {      
        var data = $('#comment-form').serialize();
        var saveUrl = module.get_value('base_url')+ module.get_value('module_link') + "/ajax_save_comment"      

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
                
                // if( callback != undefined && callback != '' ) eval( callback )
            }
        });
    }
    else{
        return false;
    }
    return true;
}   

function validate_form()
{
	$('.required').each(function(){
		if ($(this).is('[readonly]') == false){
			if ($(this).closest('tr').css('display') != "none"){
				if ($(this).val() == ""){
					add_error($(this).attr('cname'),$(this).attr('cname'), "This field is mandatory.");
				}
			}
		}
	});

    var key_weight_error = 0;

    $('.performance_objective').each(function(){

        var total_key = 0;
        var ratio = $(this).attr('ratio');

        $(this).find('.key_weight').each(function(){
            total_key += parseFloat($(this).val());
        });

        if( total_key != 100 ){
            key_weight_error++;
        }

    });

    if( key_weight_error > 0 ){
        add_error('key_weight','Key Weight','Total Key Weight must be equal to 100');
    }


    $('.target').each(function(){
        var actual_val = $(this).val();
        var fieldlen = $(this).length;
        
        if( actual_val !== "" && fieldlen > 0){
            // remove comma separations
            var integer_val = actual_val.replace(",", "");    
            
            //test if integer
            var valid = /^[-+]?\d+(\.\d+)?$/.test(actual_val.trim());
            if( !valid ){
                console.log(actual_val);
                add_error('target', 'Target', "This field only accept integers.");
                return false;
            }
        }


        fieldval = parseFloat($(this).val());
        weight_val = $(this).closest('tr').find('.weight').val();

        var valid = /^[-+]?\d+(\.\d+)?$/.test( fieldval );
        if( fieldval.toString() != "" && valid){
            if( fieldval <= 0 && weight_val > 0){
                add_error('target', 'Target', "This field should be greater than 0");
                return false;
            }
        }
    });
/*    var distribution_error = 0;

    $('.perspective').each(function(){

        var perspective_id = $(this).attr('perspectiveid');
        var total_distribution = 0;

        $('.performance_objective').find('.distribution_'+perspective_id).each(function(){
            total_distribution += parseFloat($(this).val());
        });        

        if( total_distribution != 100 ){
            distribution_error++;
        }
    });

    if( distribution_error > 0 ){
        add_error('distribution','% Distribution','Total % Distribution per Perspective must be 100');
    }*/


    //errors
    if(error.length > 0){
        var error_str = "Please correct the following errors:<br/><br/>";
        for(var i in error){
            if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
            error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
        }
        $('#message-container').html(message_growl('error', error_str));
        
        //reset errors
        error = new Array();
        error_ctr = 0
        return false;
    }
    
    //no error occurred
    return true;
}


function ajax_save( on_success, is_wizard , callback, status ){

            if( is_wizard == 1 ){
                var current = $('.current-wizard');
                var fg_id = current.attr('fg_id');
                var ok_to_save = eval('validate_fg'+fg_id+'()')
            }
            else{
                if (callback == 'no_validate'){
                    ok_to_save = true;
                }
                else{
                    ok_to_save = validate_form();
                }
               //ok_to_save = true;
            }
            
            if( ok_to_save ) {      
                $('#record-form').find('.chzn-done').each(function (index, elem) {
                    if (elem.multiple) {
                        if ($(elem).attr('name') != $(elem).attr('id') + '[]') {
                            $(elem).attr('name', $(elem).attr('name') + '[]');
                        }
                        
                        var values = new Array();
                        for(var i=0; i< elem.options.length; i++) {
                            if(elem.options[i].selected == true) {
                                values[values.length] = elem.options[i].value;
                            }
                        }
                        $(elem).val(values);
                    }
                });


                var data = $('#record-form').serialize() + '&status='+status;
                var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"     

                $.ajax({
                    url: saveUrl,
                    type:"POST",
                    data: data,
                    dataType: "json",
                    /**async: false, // Removed because loading box is not displayed when set to false **/
                    beforeSend: function(){
                            show_saving_blockui();
                    },
                    success: function(data){
                        if( data.msg_type != "error" && data.record_id != null ){                   
                            switch( on_success ){
                                case 'back':
                                    // go_to_previous_page( data.msg );
                                     window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + $('#appraisee_id').val() + '/'+ $('#period_id').val();
                                break;
                                case 'email':
                                    if (data.record_id > 0 && data.record_id != '') {
                                        // Ajax request to send email.                    
                                        $.ajax({
                                            url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                            data: 'record_id=' + data.record_id,
                                            dataType: 'json',
                                            type: 'post',
                                            async: false,
                                            beforeSend: function(){
                                                    show_saving_blockui();
                                                },                              
                                            success: function () {
                                                $.unblockUI({
                                                    onUnblock: function() {
                                                        message_growl(data.msg_type, data.msg)
                                                    }
                                                });                                     
                                            }
                                        });
                                    }                           
                                    //custom ajax save callback
                                    if (typeof(callback) == typeof(Function)) callback( data );
                                   window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + $('#appraisee_id').val() + '/'+ $('#period_id').val();
                            break;
                                 default:
                                    if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
                                            window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                                    }
                                    else{
                                        //check if new record, update record_id
                                        if($('#record_id').val() == -1 && data.record_id != ""){
                                            $('#record_id').val(data.record_id);
                                            $('#record_id').trigger('change');
                                            if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                                        }
                                        else{
                                            $('#record_id').val( data.record_id );
                                        }
                                        //generic ajax save callback
                                        if(typeof window.ajax_save_callback == 'function') ajax_save_callback();
                                        //custom ajax save callback
                                        if (typeof(callback) == typeof(Function)) callback( data );
                                        $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                                    }
                                    break;
                            }   
                        }
                        else{
                            $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                        }
                    }
                });
            }
            else{
                return false;
            }
            return true;
        }