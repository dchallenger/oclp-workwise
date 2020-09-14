var globalvar=0;
var globaltag=false;
$(document).ready(function () {


     window.onload = function(){

         if( $('ul#grid-filter li').length > 0 ){  

            $('ul#grid-filter li').each(function(){ 

                if( $(this).hasClass('active') ){

                    if($(this).attr('filter') == 'for_approval'){
                       $('.status-buttons').parent().show();
                    }
                    else{
                       $('.status-buttons').parent().hide();
                    }

                }
            });

        }
        else{
            $('.status-buttons').parent().hide();
        }

    }

    init_datepick();
    $('#fg-254 h3').html('Permanent Address &nbsp;&nbsp;<input type="checkbox" id="same_above"/><label style="font-size:12px;font-weight:normal">Same as Above</label><a href="javascript:void(0)" class="align-right other-link noborder" onclick="toggleFieldGroupVisibility( $( this ) );" style="font-size: 12px;line-height: 18px;">Hide</a></h3>');
    $("#same_above").change(function() {
        if($("input[type=checkbox]:checked").val()=="on")
        {
          $('#perm_address1').val($("#pres_address1").val());
          $('#perm_address2').val($("#pres_address2").val());
          $('#perm_city').val($("#pres_city").val());
          $('#perm_city').siblings().find('span').text($('#pres_city').siblings().find('span').text());
          $('#perm_province').val($("#pres_province").val());
          $('#perm_zipcode').val($("#pres_zipcode").val());
          $('#perm_address1').attr('readonly', true);
          $('#perm_address2').attr('readonly', true);
          $('#perm_city').attr('readonly', true);
          $('#perm_province').attr('readonly', true);
          $('#perm_zipcode').attr('readonly', true);
        }
        else
        {
          $('#perm_address1').val('');
          $('#perm_address2').val('');
          $('#perm_city').val('');
          $('#perm_city').siblings().find('span').text('Select an Option');
          $('#perm_province').val('');
          $('#perm_zipcode').val('');
          $('#perm_address1').attr('readonly', false);
          $('#perm_address2').attr('readonly', false);
          $('#perm_city').attr('readonly', false);
          $('#perm_province').attr('readonly', false);
          $('#perm_zipcode').attr('readonly', false);
        }
    });

   $('.dater:first').select(function () {
        //$('').each(function(){
            $(this).datepicker();
        //});
    });

 

    $('#employee_id').change(function () {
        if ($(this).val() == 0) {
            $('label[for="dummy_position"]').siblings('.text-input-wrap').text('');
            $('label[for="dummy_department"]').siblings('.text-input-wrap').text('');
            $('label[for="dummy"]').siblings('.text-input-wrap').text('');
            $('label[for="dummy_company"]').siblings('.text-input-wrap').text('');
            
            if(module.get_value('record_id')==-1){
                 show_previousinformation(this);

                 //changes for personal
                 show_previous_personal(this);
                 //changes for personal

                 show_familyinformation(this);
             }
             else
              {

                 //$('#employee_id').val(user.get_value('user_id'));
                 show_previous_previousinformation(module.get_value('record_id'));
                 show_family_previous_information(module.get_value('record_id'));

                 //changes for personal
                 show_edited_personal(module.get_value('record_id'));
                 //changes for personal
              }


            if(user.get_value('post_control') != 1) {
                //not permanent just to show the name list when super admin is logged in
                if(user.get_value('user_id') != 1){
                    var testval=$('#employee_id option[value="' + user.get_value('user_id') + '"]').text();
                    $('#employee_id').val(user.get_value('user_id'));
                    show_familyinformation(this);
                    show_previousinformation(this);
                    //changes for personal
                    show_previous_personal(this);
                    //changes for personal
                    show_otherinformation(this);
                    $('#employee_id').replaceWith('<input type="hidden" id="employee_id" name="employee_id" value="'+user.get_value('user_id')+'" /><input type="textbox" style="border:0px" readonly=true value="'+ testval+'"/>');
                }
            }

        } else {
        //alert(module.get_value('record_id'));
        if(module.get_value('record_id')==-1){
            show_previousinformation(this);
            //changes for personal
            show_previous_personal(this);
            //changes for personal
            show_familyinformation(this);
        }
        else
        {

            //changes for personal
            show_edited_personal(module.get_value('record_id'));
            //changes for personal

            show_family_previous_information(module.get_value('record_id'));
            //var testval=$('#employee_id option[value="' + user.get_value('user_id') + '"]').text();
            //$('#employee_id').val(user.get_value('user_id'));
            if(user.get_value('post_control') == 1) {
                //not permanent just to show the name list when super admin is logged in
                if(user.get_value('user_id') != 1){
                    var fix_name=$('#employee_id option[value="' + $('#employee_id').val() + '"]').text();
                    if(module.get_value('record_id') == '-1') $('#employee_id').val(user.get_value('user_id'));
                    $('#employee_id').replaceWith('<input type="hidden" id="employee_id" name="employee_id" value="'+$('#employee_id').val()+'" /><input type="textbox" style="border:0px" readonly=true value="'+fix_name+'"/>');
                }
            }
            // $('#employee_id').val(user.get_value('user_id'));
            // show_previous_previousinformation(module.get_value('record_id'));
        }

            show_otherinformation(this);
            if(user.get_value('post_control') != 1) {
                //not permanent just to show the name list when super admin is logged in
                if(user.get_value('user_id') != 1){
                    var testval=$('#employee_id option[value="' + user.get_value('user_id') + '"]').text();
                    $('#employee_id').val(user.get_value('user_id'));
                    $('#employee_id').replaceWith('<input type="hidden" id="employee_id" name="employee_id" value="'+user.get_value('user_id')+'" /><input type="textbox" style="border:0px" readonly=true value="'+ testval+'"/>');
                }
            }
        }
    });

    $('#employee_id').trigger('change');

    $('.icon-16-approve').stop().live('click', function () {
        if(!$(this).hasClass('status-buttons')){
          change_status($(this).parents('tr').attr('id'), 'approve');
        }
        else{


              var selected = $("#"+$(this).attr('container')).jqGrid("getGridParam", "selarrrow");

              if(selected.length > 0)
              {
                  if(selected[0] == '')
                  {
                      //remove the value for "check all"
                      selected.shift();
                  }
                  approve_record(selected, $(this).attr('module_link'), $(this).attr('container'));
              }
              else{
                  $('#message-container').html(message_growl('attention', 'No record was selected!'))
              }


        }
    });


    $('.icon-16-disapprove').live('click', function () {
        if(!$(this).hasClass('status-buttons')){
          change_status($(this).parents('tr').attr('id'), 'decline');
        }
        else{


                var selected = $("#"+$(this).attr('container')).jqGrid("getGridParam", "selarrrow");

                if(selected.length > 0)
                {
                    if(selected[0] == '')
                    {
                        //remove the value for "check all"
                        selected.shift();
                    }
                    disapprove_record(selected, $(this).attr('module_link'), $(this).attr('container'));
                }
                else{
                    $('#message-container').html(message_growl('attention', 'No record was selected!'))
                }

        }
    }); 
    
    if(module.get_value('view') == "edit" || module.get_value('view') == "index"){
         $("#sss").mask("99-9999999-9", {placeholder: "x"});
         $("#philhealth").mask("99-999999999-9",{placeholder: "x"});
         $("#tin").mask("999-999-999-999", {placeholder: "x"});
         $("#pagibig").mask("9999-9999-9999", {placeholder: "x"});
    }

    $('.d').change(function () {
       if(Date.parse($(this).val()) > Date.now())
       {
            Boxy.alert('Date Cannot Be Greater Than The Current Date');
            $(this).val('');
       }
    });

    if(module.get_value('view')=='detail')
    {
        $('label[for="emergency_tag"]').siblings('.text-input-wrap').remove();
        $('label[for="emergency_tag"]').removeClass('label-desc');
        $('label[for="emergency_tag"]').removeClass('view');
        $('label[for="emergency_tag"]').removeClass('gray');
        $('label[for="emergency_tag"]').css('font-weight','Bold');
        $('label[for="emergency_tag"]').css('margin-top','100px');
    }

    if(module.get_value('view')=='edit')
    {
        $('#civil_status_id').change(function () {
            if($('#civil_status_id').val()==1)
            {
                $('#date_of_marriage').parent().parent().hide();
                $('#date_of_marriage').val('');
            }
            else
                $('#date_of_marriage').parent().parent().show();
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

        $('.bir_dependents').click(function(){
          var age = get_age(  $(this).parent().siblings('.bday').children('.text-input-wrap').find('input').val() );
          if(age > 21 || age == null || age == "" || age == undefined || isNaN(age))
          {
              $(this).attr('checked', false);
              $(this).parent().find('.dependent_value').val('0');
              $('#message-container').html(message_growl('attention', 'Bir dependent must be below 21'));
          }
        });

        $('.icon-16-disk-back').attr('onclick','save_and_email(false)');
        $('.icon-16-disk').parent().remove();
        $('.icon-16-disk-back').children('span').text('Save & Send');
        //alert('hey');

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

        $('a.add-more-eu').click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var form_to_get = $(this).attr('rel');
            url = module.get_value('base_url') +'employees/get_form/' + $(this).attr('rel');
            var data = '';
            if($(this).attr('rel') == 'attachment')
            {
                data = 'counter_line='+(parseFloat($('.count_attachment').val())+1);
                $('.count_attachment').val(parseFloat($('.count_attachment').val())+1);
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
                }
            });
        });          
    } 

  if(module.get_value('view')=='detail')
    {
        var emp_id=$.trim($('label[for="employee_id"]').siblings('.text-input-wrap').text());
        var nicename=$.trim(user.get_value('nicename'));
        if(user.get_value('nicename')!=emp_id)
        {
            $('.icon-16-edit').parent().hide();
            $('.or').css('margin-left','5px');
        }
        //alert(module.get_value('record_id'));
        check_status(module.get_value('record_id'));
        //$('label[for="personal_fname"]').siblings('.text-input-wrap').text();
    }

    $('a.add-more').click(function(event) {
        event.preventDefault();
        var obj = $(this);
        url = module.get_value('base_url') + module.get_value('module_link') + '/get_form_attachment';
        data = 'counter_line='+(parseFloat($('.count_attachment').val())+1);
        $('.count_attachment').val(parseFloat($('.count_attachment').val())+1);
        $.ajax({
            url: url,
            dataType: 'html',
            type:"POST",
            data: data,
            success: function (response) {                
                $('.attachement-container').prepend(response);                
                init_datepick();

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
            }
        });
    });  

    $('a.delete-detail').live('click', function () {
        $(this).parents('div.form-multiple-add').remove();
    });
});

function change_status_multiple(record_id, update_status_id, callback) {
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status_multiple',
        data: 'record_id=' + record_id + '&update_status_id=' + update_status_id,
        type: 'post',
        dataType: 'json',
        success: function(response) {
            message_growl(response.type, response.message);
                    
            if (typeof(callback) == typeof(Function))
                callback(response);
        }
    }); 
}


function approve_record(selected, action_url, container){
    Boxy.ask("Approve "+ selected.length +" selected record(s)?", ["Yes", "Cancel"],
        function( choice ) {
            if(choice == "Yes"){

                change_status_multiple(selected,
                    2,
                    function () {
                        $('#jqgridcontainer').trigger('reloadGrid');
                        /*
                        $.ajax({
                            url: module.get_value('base_url') + module.get_value('module_link') + '/get_for_approval_count',
                            type: 'get',
                            dataType: 'json',
                            success: function (response) {
                                if(response){
                                    $('#approval-counter').html(response.count);
                                }
                            }
                        });
*/
                    }
                );

            }
        },
        {
            title: "Approve Record"
        });
    }

    function disapprove_record(selected, action_url, container){
        Boxy.ask("Disapprove "+ selected.length +" selected record(s)?", ["Yes", "Cancel"],
            function( choice ) {
                if(choice == "Yes"){

                    change_status_multiple(selected,
                        3,
                        function () {
                            $('#jqgridcontainer').trigger('reloadGrid');
                            /*
                            $.ajax({
                                url: module.get_value('base_url') + module.get_value('module_link') + '/get_for_approval_count',
                                type: 'get',
                                dataType: 'json',
                                success: function (response) {
                                    if(response){
                                        $('#approval-counter').html(response.count);
                                    }
                                }
                            });
*/
                        }
                    );

                }
            },
            {
                title: "Disapprove Record"
            });
    }

function check_status(id)
{
  $.ajax({
    url: module.get_value('base_url') +'employee/employee_update/check_status',
    data: 'record_id=' + id,
    type: 'post',
    dataType: 'json',
    success: function (response) {     

      employee = response.data;

      if(employee.employee_update_status_id==1){

          // for Approval
          old_val_array(module.get_value('record_id'));
          old_val_family(module.get_value('record_id'));

      } else if(employee.employee_update_status_id>1){

          // Approved
          old_val_array_bu(module.get_value('record_id'));
          old_val_family_bu(module.get_value('record_id'));

      }
    }   
  });
}
// This set of function will indicate the changes or red in detail view

// This two functions (old_val_array_bu, old_val_family_bu) will indicate the changes when approved detailview is clicked
function old_val_family_bu(rec_id)
{
    var send='record_id='+rec_id;
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/old_val_family_bu',
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'No_changes') {
                $('.fam').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
            } else {
                employee = response.data;
                ctr=0;
                for(var i in employee)
                {
                    ctr++;
                    var flag = parseInt(i)+1;
                    var fam_name = $.trim($('label[for="'+flag+'name"]').siblings('.text-input-wrap').text());
                    var fam_relationship = $.trim($('label[for="'+flag+'relationship"]').siblings('.text-input-wrap').text());
                    var fam_birthdate = $.trim($('label[for="'+flag+'birthdate"]').siblings('.text-input-wrap').text());
                    var fam_occupation = $.trim($('label[for="'+flag+'occupation"]').siblings('.text-input-wrap').text());
                    var fam_employer = $.trim($('label[for="'+flag+'employer"]').siblings('.text-input-wrap').text());
                    var fam_educational_attainment = $.trim($('label[for="'+flag+'educational_attainment"]').siblings('.text-input-wrap').text());
                    var fam_degree = $.trim($('label[for="'+flag+'degree"]').siblings('.text-input-wrap').text());
                    var fam_ecf = $.trim($('label[for="'+flag+'ecf_dependent"]').siblings('.text-input-wrap').text());
                    var fam_bir = $.trim($('label[for="'+flag+'bir_dependent"]').siblings('.text-input-wrap').text());
                    var fam_hospitalization = $.trim($('label[for="'+flag+'hospitalization_dependent"]').siblings('.text-input-wrap').text());

                    var ecf_dependent = (employee[i].ecf_dependent > 0 ? 'Yes' : 'No');
                    var bir_dependent = (employee[i].bir_dependent > 0 ? 'Yes' : 'No');
                    var hospitalization_dependent = (employee[i].hospitalization_dependent > 0 ? 'Yes' : 'No');
                    
                    if(employee[i].name!=fam_name)
                    {
                        $('label[for="'+flag+'name"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].name);
                    }
                    if(employee[i].relationship!=fam_relationship)
                    {
                        $('label[for="'+flag+'relationship"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].relationship);
                    }
                    if(fam_birthdate!=''){
                        var birthdate_w_format=$.datepicker.formatDate('mm/dd/yy',new Date(employee[i].birth_date));
                        if(birthdate_w_format!=fam_birthdate)
                            $('label[for="'+flag+'birthdate"]').siblings('.text-input-wrap').css('color','RED').attr('title',birthdate_w_format);
                    }
                    // if(employee[i].birth_date!=fam_birthdate)
                    // {
                    //     $('label[for="'+flag+'birthdate"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].birth_date);
                    // }
                    if(employee[i].occupation!=fam_occupation)
                    {
                        $('label[for="'+flag+'occupation"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].occupation);
                    }
                    if(employee[i].employer!=fam_employer)
                    {
                         $('label[for="'+flag+'employer"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].employer);
                    }
                    if(employee[i].educational_attainment!=fam_educational_attainment)
                    {
                        $('label[for="'+flag+'educational_attainment"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].educational_attainment);
                    }
                    if(employee[i].degree!=fam_degree)
                    {
                        $('label[for="'+flag+'degree"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].degree);
                    }

                    if(ecf_dependent!=fam_ecf)
                    {
                         $('label[for="'+flag+'ecf_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title',ecf_dependent);
                    }
                    if(bir_dependent!=fam_bir)
                    {
                        $('label[for="'+flag+'bir_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title',bir_dependent);
                    }
                    if(hospitalization_dependent!=fam_hospitalization)
                    {
                        $('label[for="'+flag+'hospitalization_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title',hospitalization_dependent);
                    }
                }
                var total_count_of_fam=$('#total_count_fam_in_detail').val();
                var total_family_left=parseInt(total_count_of_fam)-parseInt(ctr);
                for(flag=parseInt(ctr)+1;flag<=total_count_of_fam;flag++)
                {
                    $('label[for="'+flag+'name"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'relationship"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'birthdate"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'occupation"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'employer"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'educational_attainment"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'degree"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'ecf_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'bir_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'hospitalization_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                }

            }
        }
    });
}

function old_val_array_bu(rec_id)
{
    var send='record_id='+rec_id;
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/old_val_array_bu',
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
               // $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                var fname=$.trim($('label[for="personal_fname"]').siblings('.text-input-wrap').text());
                var mname=$.trim($('label[for="personal_mname"]').siblings('.text-input-wrap').text());
                var lname=$.trim($('label[for="personal_lname"]').siblings('.text-input-wrap').text());
                var dom=$.trim($('label[for="personal_dom"]').siblings('.text-input-wrap').text());
                var civil_status=$.trim($('label[for="civil_status_id"]').siblings('.text-input-wrap').text());
                var mobile=$.trim($('label[for="mobile"]').siblings('.text-input-wrap').text());
                var home_phone=$.trim($('label[for="home_phone"]').siblings('.text-input-wrap').text());
                var emergency_name=$.trim($('label[for="emergency_name"]').siblings('.text-input-wrap').text());
                var emergency_phone=$.trim($('label[for="emergency_phone"]').siblings('.text-input-wrap').text());
                var emergency_relationship=$.trim($('label[for="emergency_relationship"]').siblings('.text-input-wrap').text());
                var emergency_address=$.trim($('label[for="emergency_address"]').siblings('.text-input-wrap').text());
                var pres_address1=$.trim($('label[for="pres_address1"]').siblings('.text-input-wrap').text());
                var pres_address2=$.trim($('label[for="pres_address2"]').siblings('.text-input-wrap').text());
                var pres_city=$.trim($('label[for="pres_city"]').siblings('.text-input-wrap').text());
                var pres_province=$.trim($('label[for="pres_province"]').siblings('.text-input-wrap').text());
                var pres_zipcode=$.trim($('label[for="pres_zipcode"]').siblings('.text-input-wrap').text());
                var perm_address1=$.trim($('label[for="perm_address1"]').siblings('.text-input-wrap').text());
                var perm_address2=$.trim($('label[for="perm_address2"]').siblings('.text-input-wrap').text());
                var perm_city=$.trim($('label[for="perm_city"]').siblings('.text-input-wrap').text());
                var perm_province=$.trim($('label[for="perm_province"]').siblings('.text-input-wrap').text());
                var perm_zipcode=$.trim($('label[for="perm_zipcode"]').siblings('.text-input-wrap').text());
                
                if(employee.firstname!=fname)
                    $('label[for="personal_fname"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.firstname);
                if(employee.middlename!=mname)
                    $('label[for="personal_mname"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.middlename);
                if(employee.lastname!=lname)
                    $('label[for="personal_lname"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.lastname);
                if(dom!=''){
                    var birthdate_w_format=$.datepicker.formatDate('mm/dd/yy',new Date(employee.date_of_marriage));
                    if(birthdate_w_format!=dom)
                        $('label[for="personal_dom"]').siblings('.text-input-wrap').css('color','RED').attr('title',birthdate_w_format);
                }

                if(employee.civil_status!=civil_status)
                    $('label[for="civil_status_id"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.civil_status);
                
                if(employee.mobile!=mobile)
                    $('label[for="mobile"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.mobile);
                if(employee.home_phone!=home_phone)
                    $('label[for="home_phone"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.home_phone);
                if(employee.emergency_name!=emergency_name)
                    $('label[for="emergency_name"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.emergency_name);
                if(employee.emergency_phone!=emergency_phone)
                    $('label[for="emergency_phone"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.emergency_phone);
                if(employee.emergency_relationship!=emergency_relationship)
                    $('label[for="emergency_relationship"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.emergency_relationship);
                if(employee.emergency_address!=emergency_address)
                    $('label[for="emergency_address"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.emergency_address);

                if(employee.pres_address1!=pres_address1)
                    $('label[for="pres_address1"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_address1);
                if(employee.pres_address2!=pres_address2)
                    $('label[for="pres_address2"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_address2);
                if(employee.pres_city_full!=pres_city)
                    $('label[for="pres_city"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_city_full);
                if(employee.pres_province!=pres_province)
                    $('label[for="pres_province"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_province);
                if(employee.pres_zipcode!=pres_zipcode)
                    $('label[for="pres_zipcode"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_zipcode);

                if(employee.perm_address1!=perm_address1)
                    $('label[for="perm_address1"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_address1);
                if(employee.perm_address2!=perm_address2)
                    $('label[for="perm_address2"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_address2);
                if(employee.perm_city_full!=perm_city)
                    $('label[for="perm_city"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_city_full);
                if(employee.perm_province!=perm_province)
                    $('label[for="perm_province"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_province);
                if(employee.perm_zipcode!=perm_zipcode)
                    $('label[for="perm_zipcode"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_zipcode);
            }
        }
    });
}
// This two functions (old_val_array_bu, old_val_family_bu) will indicate the changes when approved detailview is clicked

// This two functions (old_val_array, old_val_family) will indicate the changes when for approval detailview is clicked
function old_val_array(rec_id)
{
    // record_id is use to validate even if admin was the one who created the file for the employee.
    var send = 'record_id='+rec_id;

    // to get value changes for pioneer special scenario
    if(module.get_value('client_no') == 1)
      var url = module.get_value('base_url') + 'employee/employee_update/old_val_array_bu';
    else
      var url = module.get_value('base_url') + 'employee/employee_update/old_val_array'

    $.ajax({
        url: url,
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
              //  $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                var fname=$.trim($('label[for="personal_fname"]').siblings('.text-input-wrap').text());
                var mname=$.trim($('label[for="personal_mname"]').siblings('.text-input-wrap').text());
                var lname=$.trim($('label[for="personal_lname"]').siblings('.text-input-wrap').text());
                var dom=$.trim($('label[for="personal_dom"]').siblings('.text-input-wrap').text());
                
                var mobile=$.trim($('label[for="mobile"]').siblings('.text-input-wrap').text());
                var home_phone=$.trim($('label[for="home_phone"]').siblings('.text-input-wrap').text());
                var emergency_name=$.trim($('label[for="emergency_name"]').siblings('.text-input-wrap').text());
                var emergency_phone=$.trim($('label[for="emergency_phone"]').siblings('.text-input-wrap').text());
                var emergency_relationship=$.trim($('label[for="emergency_relationship"]').siblings('.text-input-wrap').text());
                var emergency_address=$.trim($('label[for="emergency_address"]').siblings('.text-input-wrap').text());
                var pres_address1=$.trim($('label[for="pres_address1"]').siblings('.text-input-wrap').text());
                var pres_address2=$.trim($('label[for="pres_address2"]').siblings('.text-input-wrap').text());
                var pres_city=$.trim($('label[for="pres_city"]').siblings('.text-input-wrap').text());
                var pres_province=$.trim($('label[for="pres_province"]').siblings('.text-input-wrap').text());
                var pres_zipcode=$.trim($('label[for="pres_zipcode"]').siblings('.text-input-wrap').text());
                var perm_address1=$.trim($('label[for="perm_address1"]').siblings('.text-input-wrap').text());
                var perm_address2=$.trim($('label[for="perm_address2"]').siblings('.text-input-wrap').text());
                var perm_city=$.trim($('label[for="perm_city"]').siblings('.text-input-wrap').text());
                var perm_province=$.trim($('label[for="perm_province"]').siblings('.text-input-wrap').text());
                var perm_zipcode=$.trim($('label[for="perm_zipcode"]').siblings('.text-input-wrap').text());

                if($.trim(employee.firstname)!=fname)
                    $('label[for="personal_fname"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.firstname);
                if($.trim(employee.middlename)!=mname)
                    $('label[for="personal_mname"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.middlename);
                if($.trim(employee.lastname)!=lname)
                    $('label[for="personal_lname"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.lastname);
                if(dom!=''){
                    var birthdate_w_format=$.trim($.datepicker.formatDate('mm/dd/yy',new Date(employee.date_of_marriage)));
                    if(birthdate_w_format!=dom)
                        $('label[for="personal_dom"]').siblings('.text-input-wrap').css('color','RED').attr('title',birthdate_w_format);
                }

                if(module.get_value('client_no') != 1)
                {
                  var civil_status=$.trim($('label[for="civil_status_id"]').siblings('.text-input-wrap').text());

                  if($.trim(employee.civil_status)!=civil_status)
                      $('label[for="civil_status_id"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.civil_status);
                }
                if($.trim(employee.mobile)!=mobile)
                    $('label[for="mobile"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.mobile);
                if($.trim(employee.home_phone)!=home_phone)
                    $('label[for="home_phone"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.home_phone);
                if($.trim(employee.emergency_name)!=emergency_name)
                    $('label[for="emergency_name"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.emergency_name);
                if($.trim(employee.emergency_phone)!=emergency_phone)
                    $('label[for="emergency_phone"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.emergency_phone);
                if($.trim(employee.emergency_relationship)!=emergency_relationship)
                    $('label[for="emergency_relationship"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.emergency_relationship);
                if($.trim(employee.emergency_address)!=emergency_address)
                    $('label[for="emergency_address"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.emergency_address);

                if($.trim(employee.pres_address1)!=pres_address1)
                    $('label[for="pres_address1"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_address1);
                if($.trim(employee.pres_address2)!=pres_address2)
                    $('label[for="pres_address2"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_address2);
                if($.trim(employee.pres_city_full)!=pres_city)
                    $('label[for="pres_city"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_city_full);
                if($.trim(employee.pres_province)!=pres_province)
                    $('label[for="pres_province"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_province);
                if($.trim(employee.pres_zipcode)!=pres_zipcode)
                    $('label[for="pres_zipcode"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.pres_zipcode);

                if($.trim(employee.perm_address1)!=perm_address1)
                    $('label[for="perm_address1"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_address1);
                if($.trim(employee.perm_address2)!=perm_address2)
                    $('label[for="perm_address2"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_address2);
                if($.trim(employee.perm_city_full)!=perm_city)
                    $('label[for="perm_city"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_city_full);
                if($.trim(employee.perm_province)!=perm_province)
                    $('label[for="perm_province"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_province);
                if($.trim(employee.perm_zipcode)!=perm_zipcode)
                    $('label[for="perm_zipcode"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.perm_zipcode);
            }
        }
    });

    if(module.get_value('client_no') != 1)
    {
      $.ajax({
          url: module.get_value('base_url') + 'employee/employee_update/old_val_array',
          data: send,
          dataType: 'json',
          type: 'post',
          success: function (response) {
              if (response.msg_type == 'error') {
                //  $('#message-container').html(message_growl(response.msg_type, response.msg));
              } else {
                employee = response.data;
                var civil_status=$.trim($('label[for="civil_status_id"]').siblings('.text-input-wrap').text());

                if(employee.civil_status!=civil_status)
                      $('label[for="civil_status_id"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee.civil_status);
              }
          }
      });
    }
}

function old_val_family(rec_id)
{
    var send='record_id='+rec_id;
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/old_val_family',
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'No_changes') {
                $('.fam').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
            } else {
                employee = response.data;
                var ctr=0;
                for(var i in employee)
                {
                    ctr++;
                    var flag = parseInt(i)+1;
                    var fam_name = $.trim($('label[for="'+flag+'name"]').siblings('.text-input-wrap').text());
                    var fam_relationship = $.trim($('label[for="'+flag+'relationship"]').siblings('.text-input-wrap').text());
                    var fam_birthdate = $.trim($('label[for="'+flag+'birthdate"]').siblings('.text-input-wrap').text());
                    var fam_occupation = $.trim($('label[for="'+flag+'occupation"]').siblings('.text-input-wrap').text());
                    var fam_employer = $.trim($('label[for="'+flag+'employer"]').siblings('.text-input-wrap').text());
                    var fam_educational_attainment = $.trim($('label[for="'+flag+'educational_attainment"]').siblings('.text-input-wrap').text());
                    var fam_degree = $.trim($('label[for="'+flag+'degree"]').siblings('.text-input-wrap').text());
                    var fam_ecf = $.trim($('label[for="'+flag+'ecf_dependent"]').siblings('.text-input-wrap').text());
                    var fam_bir = $.trim($('label[for="'+flag+'bir_dependent"]').siblings('.text-input-wrap').text());
                    var fam_hospitalization = $.trim($('label[for="'+flag+'hospitalization_dependent"]').siblings('.text-input-wrap').text());

                    var ecf_dependent = (employee[i].ecf_dependent > 0 ? 'Yes' : 'No');
                    var bir_dependent = (employee[i].bir_dependents > 0 ? 'Yes' : 'No');
                    var hospitalization_dependent = (employee[i].hospitalization_dependents > 0 ? 'Yes' : 'No');
                    
                    if(employee[i].name!=fam_name)
                    {
                        $('label[for="'+flag+'name"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].name);
                    }

                    if(employee[i].relationship!=fam_relationship)
                    {
                        $('label[for="'+flag+'relationship"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].relationship);
                    }
                    if(fam_birthdate!=''){
                        var birthdate_w_format=$.datepicker.formatDate('mm/dd/yy',new Date(employee[i].birth_date));
                        if(birthdate_w_format!=fam_birthdate)
                            $('label[for="'+flag+'birthdate"]').siblings('.text-input-wrap').css('color','RED').attr('title',birthdate_w_format);
                    }

                    if(employee[i].occupation!=fam_occupation)
                    {
                        $('label[for="'+flag+'occupation"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].occupation);
                    }

                    if(employee[i].employer!=fam_employer)
                    {
                        $('label[for="'+flag+'employer"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].employer);
                    }

                    if(employee[i].educational_attainment!=fam_educational_attainment)
                    {
                        $('label[for="'+flag+'educational_attainment"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].educational_attainment);
                    }

                    if(employee[i].degree!=fam_degree)
                    {
                        $('label[for="'+flag+'degree"]').siblings('.text-input-wrap').css('color','RED').attr('title',employee[i].degree);
                    }
                    // alert("ECF"+ecf_dependent+" "+fam_ecf);
                    if(ecf_dependent!=fam_ecf)
                    {
                         $('label[for="'+flag+'ecf_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title',ecf_dependent);
                    }
                    // alert("BIR"+bir_dependent+" "+fam_bir);
                    if(bir_dependent!=fam_bir)
                    {
                        $('label[for="'+flag+'bir_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title',bir_dependent);
                    }
                    if(hospitalization_dependent!=fam_hospitalization)
                    {
                        $('label[for="'+flag+'hospitalization_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title',hospitalization_dependent);
                    }
                }
                var total_count_of_fam=$('#total_count_fam_in_detail').val();
                var total_family_left=parseInt(total_count_of_fam)-parseInt(ctr);
                for(flag=parseInt(ctr)+1;flag<=total_count_of_fam;flag++)
                {
                    $('label[for="'+flag+'name"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'relationship"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'birthdate"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'occupation"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'employer"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'educational_attainment"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'degree"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'ecf_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'bir_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                    $('label[for="'+flag+'hospitalization_dependent"]').siblings('.text-input-wrap').css('color','RED').attr('title','Not part of the family before');
                }
            }
        }
    });
}
// This two functions (old_val_array, old_val_family) will indicate the changes when for approval detailview is clicked

// This set of function will indicate the changes or red in detail view


function clone()
{
    if($('#family_container div.1f:first').find('input:text').val()!==""){

        $('#family_container').prepend($('#family_sample_form div.1f:first').clone(true));
        $('#family_container div.1f:first').find('input:text').val('');
        globalvar=globalvar+1;
        $('#family_container div.1f:first').find('input:text').eq(9).val(globalvar);
        $('#family_container div.1f:first').find('.add-more-div').show();    
        $('#family_container div.1f:first').css('display','block');
        $(".d").removeClass("hasDatepicker").attr('id','');
        $(".d").addClass("date");
        init_datepick();
        $('.d').parent().find('.ui-datepicker-trigger:last').remove();
    }
}

function removeClone(delme)
{
    $(delme).parent().parent().parent().remove();
}

function show_otherinformation(val_id)
{
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/get_employee',
        data: $(val_id).serialize(),
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
                //$('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                $('label[for="dummy_position"]').siblings('.text-input-wrap').text(employee.position);
                $('label[for="dummy_department"]').siblings('.text-input-wrap').text(employee.department);
                $('label[for="dummy"]').siblings('.text-input-wrap').text(employee.employed_date);
                $('label[for="dummy_company"]').siblings('.text-input-wrap').text(employee.company);
                if(employee.employed_date==null)
                    $('label[for="dummy"]').siblings('.text-input-wrap').text('No Employed Date');
            }
        }
    });
}


function show_familyinformation(val_id)
{
    clear_fam_val();
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/get_family',
        data: $(val_id).serialize(),
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
              return;
              //  $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                for(var i in employee){
                    clone();
                    $('.1f input:text').eq(0).val(employee[i].name);
                    $('.1f select:first').val(employee[i].relationship);
                    if(employee[i].birth_date==null || employee[i].birth_date=="1970-01-01" || employee[i].birth_date=="0000-00-00")
                        $('.1f input:text').eq(1).val('');
                    else
                    {
                        var birthdate_w_format=$.datepicker.formatDate('mm/dd/yy',new Date(employee[i].birth_date));
                        $('.1f input:text').eq(1).val(birthdate_w_format);
                    }
                    if(employee[i].occupation.indexOf('(DECEASED)') != -1)
                    {
                      $('.1f input:text').eq(0).attr('readonly', 'readonly');
                      // $('.1f input:text').eq(1).attr('readonly', 'readonly');
                      $('.1f input:text').eq(2).attr('readonly', 'readonly');
                      $('.1f input:text').eq(3).attr('readonly', 'readonly');
                      $('.1f input:text').eq(4).attr('readonly', 'readonly');
                      // $('.1f select:eq(1)').attr('readonly', 'readonly');
                    }

                    $('.1f input:text').eq(2).val(employee[i].occupation);
                    $('.1f input:text').eq(3).val(employee[i].employer);
                    //added
                    $('.1f select:eq(1)').val(employee[i].educational_attainment);
                    $('.1f input:text').eq(4).val(employee[i].degree);
                    $('.1f input:text').eq(5).val(employee[i].ecf_dependent);
                    $('.1f input:text').eq(6).val(employee[i].bir_dependents);
                    $('.1f input:text').eq(7).val(employee[i].hospitalization_dependents);

                    if(employee[i].occupation.indexOf('(DECEASED)') != -1)
                    {
                      $('.1f input:checkbox:eq(0)').attr('disabled', 'disabled');
                      $('.1f input:checkbox:eq(1)').attr('disabled', 'disabled');
                      $('.1f input:checkbox:eq(2)').attr('disabled', 'disabled');
                    }

                    $('.1f input:checkbox:eq(0)').prop('checked', (employee[i].ecf_dependent > 0 ? true : false));
                    $('.1f input:checkbox:eq(1)').prop('checked', (employee[i].bir_dependents > 0 ? true : false));
                    $('.1f input:checkbox:eq(2)').prop('checked', (employee[i].hospitalization_dependents > 0 ? true : false));
                    $('.1f input:text').eq(8).val(Number(i)+1);
                    //added
                    //$('.1f input:text').eq(4).val(Number(i)+1);
                    globalvar=Number(i)+1;
                }

            }
        }
    });
}

function show_family_previous_information(rec_id)
{
    //clear_fam_val();
    var send='record_id='+rec_id;
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/get_previous_family',
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
                $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;   
        
                for(var i in employee){
                    clone();
                    $('.1f input:text').eq(0).val(employee[i].name);
                    $('.1f select:first').val(employee[i].relationship);
                    if(employee[i].birthdate=="1970-01-01")
                        $('.1f input:text').eq(1).val('');
                    else
                    {
                        var birthdate_w_format=$.datepicker.formatDate('mm/dd/yy',new Date(employee[i].birthdate));
                        $('.1f input:text').eq(1).val(birthdate_w_format);
                    }
                    $('.1f input:text').eq(2).val(employee[i].occupation);
                    $('.1f input:text').eq(3).val(employee[i].employer);
                     //added
                    $('.1f select:eq(1)').val(employee[i].educational_attainment);
                    $('.1f input:text').eq(4).val(employee[i].degree);
                    $('.1f input:text').eq(5).val(employee[i].ecf_dependent);
                    $('.1f input:text').eq(6).val(employee[i].bir_dependent);
                    $('.1f input:text').eq(7).val(employee[i].hospitalization_dependent);

                    $('.1f input:checkbox:eq(0)').prop('checked', (employee[i].ecf_dependent > 0 ? true : false));
                    $('.1f input:checkbox:eq(1)').prop('checked', (employee[i].bir_dependent > 0 ? true : false));
                    $('.1f input:checkbox:eq(2)').prop('checked', (employee[i].hospitalization_dependent > 0 ? true : false));
                    // $('.1f input:text').eq(8).val(Number(i)+1);
                    //added

                    $('.1f input:text').eq(8).val(employee[i].already_exist);
                    $('.1f input:text').eq(9).val(employee[i].flagcount);
                }
            }
        }
    });
}

function show_previousinformation(val_id)
{
    //clear_val();
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/get_previousinformation',
        data: $(val_id).serialize(),
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
                // $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                for(var i in employee){
                    $('#pres_address1').val(employee[i].pres_address1);
                    $('#pres_address2').val(employee[i].pres_address2);
                    if(employee[i].present_city != null)
                    {
                      $('#pres_city').val(employee[i].pres_city);
                      $('#pres_city').siblings().find('span').replaceWith('<span>'+employee[i].present_city+'</span><abbr class="search-choice-close"></abbr>').text();
                      $('#pres_city_chzn').find('a').removeClass('chzn-default');
                    }
                    $('#pres_province').val(employee[i].pres_province);
                    $('#pres_zipcode').val(employee[i].pres_zipcode);

                    $('#perm_address1').val(employee[i].perm_address1);
                    $('#perm_address2').val(employee[i].perm_address2);
                    //$('#perm_city').val(employee[i].perm_city);
                    if(employee[i].permanent_city != null)
                    {
                      $('#perm_city').val(employee[i].perm_city);
                      $('#perm_city').siblings().find('span').replaceWith('<span>'+employee[i].permanent_city+'</span><abbr class="search-choice-close"></abbr>').text();
                      $('#perm_city_chzn').find('a').removeClass('chzn-default');
                    }
                    $('#perm_province').val(employee[i].perm_province);
                    $('#perm_zipcode').val(employee[i].perm_zipcode);

                    //Changes for Personal
                    $('#home_phone').val(employee[i].home_phone);
                    $('#mobile').val(employee[i].mobile);
                    $('#emergency_name').val(employee[i].emergency_name);
                    $('#emergency_relationship').val(employee[i].emergency_relationship);
                    $('#emergency_phone').val(employee[i].emergency_phone);
                    $('#emergency_address').val(employee[i].emergency_address);
                    $('#civil_status_id').val(employee[i].civil_status_id)
                }

            }
        }
    });
}

function show_previous_previousinformation(rec_id)
{
    clear_val();
    var send='record_id='+rec_id;
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/get_editedinfo',
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
               // $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                for(var i in employee){
                    $('#pres_address1').val(employee[i].pres_address1);
                    $('#pres_address2').val(employee[i].pres_address2);
                    //$('#pres_city').val(employee[i].pres_city);
                    if(employee[i].present_city != null)
                    {
                      $('#pres_city').val(employee[i].pres_city);
                      $('#pres_city').siblings().find('span').replaceWith('<span>'+employee[i].present_city+'</span><abbr class="search-choice-close"></abbr>').text();
                      $('#pres_city_chzn').find('a').removeClass('chzn-default');
                    }
                    $('#pres_province').val(employee[i].pres_province);
                    $('#pres_zipcode').val(employee[i].pres_zipcode);

                    $('#perm_address1').val(employee[i].perm_address1);
                    $('#perm_address2').val(employee[i].perm_address2);
                    //$('#perm_city').val(employee[i].perm_city);
                    if(employee[i].permanent_city != null)
                    {
                      $('#perm_city').val(employee[i].perm_city);
                      $('#perm_city').siblings().find('span').replaceWith('<span>'+employee[i].permanent_city+'</span><abbr class="search-choice-close"></abbr>').text();
                      $('#perm_city_chzn').find('a').removeClass('chzn-default');
                    }
                    $('#perm_province').val(employee[i].perm_province);
                    $('#perm_zipcode').val(employee[i].perm_zipcode);

                    //Changes for Personal
                    $('#home_phone').val(employee[i].home_phone);
                    $('#mobile').val(employee[i].mobile);
                    $('#emergency_name').val(employee[i].emergency_name);
                    $('#emergency_relationship').val(employee[i].emergency_relationship);
                    $('#emergency_phone').val(employee[i].emergency_phone);
                    $('#emergency_address').val(employee[i].emergency_address);
                    $('#civil_status_id').val(employee[i].civil_status_id);
                }

            }
        }
    });
}

function show_previous_personal(val_id)
{
    //clear_val();
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/show_previous_personal',
        data: $(val_id).serialize(),
        dataType: 'json',
        type: 'post',
        success: function (response) {
             if (response.msg_type == 'error') {
            //     $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                    $('#first_name').val(employee.firstname);
                    $('#middle_name').val(employee.middlename);
                    $('#last_name').val(employee.lastname);
                    if(employee.date_of_marriage=="1970-01-01" || employee.date_of_marriage==null || employee.date_of_marriage == "0000-00-00" || employee.date_of_marriage == "NaN/NaN/NaN")
                        $('#date_of_marriage').val('');
                    else
                    {
/*                        var birthdate_w_format=$.datepicker.formatDate('mm/dd/yy',new Date(employee.date_of_marriage));
                        $('#date_of_marriage').val(birthdate_w_format);*/
                        var dom = new Date(employee.date_of_marriage);
                        var dom_w_format = ('0'+(dom.getMonth()+1)).slice(-2) + '/' + dom.getDate() + '/' +  dom.getFullYear();
                        $('#date_of_marriage').val(dom_w_format);                                            
                    }
                    ( employee.civil_status_id == 1 ? $('#date_of_marriage').parent().parent().hide() : '' );

            }
        }
    });
}

function show_edited_personal(rec_id)
{
    //clear_fam_val();
    var send='record_id='+rec_id;
    $.ajax({
        url: module.get_value('base_url') + 'employee/employee_update/show_edited_personal',
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
            //    $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                $('#first_name').val(employee.personal_fName);
                $('#middle_name').val(employee.personal_mName);
                $('#last_name').val(employee.personal_lName);
                //if(employee.personal_dom=="1970-01-01" || employee.personal_dom==null || isNaN(employee.personal_dom) || employee.personal_dom == "0000-00-00" || employee.personal_dom == "NaN/NaN/NaN"){
                if(employee.personal_dom=="1970-01-01" || employee.personal_dom==null || employee.personal_dom == "0000-00-00" || employee.personal_dom == "NaN/NaN/NaN")
                    $('#date_of_marriage').val('');
                else
                {
/*                    var birthdate_w_format=$.datepicker.formatDate('mm/dd/yy',new Date(employee.personal_dom));
                    $('#date_of_marriage').val(birthdate_w_format);*/
                    var dom = new Date(employee.personal_dom);
                    var dom_w_format = ('0'+(dom.getMonth()+1)).slice(-2) + '/' + dom.getDate() + '/' +  dom.getFullYear();
                    $('#date_of_marriage').val(dom_w_format);                    
                }
            }
        }
    });
}

function clear_fam_val()
{
    $('.1f input:text').val('');
    $('.1f').not('.1f:first').remove();
}

function clear_val()
{
    $('#pres_address1').val('');
    $('#pres_address2').val('');
    $('#pres_city').val('');
    $('#pres_province').val('');
    $('#pres_zipcode').val('');

    $('#perm_address1').val('');
    $('#perm_address2').val('');
    $('#perm_city').val('');
    $('#perm_province').val('');
    $('#perm_zipcode').val('');
}

function change_status(id, status) {

    if( module.get_value('view') != "detail" ){
      $.ajax({
          url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
          data: 'status=' + status + '&record_id=' + id,
          type: 'post',
          success: function (response) {          
              $('#message-container').html(message_growl(response.msg_type, response.msg));
              $("#jqgridcontainer").jqGrid().trigger("reloadGrid");
          }
      });
    }
}

function init_filter_tabs(){
    $('ul#grid-filter li').click(function(){
        $('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
        $(this).addClass('active');

        if( $(this).attr('filter') == 'for_approval' ){
            $('.status-buttons').parent().show();
        }
        else{
            $('.status-buttons').parent().hide();
        }

        filter_memo( 'jqgridcontainer', $(this).attr('filter') );
    });
    //changes for edit button
    if($('ul#grid-filter #approve_tab').hasClass('active'))
    {
        $('.icon-16-edit').remove();
        $("#jqgridcontainer").jqGrid().trigger("reloadGrid");
        globaltag=true;
    }
    else globaltag=false;
    //changes for edit button
}

function filter_memo( jqgridcontainer, filter )
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