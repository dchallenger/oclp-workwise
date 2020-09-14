$(document).ready(function() {
    init_datepick();

    $('#employment_status_id').change(function() {
        var status_id = $(this).val();
        var company_id = $('#company_id').val();
        get_employees(status_id, company_id);
    });

    $('#company_id').change(function() {
        var company_id = $(this).val();
        var  status_id = $('#employment_status_id').val();
        get_employees(status_id, company_id);
    });

    $('.icon-16-document-stack').live('click',function (){
        var elem = $(this);
        Boxy.ask("Are you sure you want to duplicate this record?", ["Yes", "Cancel"],
        function( choice ) {
            if(choice == "Yes"){                      
                var record_id = $(elem).parent().parent().parent().attr("id");
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/duplicate_records',
                    type: 'post',
                    dataType: 'json',
                    data: 'record_id='+record_id,
                    beforeSend:function() {
                    },
                    success: function (response) {
                        $('#jqgridcontainer').trigger('reloadGrid');
                    }
                });        
            }
        },
        {
            title: "Duplicate Record"
        });
    })

    $('a.delete-detail').live('click', function () {
        $(this).parent().parent().parent().parent().remove();
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

function get_employees(status_id, company_id){
	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_employees',
        type: 'post',
        dataType: 'json',
        data: 'status_id=' + status_id + '&record_id='+module.get_value('record_id') + '&company_id=' + company_id,
        beforeSend:function() {
        },
        success: function (response) {

        	$('#multiselect-employee_id option').remove();
        	if (response !== null) {
				$('#multiselect-employee_id').append(response.result);

                if (response.employees !== '') {
                    $.each(response.employees, function(index, values){
                        $('#multiselect-employee_id option[value="' + values + '"]').attr('selected','selected');
                    });
                };
        	};
        	$('#multiselect-employee_id').multiselect("refresh");

        }
    });
}