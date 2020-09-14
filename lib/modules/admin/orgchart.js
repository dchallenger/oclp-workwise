$(document).ready(function () {
	if( module.get_value('view') == "edit" ){
		$('#record_id').change( function(){ get_orgchart( 'true' ); } );
		if( $('#record_id').val() != "-1" ) get_orgchart( 'true' );


		$('.image-delete').each(function() {
			$(this).before('<input class="image-text nomargin multi" style="width:95px" type="textbox" name="multiple_desc['+$(this).attr('upload_id')+']" id="multiple_desc['+$(this).attr('upload_id')+']" />');
		});

		$('img[id*="file-uploaded_files"]').parent().css('padding-left', '10px');
		$('img[id*="file-uploaded_files"]').parent().css('padding-bottom', '5px');
		$('a[id*="file-uploaded_files"]').parent().css('padding-left', '10px');
		$('a[id*="file-uploaded_files"]').parent().css('padding-bottom', '5px');
		$('a[id*="file-uploaded_files"]').children('img').css('width', '100px');
		$('a[id*="file-uploaded_files"]').children('img').css('height', '100px');
		$('img[id*="file-uploaded_files"]').css('width', '100px');
		$('img[id*="file-uploaded_files"]').css('height', '100px');

		if($('img[id*="file-uploaded_files"]').length > 0)
		{	
			$('img[id*="file-uploaded_files"]').each(function() {
			var data = "doc_id="+$(this).siblings('div').attr('upload_id');
			var upld_id = $(this).attr('id');
			$.ajax({
				url: module.get_value("base_url")+module.get_value("module_link")+"/get_old_doc_value",
				data: data,
				dataType: 'json',
        		type: 'post',
				success: function(response){
						$('#'+upld_id).siblings('.image-text').val(response.data);
					}
				});
			});
		}

		if($('a[id*="file-uploaded_files"]').length > 0)
		{
			$('a[id*="file-uploaded_files"]').each(function() {
				var data = "doc_id="+$(this).siblings('div').attr('upload_id');
				var upld_id = $(this).attr('id');
				$.ajax({
					url: module.get_value("base_url")+module.get_value("module_link")+"/get_old_doc_value",
					data: data,
					dataType: 'json',
	        		type: 'post',
					success: function(response){
						$('#'+upld_id).siblings('.image-text').val(response.data);
					}
				});
			});
		}
	}
	
	if( module.get_value('view') == "company" ){
		$('#record_id').change( function(){ 
			get_orgchart( 'false' ); 
			get_docs($(this).val());
			$('#record_id').css()
		});

		get_orgchart( 'false' );
		
		if($('#record_id').val() != "")
			get_docs($('#record_id').val());
	}
});

/**
 * Get the Org Chart
 * 
 * @return void
 */
function get_orgchart( show_action ) {
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_orgchart",
		type:"POST",	
		data: "record_id="+$('#record_id').val()+'&show_action='+show_action,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function( data ){
			if( data.has_top_level ){
				$("#chart").html('');
				$('.has_top_level').remove();			
				$('.ocd-div').html( data.orgchart );	
				$("#org").jOrgChart({
		            chartElement : '#chart',
		            dragAndDrop  : false
		        });		
			}
			$.unblockUI();
		}
	});
}

function get_docs(cmpny) {
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_docs",
		type:"POST",	
		data: "record_id="+cmpny,
		dataType: "json",
		success: function( response ){
			docs = response.data;
			$('.remove_links').remove();
			if(docs != undefined)
				$('#docs').append('<div class="remove_links"><h3>Documents: </h3><br /></div>');
			else
				$('.remove_links').remove();
			var append_me = '<h3 class="form-head">Contact Info<a href="javascript:void(0)" class="align-right other-link noborder" onclick="toggleFieldGroupVisibility( $( this ) );" style="font-size: 12px;line-height: 18px;">Hide</a></h3>';
			for(var ctr in docs)
			{
				append_me = '<div class="col-2-form">';
				if(docs[ctr].description == "")
				{
					var title_name = "_";
					// get filename as title name
					// var title = docs[ctr].upload_path.split('/');
					// var title_name = title[title.length-1];
					// get filename as title name
				} else
					var title_name = docs[ctr].description;

				append_me += '<div class="remove_links"><div><div style="height: 10px; border-top: 4px solid #CCCCCC;"></div><div class="form-item odd"><label class="label-desc gray" for="attachment[short_description][]">Description:</label><div class="text-input-wrap">'+title_name+'</div></div><div class="form-item even"><label class="label-desc gray" for="attachment[dir_path][]">Result Attachment:</label><div class="text-input-wrap"><a target="_blank" href="../../'+docs[ctr].upload_path+'">'+docs[ctr].upload_path+'</a></div></div><div class="clear"></div></div></div>';

				append_me += '</div>';
            	$('#docs').append(append_me);
            	
				// $('#docs').append('<div class="remove_links"><a target="_blank" href="../../'+docs[ctr].upload_path+'">'+title_name+'</a><br /></div>');
			}
		}
	});
}

function quickedit_boxy_callback( e ){
	get_orgchart( 'true' );
}

/**
 * Quick Add/Edit Top Level of OrgChart
 * 
 * @return void
 */
function add_top_level( orgchart_id ){
	var data = 'record_id=-1&orgchart_id='+orgchart_id;
	var module_url = module.get_value('base_url') + 'admin/orgchart_detail/quick_edit';
	showQuickEditForm( module_url, data);
}

/**
 * Quick Add/Edit OrgChart Item
 * 
 * @return void
 */
function edit_orgchart_item( ocd_id, orgchart_id, parent_ocd_id ){
	var data = 'record_id='+ocd_id+'&orgchart_id='+orgchart_id+'&parent_ocd_id='+parent_ocd_id;
	var module_url = module.get_value('base_url') + 'admin/orgchart_detail/quick_edit';
	showQuickEditForm( module_url, data);
}

/**
 * Delete JD Item
 * 
 * @return void
 */
function delete_orgchart_item( ocd_id )
{
	Boxy.ask("Delete selected Org Chart item?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+"admin/orgchart_detail/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+ocd_id,
				success: function(data){
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					get_orgchart( 'true' );	
				}
			});
		}
	},
	{
		title: "Delete Org Chart Item"
	});
}


var userdetail = false;
/**
 * Get user detail upon clicking org chart
 * 
 * @return void
 */
function get_userdetail( user_id )
{
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_userdetail",
		type:"POST",	
		data: "user_id="+user_id,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function( data ){
			if( !userdetail ){
				userdetail = new Boxy('<div id="boxyhtml">'+ data.user_detail +'</div>',
				{
					title: 'User Detail',
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					afterShow: function(){ $.unblockUI(); },
					beforeUnload: function(){ userdetail = false; }
				});
				boxyHeight(userdetail, '#boxyhtml');
			}
		}
	});
}

