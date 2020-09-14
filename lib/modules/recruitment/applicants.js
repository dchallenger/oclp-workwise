$(document).ready(function () {

    $('.clear-val-from').click(function(){
        $(this).parent().find('input[name="affiliates[date_from][]"]').val('');
    }); 
    $('.clear-val-to').click(function(){
        $(this).parent().find('input[name="affiliates[date_to][]"]').val('');
    }); 
        
    init_datepick();           
    $('.close').live('click',function(){
        $('#advance_search_container').hide()
    });

    $('.advance_search').live('click',function(){
        $('#jqgridcontainer').jqGrid('setGridParam', 
        {
            postData: null
        });

        $('#jqgridcontainer').jqGrid('setGridParam', 
        {
            url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
            datatype: 'json',
            postData: {
                position : $('#position').val(),
                status : $('#status').val(),
                male : $('#male').val(),
                female : $('#female').val(),
                age : $('#age').val(),
                location : $('#location').val(),
            },  
        }).trigger("reloadGrid");
    });

    $('div.add-more-div').hide();

    window.onload = function(){

        $('#pres_city_chzn').css('width', '90%');
        $('#pres_city_chzn').children().css('width', '92%');
        $('#pres_city_chzn .chzn-search input[type="text"]').css('width', '92%');

        $('#perm_city_chzn').css('width', '90%');
        $('#perm_city_chzn').children().css('width', '92%');
        $('#perm_city_chzn .chzn-search input[type="text"]').css('width', '92%')


         $('input[name="same_address"]').parent().css('padding','0px 190px');
        
        $("#searchfield-jqgridcontainer option[value='t5application_status']").remove();
        
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
        if ($("#client_dir").val() == "oams" && module.get_value("view") == "edit") {
            
                if ($("#record_id").val() == "-1") {
                    $('label[for="application_status_id"]').parent().remove();
                }else{

                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_applicant_status',
                        dataType: 'html',
                        data: 'applicant_status_id='+$('#application_status_id').val() + '&applicant_status=' +$('#application_status_id option:selected').text(),
                        type:"POST",
                        beforeSend: function(){
                            $.blockUI({
                                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                            });         
                        },                        
                        success: function (response) {
                            $.unblockUI();
                             $('select[name="application_status_id"]').html(response);
                        }
                    });
                   
                };
                    
            
        };  
    }


    $('.bak-candidate-schedule').live('click',function(){
        var action_link = $(this).attr('module_link');
        window.location = action_link;
    });

    $('.bak-applicant').live('click',function(){
        $('#record-form').attr("action", module.get_value('base_url') + "recruitment/applicants");
        $('#record-form').submit();
        //window.location = module.get_value('base_url') + "recruitment/applicants";
    });

    $('#working_since_select').change(function(){
        $('#working_since').val($(this).val());

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


    $('a.add-more').click(function(event) {

        event.preventDefault();
        var obj = $(this);
        var form_to_get = $(this).attr('rel');

        if (form_to_get == '' || form_to_get == undefined) { return; }

        url = module.get_value('base_url') + module.get_value('module_link') + '/get_form/' + $(this).attr('rel');

        var data = "";
        if($(this).attr('rel') == 'test_profile')
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
            data: data,
            type:"POST",
            success: function (response) {
                //$('.current-wizard .form-head').after(response);
                if (module.get_value('module_link') == 'recruitment/appform') {
                    response = '<fieldset>' + response + '</fieldset><div class="spacer"></div>';
                }

                 $('select[name="education[education_level][]"]').live('change', function () {
                    if ($(this).val() == '') {
                        return;
                    }
                  change_educ_level ($(this));
                });

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
                    // var school_name = $('#education_school').attr('name') + '['+val+'][]';
                    // $('#education_school').attr('name',school_name)
                }

                $('.radioG').live('click', function(){
                    $(this).parents('div.form-multiple-add').find('input[name="education[degree][]"]').attr('readonly', false).val('');
                });

                $('.radioUG').live('click', function(){
                    $(this).parents('div.form-multiple-add').find('input[name="education[degree][]"]').attr('readonly', true).val('');
                });
                                
                //clear dates on add education
                $('.clear-val-from').click(function(){
                    $(this).parent().find('input[name="education[date_from][]"]').val('');
                }); 
                $('.clear-val-to').click(function(){
                    $(this).parent().find('input[name="education[date_to][]"]').val('');
                }); 

                if (form_to_get == 'family') {
                    $('.form-multiple-add-' + form_to_get).append(response);
                } else {
                    $('.form-multiple-add-' + form_to_get).prepend(response);
                }
                
                $('.current-wizard').find('input').first().focus();
                init_datepick();

                form_count = $('.current-wizard div.form-multiple-add').size();

                $('.current-wizard')
                    .find('div.form-multiple-add').first()
                    .find('input[type="radio"]')
                    .each(function (index, elem) {
                        $(elem).attr('name', $(elem).attr('name') + '[' + (form_count - 1) + ']');
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

                    if( form_to_get == 'test_profile')
                    {   

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

                    }


            }
        });
    });

    $('a.back-to-candidates').live('click', function () {

        if( $('#rp').val() == "" ){

            if( module.get_value('view') == "edit" ){    
                window.location = module.get_value('base_url') + "recruitment/applicants/detail/" + $('#candidate_id').val();
            }
            if( module.get_value('view') == "detail" ){    
                window.location = module.get_value('base_url') + "recruitment/candidates/index/" + $('#mrf_id').val();
            }

        }
        else{

            if( $('#rp').val() == 2 ){
                 window.location = module.get_value('base_url') + "recruitment/candidate_contract_signing";
            }
            else{
                window.location = module.get_value('base_url') + "recruitment/candidate_job_offer";
            }

        }

    });

    $('a.delete-detail').live('click', function () {        
        $(this).parents('div.form-multiple-add').remove();
    });

    current = $('.current-wizard');

    if (typeof(activate_add_more) == typeof(Function)) {
        activate_add_more(current);
    }   

    $('#refered_by_id').change(function(){
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


    $('input[name="same_address"]').click(function(){
        if( $(this).attr('checked') == 'checked' ){
            $('#perm_address1').val($('#pres_address1').val());
            $('#perm_address2').val($('#pres_address2').val());
            $('#perm_city').val($('#pres_city').val());
            $('#perm_province').val($('#pres_province').val());
            $('#perm_zipcode').val($('#pres_zipcode').val());
            $('#perm_city').trigger('liszt:updated');
        }
    });
    
    if(view == "edit"){

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
                    
        $('#middlename').change(function(){

            var middle_name = $(this).val();
            $('#middleinitial').val(middle_name.substring(0,1)+'.');

        });
            
        $('#referred_by_id').trigger('change');

        $('label[for="blacklisted"]').append('<input type="checkbox" id="same_as_present" >Same as Present</input>');

        $('#same_as_present').live('click', function() {
            $('#perm_address1').val($('#pres_address1').val());
            $('#perm_address2').val($('#pres_address2').val());
            $('#perm_city').val($('#pres_city').val());
            $('#perm_province').val($('#pres_province').val());
            $('#perm_zipcode').val($('#pres_zipcode').val());
            $('#perm_city').trigger('liszt:updated');
        }); 

        $('#firstname').width('260');
        if ($('#record_id').val() != '-1'){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_applicant',
                data: 'applicant_id=' + $('#record_id').val(),
                type: 'post',
                dataType: 'json',
                beforeSend: function(){
                    //$('#appraisal-template-criteria-container').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });          
                },      
                success: function (response) {
                    $('<input type="text" class="input-text" value="'+ response.data.aux +'" id="aux" name="aux" style="width:100px">').insertAfter('#firstname');
                }
            });
        }
        else{
            $('<input type="text" class="input-text" value="" id="aux" name="aux" style="width:100px">').insertAfter('#firstname');
        }   

        $('.clear-val-from').click(function(){
            $(this).parent().find('input[name="education[date_from][]"]').val('');
        }); 
        $('.clear-val-to').click(function(){
            $(this).parent().find('input[name="education[date_to][]"]').val('');
        }); 

        //display school and degree dropdown on edit view
         $('select[name="education[education_level][]"]').live('change', function () {
            if ($(this).val() == '') {
                return;
            }
            change_educ_level ($(this));
        });

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

                var school =  educ_level.parents('div.form-multiple-add')
                    .find('select[name="education[education_school_id][]"], select[id="education_school_id"]'); //.parent().parent();
  

                if (school.val() != '-1') {

                    educ_level.parents('div.form-multiple-add')
                        .find('input[name="education[school][]"]').parent().parent()
                        .addClass('hidden');

                    educ_level.parents('div.form-multiple-add')
                    .find('input[name="education[school][]"]')
                    .val(' ');

                }else{
                     educ_level.parents('div.form-multiple-add')
                        .find('input[name="education[school][]"]').parent().parent()
                        .removeClass('hidden');
                };

                
            }
         }                     
    }    
    else if (module.get_value('view') == 'detail'){
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_applicant',
            data: 'applicant_id=' + $('#record_id').val(),
            type: 'post',
            dataType: 'json',
            beforeSend: function(){
                //$('#appraisal-template-criteria-container').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });          
            },      
            success: function (response) {
                $('label[for="lastname"]').next().append('<span>'+ response.data.aux +'</span>');
            }
        });
    } 

    $('.wizard-leftcol li').css('width', '10%');    

    $('#civil_status_id').click(function () {
        if ($(this).val() == 1) {
            $('#spouse_name, #spouse_work, #spose_work').attr('readonly', 'readonly');
            $('#spouse_name, #spouse_work, #spose_work').val('');
            $('#date_of_marriage-temp').datepicker( 'disable' );
        } else {
            $('#spouse_name, #spouse_work, #spose_work').removeAttr('readonly');
            $('#date_of_marriage-temp').datepicker( 'enable' );
        }
    });


    $('input[name="previously_employed"]').click(function () {
        if ($(this).val() == 0) {
            $('input[name="previously_employed_date-temp"]').datepicker('disable');
        } else {
            $('input[name="previously_employed_date-temp"]').datepicker('enable');
        }
    })    


    setTimeout(function () {
            if( module.get_value('view') == "edit" ){
                $('label[for="birth_date"]').next().append('<span class="calculatedage"></span>');
                $('label[for="employed_date"]').next().append('<span class="calculatedservice"></span>');
                $('input[name="family[birth_date][]"]').each(function(){
                    if( $(this).val() != "" ){
                    var age = get_age(  $(this).val() );
                        $(this).next().next().html('&nbsp;and is now&nbsp;<u>&nbsp;'+age+"&nbsp;</u>&nbsp;years of age") 
                    }
                });
            }      
              
            $('input[name="birth_date-temp"]').datepicker('option', 'yearRange', 'c-90:c+30'); 
            $('input[name="birth_date-temp"], input[name="residence_certno_date_of_issue-temp"]').datepicker( "option", "maxDate", new Date() );            

            if ($('#record_id').val() == "-1") {
                $('#application_date').datepicker('setDate', new Date());
            }

            $('input[name="previously_employed"]').trigger('click');
        },
        1000
    );

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
/*            $(this).parents('div.form-multiple-add')
                .find('input[name="education[degree][]"], input[name="education[course][]"]').parent().parent()            
                .removeClass('hidden');*/

            $(this).parents('div.form-multiple-add')
                .find('input[name="education[degree][]"]').attr('readonly', 'readonly');
        }
    });

    $('select[name="education[education_level][]"]').trigger('change');

    /*
    $('input[name="no_work_experience"]').click(function (event) {
        
        if ($(this).is(':checked')) {
            $('.add-more-div a.add-more').removeAttr('rel');
            $('.form-multiple-add-employment').find('.add-more-flag').val('');
            $('input[name^="employment["]').attr('disabled', 'disabled').val('');            
        } else {
            $('input[name^="employment["]').removeAttr('disabled');
            $('.form-multiple-add-employment').find('.add-more-flag').val('employment');
            $('.add-more-div a.add-more').attr('rel', 'employment');
        }
    });
    


    if ($('input[name="no_work_experience"]').is(':checked')) {
        $('input[name^="employment["]').attr('disabled', 'disabled').val('');
        $('div.add-more-div').hide();
    } else {
        $('input[name^="employment["]').removeAttr('disabled');
        $('div.add-more-div').show();
    }       

    $('#civil_status_id').change(function () {
        if($(this).val() == 2) {
            $('select[name="family[relationship][]"]')
                .append('<option value="Spouse">Spouse</option>');
        } else {
            $('select[name="family[relationship][]"] option[value="Spouse"]').remove();
        }
    });

*/

    $('#civil_status_id').trigger('change');

    $('input[name^="education[graduate]"]').live('change', function() {        
        if ($(this).val() == 0) {        
            $(this).parents('.form-multiple-add')
                .find('input[name="education[degree][]"]')
                .attr('readonly', 'readonly').val('');
        } else {
            $(this).parents('.form-multiple-add')
                .find('input[name="education[degree][]"]')
                .removeAttr('readonly');
        }
    });    
        
    if(module.get_value('view') == "edit"){
         $("#sss").mask("99-9999999-9", {placeholder: "x"});
         $("#philhealth").mask("99-999999999-9",{placeholder: "x"});
         $("#tin").mask("999-999-999-999", {placeholder: "x"});
         $("#pagibig").mask("9999-9999-9999", {placeholder: "x"});
         $("#home_phone").live('keyup', maskNum);
         $("#alternate_home_phone").live('keyup', maskNum);
         $("#mobile").live('keyup', maskNum);
         $("#alternate_mobile").live('keyup', maskNum);


        $('#expected_salary_range').parent().parent().hide();
        var select = '<span class="select-input-wrap"><select id="expected_salary_range_temp" style="width:25%">'+
                        '<option value="">Select...</option>'+
                        '<option value="hourly">Hourly</option>'+
                        '<option value="monthly">Monthly</option>'+
                      '</select> </span>';
       
        $('#expected_salary').css('width','60%');   
        $(select).insertAfter('#expected_salary');
        
        if (module.get_value('record_id') !== "-1" ) {
         $("#expected_salary_range_temp").val($('#expected_salary_range').val());
        };

         $("#expected_salary_range_temp").change(function() {
             var value = $(this).val();
             $("#expected_salary_range").val(value);

         })
    }


    if(module.get_value('view') == "detail"){

        $('label[for="expected_salary_range"]').parent().hide();
        var start_from = $('label[for="expected_salary"]').next().html();
        var start_to = $('label[for="expected_salary_range"]').next().html();

        if (start_to !== null && start_to.trim() !== '&nbsp;') {
            $('label[for="expected_salary"]').next().text(start_from.trim()+' ' +start_to.trim());     
        };
  
        
        

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

    $('#ve').live('click', function () {
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/verify_applicant',
            data: $('#firstname, #lastname, #middlename, input[name="sex"], #birth_date').serialize(),
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
    });

    $('#ce').live('click', function () {
        $.unblockUI();
    }); 


    $('.position_applied_list').live('click',function(){


        var id = $(this).attr('applicant_id');

            $.ajax({
                    url: module.get_value('base_url') + 'recruitment/applicants/position_applied_list',
                    data: 'applicant_id=' + id,
                    type: 'post',
                    dataType: 'json',
                    beforeSend: function(){
                        $.blockUI({
                            message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                        });         
                    },  
                    success: function(response) {

                        if(response.msg_type == 'attention'){
                        
                            $.unblockUI();  
                            message_growl(response.msg_type, response.msg);

                        }
                        else{

                        $.unblockUI();  

                            template_form = new Boxy('<div id="boxyhtml" style="">'+ response.form +'</div>',
                            {
                                    title: 'Position Applied',
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

                    }
            });
        
    });

});

function maskNum( e )
{
    var value = $(this).val();
    var str_length = value.length;
    if( (e.keyCode <= 57 && e.keyCode >= 48) || (e.keyCode <= 105 && e.keyCode >= 96) || e.keyCode == 190 || e.keyCode == 110){
        value = value.replace(/\,/g,'');
    }
    else if(e.keyCode == 109 || e.keyCode == 173){
        value = value.replace(/\,/g,'');
    }
    else if( (e.keyCode <=31 && e.keyCode >= 8) ||  (e.keyCode <=40 && e.keyCode >= 37) || (e.keyCode <= 93 && e.keyCode >= 91) || (e.keyCode <= 145 && e.keyCode >= 112) ){
        // do nothing
        value = value.replace(/\,/g,'');

    }
    else{
        value = value.slice(value, str_length-1);
    }
    $(this).val(value);
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

function edit_detail_candidates() {
    // Get current fg_id so we know which fieldgroup to open on edit
    var current = $('.current-wizard');

    var fg_id = current.attr('fg_id');

    $('#record-form').append('<input type="hidden" name="default_fg" value="' + fg_id +'" />');
    $('#record-form').append('<input type="hidden" name="candidate_id" value="' + $('#candidate_id').val() +'" />');

    edit();
}

function qualify_candidates( position_id, position2_id ) {
    var mrf_from_posted_jobs = $('#mrf_from_posted_jobs').val();
    $.ajax({
            url: module.get_value('base_url') + 'recruitment/candidates/qualify_candidate_form',
            type: 'post',
            dataType: 'json',
            data: 'position_id='+position_id+'&position2_id='+position2_id+'&applicant_id='+module.get_value('record_id')+'&mrf_from_posted_jobs='+mrf_from_posted_jobs,
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });         
            },  
            success: function(response) {

                if(response.msg_type == 'error'){
                
                    $.unblockUI();  
                    message_growl(response.msg_type, response.msg);

                }
                else{

                $.unblockUI();  

                    template_form = new Boxy('<div id="boxyhtml" style="">'+ response.form +'</div>',
                    {
                            title: 'Add Applicant to MRF',
                            draggable: false,
                            modal: true,
                            center: true,
                            unloadOnHide: true,
                            beforeUnload: function (){
                                template_form = false;
                            },
                            afterShow: function (){
                                $('input[name="application_status"]').live('click',function(){
                                    if( $(this).attr('checked') == 'checked' ){
                                        var candidate_status = $(this).val();
                                        var text = $(this).attr('attrib');
                                        if (text.trim() == 'rfp'){
                                            $('#mrf_form').show();
                                        }
                                        else{
                                            $('#mrf_form').hide();
                                        }

                                        $('#candidate_status_id').val(candidate_status);
                                    }
                                });
                            }
                        });
                        boxyHeight(template_form, '#boxyhtml');         
                }

            }
    });

}

function disqualify_candidates() {
    
    $.ajax({
            url: module.get_value('base_url') + 'recruitment/candidates/disqualify_candidate_form',
            type: 'post',
            dataType: 'json',
            data: '&applicant_id='+module.get_value('record_id'),
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });         
            },  
            success: function(response) {

                if(response.msg_type == 'error'){
                
                    $.unblockUI();  
                    message_growl(response.msg_type, response.msg);

                }
                else{

                $.unblockUI();  

                    template_form = new Boxy('<div id="boxyhtml" style="">'+ response.form +'</div>',
                    {
                            title: 'Move Applicant',
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

            }
    });

}

function advance_search() {
    if ($('#advance_search_container').length > 0){
        $('#advance_search_container').show();
    }
    else{
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_advance_search',
            dataType: 'html',
            type:"POST",
            beforeSend: function(){
    /*            $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                }); */        
            },                        
            success: function (response) {
                //$.unblockUI();
                $('#t_jqgridcontainer').after(response) 
                $("#position,#status,#location").chosen();           
            }
        });  
    }  
}

function validate_fg178() { return validate_fg46(); }


function validate_fg46(){

    validate_mandatory("references[name][]", "Name");
    validate_mandatory("references[telephone][]", "Contact Number");
    validate_mandatory("references[email][]", "Email");
    validate_email("references[email][]", "Email");
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
    }

    //no error occurred
    return true;
}

function validate_fg177() { return validate_fg45(); }

/**
 * Employment validation.
 * @return {[type]}
 */
function validate_fg45(){
 
 validate_mandatory("employment[company][]", "Name of Employer");
 validate_mandatory("employment[address][]", "Address");
 validate_mandatory("employment[contact_number][]", "Contact Number");

    $.each($('input[name="employment[from_date][]"]'), function (index, elem) {                
        validate_date_from('employment[from_date][]', 'Employment date from', $(elem), $($('input[name="employment[to_date][]"]').get(index)));
    });

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
    }

    //no error occurred
    return true;  
}

function validate_fg44() {
/*    validate_mandatory_array('family[name][]', "Famiy member's name");

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
*/
    //no error occurred
    return true;
}

function validate_fg174() { return validate_fg42(); }

/**
 * Education validation.
 * @return {[type]}
 */
function validate_fg42() {
    $.each($('input[name="education[date_from][]"]'), function (index, elem) {                
        validate_date_from('education[date_from][]', 'Education date from', $(elem), $($('input[name="education[date_to][]"]').get(index)));
    });

    validate_mandatory("education[education_level][]", "Educational Attainment");
    
    var educ_error_count = 0;
    $('select[name="education[education_level][]"]').each(function () {
        if($(this).val() == ''){
            educ_error_count++;
        }
    });

    if (educ_error_count > 0){
            var error_str = "Please correct the following errors:<br />";
            error_str = error_str + "1. Educational Attainment - This field is mandatory."
            $('#message-container').html(message_growl('error', error_str));
            //reset errors
            error = new Array();
            error_ctr = 0
            return false;
    }else{
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
}

function validate_fg175() { return validate_fg43(); }

/**
 * Trainings validation.
 * @return {[type]}
 */
function validate_fg43() {
    $.each($('input[name="training[from_date][]"]'), function (index, elem) {                
        validate_date_from('training[from_date][]', 'Training date from', $(elem), $($('input[name="training[to_date][]"]').get(index)));
    });

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
    }

    //no error occurred
    return true;     
}

function validate_fg43() {
    return true;
}

function validate_fg47() {
    return true;
}

function validate_fg378() {
    return true;
}

function validate_fg377() {
    return true;
}

function validate_fg36() {
    return true;
}