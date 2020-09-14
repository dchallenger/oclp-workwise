$(document).ready(function(){
	$('select[name=memo_type_id]').change(function(){
		if($(this).val() == 1){
			$('label[for=employee_id]').parent().addClass('hidden');			
		}
		else{
			$('label[for=employee_id]').parent().removeClass('hidden');
			$('#employee_id_chzn').css({"width": "92%"});
			$('.chzn-drop').css({minWidth: '100%', width: 'auto'});	
			$('.chzn-search').children().css({"width": "92%"});	
		}
	});
	
	if(module.get_value('view') == 'edit'){
		if(module.get_value('record_id') == "-1"){
			if( $('select[name=memo_type_id]').val() == "" ) $('select[name=memo_type_id]').val(1);
		}
		$('select[name=memo_type_id]').trigger('change');	

		var data = $('#record-form').serialize();
		$.ajax({
	        url: module.get_value('base_url') + module.get_value('module_link') + '/get_company',
	        data: data,
	        dataType: 'json',
	        type: 'post',
	        async: false,          
	        success: function ( response ) 
	        {   
				$('label[for="company_recipients"]').parent().remove();
	            var company = '<div class="form-item odd ">';
	            company = company + '<label class="label-desc gray" for="company_recipients"> Company Recipients :</label>';
	            company = company + '<div class="multiselect-input-wrap">';
	            company = company + response.company_html;
	            company = company + '</div><br clear="left">';
	            company = company + '</div>';
	            $('label[for="recipients"]').parent().before(company);
	            $("#company_recipients").multiselect({
			        show:['blind',250],
			        hide:['blind',250],
			        close:function(event, ui)
			        {
			        	get_employee();
			        }
			    });	            
	        }
	    });
	    if($('#record_id').val() != '-1') {
	    	get_employee();
	    }	
	}
	if(module.get_value('view') == 'index'){
		$('#record-form').prepend('<input name="memo_type_id" value="1" type="hidden">');
	}	
	
});

function get_employee() {
	var data = $('#record-form').serialize();
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee',
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
            $('label[for="recipients"]').parent().remove();
            var employee = '<div class="form-item odd ">';
	            employee = employee + '<label class="label-desc gray" for="recipients"> Recipients : </label>';
	            employee = employee + '<div class="multiselect-input-wrap">';
	            employee = employee + response.employee_html;
	            employee = employee + '</div><br clear="left">';
	            employee = employee + '</div>';
            $('label[for="company_recipients"]').parent().after(employee);
            $("#recipients").multiselect().multiselectfilter({
		        show:['blind',250],
		        hide:['blind',250]
		    });
        }
    });
}

function init_filter_tabs(){
	$('ul#grid-filter li').click(function(){
		$('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
		$(this).addClass('active');
		filter_memo( 'jqgridcontainer', $(this).attr('filter_id') );
	});
}

function filter_memo( jqgridcontainer, memo_type_id )
{
	$('#record-form input[name="memo_type_id"]').val( memo_type_id );

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
			filter: memo_type_id
		}, 	
	}).trigger("reloadGrid");	
}
