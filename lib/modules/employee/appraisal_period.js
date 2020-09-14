$(document).ready(function () {
    var action = module.get_value('view');

    var ap = new Appraisal_period(action);
    
    fn = ap[action];
    
    if (typeof(fn) === typeof(Function)) {
        fn();
    }

    $('label[for="conformed"]').parent().hide();

    $('a.delete-detail').live('click', function () {
        $(this).parent().parent().parent().parent().remove();
    });

    if($('#record_id').val() != '-1' && action == 'edit') {
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/employee_status_change',
            type: 'post',
            dataType: 'json',
            data: 'appraisal_period='+$('#planning_period_id').val(),
            beforeSend:function() {
                $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
                
            },
            success: function (response) {
                $.unblockUI();
                $('#employment_status').val(response.employment_status);
                $('#company_id').val(response.employee_company);
            }
        });
    }

    if (action == 'edit') {
        var record_id = $('#record_id').val();
        get_period_id(record_id);
    };

    // planning_period_id
    $('#planning_period_id').change(function()
    {
        if($(this).val() != '') {
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/employee_status_change',
                type: 'post',
                dataType: 'json',
                data: 'appraisal_period='+$(this).val(),
                beforeSend:function() {
                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
                
                },
                success: function (response) {
                    $.unblockUI();
                    $('#employment_status').val(response.employment_status);
                    $('#company_id').val(response.employee_company);
                }
            });
        } else {
            $('#employment_status').val('');
            $('#company_id').val('');
        }
    });

if ( module.get_value('view') != "index" && parseInt(module.get_value('record_id')) >= 1) {
     $('#employment_status_id').trigger('change');
    $('.reminder_date').datepicker({
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
            
        },
        onClose: function(dateText) {

        }
    });
           
     var count_file = $('.count_attachment').val();
    for(i=1; i<=count_file; i++)
    {
        $('#attachment-photo'+ i).uploadify({
                'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
                'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify3.php',
                'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
                'folder'    : 'uploads/' + module.get_value('module_link'),
                'fileExt'   : '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
                'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
                'auto'      : true,
                'method'    : 'POST',
                'scriptData': {module: module.get_value('module_link'), fullpath: module.get_value('fullpath'), path: "uploads/" + module.get_value('module_link'),field:"attachment-photo",text_id:""+i+""},
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
}

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

});

function Appraisal_period(action) 
{
    this.index = function () {
        
    }

    this.edit = function () {

    }
}

function toggleModuleInactive(obj,val){
    if ($(obj).hasClass('icon-16-active')){
/*      $(obj).removeClass('icon-16-active');
        $(obj).addClass('icon-16-xgreen-orb');*/
        var active = "active=2";
    }
    else{
/*      $(obj).removeClass('icon-16-xgreen-orb');       
        $(obj).addClass('icon-16-active');*/
        var active = "active=1";
    }
    var data = active+"&record_id="+val;
    simpleAjax( module.get_value('base_url') + module.get_value('module_link') + '/toggleonoff', data );
    $('#jqgridcontainer').trigger("reloadGrid");
}

function add_reminder (event) {
   data = 'counter_line='+(parseFloat($('.count_attachment').val())+1);
    $('.count_attachment').val(parseFloat($('.count_attachment').val())+1);
    event.preventDefault();
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/add_reminder_form',
        type: 'post',
        dataType: 'json',
        data: data,
        beforeSend:function() {
        },
        success: function (response) {

            $('#reminder-list').append(response.status_form);

            $('.reminder_date').datepicker({
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
                    
                },
                onClose: function(dateText) {

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

        }
    });
}

function get_period_id(record_id) {

    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_appraisal_planning_period',
        type: 'post',
        dataType: 'json',
        data: 'record_id='+record_id,
        beforeSend:function() {
            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
        
        },
        success: function (response) {
            $.unblockUI();
            $('#planning_period_id').html(response.html);
        }
    });
}