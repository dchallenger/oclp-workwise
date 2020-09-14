$(document).ready(function(){

    window.onload = function(){

        $('.form-submit-btn').find('a.icon-16-disk').attr('onclick','prompt_applicant("",1)');

         for (i = new Date().getFullYear(); i > 1900; i--)
        {
            if( $('#working_since').val() != "" ){
                if( i == $('#working_since').val() ){
                    $('#working_since_select').append($('<option selected="selected" />').val(i).html(i));
                }
                else{
                    $('#working_since_select').append($('<option />').val(i).html(i));
                }
            }
            else{
                $('#working_since_select').append($('<option />').val(i).html(i));
            }
        }

          if($('.count_test_profile').val() != 0)
            {

                var count_file = $('.count_test_profile').val();
                for(i=1; i<=count_file; i++)
                {

                    $('#test_profile-photo'+i).uploadify({
                        'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
                        'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify3.php',
                        'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
                        'folder'    : 'uploads/' + module.get_value('module_link'),
                        'fileExt'   : '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
                        'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
                        'auto'      : true,
                        'method'    : 'POST',
                        'scriptData': {module: module.get_value('module_link'), fullpath: module.get_value('fullpath'), path: "uploads/" + module.get_value('module_link'),field:"test_profile-photo",text_id:""+i+""},
                        'onComplete': function(event, ID, fileObj, response, data)
                        {        
                            var split_res = response.split('|||||');
                            $('#result_attach'+split_res[1]).val(split_res[0]);
                            if(split_res[2] == 'image')
                            {
                                var img = '<div class="nomargin image-wrap"><img id="file-photo-'+ split_res[1] +'" src="' + module.get_value('base_url') + split_res[0] +'" width="100px"><div class="delete-image nomargin multi" field="result_attach'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';                            
                            }
                            else
                            {
                                var img = '<div class="nomargin image-wrap"><a id="file-photo-'+ split_res[1] +'" href="' + module.get_value('base_url') + split_res[0] + '" width="100px" target="_blank"><img src="' + module.get_value('base_url') + 'themes/slategray/images/file-icon-md.png"></a><div class="delete-image nomargin multi" field="result_attach'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';
                            }
                            $('#photo-upload-container_1'+split_res[1]).html('');
                            $('#photo-upload-container_1'+split_res[1]).append(img);
                        },
                        'onError': function (event,ID,fileObj,errorObj) {
                            $('#error-photo').html(errorObj.type + ' Error: ' + errorObj.info);
                        },
                        'onCancel': function (){
                            var split_res = $(event.target).attr('rel');
                            $( '#result_attach'+split_res ).val('');
                            $(this).parent('#test_profile'+split_res+'photoQueue').remove();                    
                        }
                    });
                }
            }
            
          $('#pres_city_chzn').css('width', '67%');
        $('#pres_city_chzn').children().css('width', '92%');
        $('#pres_city_chzn .chzn-search input[type="text"]').css('width', '92%');

        $('#perm_city_chzn').css('width', '67%');
        $('#perm_city_chzn').children().css('width', '92%');
        $('#perm_city_chzn .chzn-search input[type="text"]').css('width', '92%')

    }


	// Disable backspace
    $(document).keydown(function(e) {
	    var element = e.target.nodeName.toLowerCase();
	    if (element != 'input' && element != 'textarea') {
	        if (e.keyCode === 8) {
	            return false;
	        }
	    }
	});

    $('.affiliates_active').click(function(){

                            if($(this).val() == 1 ){

                                $(this).parent().find('.active_hidden').val('1');
                                
                                $(this).parent().parent().parent().find('#affiliates_date_resigned').parent().find('img').replaceWith(function(){
                                    return $('<img class="datebutton" src="'+module.get_value('base_url')+'themes/slategray/icons/calendar-month.png" alt="" title="" >');
                                });

                                $(this).parent().parent().parent().find('#affiliates_date_resigned').replaceWith(function(){
                                    return $('<input type="text" class="input-text" readonly="readonly" name="affiliates[date_resigned][]" style="width:30%;" id="affiliates_date_resigned" />');
                                });

                            }
                            else{

                                 $(this).parent().find('.active_hidden').val('0');
                                 $(this).parent().parent().parent().find('#affiliates_date_resigned').replaceWith(function(){
                                    var sample_datepicker =  $('<input type="text" class="input-text date_from month-year" style name="affiliates[date_resigned][]" id="affiliates_date_resigned" />').datepicker( {
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
                                                    $(this).datepicker('setDate', new Date(year, month, 1));
                                                },
                                                beforeShow: function(input, inst) {
                                                    inst.dpDiv.addClass('monthonly');
                                                }
                                            }).val($(this).val());    

                                            return sample_datepicker;
                                });

                                $(this).parent().parent().parent().find('#affiliates_date_resigned').parent().find('img').show();

                                $(this).parent().parent().parent().find('#affiliates_date_resigned').parent().find('img').click(function(){

                                    $(this).parent().find('#affiliates_date_resigned').trigger('focus');

                                });


                            }
                        });


	$('#page-footer').remove();

    $('a.delete-detail').die('click');

    $('a.delete-detail').live('click', function () {        
        $(this).parents('fieldset').remove();
    });

    $('#uinsub').live('click', function() {
    	$.ajax({
    		url: module.get_value('base_url') + module.get_value('module_link') + '/verify_applicant_code',
    		data: $('input[name="uin"], #firstname, #lastname, #middlename, input[name="sex"], #birth_date').serialize(),
    		type: 'post',
    		dataType: 'json',
    		success: function(response) {
    			if (response.record_id > 0) {
	    			$('#record_id').val(response.record_id);
	    			$('#record-form').attr('action', module.get_value('base_url') + module.get_value('module_link') + '/edit');
	    			$('#record-form').submit();
	    		} else {
	    			alert('invalid');
	    		}
    		}
    	});
    })
   var current = new Date();
   // var currentTime = current.getHours()+":"+current.getMinutes();
   var currentDate = (current.getMonth()+1)+"/"+current.getDate()+"/"+current.getFullYear();
   $('#application_date').css('width','30%').val(currentDate).attr('readonly', true);
    $('#lastname').css('width', '67%');

   if(module.get_value('view') == "index"){
    $('<input type="text" class="input-text" value="" id="aux" name="aux" style="width:100px">').insertAfter('#lastname');
   
       $('select[name="test_profile[exam_type][]"]').live('change',function(){
              
                var parent_elem = $(this).closest('.form-multiple-add');                
                if ($(this).val() == 'Professional Exam'){
                    $(parent_elem).find('#exam_title_id').show();
                    $(parent_elem).find('#license_no').show();
                    $(parent_elem).find('#exam_title').hide();
                }
                else if ($(this).val() == 'Government Examination'){
                    $(parent_elem).find('#exam_title').show();
                    $(parent_elem).find('#exam_title_id').hide();
                    $(parent_elem).find('#license_no').hide();                    
                }
                else{
                    $(parent_elem).find('#exam_title_id').hide();
                    $(parent_elem).find('#license_no').hide();
                    $(parent_elem).find('#exam_title').hide();                    
                }
            });

            $('select[name="education[education_level][]"]').live('change', function () {
                if ($(this).val() == '') {
                    return;
                }
                change_educ_level ($(this));
            }); 

        $('#expected_salary_range').parent().parent().hide();
        var select = '<span class="select-input-wrap"><select id="expected_salary_range_temp" style="width:25%">'+
                        '<option value="">Select...</option>'+
                        '<option value="hourly">Hourly</option>'+
                        '<option value="monthly">Monthly</option>'+
                      '</select> </span>';
        $('#expected_salary').css('width','60%');   
        $(select).insertAfter('#expected_salary');

         $("#expected_salary_range_temp").change(function() {
             var value = $(this).val();
             $("#expected_salary_range").val(value);

         })
    }

	if(module.get_value('view') == "edit" || module.get_value('view') == "index"){
		 $("#sss").mask("99-9999999-9", {placeholder: "x"});
		 $("#philhealth").mask("99-999999999-9",{placeholder: "x"});
		 $("#tin").mask("999-999-999-999", {placeholder: "x"});
         $("#pagibig").mask("9999-9999-9999", {placeholder: "x"});
         $("#home_phone").live('keyup', maskNum);
         $("#alternate_home_phone").live('keyup', maskNum);
         $("#mobile").live('keyup', maskNum);
         $("#alternate_mobile").live('keyup', maskNum);
	}
});

function ajax_application_save( on_success, is_wizard , callback ){
    if( is_wizard == 1 ){
        var current = $('.current-wizard');
        var fg_id = current.attr('fg_id');
        var ok_to_save = eval('validate_fg'+fg_id+'()')
    }
    else{
        ok_to_save = validate_form();
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

        var data = $('#record-form').serialize();
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
                if(  data.record_id != null ){
                    //check if new record, update record_id
                    if($('#record_id').val() == -1 && data.record_id != ""){
                        $('#record_id').val(data.record_id);
                        $('#record_id').trigger('change');
                        if( is_wizard == 1 ){ 
                            message_growl('success', 'Your Application is successfully save');
                            setTimeout(window.location = module.get_value('base_url') + module.get_value('module_link'),5000);
                            // setTimeout(window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id,5000);
                        }
                    }
                    else{
                        $('#record_id').val( data.record_id );
                    }
                }

                if( data.msg_type != "error"){                  
                    switch( on_success ){
                        case 'back':
                            go_to_previous_page( data.msg );
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
                                            //show_saving_blockui();
                                        },                              
                                    success: function () {
                                    }
                                });
                            }                           
                            //custom ajax save callback
                            //if (typeof(callback) == typeof(Function)) callback( data );
                        default:
                            if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
                                    window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                            }
                            else{
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

function prompt_applicant(on_success, is_wizard , callback){

    Boxy.ask("Are you sure you want to submit your application?", ["Yes", "No"],function( choice ) {
    if(choice == "Yes"){
          
            ajax_application_save(on_success, is_wizard , callback);

        }
    },
    {
        title: "Submit Application"
    });

}
         function change_educ_level (educ_level){

            if (educ_level.val() == 8) {
                educ_level.parents('div.form-multiple-add')
                    .find('input[type="radio"]').parent().parent()
                    .addClass('hidden').val('');
            } else {
                educ_level.parents('div.form-multiple-add')
                    .find('input[type="radio"]').parent().parent()
                    .removeClass('hidden');        
            }

            if (educ_level.val() == 9 || educ_level.val() == 8) { //highschool, elementary
                educ_level.parents('div.form-multiple-add')
                    .find('select[name="education[employee_degree_obtained_id][]"], select[id="education_school"]').parent().parent()
                    .addClass('hidden');

                educ_level.parents('div.form-multiple-add')
                    .find('select[name="education[employee_degree_obtained_id][]"], select[id="education_school"]')
                    .val('');

                educ_level.parents('div.form-multiple-add')
                    .find('input[name="education[school][]"]').parent().parent()            
                    .removeClass('hidden');

                educ_level.parents('div.form-multiple-add')
                    .find('input[name="education[school][]"]')
                    .val('');

            } else {
                educ_level.parents('div.form-multiple-add')
                    .find('select[name="education[employee_degree_obtained_id][]"], select[id="education_school"]').parent().parent()            
                    .removeClass('hidden');

                educ_level.parents('div.form-multiple-add')
                    .find('input[name="education[school][]"]').parent().parent()
                    .addClass('hidden');

                educ_level.parents('div.form-multiple-add')
                    .find('input[name="education[school][]"]')
                    .val(' ');
            }
         } 

function appform_prev_app(response) 
{
	if (response.blacklisted) {
		$.blockUI({message: '<div class="now-loading align-center">Blacklisted Applicant.</div>'});
	} else {
		$.blockUI({message: '<div class="now-loading align-center"><small>Your information was currently in the system. Please verify this to the Recruitment Officer. </small><hr>Enter Applicant Code: <input name="uin" type="text" /><br /><br /><input type="button" id="uinsub" value="Submit" /><br /><br /></div>'});
	}				
}

function validate_fg176() {return true;}
function validate_fg374() {return true;}

function validate_fg590() {

   validate_email("referral[email][]", "Email");
    //errors
    if(error.length > 0){
        var error_str = "Please correct the following errors:<br />";
        for(var i in error){
            if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
            error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
        }
        $('#message-container').html(message_growl('error', error_str));

        //reset errors
        error = new Array();
        error_ctr = 0
        return false;
    }else{
        //no error occurred
        return true;    
    }
}

