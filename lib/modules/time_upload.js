$(document).ready( function(){

	var viewport_width 			= $(window).width();
	var viewport_height 		= $(window).height();
	var width 					= .90 * viewport_width;
	var height 					= viewport_height - 100;
	var height_detail 			= height - 250;
	 $.ajax({
        url:"time_browse",
        type:"POST",
        dataType: "json",
        async: false,
        success: function(data)
        {
            fixedBoxy = new Boxy('<div id="fixed-entry-form" style="width: 300px; height: 100px">'+ data.entry_form +'</div>',
            {
                title: "Upload",
                draggable: false,
                modal: true,
                center: true,
                unloadOnHide: true,
                show: true,
                afterHide: function() { fixedBoxy = false; },
                afterShow:function(){  }
            });
            $('#upload_now').live('click', function()
			{
				var val = '0';
				if($('#userfile').val() == '')
				{
					val = '1';
					
				}
				else if($('#type_of_file').val() == '0')
				{
					val = '1';
					
				}
				if(val == '0')
				{
					$('#dtr_upload').trigger('submit');
					// alert($('#userfile').val());	
					// var data = $('#dtr_upload').serialize();
					// alert(data);
					// var saveUrl = "do_upload";
					// $.ajax({
					// 	url: saveUrl,
					// 	type:"POST",
					// 	data: data,
					// 	dataType: "json",
					// 	async: false,
					// 	success: function(data)
					// 	{

					// 	}
					// });	
				}
				else
				{
					alert('fill it up!');
				}
			});
        }
    });     
    return false;
});