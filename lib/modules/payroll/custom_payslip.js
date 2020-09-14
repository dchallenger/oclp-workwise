$( document ).ready( function() 
{    
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_parameters',
        data: '',
        dataType: 'json',
        type: 'post',
        async: false,          
        success: function ( response ) 
        {
            $('label[for="paycode_id"]').parent().remove();
            var paycode = '<div class="form-item odd ">';
            paycode = paycode + '<label class="label-desc gray" for="paycode_id"> Payment Code: <span class="red">*</span></label>';
            paycode = paycode + '<div class="multiselect-input-wrap">';
            paycode = paycode + response.paycode_html;
            paycode = paycode + '</div>';
            $('label[for="user_id"]').parent().before(paycode);
            $('#paycode_id').multiselect().multiselectfilter({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1
            });         

            var div_loader = '<div id="multi-select-loader"></div>';
            $('.icon-label').parent().before(div_loader);
        }
    });

    $("#paycode_id").multiselect({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1,
        close:function(event, ui)
        {
            $('label[for="category_id"]').parent().remove();
            $('label[for="company_id"]').parent().remove();
            $('label[for="location_id"]').parent().remove();
            $('label[for="employee_id"]').parent().remove();            
            $('label[for="division_id"]').parent().remove();
            $('label[for="department_id"]').parent().remove();
            $('label[for="project_name_id"]').parent().remove();
            $('label[for="group_name_id"]').parent().remove();
            var data = $('#report-form').serialize();
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_paycode',
                data: data,
                dataType: 'json',
                type: 'post',
                async: false, 
                beforeSend: function(){
                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
                },         
                success: function ( response ) 
                {   
                    $.unblockUI();       
                    if(response.flag) {
                        var category = '<div class="form-item even ">';
                        category = category + '<label class="label-desc gray" for="category_id"> Category: <span class="red">*</span></label>';
                        category = category + '<div class="select-input-wrap">';
                        category = category + response.category_html;
                        category = category + '</div>';
                        $('label[for="paycode_id"]').parent().after(category);
                    }
                }
            });
        }
    })


    $('#category_id').live('change', function() {
        var category_id = $(this).val();
        var data = $('#report-form').serialize();
        $('label[for="company_id"]').parent().remove();
        $('label[for="location_id"]').parent().remove();
        $('label[for="employee_id"]').parent().remove();
        $('label[for="division_id"]').parent().remove();
        $('label[for="department_id"]').parent().remove();
        $('label[for="project_name_id"]').parent().remove();
        $('label[for="group_name_id"]').parent().remove();
        if(category_id != 0 ){
            switch(category_id) {
                case '5'://company                            
                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_company',
                        type:"POST",
                        data: data,
                        dataType: "json",
                        async: false,
                        beforeSend: function(){
                            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
                        },
                        success: function( response )
                        { 
                            $.unblockUI();        
                            var company = '<div class="form-item even ">';
                            company = company + '<label class="label-desc gray" for="company_id"> Company: <span class="red">*</span></label>';
                            company = company + '<div class="multiselect-input-wrap">';
                            company = company + response.company_html;
                            company = company + '</div>';
                            $('label[for="category_id"]').parent().after(company);
                            $('#company_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var location = '<div class="form-item even ">';
                            location = location + '<label class="label-desc gray" for="location_id"> Location: </label>';
                            location = location + '<div class="multiselect-input-wrap">';
                            location = location + response.location_html;
                            location = location + '</div>';
                            $('label[for="user_id"]').parent().after(location);
                            $('#location_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var employee = '<div class="form-item even ">';
                            employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                            employee = employee + '<div class="multiselect-input-wrap">';
                            employee = employee + response.employee_html;
                            employee = employee + '</div>';
                            $('label[for="location_id"]').parent().after(employee);
                            $('#employee_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });
                        }
                    });
                    break;
                case '1'://division
                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_division',
                        type:"POST",
                        data: data,
                        dataType: "json",
                        async: false,
                        beforeSend: function(){
                            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
                        },
                        success: function( response )
                        { 
                            $.unblockUI();
                            var division = '<div class="form-item even ">';
                            division = division + '<label class="label-desc gray" for="division_id"> Division: <span class="red">*</span></label>';
                            division = division + '<div class="multiselect-input-wrap">';
                            division = division + response.division_html;
                            division = division + '</div>';
                            $('label[for="category_id"]').parent().after(division);
                            $('#division_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var location = '<div class="form-item even ">';
                            location = location + '<label class="label-desc gray" for="location_id"> Location: </label>';
                            location = location + '<div class="multiselect-input-wrap">';
                            location = location + response.location_html;
                            location = location + '</div>';
                            $('label[for="user_id"]').parent().after(location);
                            $('#location_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var employee = '<div class="form-item even ">';
                            employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                            employee = employee + '<div class="multiselect-input-wrap">';
                            employee = employee + response.employee_html;
                            employee = employee + '</div>';
                            $('label[for="location_id"]').parent().after(employee);
                            $('#employee_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });
                        }
                    });    
                    break;
                case '4'://department
                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_dept',
                        type:"POST",
                        data: data,
                        dataType: "json",
                        async: false,
                        beforeSend: function(){
                            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
                        },
                        success: function( response )
                        { 
                            $.unblockUI();
                            var department = '<div class="form-item even ">';
                            department = department + '<label class="label-desc gray" for="department_id"> Department: <span class="red">*</span></label>';
                            department = department + '<div class="multiselect-input-wrap">';
                            department = department + response.department_html;
                            department = department + '</div>';
                            $('label[for="category_id"]').parent().after(department);
                            $('#department_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var location = '<div class="form-item even ">';
                            location = location + '<label class="label-desc gray" for="location_id"> Location: </label>';
                            location = location + '<div class="multiselect-input-wrap">';
                            location = location + response.location_html;
                            location = location + '</div>';
                            $('label[for="user_id"]').parent().after(location);
                            $('#location_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var employee = '<div class="form-item even ">';
                            employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                            employee = employee + '<div class="multiselect-input-wrap">';
                            employee = employee + response.employee_html;
                            employee = employee + '</div>';
                            $('label[for="location_id"]').parent().after(employee);
                            $('#employee_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });
                        }
                    }); 
                    break;
                case '2'://project
                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_proj',
                        type:"POST",
                        data: data,
                        dataType: "json",
                        async: false,
                        beforeSend: function(){
                            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
                        },
                        success: function( response )
                        { 
                            $.unblockUI();
                            var project_name = '<div class="form-item even ">';
                            project_name = project_name + '<label class="label-desc gray" for="project_name_id"> Project: <span class="red">*</span></label>';
                            project_name = project_name + '<div class="multiselect-input-wrap">';
                            project_name = project_name + response.project_name_html;
                            project_name = project_name + '</div>';
                            $('label[for="category_id"]').parent().after(project_name);
                            $('#project_name_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var location = '<div class="form-item even ">';
                            location = location + '<label class="label-desc gray" for="location_id"> Location: </label>';
                            location = location + '<div class="multiselect-input-wrap">';
                            location = location + response.location_html;
                            location = location + '</div>';
                            $('label[for="user_id"]').parent().after(location);
                            $('#location_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var employee = '<div class="form-item even ">';
                            employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                            employee = employee + '<div class="multiselect-input-wrap">';
                            employee = employee + response.employee_html;
                            employee = employee + '</div>';
                            $('label[for="location_id"]').parent().after(employee);
                            $('#employee_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });
                        }
                    });    
                    break;
                case '3'://group
                     $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_group',
                        type:"POST",
                        data: data,
                        dataType: "json",
                        async: false,
                        beforeSend: function(){
                            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
                        },
                        success: function( response )
                        { 
                            $.unblockUI();
                            var group_name = '<div class="form-item even ">';
                            group_name = group_name + '<label class="label-desc gray" for="group_name_id"> Group: <span class="red">*</span></label>';
                            group_name = group_name + '<div class="multiselect-input-wrap">';
                            group_name = group_name + response.group_name_html;
                            group_name = group_name + '</div>';
                            $('label[for="category_id"]').parent().after(group_name);
                            $('#group_name_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var location = '<div class="form-item even ">';
                            location = location + '<label class="label-desc gray" for="location_id"> Location: </label>';
                            location = location + '<div class="multiselect-input-wrap">';
                            location = location + response.location_html;
                            location = location + '</div>';
                            $('label[for="user_id"]').parent().after(location);
                            $('#location_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });

                            var employee = '<div class="form-item even ">';
                            employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                            employee = employee + '<div class="multiselect-input-wrap">';
                            employee = employee + response.employee_html;
                            employee = employee + '</div>';
                            $('label[for="location_id"]').parent().after(employee);
                            $('#employee_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });
                        }
                    });       
                    break;
                case '6'://employee
                     $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_emp',
                        type:"POST",
                        data: data,
                        dataType: "json",
                        async: false,
                        beforeSend: function(){
                            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
                        },
                        success: function( response )
                        { 
                            $.unblockUI();
                            var employee = '<div class="form-item even ">';
                            employee = employee + '<label class="label-desc gray" for="employee_id"> Employee:<span class="red">*</span></label>';
                            employee = employee + '<div class="multiselect-input-wrap">';
                            employee = employee + response.employee_html;
                            employee = employee + '</div>';
                            $('label[for="category_id"]').parent().after(employee);
                            $('#employee_id').multiselect().multiselectfilter({
                                show:['blind',250],
                                hide:['blind',250],
                                selectedList: 1
                            });
                        }
                    });   
                    break;
            }

            $('#company_id').multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1,
                close:function(event, ui)
                {
                    change_2nd_layer();
                }
            });

            $('#division_id').multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1,
                close:function(event, ui)
                {
                    change_2nd_layer();
                }
            });

            $('#department_id').multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1,
                close:function(event, ui)
                {
                    change_2nd_layer();
                }
            });

            $('#project_name_id').multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1,
                close:function(event, ui)
                {
                    change_2nd_layer();
                }
            });

            $('#group_name_id').multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1,
                close:function(event, ui)
                {
                    change_2nd_layer();
                }
            });

            $('#location_id').multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1,
                close:function(event, ui)
                {
                    change_location();
                }
            });
        }
    });
});

function change_2nd_layer() {
    var category_id = $('#category_id').val();
    var data = $('#report-form').serialize();
    $('label[for="location_id"]').parent().remove();
    $('label[for="employee_id"]').parent().remove();
    if(category_id != 0 ){                    
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_2nd_layer',
            type:"POST",
            data: data,
            dataType: "json",
            async: false,
            beforeSend: function(){
                $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
            },
            success: function( response )
            { 
                $.unblockUI();        

                var location = '<div class="form-item even ">';
                location = location + '<label class="label-desc gray" for="location_id"> Location: </label>';
                location = location + '<div class="multiselect-input-wrap">';
                location = location + response.location_html;
                location = location + '</div>';
                $('label[for="user_id"]').parent().after(location);
                $('#location_id').multiselect().multiselectfilter({
                    show:['blind',250],
                    hide:['blind',250],
                    selectedList: 1
                });

                var employee = '<div class="form-item even ">';
                employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                employee = employee + '<div class="multiselect-input-wrap">';
                employee = employee + response.employee_html;
                employee = employee + '</div>';
                $('label[for="location_id"]').parent().after(employee);
                $('#employee_id').multiselect().multiselectfilter({
                    show:['blind',250],
                    hide:['blind',250],
                    selectedList: 1
                });

                $('#location_id').multiselect({
                show:['blind',250],
                hide:['blind',250],
                selectedList: 1,
                close:function(event, ui)
                {
                    change_location();
                }
            });
            }
        });
    }
}

function change_location() {
    var category_id = $('#category_id').val();
    var data = $('#report-form').serialize();
    $('label[for="employee_id"]').parent().remove();
    if(category_id != 0 ){                    
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_emp_location',
            type:"POST",
            data: data,
            dataType: "json",
            async: false,
            beforeSend: function(){
                $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
            },
            success: function( response )
            { 
                $.unblockUI();        

                var employee = '<div class="form-item even ">';
                employee = employee + '<label class="label-desc gray" for="employee_id"> Employee: </label>';
                employee = employee + '<div class="multiselect-input-wrap">';
                employee = employee + response.employee_html;
                employee = employee + '</div>';
                $('label[for="location_id"]').parent().after(employee);
                $('#employee_id').multiselect().multiselectfilter({
                    show:['blind',250],
                    hide:['blind',250],
                    selectedList: 1
                });
            }
        });
    }
}

function generate_report() {
    if($('select[name="company_id"]').val() != '')
    {
        if($('#date_range_from').val() == '' || $('#date_range_to').val() == '')
        {
             $('#message-container').html(message_growl('error', 'Date is needed!'));
        }
        else
        {
            $('#report-form input[name="print_type"]').val('1');
            $('#report-form').attr('target', '_blank');
            $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
            $('#report-form').trigger('submit');
            $('#report-form').attr('target', '_self'); 
        }
    }
    else
    {
        $('#message-container').html(message_growl('error', 'Select Company!'));
    }
}

function export_report() {   
    if($('#paycode_id').val() != '') {
        if($('#user_id').val() != '') {  
            var category_id = $('#category_id').val();
            if(category_id != '') {
                switch(category_id) {   
                     case '1':
                        if($('division_id').val() != '') {
                            $('#report-form input[name="print_type"]').val('');
                            $('#report-form').attr('target', '_blank');
                            $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
                            $('#report-form').trigger('submit');
                            $('#report-form').attr('target', '_self');
                        } else {
                            $('#message-container').html(message_growl('error', 'Select Division!'));
                        }
                     break;
                     case '2':
                        if($('project_name_id').val() != '') {
                            $('#report-form input[name="print_type"]').val('');
                            $('#report-form').attr('target', '_blank');
                            $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
                            $('#report-form').trigger('submit');
                            $('#report-form').attr('target', '_self');
                        } else {
                           $('#message-container').html(message_growl('error', 'Select Project!')); 
                        }
                     break;
                     case '3':
                        if($('group_name_id').val() != '') {
                            $('#report-form input[name="print_type"]').val('');
                            $('#report-form').attr('target', '_blank');
                            $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
                            $('#report-form').trigger('submit');
                            $('#report-form').attr('target', '_self');
                        } else {
                            $('#message-container').html(message_growl('error', 'Select Group!'));
                        }
                     break;
                     case '4':
                        if($('department_id').val() != '') {
                            $('#report-form input[name="print_type"]').val('');
                            $('#report-form').attr('target', '_blank');
                            $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
                            $('#report-form').trigger('submit');
                            $('#report-form').attr('target', '_self');
                        } else {
                           $('#message-container').html(message_growl('error', 'Select Department!')); 
                        }
                     break;
                     case '5':
                        if($('company_id').val() != '') {
                            $('#report-form input[name="print_type"]').val('');
                            $('#report-form').attr('target', '_blank');
                            $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
                            $('#report-form').trigger('submit');
                            $('#report-form').attr('target', '_self');
                        } else {
                           $('#message-container').html(message_growl('error', 'Select Company!')); 
                        }
                     break;
                     case '6':
                        if($('employee_id').val() != '') {
                            $('#report-form input[name="print_type"]').val('');
                            $('#report-form').attr('target', '_blank');
                            $('#report-form').attr('action', module.get_value('base_url') + module.get_value('module_link')+"/export_report");
                            $('#report-form').trigger('submit');
                            $('#report-form').attr('target', '_self');
                        } else {
                            $('#message-container').html(message_growl('error', 'Select Employee!'));
                        }
                     break;
                }
            } else {
                $('#message-container').html(message_growl('error', 'Select Category!'));
            }
        } else {
            $('#message-container').html(message_growl('error', 'Payroll Date is Needed!'));
        }
    } else {
        $('#message-container').html(message_growl('error', 'Select Payment Code!'));
    }
    

}

function isset() {
    var a = arguments,
        l = a.length,        
        i = 0,
        undef; 
    if (l === 0)  {
        throw new Error('Empty isset');    
    }
 
    while (i !== l)  {
        if (a[i] === undef || a[i] === null)  {
            return false;        
        }
        i++;
    }
    return true;
}