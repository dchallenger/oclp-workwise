var employee_module_link = 'employees', schedule_boxy;
// var global_rehire = 0;
$(document).ready(function () { 
    $('#benefits').attr('readonly', true);
    $('.icon-16-xgreen-orb').live('click', function(){
        toggle_active("xactive", $(this).parent().parent().parent().attr("id"),$(this))                                            
    });

    $('label[for="lbl_chkbox"]').append('<input type="checkbox" id="same_as_present" >Same as Present</input>');

    $('#same_as_present').live('click', function() {
        if($('#same_as_present').prop('checked'))
        {
            $('#perm_address1').val($('#pres_address1').val());
            $('#perm_address2').val($('#pres_address2').val());
            $('#perm_province').val($('#pres_province').val());
            $('#perm_zipcode').val($('#pres_zipcode').val());
            $('#perm_city').val($('#pres_city').val());
            $('#perm_city').trigger('liszt:updated');
        }
    });
    // added for rehire
    $('.icon-16-rehire').live('click', function(){
        record_action("edit/rehire", $(this).parent().parent().parent().attr("id"), $(this).attr('module_link'));
    });
    // added for rehire

    if(module.get_value("view") == "edit") {
        $('#family_benefit').multiselect().multiselectfilter({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        }); 

        $('input[name="same_address"]').live('click',function(){

            if( $(this).attr('checked') == 'checked' ){

                $('#perm_address1').val( $('#pres_address1').val() );
                $('#perm_address2').val( $('#pres_address2').val() );
                $('#perm_city').val( $('#pres_city').val() );
                $('#perm_city').trigger("liszt:updated");
                $('#perm_province').val( $('#pres_province').val() );
                $('#perm_zipcode').val( $('#pres_zipcode').val() );



            }
            else{

                $('#perm_address1').val('');
                $('#perm_address2').val('');
                $('#perm_city').val('');
                $('#perm_city').trigger("liszt:updated");
                $('#perm_province').val('');
                $('#perm_zipcode').val('');

            }

        });

        if($('#record_id').val() != -1)
        {
            url = window.location.pathname;
            var code = url.split('/');
            if($.inArray("rehire", code) > 0)
            {
                $('#record_id').val("-1");
                $('#id_number').val("");
                $('#biometric_id').val("");
                $('#original_hired_date').val($('#employed_date').val());
                $('#original_hired_date-temp').val($('#employed_date-temp').val());
                $('#employed_date').val("");
                $('#employed_date-temp').val("");
                $('#status_effectivity').val("");
                $('#status_effectivity-temp').val("");
                $('#regular_date').val("");
                $('#regular_date-temp').val("");
                $('#lastname').val($('#lastname').val().replace(' *', ''));

                // $('#status_id').find('option').remove();
                // options.append($('<option></option>').val(index).text(item));
            } else {
                var emp_id = "employee_id="+$('#record_id').val()   
                $.ajax({
                    url: module.get_value('base_url')+module.get_value('module_link')+"/is_resigned",
                    data: emp_id,
                    type: "post",
                    success: function(response)
                    {
                        if(response.is_resigned)
                        {
                            $('#status_id').find('option').remove();
                            var dropdown = response.dropdown;
                            var drpdwn_value = response.data;
                            $('#status_id').append($('<option></option>').val('0').text("Select..."));
                            for(var ctr in dropdown)
                                $('#status_id').append($('<option></option>').val(dropdown[ctr].employment_status_id).text(dropdown[ctr].employment_status));
                            $('#status_id').val(drpdwn_value.status_id);
                        }
                    }
                });
            }
        }
        else{
            $.ajax({
                url: module.get_value('base_url')+module.get_value('module_link')+"/get_employee_id_no",
                type: "post",
                dataType: 'json',
                success: function(response)
                {
                    if (response.msg_type != 'error'){
                        $('#id_number').val(response.employee_id_last_series);
                        $('<input name="last_series_no" type="hidden" value="'+response.last_series+'">').insertAfter('#record_id');
                    }
                    else{
                        $('#id_number').val('');
                        //$('#message-container').html(message_growl(response.msg_type, response.msg));                        
                    }
                }
            });            
        }

        $('label[for="permanent_address"]').append('<input type="checkbox" id="same_as_present" >Same as Present</input>');

        $('#same_as_present').live('click', function() {
            if($('#same_as_present').prop('checked'))
            {
                $('#perm_address1').val($('#pres_address1').val());
                $('#perm_address2').val($('#pres_address2').val());
                $('#perm_province').val($('#pres_province').val());
                $('#perm_zipcode').val($('#pres_zipcode').val());
                $('#perm_city').val($('#pres_city').val());
                $('#perm_city').trigger('liszt:updated');
                $('#pres_province_id').val($('#perm_province_id').val());
            }
        });
    }

    $('#criteria').live('change', function() {
        if ($(this).val() != '') {
            $.ajax({
                url: module.get_value('base_url') + 'employees/get_query_fields2',
                type: 'post',
                dataType: 'json',
                data: 'export_query_id=' + $('#quick_export_query').val() + '&criteria=' + $(this).val(),
                success: function(response) {

                        $('#field-container').empty();
                        $('#field-container').html(response.html);
                        $('#export-buttons').removeClass('hidden');
                        $('#export-form').attr('action',module.get_value('base_url') + 'employees/export2');
                }
            });
        }
        else{
            $('#field-container').empty();
            $('#export-buttons').addClass('hidden');
        }
    });

    $('.quickclaim_received').live('click', function () {

        $.ajax({
            url: module.get_value('base_url') + 'employees/quitclaim_received',
            data: 'record_id=' + $(this).attr('employee_id'),
            type: 'post',
            dataType: 'json',
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });         
            },
            success: function(response){
                $.unblockUI();
                $('#message-container').html(message_growl(response.msg_type, response.msg));
                 window.location.reload( false );
                
            }
        });
    });

    $('.module-export-employees').live('click', function () {
        $.ajax({
            url: module.get_value('base_url') + 'employees/module_export_options',
            data: 'module_id=' + module.get_value('module_id'),
            type: 'post',
            dataType: 'json',
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });         
            },
            success: function(data){
                $.unblockUI();
                
                if(data.html != ""){
                    var width = $(window).width()*.7;
                    quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:450px;">'+ data.html +'</div>',
                    {
                        title: 'Select Export Type',
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
    });

$('.no_work_experience').live('click', function() {

        if( $(this).val() == '1' ){
            $('input[name^="employment["]').removeAttr('disabled');
            $('.form-multiple-add-employment').find('.add-more-flag').val('employment');
            $('.add-more-div a.add-more').attr('rel', 'employment');
            $('input[name="working_since"]').val('');
            $('.working_since_container').show('');

            $('.form-multiple-add').each(function(){

                $(this).parent().show();

            });

        }
        else{
            $('.add-more-div a.add-more').removeAttr('rel');
            $('.form-multiple-add-employment').find('.add-more-flag').val('');
            $('input[name="working_since"]').val('');
            $('.working_since_container').hide('');

            $('.form-multiple-add').each(function(){

                $(this).parent().hide();

            });

        }

    }); 

    $('.cost_center_percentage').live('change',function(){

        get_total_weight();

        var parse = parseFloat($(this).val());

        $(this).val(parse);

    });


   window.onload = function(){

        /*
        if( $('.no_work_experience').val() == '1' ){
            $('input[name^="employment["]').removeAttr('disabled');
            $('.form-multiple-add-employment').find('.add-more-flag').val('employment');
            $('.add-more-div a.add-more').attr('rel', 'employment');
            $('.working_since_container').show('');

            $('.form-multiple-add').each(function(){

                $(this).parent().show();

            });

        }
        else{
            $('.add-more-div a.add-more').removeAttr('rel');
            $('.form-multiple-add-employment').find('.add-more-flag').val('');
            $('input[name="working_since"]').val('');
            $('.working_since_container').hide('');

            $('.form-multiple-add').each(function(){

                $(this).parent().hide();

            });

        }
        */

        if( module.get_value('view') == "my201" ){

            if( ( $('#enable_edit').length > 0 ) ){

                //disable all company fields
                $('#division_id').attr('disabled','disabled');
                $('#division_id').trigger("liszt:updated");

                $('#reporting_to').attr('disabled','disabled');
                $('#reporting_to').trigger("liszt:updated");

                $('#direct_subordinates').attr('disabled','disabled');
                $('#direct_subordinates').trigger("liszt:updated");

                $('#segment_1_id').attr('disabled','disabled');
                $('#segment_1_id').trigger("liszt:updated");

                $('#segment_2_id').attr('disabled','disabled');
                $('#segment_2_id').trigger("liszt:updated");

            }

        }


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

        if( user.get_value('post_control') != 1 && module.get_value('view') == 'edit' ){
             $('label[for="direct_subordinates"]').parent().remove();
        }

        $('#supervisor_id_chzn').css('width','87%');
        $('#supervisor_id_chzn').find('.chzn-drop').css('width','100%');
        $('#supervisor_id_chzn').find('.chzn-search input:text').css('width','92%');
        $('.chzn-container').css('width', '87%');
        $('.chzn-drop').css('width', '100%');
        $('.chzn-search input:text').css('width','92%');

        if(module.get_value('view') == 'edit'){

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

            $('#status_id').trigger('change');

            $('#employed_date-temp').addClass('date_hired');

            var middle_name = $('#middlename').val();
            $('#middleinitial').val(middle_name.substring(0,1)+'.');

            $('select[name="skill[skill_type_id][]"]').live('change',function(){
                var parent_elem = $(this).parent().parent().parent();                
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_skill_name',
                    data: 'skill_type_id=' + $(this).val(),
                    type: 'post',
                    dataType: 'json',
                    beforeSend: function(){
                        //$('#appraisal-template-criteria-container').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });          
                    },      
                    success: function (response) {
                        $(parent_elem).find('input[name="skill[skill_name][]"]').val(response.skill_name);
                    }
                });
            })

            $('.multi-select').multiselect().multiselectfilter({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1
            });  

            if( ( $('label[for="specific_religion"]').length > 0 ) ){

                if( $('#religion_id').val() != 5 ){

                    $('label[for="specific_religion"]').parent().hide();

                }

            }

            // for openaccess client
            if(module.get_value('client_no') == 2)
            {
                $('#id_number').keyup(function (){
                   $('#biometric_id').val($('#id_number').val());
                });
            }


             get_employee_type($('#rank_id').val(), 'edit', module.get_value('record_id'));
             $('#employee_type').parent().append('<input type="hidden" name="employee_type_id" id="employee_type_id" val="" />');
             if ($('#job_level').is('input:text')){
                $('#job_level').parent().append('<input type="hidden" name="job_level_id" id="job_level_id" val="" />');
             }
             if ($('#region_id').is('input:text')){
                $('#region_id').parent().append('<input type="hidden" name="region" id="region" val="" />');
             }             
             $('#range_of_rank').parent().append('<input type="hidden" name="range_of_rank_id" id="range_of_rank_id" val="" />');
             
            if( module.get_value('record_id') != -1 ){
                with_job_description(module.get_value('record_id'));
            }

            get_region_by_location($('#location_id').val(), 'edit', module.get_value('record_id'));

             if( $('#sss_existing_loan-yes').attr('checked') == "checked" ){

                $('#sss_current_balance').removeAttr('disabled');

             }
             else if( $('#sss_existing_loan-no').attr('checked') == "checked" ){

                $('#sss_current_balance').attr('disabled','disabled').css('opacity','0.5');

                $('#sss_balance_date-temp').parent().find('img').remove();
                $('#sss_balance_date-temp').replaceWith(function(){
                    return $('<input type="text" class="input-text" readonly="readonly" name="sss_balance_date-temp" style="width:30%; opacity:0.5;" id="sss_balance_date-temp" /><img id="sss_datebutton" class="sss_datebutton" src="http://localhost/hdi.pioneer/themes/slategray/icons/calendar-month.png" alt="" title="" style="display: none;">');
                });

             }


             if( $('#pagibig_existing_load-yes').attr('checked') == "checked" ){

                $('#pagibig_current_balance').removeAttr('disabled');

             }
             else if( $('#pagibig_existing_load-no').attr('checked') == "checked" ){

                $('#pagibig_current_balance').attr('disabled','disabled').css('opacity','0.5');;

                $('#pagibig_balance_date-temp').parent().find('img').remove();
                $('#pagibig_balance_date-temp').replaceWith(function(){
                    return $('<input type="text" class="input-text" readonly="readonly" name="pagibig_balance_date-temp" style="width:30%; opacity:0.5;" id="pagibig_balance_date-temp" /><img id="pagibig_datebutton" class="pagibig_datebutton" src="http://localhost/hdi.pioneer/themes/slategray/icons/calendar-month.png" alt="" title="" style="display: none;">');
                });

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
                        'scriptData': {module: module.get_value('module_link'), fullpath: module.get_value('fullpath'), path: "uploads/" + module.get_value('module_link'),field:"test_profile-photo"+i+"",text_id:""+i+""},
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
                        'onCancel': function (event, ID, fileObj, response, data)
                        {
                            var split_res = $(event.target).attr('rel');
                            $( '#result_attach'+split_res ).val('');
                            $(this).parent('#test_profile-photo'+split_res+'Queue').remove();
                        }
                    });
                }
            }

            if($('.count_attachment').val() != 0)
            {
                var count_file = $('.count_attachment').val();
                for(i=1; i<=count_file; i++)
                {
                    $('#attachment-photo'+i).uploadify({
                        'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
                        'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify3.php',
                        'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
                        'folder'    : 'uploads/' + module.get_value('module_link'),
                        'fileExt'   : '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
                        'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
                        'auto'      : true,
                        'method'    : 'POST',
                        'scriptData': {module: module.get_value('module_link'), fullpath: module.get_value('fullpath'), path: "uploads/" + module.get_value('module_link'),field:"attachment-photo"+i+"",text_id:""+i+""},
                        'onComplete': function(event, ID, fileObj, response, data)
                        {              
                            var split_res = response.split('|||||');
                            $('#dir_path'+split_res[1]).val(split_res[0]);
                            if(split_res[2] == 'image')
                            {
                                var img = '<div class="nomargin image-wrap"><img id="file-photo-'+ split_res[1] +'" src="' + module.get_value('base_url') + split_res[0] +'" width="100px"><div class="delete-image nomargin multi" field="dir_path'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';                            
                            }
                            else
                            {
                                var img = '<div class="nomargin image-wrap"><a id="file-photo-'+ split_res[1] +'" href="' + module.get_value('base_url') + split_res[0] + '" width="100px" target="_blank"><img src="' + module.get_value('base_url') + 'themes/slategray/images/file-icon-md.png"></a><div class="delete-image nomargin multi" field="dir_path'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';       
                            }
                            $('#photo-upload-container_2'+split_res[1]).html('');
                            $('#photo-upload-container_2'+split_res[1]).append(img);
                        },
                        'onError': function (event,ID,fileObj,errorObj) {
                            $('#error-photo').html(errorObj.type + ' Error: ' + errorObj.info);
                        },
                        'onCancel': function (event, ID, fileObj, response, data)
                        {
                            var split_res = $(event.target).attr('rel');
                            $( '#dir_path'+split_res ).val('');
                            $(this).parent('#attachment-photo'+split_res+'Queue').remove();                    
                        }
                    });
                }
            }

            var dummy_upload = module.get_value('base_url') + user.get_value('user_theme') +"/images/no-photo.jpg";

            $( ".image-wrap" ).live('mouseenter', function (){
                var src = $( this ).find('img').attr('src');             
                if( src != dummy_upload ) $( this ).find( ".delete-image" ).show();
            });
            
            $( ".image-wrap" ).live('mouseleave', function (){
                $( this ).find( ".delete-image" ).hide();
            });

            $('.delete-image').live('click', function(){
                var delete_button = $( this );
                var field = $( this ).attr('field');
                Boxy.ask("Are you sure you want to delete uploaded file?", ["Yes", "Cancel"],
                function( choice ) {
                    if(choice == "Yes"){                      
                        delete_button.parent().parent().find('#' + field ).val('');
                        delete_button.parent().remove();
                    }
                },
                {
                    title: "Delete Record"
                });
            });

            $('#religion_id').live('change',function(){

                if( $(this).val() == 5 ){
                    $('label[for="specific_religion"]').parent().show();
                }
                else{
                    $('label[for="specific_religion"]').next().find('input').val('');
                    $('label[for="specific_religion"]').parent().hide();
                }

            });

             //work assignment
            $('.assignment').live('click',function(){
                var assignment_type = $(this).val();
                var parent_elem = $(this).closest('.form-multiple-add');
                show_work_assignment(assignment_type,parent_elem);

                var elem_radio = $(this);
                var ctr = 0;
                $('.assignment').each(function(index,elem){
                    if ($(elem).attr("checked") == "checked" && $(elem).val() == 1) {
                        ctr++;
                        if (ctr == 2){
                            $('#message-container').html(message_growl('error', 'Only one primary can use.'));
                            $(elem_radio).next().next().attr("checked","checked");
                            $(elem_radio).next().next().trigger('click');
                        }
                    }
                }); 
            });

            $('.work_assignment').live('change',function(){
                var category_id = $(this).val();
                var parent_elem = $(this).closest('.form-multiple-add');
                assignment_category_show(category_id,parent_elem);      
            })  

            $('.project_name_id').live('change',function(){
                var parent_elem = $(this).closest('.form-multiple-add');
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_cost_code_division',
                    data: 'project_name_id=' + $(this).val(),
                    type: 'post',
                    dataType: 'json',
                    beforeSend: function(){
                        //$('#appraisal-template-criteria-container').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });          
                    },      
                    success: function (response) {
                        $(parent_elem).find('.cost_code').parent().parent().show();
                        $(parent_elem).find('.cost_code').val(response.cost_code);
                        $(parent_elem).find('.division_id').val(response.division_id)
                    }
                });     
            })      

            $('.department_id').live('change',function(){
                var parent_elem = $(this).closest('.form-multiple-add');
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_department_code_group',
                    data: 'department_id=' + $(this).val(),
                    type: 'post',
                    dataType: 'json',
                    beforeSend: function(){
                        //$('#appraisal-template-criteria-container').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });          
                    },      
                    success: function (response) {
                        $(parent_elem).find('.group_name_id').val(response.group_name_id)
                    }
                });     
            })

            $('.assignment').each(function(index,elem){
                var assignment = $(elem).val();
                var parent_elem = $(elem).closest('.form-multiple-add')

                if (assignment == 1){
                    reset_elem_case(parent_elem);
                }
                if ($(parent_elem).find('.work_assignment').val() == ''){
                    $(parent_elem).find('.work_assignment').parent().parent().hide();
                }
            });
            //work assignment

         }else if(module.get_value('view') == 'detail'){

            enable_status_options();

            if( $.trim($('label[for="religion_id"]').next().text()) != 'Others' ){

                $('label[for="specific_religion"]').parent().hide();

            }

            get_employee_type('', 'detail', module.get_value('record_id'));
            get_rank_range('', 'detail', module.get_value('record_id'));
            get_region_by_location('', 'detail', module.get_value('record_id'));
            ($('label[for="range_of_rank"]').parent().find("div.text-input-wrap").text() == 0 ? $('label[for="range_of_rank"]').parent().find("div.text-input-wrap").text(" ") : $('label[for="range_of_rank"]').parent().find("div.text-input-wrap").text());
         }
         else if(module.get_value('view') == 'my201'){
            get_employee_type('', 'detail', user.get_value('user_id'));
            get_rank_range('', 'detail', user.get_value('user_id'));
            ($('label[for="range_of_rank"]').parent().find("div.text-input-wrap").text() == 0 ? $('label[for="range_of_rank"]').parent().find("div.text-input-wrap").text(" ") : $('label[for="range_of_rank"]').parent().find("div.text-input-wrap").text());

            //tirso : for hdi not to display rank if not hr
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_type',
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response.client == "hdi"){
                        if (response.user_type == 0){
                            $('label[for="rank_id"]').next().hide();
                        }
                        else{
                            $('label[for="rank_id"]').next().show();
                        }
                    }
                }
            }); 
            //tirso : for hdi not to display rank if not hr             
         }

         $('#sss_existing_loan-yes').click(function(){

            $('#sss_current_balance').removeAttr('disabled').css('opacity','1');

             $(this).parent().parent().parent().find('#sss_balance_date-temp').replaceWith(function(){
                var sample_datepicker =  $('<input type="text" class="input-text date_from" name="sss_balance_date-temp" id="sss_balance_date-temp" />').datepicker( {
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
                            }
                        }).val($(this).val());    

                        return sample_datepicker;
            });

            $(this).parent().parent().parent().find('#sss_balance_date-temp').parent().find('img').show();

  
            $('#sss_datebutton').click(function(){

                $(this).parent().find('#sss_balance_date-temp').trigger('focus');

            });
            

         });

         $('#sss_existing_loan-no').click(function(){

            $('#sss_current_balance').attr('disabled','disabled').css('opacity','0.5');;

            $(this).parent().parent().parent().find('#sss_balance_date-temp').parent().find('img').remove();
            $(this).parent().parent().parent().find('#sss_balance_date-temp').replaceWith(function(){
                return $('<input type="text" class="input-text" readonly="readonly" name="sss_balance_date-temp" style="width:30%; opacity:0.5;" id="sss_balance_date-temp" /><img id="sss_datebutton" class="sss_datebutton" src="http://localhost/hdi.pioneer/themes/slategray/icons/calendar-month.png" alt="" title="" style="display: none;">');
            });
            
         });

        

         $('#pagibig_existing_load-yes').click(function(){

            $('#pagibig_current_balance').removeAttr('disabled').css('opacity','1');

            $(this).parent().parent().parent().find('#pagibig_balance_date-temp').replaceWith(function(){
                var sample_datepicker =  $('<input type="text" class="input-text date_from" name="pagibig_balance_date-temp" id="pagibig_balance_date-temp" />').datepicker( {
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
                            }
                        }).val($(this).val());    

                        return sample_datepicker;
            });

            $(this).parent().parent().parent().find('#pagibig_balance_date-temp').parent().find('img').show();

            $('#pagibig_datebutton').click(function(){

                $(this).parent().find('#pagibig_balance_date-temp').trigger('focus');

            });

         });

         $('#pagibig_existing_load-no').click(function(){

            $('#paibig_current_balance').attr('disabled','disabled').css('opacity','0.5');;

            $(this).parent().parent().parent().find('#pagibig_balance_date-temp').parent().find('img').remove();
            $(this).parent().parent().parent().find('#pagibig_balance_date-temp').replaceWith(function(){
                return $('<input type="text" class="input-text" readonly="readonly" name="pagibig_balance_date-temp" style="width:30%; opacity:0.5;" id="pagibig_balance_date-temp" /><img id="pagibig_datebutton" class="pagibig_datebutton" src="http://localhost/hdi.pioneer/themes/slategray/icons/calendar-month.png" alt="" title="" style="display: none;">');
            });
            
         });

         $('.pagibig_datebutton').click(function(){

            $(this).parent().find('#pagibig_balance_date-temp').trigger('focus');

        });

         $('#with_job_description-yes').click(function(){

                $('#job_level').removeAttr('disabled');
                $('#range_of_rank').removeAttr('disabled');
                $('#rank_code').removeAttr('disabled');

         });


         $('#with_job_description-no').click(function(){

            $('#job_level').attr('disabled','disabled');
                $('#range_of_rank').attr('disabled','disabled');
                $('#rank_code').attr('disabled','disabled');


         });

         $('#rank_id').change(function(){

            get_employee_type($(this).val(), 'edit', module.get_value('record_id'));

         });

         $('#location_id').change(function(){

            get_region_by_location($(this).val(), 'edit', module.get_value('record_id'));

         });

         $('#job_level').change(function(){

            get_rank_range($(this).val(), 'edit', module.get_value('record_id'));

         });

         $('#position_id').change(function(){
            get_rt_incumbent($(this).val());
         });

         init_datepick();

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

         $('.datebutton').click(function(){

            $(this).parent().find('#affiliates_date_resigned').trigger('focus');

        });           
    }

    $('#middlename').change(function(){

        var middle_name = $(this).val();
        $('#middleinitial').val(middle_name.substring(0,1)+'.');

    });

    $('.dependent_check').click(function(){
        if($(this).prop('checked')){
            $(this).val('1');
            $(this).parent().find('.dependent_value').val('1');
        }
        else{
            $(this).val('0');
            $(this).parent().find('.dependent_value').val('0');
        }
    });

    $('#status_id').live('change',function(){
        enable_status_options();
    });


    $('.date_hired, #terms').live('change',function(){

        compute_end_date();

    });

    // $('.hospitalization_dependents').click(function(){
    //     var immediate_family = $.trim($(this).parent().siblings('.relation_id').children('.select-input-wrap').find('select').val());
    //     if(immediate_family == "Guardian" || immediate_family == "Brother" || immediate_family == "Sister" || immediate_family == "spouse" || immediate_family == "" || immediate_family == null || immediate_family == "" || immediate_family == undefined)
    //     {
    //         $(this).attr('checked', false);
    //         setTimeout(function () {
    //             $(this).val('0');
    //             $(this).siblings('.dependent_value').val('0');
    //             },
    //           100
    //         );
    //         $('#message-container').html(message_growl('attention', 'hospitalization dependent must be an Immediate Family'));
    //     }
    // });

    $('.bir_dependents').click(function(){
        var age = get_age(  $(this).parent().siblings('.bday').children('.text-input-wrap').find('input').val() );
        if(age > 21 || age == null || age == "" || age == undefined || isNaN(age))
        {
            if (age != 0){
                $(this).attr('checked', false);
                $(this).parent().find('.dependent_value').val('0');
                $('#message-container').html(message_growl('attention', 'Bir dependent must be below 21'));
            }
        }
    });

    $('.radioG').live('click', function(){
        $(this).parents('div.form-multiple-add').find('input[name="education[degree][]"]').attr('readonly', false).val('');
    });

    $('.radioUG').live('click', function(){
        $(this).parents('div.form-multiple-add').find('input[name="education[degree][]"]').attr('readonly', true).val('');
    });

    setTimeout(function(){
        if( module.get_value('view') == "edit" ){
            $('label[for="birth_date"]').next().append('<span class="calculatedage"></span>');
            $('label[for="employed_date"]').next().append('<span class="calculatedservice"></span>');
            $('input[name="family[birth_date][]"]').each(function(){
                if( $(this).val() != "" ){
                var age = get_age(  $(this).val() );
                    $(this).next().next().html('&nbsp;and is now&nbsp;<u>&nbsp;'+age+"&nbsp;</u>&nbsp;years of age") 
                }
            });

            // $('#fg-315').remove();
            if(module.client_no != 1 || module.client_no != 2)
            {
                $('.icon-16-listback').replaceWith('<a class="icon-16-listback" href="javascript:void(0);"><span>Back to list</span></a>');
                $('.icon-16-listback').live('click', function() {
                    Boxy.ask("Are you sure you want to exit without saving?", ["Yes", "No"],function( choice ) {
                    if(choice == "Yes"){
                            window.location = module.get_value('base_url') + module.get_value('module_link');   
                        }
                    },
                    {
                        title: "Prompt Message"
                    });
                });
            }
        }

        if( module.get_value('view') == "edit" ){
            $('.spouseshow').show();
            //$('label[for="birth_date"]').next().append('<span class="calculatedage"></span>');
/*            $('select[name="family[relationship][]"]').each(function(){
                if($('select[name="family[relationship][]"]').val()=="Spouse")
                    $(this).parent().parent().parent().find('.spouseshow').show();
                else
                {
                    $(this).parent().parent().parent().find('.spouseshow').find('input').val('');
                    $(this).parent().parent().parent().find('.spouseshow').hide();
                }
                // var age = get_age(  $(this).val() );
            });*/
        }

        if( module.get_value('view') == "detail" ){
            $('label[for="family[birth_date][]"]').each(function(){

                if( $(this).next().html() != "" ){
                    var age = get_age(  $(this).next().html() );
                    $(this).next().append('&nbsp;and is now&nbsp;<u>&nbsp;'+age+"&nbsp;</u>&nbsp;years of age") 
                }

            });
        }
        $('#birth_date-temp').live('change', age_field);
        if( module.get_value('view') != "index" && module.get_value('record_id') != "-1" ){
            age_field();
        }
		
        $('input[name="family[birth_date][]"]').live('change', function(){
            var age = get_age(  $(this).val() );
            $(this).next().next().html('&nbsp;and is now&nbsp;<u>&nbsp;'+age+"&nbsp;</u>&nbsp;years of age")  
        });


        $('#employed_date-temp').live('change', service_field);
        if( module.get_value('view') != "index" && module.get_value('record_id') != "-1" ){
            service_field();
        }


/*        $('select[name="family[relationship][]"]').live('change', function(){
            // $('select[name="family[relationship][]"]').each(function(){
                if($(this).val()=="Spouse")
                    $(this).parent().parent().parent().find('.spouseshow').show();
                else
                {
                    $(this).parent().parent().parent().find('.spouseshow').find('input').val('');
                    $(this).parent().parent().parent().find('.spouseshow').hide();
                }
            // });
        });*/

        $('#referred_by_id').live('change', function(){
			$('#referred_by').attr('disabled', 'disabled');
			$('#referred_by_employee_id').attr('disabled', 'disabled');
			$('#referred_by_others').attr('disabled', 'disabled');
			switch($(this).val()){
				case '1':
					$('#referred_by').removeAttr('disabled');
					$('#referred_by_employee_id').removeAttr('disabled');
					$('#referred_by_others').val('');
					break;
				case '2':
					$('#referred_by_others').removeAttr('disabled');
					$('#referred_by').val('');
					$('#referred_by_employee_id').val('');
					break;
				default:
					$('#referred_by_others').val('');
					$('#referred_by').val('');
					$('#referred_by_employee_id').val('');
					break;	
			}
		});
		$('#referred_by_id').trigger('change');

        if($("#sss").size() > 0){
             $("#sss").mask("99-9999999-9", {placeholder: "x"});
             $("#philhealth").mask("99-999999999-9",{placeholder: "x"});
             $("#tin").mask("999-999-999-999", {placeholder: "x"});
             $("#pagibig").mask("9999-9999-9999", {placeholder: "x"});
        }         
	}, 100);
    
	init_datepick();
    
    // Remove options for department and position dropdowns, need it to be dynamic.
    if( $('#record_id').val() == '-1') $('select[name="department_id"]').find('option').remove();
    
    $('select[name="company_id"]').die('change');    
    $('select[name="company_id"]').live('change', function () {        
        populate_company_relations($(this).val());
    });
    
    if ($('select[name="company_id"]').val() > 0) {
        populate_company_relations($('select[name="company_id"]').val());
    }
    
    $('#employees-quick-edit-form input[name="applicant_id"]').die('change').live('change', populate_form);

    $('a.delete-detail').live('click', function () {
        $(this).parents('div.form-multiple-add').remove();
    });

    //segment division
    $('#department_id').change(function () {
        get_division_segment($('#department_id').val());
    });
    //segment division
    
/*    $('.add-more').live('click',function(){     
        var val = parseFloat($('#array_incrementation').val());
        var total_val = val + 1;

        $('#array_incrementation').val(total_val);

        alert($('#multiple-form-container').children().find('input[name="work assignment[assignment]"]').length);
        
        if ($('#multiple-form-container').children().find('input[name="work assignment[assignment]"]').length > 0){
            if ($('.tmp_locator').length > 0){
                var new_name = $('input[name="work assignment[assignment]"]').attr('name') + '['+total_val+']';
                $('input[name="work assignment[assignment]"]').attr('name',new_name)
            }    
        }     
    });
*/
    $('a.add-more').click(function(event) {
        event.preventDefault();
        var obj = $(this);
        var form_to_get = $(this).attr('rel');
        url = module.get_value('base_url') + module.get_value('module_link') + '/get_form/' + $(this).attr('rel');
        var data = '';
        var elem = $(this);
        if($(this).attr('rel') == 'attachment')
        {
            data = 'counter_line='+(parseFloat($('.count_attachment').val())+1);
            $('.count_attachment').val(parseFloat($('.count_attachment').val())+1);
        }
        else if($(this).attr('rel') == 'test_profile')
        {
            data = 'counter_line='+(parseFloat($('.count_test_profile').val())+1);
            $('.count_test_profile').val(parseFloat($('.count_test_profile').val())+1);
        }
        else
        {
            data = 'counter_line=0';
        }
        $.ajax({
            url: url,
            dataType: 'html',
            type:"POST",
            data: data,
            success: function (response) {                
                //$('.current-wizard .form-head').after(response);
                if (module.get_value('module_link') == 'recruitment/appform') {
                    response = '<fieldset>' + response + '</fieldset><div class="spacer"></div>';
                }

                $('.form-multiple-add-' + form_to_get).prepend(response);                

                $('.current-wizard').find('input').first().focus();
                init_datepick();

               $('.dependent_check').click(function(){
                    if($(this).val()==0){
                        $(this).val('1');
                        $(this).parent().find('.dependent_value').val('1');
                    }
                    else{
                        $(this).val('0');
                        $(this).parent().find('.dependent_value').val('0');
                    }
                });

                $('#family_benefit').multiselect().multiselectfilter({
                    show:['blind',250],
                    hide:['blind',250],
                    selectedList: 1
                });                

/*                $('#education_school').multiselect().multiselectfilter({
                    show:['blind',250],
                    hide:['blind',250],
                    selectedList: 1
                });  */ 

                if ($('#no_family').length > 0){
                    var val = parseFloat($('#no_family').val());
                    var total_val = val + 1;

                    $('#no_family').val(total_val);
                    var benefit_name = $('#family_benefit').attr('name') + '['+val+'][]';
                    $('#family_benefit').attr('name',benefit_name)
                }

                if ($('#no_education').length > 0){
                    var val = parseFloat($('#no_education').val());
                    var total_val = val + 1;

                    $('#no_education').val(total_val);
                    var school_name = $('#education_school').attr('name') + '['+val+'][]';
                    $('#education_school').attr('name',school_name)
                }

                //work assignment tirso
                if ($('#no_work_assignment').length > 0){
                    var val = parseFloat($('#no_work_assignment').val());                                    
                    var total_val = val + 1;

                    $('#no_work_assignment').val(total_val);
                    var assignment_name = $('input[name="work_assignment[assignment]"]').attr('name') + '['+total_val+']';
                    $('input[name="work_assignment[assignment]"]').attr('name',assignment_name);
                    $('#employee_work_assignment_category_id').attr('id','employee_work_assignment_category_id'+total_val+'')
                }

                var val = parseFloat($('#no_work_assignment').val());                                    
/*                $('label[for="work_assignment[assignment]"]').parent().nextAll().hide();*/
                $('input[name="work_assignment[assignment]['+val+']"]').parent().nextAll().hide();                                      
                //work assignment

                // $('.hospitalization_dependents').click(function(){
                //     var immediate_family = $.trim($(this).parent().siblings('.relation_id').children('.select-input-wrap').find('select').val());
                //     if(immediate_family == "Guardian" || immediate_family == "Brother" || immediate_family == "Sister" || immediate_family == "spouse" || immediate_family == "" || immediate_family == null || immediate_family == "" || immediate_family == undefined)
                //     {
                //         $(this).attr('checked', false);
                //         setTimeout(function () {
                //             $(this).val('0');
                //             $(this).siblings('.dependent_value').val('0');
                //             },
                //           100
                //         );
                //         $('#message-container').html(message_growl('attention', 'hospitalization dependent must be an Immediate Family'));
                //     }
                // });

                 $('.bir_dependents').click(function(){
                    var age = get_age(  $(this).parent().siblings('.bday').children('.text-input-wrap').find('input').val() );
                    if(age > 21 || age == null || age == "" || age == undefined)
                    {
                        if (age != 0){
                            $(this).attr('checked', false);
                            $(this).parent().find('.dependent_value').val('0');
                            $('#message-container').html(message_growl('attention', 'Bir dependent must be below 21'));
                        }
                    }
                });

                $('.radioG').live('click', function(){
                    $(this).parents('div.form-multiple-add').find('input[name="education[degree][]"]').attr('readonly', false).val('');
                });

                $('.radioUG').live('click', function(){
                    $(this).parents('div.form-multiple-add').find('input[name="education[degree][]"]').attr('readonly', true).val('');
                });

                // $('input[name="education[graduate]"]').click(function(){
                //     if($(this).hasClass('radioG'))
                //         $(this).parents('div.form-multiple-add').find('input[name="education[degree][]"]').attr('readonly', false).val('');
                //     else
                //         $(this).parents('div.form-multiple-add').find('input[name="education[degree][]"]').attr('readonly', true).val('');
                // });

                 $('select[name="education[education_level][]"]').live('change', function () {
                    if ($(this).val() == '') {
                        return;
                    }

                    if ($(this).val() == 8) {
                        $(this).parents('div.form-multiple-add')
                            .find('input[type="radio"]').parent().parent()
                            .addClass('hidden').val('');
                    } else {
                        $(this).parents('div.form-multiple-add')
                            .find('input[type="radio"]').parent().parent()
                            .removeClass('hidden');        
                    }

                    if ($(this).val() == 9 || $(this).val() == 8) {
                        $(this).parents('div.form-multiple-add')
                            .find('input[name="education[degree][]"], input[name="education[course][]"]').parent().parent()
                            .addClass('hidden');

                        $(this).parents('div.form-multiple-add')
                            .find('input[name="education[degree][]"], input[name="education[course][]"]')
                            .val('');
                    } else {
                        $(this).parents('div.form-multiple-add')
                            .find('input[name="education[degree][]"], input[name="education[course][]"]').parent().parent()            
                            .removeClass('hidden');

                        // $(this).parents('div.form-multiple-add')
                        //     .find('input[name="education[degree][]"]').attr('readonly', 'readonly');
                    }
                });

                // $('select[name="education[education_level][]"]').trigger('change');

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

                $('#test_profile-photo'+$('.count_test_profile').val()).uploadify({
                    'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
                    'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify3.php',
                    'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
                    'folder'    : 'uploads/' + module.get_value('module_link'),
                    'fileExt'   : '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
                    'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
                    'auto'      : true,
                    'method'    : 'POST',
                    'scriptData': {module: module.get_value('module_link'), fullpath: module.get_value('fullpath'), path: "uploads/" + module.get_value('module_link'),field:"test_profile-photo",text_id:""+$('.count_test_profile').val()+""},
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

                $('#attachment-photo'+ $('.count_attachment').val()).uploadify({
                    'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
                    'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify3.php',
                    'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
                    'folder'    : 'uploads/' + module.get_value('module_link'),
                    'fileExt'   : '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
                    'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
                    'auto'      : true,
                    'method'    : 'POST',
                    'scriptData': {module: module.get_value('module_link'), fullpath: module.get_value('fullpath'), path: "uploads/" + module.get_value('module_link'),field:"attachment-photo",text_id:""+$('.count_attachment').val()+""},
                    'onComplete': function(event, ID, fileObj, response, data)
                    {       
                        var split_res = response.split('|||||');
                        $('#dir_path'+split_res[1]).val(split_res[0]);
                        if(split_res[2] == 'image')
                        {
                            var img = '<div class="nomargin image-wrap"><img id="file-photo-'+ split_res[1] +'" src="' + module.get_value('base_url') + split_res[0] +'" width="100px"><div class="delete-image nomargin multi" field="dir_path'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';                           
                        }
                        else
                        {
                            var img = '<div class="nomargin image-wrap"><a id="file-photo-'+ split_res[1] +'" href="' + module.get_value('base_url') + split_res[0] + '" width="100px" target="_blank"><img src="' + module.get_value('base_url') + 'themes/slategray/images/file-icon-md.png"></a><div class="delete-image nomargin multi" field="dir_path'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';                                   
                        }
                        $('#photo-upload-container_2'+split_res[1]).html('');                       
                        $('#photo-upload-container_2'+split_res[1]).append(img);
                    },
                    'onError': function (event,ID,fileObj,errorObj) {
                        $('#error-photo').html(errorObj.type + ' Error: ' + errorObj.info);
                    },
                    'onCancel': function ()
                    {
                        var split_res = $(event.target).attr('rel');
                        $( '#dir_path'+split_res ).val('');
                        $(this).parent('#attachment-photo'+split_res+'Queue').remove();                   
                    }
                });

                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + "/get_clearance_approver",
                    dataType: 'html',
                    success: function (response) {
                        $('#clearance_approver').html(response);              
                    }
                });
            }
        });
    });  
    
    $('#fglabel_span').text($('.wizard-active .wizard-label').text());

    $('a.leftcol-control').live('click', function () {
        $('#fglabel_span').text($('.wizard-active .wizard-label').text());
    });

    $('#civil_status_id').click(function () {
        if ($(this).val() == 1) {
            $('#spouse_name, #spouse_work').attr('readonly', 'readonly');
            $('#spouse_name, #spouse_work').val('');
            $('#date_of_marriage-temp').datepicker( 'disable' );
        } else {
            $('#spouse_name, #spouse_work').removeAttr('readonly');
            $('#date_of_marriage-temp').datepicker( 'enable' );
        }
    });

    setTimeout(function () {
            if ($('#employees-quick-edit-form #record_id').val() == '-1') {
                if( $('#employees-quick-edit-form input[name="applicant_id"]').length == 0 ){
                   $('#employees-quick-edit-form').prepend('<input type="hidden" value="'+ $('#record-form #applicant_id').val() +'" name="applicant_id">');
                   $('#employees-quick-edit-form input[name="applicant_id"]').die('change').live('change', populate_form);
                }
                $('#employees-quick-edit-form input[name="applicant_id"]').trigger('change');
                $('#employees-quick-edit-form').prepend('<input type="hidden" value="true" name="page_refresh">');
            }        

            $('input[name="birth_date-temp"]').datepicker('option', 'yearRange', 'c-30:c+30');
            $('input[name="birth_date-temp"]').datepicker( "option", "maxDate", new Date() );
        },
        100
    );

    if($("#sss").size() > 0){
         $("#sss").mask("99-9999999-9", {placeholder: "x"});
         $("#philhealth").mask("99-999999999-9",{placeholder: "x"});
         $("#tin").mask("999-999-999-999", {placeholder: "x"});
         $("#pagibig").mask("9999-9999-9999", {placeholder: "x"});
    } 
		
	$('#referred_by_id').change(function(){
		$('#referred_by').attr('disabled', 'disabled');
		$('#referred_by_employee_id').attr('disabled', 'disabled');
		$('#referred_by_others').attr('disabled', 'disabled');
		switch($(this).val()){
			case '1':
				$('#referred_by').removeAttr('disabled');
				$('#referred_by_employee_id').removeAttr('disabled');
				$('#referred_by_others').val('');
				break;
			case '2':
				$('#referred_by_others').removeAttr('disabled');
				$('#referred_by').val('');
				$('#referred_by_employee_id').val('');
				break;
			default:
				$('#referred_by_others').val('');
				$('#referred_by').val('');
				$('#referred_by_employee_id').val('');
				break;	
		}
	});    

    if( module.get_value('view') == "my201" ){
        setTimeout(function () {
        //personal info
        disable_field( $('input[name="sex"]') );
        disable_field( $('#referred_by_id') );
        disable_field( $('#referred_by') );
        disable_field( $('#referred_by_others') );
      
        $('#record_id').parent().append('<input type="hidden" value="my201" name="my201">');

        $('span.fh-delete').remove();

        $('#fg-113 input').each(function(){
             disable_field( $(this) );
        });
        $('#fg-113 select').each(function(){
             disable_field( $(this) );
        });
        $('#fg-113 .ui-datepicker-trigger').remove();

        $('#fg-490 input').each(function(){
             disable_field( $(this) );
        });
        $('#fg-490 select').each(function(){
             disable_field( $(this) );
        });

        //my201 general information
        $('#fg-315 input,select').each(function(){
                disable_field( $(this) );
        });
        $("#position_id").trigger("liszt:updated");
        $('#fg-315 #nickname').attr('disabled',false);
        //my201 general information

        $('#fg-490 .ui-datepicker-trigger').remove();
        $('#fg-490 .add-more-div').parent().hide()

        $('#supervisor_id_chzn').remove();
        $('#supervisor_id').css('display', '');

        $('#job_rank_id_chzn').remove();
        $('#job_rank_id').css('display', '');
        }, 1000);

        if( user.get_value('post_control') != 1 ){
            $('label[for="critical"]').parent().remove();
        }
    }

    if( module.get_value('view') == "detail" || module.get_value('view') == "my201" ){
        $('.today-shift').removeClass('hidden').appendTo( $('label[for="shift_calendar_id"]').next() );
    }     


});

function get_region_by_location(location_id , type, record_id){
    $.ajax({
        url: module.get_value('base_url') + 'employees/get_region_by_location',
        type: 'post',
        dataType: 'json',
        data: 'location_id=' + location_id + '&record_id=' + record_id,
        success: function (response) {

            if( type == "edit" ){

                $('#region_id').val(response.region);
                $('#region').val(response.region_id);
            }
            else if( type == "detail" ){
                $('label[for="region_id"]').parent().find("div.text-input-wrap").html(response.region);
            }

        }
    });    
}
function get_division_segment(dept_id){
    var send='department_id='+dept_id;
    if(dept_id==""){
        $('#division_id').val('');
        $('#segment_1_id').val('');
        $('#segment_2_id').val('');
    }else{
    //alert(send);
        $.ajax({
        url: module.get_value('base_url') + 'employees/get_division_segment',
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
                $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                    // $("#division_id option[text=" +employee.division_id +"]").attr("selected","selected") ;
                    // $("#segment_1_id option[text=" +employee.division_id +"]").attr("selected","selected") ;
                    $('#division_id').val(employee.division_id);
                    $('#segment_1_id').val(employee.segment1_id);
                    $('#segment_2_id').val(employee.segment2_id);
                    //alert(employee.segment_1_id);
            }
        }
    });
    }
}

function get_employee_type(rank_id , type, record_id){

     $.ajax({
            url: module.get_value('base_url') + 'employees/get_employee_type',
            type: 'post',
            dataType: 'json',
            data: 'rank_id=' + rank_id + '&record_id=' + record_id,
            success: function (response) {

                if( type == "edit" ){
                    if (typeof response.employee_type === 'undefined'){
                        $('#employee_type').val();
                    }else{
                        $('#employee_type').val(response.employee_type);
                        // $('#benefits').val(response.benefits);
                    }
                    
                    $('#employee_type_id').val(response.employee_type_id);
                    if ($('#job_level_id').length > 0){
                        $('#job_level').val(response.rank_level);
                        $('#job_level_id').val(response.rank_level_id);
                    }

                }
                else if( type == "detail" ){

                    $('label[for="employee_type"]').parent().find("div.text-input-wrap").html(response.employee_type);
                     // $('label[for="benefits"]').parent().find("div.text-input-wrap").html(response.benefits);
                    if (response.job_level_auto){
                        $('label[for="job_level"]').parent().find("div.text-input-wrap").html(response.rank_level);
                    }
                }

            }
        });

}

function get_rank_range(rank_level, type, record_id){

     $.ajax({
            url: module.get_value('base_url') + 'employees/get_rank_range',
            type: 'post',
            dataType: 'json',
            data: 'rank_level=' + rank_level + '&record_id=' + record_id,
            success: function (response) {


                if( type == "edit"){
                    if(rank_level != "")
                    {
                        $('#range_of_rank').val(response.job_rank_range);
                        $('#range_of_rank_id').val(response.job_rank_range_id);
                    } else {
                        $('#range_of_rank').val('');
                        $('#range_of_rank_id').val('');
                    }
                }
                else if( type == "detail" ){

                    $('label[for="range_of_rank"]').parent().find("div.text-input-wrap").html(response.job_rank_range);

                }
                else if( type == "my201" ){

                    $('label[for="employee_type"]').parent().find("div.text-input-wrap").html(response.employee_type);
                    
                }

            }
        });

}

function with_job_description(record_id){

    $.ajax({
        url: module.get_value('base_url') + 'employees/with_job_description',
        type: 'post',
        dataType: 'json',
        data: '&record_id=' + record_id,
        success: function (response) {

            if( response.with_job_description == 1 ){
                $('#job_level').removeAttr('disabled');
                $('#range_of_rank').removeAttr('disabled');
                $('#rank_code').removeAttr('disabled');
                get_rank_range($('#job_level').val(), 'edit', module.get_value('record_id'));
            }
            else{
                $('#job_level').attr('disabled','disabled');
                $('#range_of_rank').attr('disabled','disabled');
                $('#rank_code').attr('disabled','disabled');
            }
        }
    });

}


function get_job_level_info(id,type){


         $.ajax({
            url: module.get_value('base_url') + 'employees/get_job_level_info',
            type: 'post',
            dataType: 'json',
            data: 'job_rank_id=' + id + '&type=' + type + '&record_id=' + module.get_value('record_id') ,
            success: function (response) {

                if( module.get_value('view') == "edit" ){

                    $('#rank').val(response.job_rank);
                    $('#range_of_rank').val(response.job_rank_range);
                    $('#rank_code').val(response.job_rank_code);
                }
                else{

                    if( response != 0 ){

                         $('label[for="rank"]').parent().find("div.text-input-wrap").html(response.job_rank);
                         $('label[for="range_of_rank"]').parent().find("div.text-input-wrap").html(response.job_rank_range);
                         $('label[for="rank_code"]').parent().find("div.text-input-wrap").html(response.job_rank_code);
                
                    }
                    else{

                         $('label[for="rank"]').parent().find("div.text-input-wrap").html();
                         $('label[for="range_of_rank"]').parent().find("div.text-input-wrap").empty();
                         $('label[for="rank_code"]').parent().find("div.text-input-wrap").empty();

                    }
                }
            }
        });
}

function populate_form() {

   
    if(module.get_value('client_no') == 2)
    {
         var applicant = $('#record-form input[name="applicant_id"]').val();
    }else{

       var applicant = $(this).val();
    }
    
       if (applicant <= 0) {
            return false;
        } 

    $.ajax({
        url: module.get_value('base_url') + 'recruitment/applicants/get_applicant_data',
        type: 'post',
        dataType: 'json',
        data: 'record_id=' + applicant,
        success: function (response) {
            if (response.type == 'error') {
                $('#message-container').html(message_growl('error', response.message));
            } else if (response != 0) {
                $.each(response.data, function (index, value) {
                    $('#employees-quick-edit-form #' + index).val(value);
                });

                if (response.data.sex == 'male') {
                    $('input[value="male"]').prop('checked', true);
                } else {
                    $('input[value="female"]').prop('checked', true);
                }
                // for openaccess client
                if(module.get_value('client_no') == 2)
                {
                    $('#id_number').keyup(function (){
                       $('#biometric_id').val($('#id_number').val());
                    });

                    $('#employees-quick-edit-form input[name="applicant_id"]').val(applicant);
                }
            

                $('#position_id').val(response.data.position_id);
                $('#position_id').siblings().find('span').replaceWith('<span>'+response.data.position_name+'</span>').text();
                $('#position_id_chzn').find('a').removeClass('chzn-default');

                birth_date = new Date(response.data.birth_date);                                
                $('input[name="birth_date-temp"]').datepicker('setDate', birth_date);
                
                residence_certno_date_of_issue = new Date(response.data.residence_certno_date_of_issue);
                if (!isNaN(residence_certno_date_of_issue.getMonth())) {
                    $('input[name="residence_certno_date_of_issue-temp"]').datepicker('setDate', residence_certno_date_of_issue);
                }

                date_of_marriage = new Date(response.data.date_of_marriage);
                if (!isNaN(date_of_marriage.getMonth())) {                    
                    $('input[name="date_of_marriage-temp"]').datepicker('setDate', date_of_marriage);
                }                

                employed_date = new Date(response.data.employed_date);                  

                if (!isNaN(employed_date.getMonth())) {                    
                    $('input[name="employed_date-temp"]').datepicker('setDate', employed_date);
                }

                application_date = new Date(response.data.application_date);                
                $('input[name="application_date-temp"]').datepicker('setDate', application_date);
            }
        }
    });              
}

function populate_company_relations(company_id) {
    
    // Departments dropdown populate.
    departments = module.get_value('base_url') + employee_module_link + '/get_company_departments';
    data = 'company_id=' + company_id + '&record_id=' + $('#record_id').val();
    
    $('select[name="department_id"]').find('option').remove();   
    $('select[name="department_id"]').
    append($("<option></option>").attr("value",'').text("Select..."));             
        
    $.ajax({
        url: departments,
        type: 'post',
        data: data,
        success: function (response) {                    
            // Append the new values to department dropdown.
            var selected_option = false;
            $.each(response.departments, function(index, value)
            {   
                selected_option = false;
                if (response.value['raw'] != undefined) {
                    var valuestemp = response.value['raw'];
                    var values = valuestemp.split(',');
                    for( var i in values ){
                        if(values[i] == value.department_id) selected_option = true;
                    }
                    
                }
                $('select[name="department_id"]').
                append($("<option></option>").attr('selected', selected_option).attr("value",value.department_id).text(value.department)); 
            });     
            
           
            $('select[id="department_id"]').trigger("liszt:updated");
        }                
    });        
}

function  validate_fg490(){
    //validate_mandatory_array('work_assignment[end_date][]', "End Date");
    validate_mandatory_array('work_assignment[start_date][]', "Start Date");    

    return process_errors();
}

function validate_fg493() {
    return true;
}

function validate_fg106() {
    validate_mandatory_array('education[school][]', "School");
    
    return process_errors();
}

function validate_fg352() {
    return true;
}

function validate_fg107() {
    return true;
}

function validate_fg291() {
    return true;
}

function validate_fg332() {
    return true;
}

function validate_fg339(){
    return true;
}

function validate_fg342(){
    return true;
}

function validate_fg343(){
    return true;
}

function validate_fg315(){
    return true;
}

function validate_fg375(){
    return true;
}

function validate_fg108() {
    validate_mandatory_array('family[name][]', "Famiy member's name");
    
    return process_errors();
}

function validate_fg109() {
    validate_mandatory("employment[company][]", "Company");

    var data = $('#record-form').serialize();

    if(module.get_value("view") == "edit") {
        $.ajax({
            url: module.get_value('base_url') +'employees/validate_employment_inclusive_dates',
            type: 'post',
            data: data,
            success: function (response) {

                if(response.msg_type == 'error'){

                    add_error('Inclusive Dates', 'Inclusive Dates', "Invalid Inclusive Dates.");

                }
            }

        });
    }
    
    return process_errors();
}

function validate_fg110() {
    return process_errors();
}

function validate_fg324() {
    validate_mandatory("test_result[test_taken][]", "Test Taken");
    validate_mandatory("test_result[date_taken][]", "Date Taken");
    validate_float("test_result[rate][]", "Rate");
    return process_errors();    
}

function validate_fg344(){
    return true;
}

function validate_fg487(){

    var count = 0;

}

function process_errors() {
    if(error.length > 0){
        var error_str = "Please correct the following errors:<br/><br/>";
        for(var i in error){
            if(i == 0) $('input[name="'+error[i][0] + '"]').focus(); //set focus on the first error
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

function age_field(){
    setTimeout(
        function () {

            if( module.get_value('view') == "detail" ){

                var age = get_age(  $('label[for="birth_date"]').next().html() );

                if ($('.calculatedage').length < 1){
                    $('label[for="birth_date"]').next().append('<span class="calculatedage"></span>');
                }
                
            }    

            if( module.get_value('view') == "edit" ){ 
                var age = get_age(  $('#birth_date').val() );

            }

             $('span.calculatedage').html('&nbsp;and is now&nbsp;<u>&nbsp;'+age+"&nbsp;</u>&nbsp;years of age")  

            
        },
        2000
    );
}

function service_field(){
    setTimeout(
        function () {

            if( module.get_value('view') == "detail" ){

                var service = get_service(  $('label[for="employed_date"]').next().html() );

                if ($('.calculatedservice').length < 1){
                    $('label[for="employed_date"]').next().append('<span class="calculatedservice"></span>');
                }
                
            }    

            if( module.get_value('view') == "edit" ){ 
                var service = get_service(  $('#employed_date').val() );

            }

             $('span.calculatedservice').html('&nbsp;and is now&nbsp;<u>&nbsp;'+service+"&nbsp;</u>&nbsp;years of service")  

            
        },
        2000
    );
}


function get_service(dateString) {
    var today = new Date();
    var birthDate = new Date(dateString);
    var age = today.getFullYear() - birthDate.getFullYear();

    var m = today.getMonth() - birthDate.getMonth();

    age = ( ( age * 12 ) + m ) / 12;

    return age.toFixed(2);
}

function get_age(dateString) {
    var today = new Date();
    var birthDate = new Date(dateString);
    var age = today.getFullYear() - birthDate.getFullYear();

    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function show_full_movement(emp_id)
{
    var send='employee_id='+emp_id;
    $.ajax({
        url: module.get_value('base_url') + 'employees/show_full_movement',
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
                $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                var width = $(window).width()*.5;
                width=width-150;
                var movementCtr=0;
                var html ='<table class="boxyTable" style="text-align:center;width:'+width+'px;"><thead style="background: #909090  ;font-weight:bolder;color:#fff;"><tr><td style="width:20%;padding:5px;border: solid 1px">Movement Type</td><td style="width:30%;padding:5px;border: solid 1px">From</td><td style="width:30%;padding:5px;border: solid 1px">To</td><td style="width:20%;padding:5px;border: solid 1px">Effectivity</td></tr></thead>';
                for(var i in employee){
                    //alert(i);
                    // if(employee[i].transfer_effectivity_date!= null || employee[i].transfer_effectivity_date!="0000-00-00" || employee[i].transfer_effectivity_date)!="1970-01-01")
                    // {
                            if(employee[i].new_department_name!=null && employee[i].transfer_to !=0)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_department_name != null ? employee[i].old_department_name : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_department_name != null ? employee[i].new_department_name : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';
                            //Added
                            if(employee[i].rank_id!=0 && employee[i].new_rank!=null)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_rank != null ? employee[i].old_rank : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_rank != null ? employee[i].new_rank : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';
                            // if(employee[i].employee_type!=null)
                            //     html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].old_employee_type+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].new_employee_type+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+$.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date))+'</td></tr>';
                            if(employee[i].job_level!=0 && employee[i].new_job_level!=null)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_job_level != null ? employee[i].old_job_level : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_job_level != null ? employee[i].new_job_level : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';
                            // if(employee[i].range_of_rank!=null)
                            //     html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].current_range_of_rank_dummy+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].range_of_rank+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+$.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date))+'</td></tr>';
                            if(employee[i].rank_code!=0 && employee[i].new_rank_code!=null)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_rank_code != null ? employee[i].old_rank_code : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_rank_code != null ? employee[i].new_rank_code : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';

                            if(employee[i].company_id!=0 && employee[i].new_cmpny!=null)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_cmpny != null ? employee[i].old_cmpny : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_cmpny != null ? employee[i].new_cmpny : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';

                            if(employee[i].division_id!=0 && employee[i].new_division!=null)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_division != null ? employee[i].old_division : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_division != null ? employee[i].new_division : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';

                            if(employee[i].location_id!=0 && employee[i].new_location!=null)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_location != null ? employee[i].old_location : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_location != null ? employee[i].new_location : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';

                            if(employee[i].segment_1_id!=0 && employee[i].new_segment_1!=null)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_segment_1 != null ? employee[i].old_segment_1 : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_segment_1 != null ? employee[i].new_segment_1 : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';

                            if(employee[i].segment_2_id!=0 && employee[i].new_segment_2!=null)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_segment_2 != null ? employee[i].old_segment_2 : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_segment_2 != null ? employee[i].new_segment_2 : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';
                            //EO Added
                            if(employee[i].new_position_name!=null && employee[i].new_position_id != 0)
                                html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].old_position_name != null ? employee[i].old_position_name : " ")+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].new_position_name != null ? employee[i].new_position_name : " ")+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].transfer_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date)) : "No specified Date")+'</td></tr>';
                    // }
                    // if(employee[i].compensation_effectivity_date!= null || employee[i].compensation_effectivity_date!="0000-00-00" || employee[i].compensation_effectivity_date)!="1970-01-01")
                    // {
                        if(employee[i].new_basic_salary!=null && employee[i].new_basic_salary>0 && employee[i].compensation_effectivity_date!=null)
                            html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].current_basic_salary+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].new_basic_salary+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].compensation_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].compensation_effectivity_date)) : "No specified Date")+'</td></tr>';
                        if(employee[i].new_total!=null && employee[i].new_total>0 && employee[i].compensation_effectivity_date!=null)
                            html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].current_total+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].new_total+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].compensation_effectivity_date != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].compensation_effectivity_date)) : "No specified Date")+'</td></tr>';
                    // }
                        if(employee[i].last_day!=null)
                            html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 "></td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 "></td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+(employee[i].last_day != null ? $.datepicker.formatDate('M dd, yy',new Date(employee[i].last_day)) : "No specified Date")+'</td></tr>';
                }
                html +='</table>';;
                // var dialogue=new Boxy(html, {title: "Dialog", modal: true});
                // //var boxySize=dialogue.getContentSize();
                // dialogue.resize();
                // dialogue.center();

                quickedit_boxy = new Boxy(html,
                {
                    title: 'Employee Movement',
                    draggable: false,
                    modal: true,
                    center: true,
                    unloadOnHide: true,
                    afterShow: function(){ $.unblockUI(); },
                    beforeUnload: function(){ $('.tipsy').remove(); }
                    
                }); 
                boxyHeight(quickedit_boxy, '#boxyhtml');

                //Boxy.alert(employee.employee_movement_type_id+" // "+employee.old_position_name+" "+employee.current_position_name+"||"+employee.old_department_name+" "+employee.new_department_name);
            }
        }
    });
}

function init_filter_tabs(){
    $('ul#grid-filter li').click(function(){
        $('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
        $(this).addClass('active');
        $('#filter').val( $(this).attr('filter') );
        filter_grid( 'jqgridcontainer', $(this).attr('filter') );
    });
}

function filter_grid( jqgridcontainer, filter )
{
    var searchfield;
    var searchop;
    var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
    
    if( $("form.search-options-"+ jqgridcontainer).hasClass("hidden") ){
        searchfield = "all";
        searchop = "";
    }else{
        searchfield = $('#searchfield-'+jqgridcontainer).val();
        searchop = $('#searchop-'+jqgridcontainer).val()    
    }

    //search history
    $('#prev_search_str').val(searchstring);
    $('#prev_search_field').val(searchfield);
    $('#prev_search_option').val(searchop);
    $('#prev_search_page').val( $("#"+jqgridcontainer).jqGrid("getGridParam", "page") );

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        search: true,
        postData: {
            searchField: searchfield, 
            searchOper: searchop, 
            searchString: searchstring,
            filter: filter
        },  
    }).trigger("reloadGrid");   
}

function get_rt_incumbent(position_id)
{
    if(position_id != "")
    {
        var data = "position_id="+position_id;
        $.ajax({
            url: module.get_value('base_url') +'employees/get_pos_reporting_to',
            type: 'post',
            data: data,
            success: function (response) { 
                // Append the new values to department dropdown.
                if(response.msg_type == 'error')
                {
                    $('#incumbent').remove();
                    $('#position_reporting_to').val('');
                }
                var position = response.data;



                // $('#position_reporting_to').val(position.approver_position);
                
                $('.incumbent').remove();
                $('#spacers').remove();
                if(response.msg_type == 'success')
                    $('#position_id_chzn').after('<span id="spacers"><br /><i class="incumbent" style="color:RED">Reporting To: '+position.approver_position+'</i><br /><i class="incumbent" style="color:RED">Incumbent: '+position.approver_name+'</i></span>');
                    // $('#position_reporting_to').after('<br /><i id="incumbent">Incumbent: '+position.approver_name+'</i>');

                // $('label[for="new_position_id"]').html('Change To:')
                // $('label[for="new_position_id"]').append('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <i> Incumbent: '+position.approver_name+'</i>');
            }                
        }); 
    }
}

function show_work_assignment(assignment_type,parent_elem){
    if (assignment_type == 1){
        $(parent_elem).find('.work_assignment').val('').parent().parent().show();
        assignment_category_show($(parent_elem).find('work_assignment').val(),parent_elem);
        //assignment_conc(0,parent_elem);
        //$(parent_elem).find('.start_date').parent().parent().hide();              
        //$(parent_elem).find('.end_date').parent().parent().hide();                                                                
    }
    else{
        $(parent_elem).find('.work_assignment').parent().parent().hide();
        assignment_conc(1,parent_elem);
        $(parent_elem).find('.start_date').parent().parent().show();              
        $(parent_elem).find('.end_date').parent().parent().show();    
        $(parent_elem).find('.code_status_id').parent().parent().show();
        $(parent_elem).find('.project_name_id').parent().parent().removeClass('odd').addClass('even');
        $(parent_elem).find('.department_id').parent().parent().removeClass('odd').addClass('even');
        $(parent_elem).find('.cost_code').parent().parent().show();
    }   
}

function assignment_conc(show_hide,parent_elem){
    if (show_hide == 0){
        $(parent_elem).find('.division_id').parent().parent().hide();
        $(parent_elem).find('.project_name_id').parent().parent().hide();
        $(parent_elem).find('.group_name_id').parent().parent().hide();
        $(parent_elem).find('.department_id').parent().parent().hide();
    }
    else{
        $(parent_elem).find('.division_id').parent().parent().show();
        $(parent_elem).find('.project_name_id').parent().parent().show();
        $(parent_elem).find('.group_name_id').parent().parent().show();
        $(parent_elem).find('.department_id').parent().parent().show();     
    }
}

function assignment_category_show(category_id,parent_elem){
    switch (category_id){
        case "1":
            $(parent_elem).find('.division_id').val('').parent().parent().show();
            $(parent_elem).find('.project_name_id').val('').parent().parent().hide();                    
            $(parent_elem).find('.group_name_id').val('').parent().parent().hide();
            $(parent_elem).find('.department_id').val('').parent().parent().hide();                    
            $(parent_elem).find('.start_date').val('').parent().parent().show();
            $(parent_elem).find('.end_date').val('').parent().parent().show();                    
            $(parent_elem).find('.code_status_id').val('').parent().parent().show();
            $(parent_elem).find('.cost_code').val('').parent().parent().hide();                                            
            break;
        case "2":
            $(parent_elem).find('.division_id').val('').parent().parent().show();
            $(parent_elem).find('.project_name_id').val('').parent().parent().show();                    
            $(parent_elem).find('.group_name_id').val('').parent().parent().hide();
            $(parent_elem).find('.department_id').val('').parent().parent().hide();                    
            $(parent_elem).find('.start_date').val('').parent().parent().show();
            $(parent_elem).find('.end_date').val('').parent().parent().show();                    
            $(parent_elem).find('.code_status_id').val('').parent().parent().show();
            if ($(parent_elem).find('.assignment').val() == 1){
                $(parent_elem).find('.project_name_id').val('').parent().parent().removeClass('odd').addClass('even');
            }
            else{
                $(parent_elem).find('.project_name_id').val('').parent().parent().removeClass('even').addClass('odd');
            }
            $(parent_elem).find('.code_status_id').val('').parent().parent().removeClass('odd').addClass('even');                 
            $(parent_elem).find('.cost_code').val('').parent().parent().show();                 
            break;
        case "3":
            $(parent_elem).find('.division_id').val('').parent().parent().hide();
            $(parent_elem).find('.project_name_id').val('').parent().parent().hide();                    
            $(parent_elem).find('.group_name_id').val('').parent().parent().show();
            $(parent_elem).find('.department_id').val('').parent().parent().hide();                    
            $(parent_elem).find('.start_date').val('').parent().parent().show();
            $(parent_elem).find('.end_date').val('').parent().parent().show();                    
            $(parent_elem).find('.code_status_id').val('').parent().parent().show();
            $(parent_elem).find('.cost_code').val('').parent().parent().hide();               
            break;
        case "4":
            $(parent_elem).find('.division_id').val('').parent().parent().hide();
            $(parent_elem).find('.project_name_id').val('').parent().parent().hide();                    
            $(parent_elem).find('.group_name_id').val('').parent().parent().show();
            $(parent_elem).find('.department_id').val('').parent().parent().show();                    
            $(parent_elem).find('.start_date').val('').parent().parent().show();
            $(parent_elem).find('.end_date').val('').parent().parent().show();                    
            $(parent_elem).find('.code_status_id').val('').parent().parent().show();
            $(parent_elem).find('.department_id').val('').parent().parent().removeClass('even').addClass('odd');
            $(parent_elem).find('.code_status_id').val('').parent().parent().removeClass('odd').addClass('even');                 
            $(parent_elem).find('.cost_code').val('').parent().parent().hide();  
            break;
        default:
            $(parent_elem).find('.division_id').val('').parent().parent().hide();
            $(parent_elem).find('.project_name_id').val('').parent().parent().hide();                    
            $(parent_elem).find('.group_name_id').val('').parent().parent().hide();
            $(parent_elem).find('.department_id').val('').parent().parent().hide();                    
            $(parent_elem).find('.start_date').val('').parent().parent().hide();
            $(parent_elem).find('.end_date').val('').parent().parent().hide();                    
            $(parent_elem).find('.code_status_id').val('').parent().parent().hide();
            $(parent_elem).find('.cost_code').val('').parent().parent().hide();                                                                                              
    }   
}

function reset_elem_case(parent_elem){
    $(parent_elem).find('.start_date').parent().parent().show();              
    $(parent_elem).find('.end_date').parent().parent().show();

    if ($(parent_elem).find('.division_id').val() != ''){
        $(parent_elem).find('.division_id').parent().parent().show();
        $(parent_elem).find('.code_status_id').parent().parent().hide;
    }   
    else{
        $(parent_elem).find('.division_id').parent().parent().hide();
        $(parent_elem).find('.code_status_id').parent().parent().hide;
    }
    
    if ($(parent_elem).find('.project_name_id').val() != ''){
        $(parent_elem).find('.project_name_id').parent().parent().show();
        $(parent_elem).find('.code_status_id').parent().parent().show;
    }       
    else{
        $(parent_elem).find('.project_name_id').parent().parent().hide();
        $(parent_elem).find('.code_status_id').parent().parent().show;        
    }
    
    if ($(parent_elem).find('.group_name_id').val() != ''){
        $(parent_elem).find('.group_name_id').parent().parent().show();
        $(parent_elem).find('.code_status_id').parent().parent().hide;
    }   
    else{
        $(parent_elem).find('.group_name_id').parent().parent().hide();
        $(parent_elem).find('.code_status_id').parent().parent().hide;        
    }

    if ($(parent_elem).find('.department_id').val() != ''){
        $(parent_elem).find('.department_id').parent().parent().show();
        $(parent_elem).find('.code_status_id').parent().parent().hide;
        $(parent_elem).find('.employee_work_assignment_category_id').val(4);  
    } 
    else{
        $(parent_elem).find('.department_id').parent().parent().hide();
    }  

    if ($(parent_elem).find('.cost_code').val() != ''){
        $(parent_elem).find('.cost_code').parent().parent().show();                       
    }            
    else{
        $(parent_elem).find('.cost_code').parent().parent().hide();                               
    }
}

function enable_status_options(){

    var view_type = module.get_value('view');

    if( module.get_value('view') == 'edit' ){
        var status_id = $('#status_id').val();
        var data = 'status_id='+status_id+'&view_type='+view_type;
    }
    else if( module.get_value('view') == 'detail' ){
        var record_id = $('#record_id').val();
        var data = '&record_id='+record_id+'&view_type='+view_type
    }

    $.ajax({
            url: module.get_value('base_url') +'employees/enable_status_options',
            type: 'post',
            data: data,
            success: function (response) { 


                if( response.enable_delegates == 1 ){
                    $('label[for="delegates_type_id"]').parent().show();
                }
                else{
                    $('label[for="delegates_type_id"]').parent().hide();

                    if( module.get_value('view') == 'edit' ){
                        $('#delegates_type_id').val('');
                    }
                }

                if( response.enable_terms == 1 ){
                    $('label[for="terms"]').parent().show();
                    $('label[for="terms_end_date"]').parent().show();
                }
                else{
                    $('label[for="terms"]').parent().hide();
                    $('label[for="terms_end_date"]').parent().hide();

                    if( module.get_value('view') == 'edit' ){
                        $('#terms').val('');
                        $('#terms_end_date').val('');
                        $('#terms_end_date-temp').val('');
                    }
                }

                if( response.enable_agency == 1 ){
                    $('label[for="agency_id"]').parent().show();
                }
                else{
                    $('label[for="agency_id"]').parent().hide();

                    if( module.get_value('view') == 'edit' ){
                        $('#agency_id').val('');
                        $('#agency_id').trigger('liszt:updated');

                    }
                }

            }
        });


}

function compute_end_date(){

    if( $('#employed_date').val() != '' && $('#terms').val() != ''  ){

        var data = 'date_hired='+$('#employed_date').val()+'&terms='+$('#terms').val();

        $.ajax({
            url: module.get_value('base_url') +'employees/compute_end_date',
            type: 'post',
            data: data,
            success: function (response) { 

                $('#terms_end_date').val(response.end_date);
                $('#terms_end_date-temp').val(response.end_date);

            }
        });
    }


}

function get_total_weight(){

        var weight = 0;

        $('.cost_center_percentage').each(function(){

            var parse = parseFloat($(this).val());

            if( parse ){
                weight = weight + parse;
            }

        });

        $('.cost_center_total').val(weight);

    }

function toggle_active(type,user_id,obj){
    if (type == "active"){
        val = 1;
        $(obj).removeClass('icon-16-active');
        $(obj).addClass('icon-16-xgreen-orb');
    }
    else{
        val = 0;
        $(obj).removeClass('icon-16-xgreen-orb');
        $(obj).addClass('icon-16-active');      
    }

    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + "/toggle_active",
        type:"POST",
        data: 'user_id=' + user_id + '&val=' + val,
        beforeSend: function(){
                    
        },
        success: function(data){
            $("#jqgridcontainer").trigger("reloadGrid"); 
        }
    });     
}    