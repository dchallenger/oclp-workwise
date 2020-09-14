$(document).ready(function () {
	var action = module.get_value('view');

	var ap = new Appraisal(action);
	
	fn = ap[action];
	
	if (typeof(fn) === typeof(Function)) {
		fn();
	}

	init_datepick();
	$('.icon-16-send-email').die('click');
	
	$('.send_invitation').live('click', function(){
		// record_action('edit', $(this).attr('invitation'), $(this).attr('module_link'), 'appraisee_id', $(this).attr('employeeid'));
		$('#record-form').append('<input type="hidden" name="company_id" value="'+ $(this).attr('period_id') +' " />');
		$('#record-form').attr('action', module.get_value('base_url') + $(this).attr('module_link'));
		$('#record-form').submit();
		$('#record-form').attr('action', '');
		return false;

	});
	$('.icon-view-log-access').live('click',function() {
		$.ajax({
			url:module.get_value('base_url') + module.get_value('module_link') + '/get_last_viewed/',
			type:"POST",	
			data: "employee_id="+$(this).attr('employeeid')+'&appraisal_period_id='+$(this).attr('period_id'),
			dataType: "html",
			beforeSend: function(){
				$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
			},
			success: function( data ){
				$.unblockUI();
				new Boxy('<div id="boxyhtml" style="width:500px;max-height:400px;overflow-y:auto;">' + data +'</div>', 
					{
						title:"Log of Access",
						modal: true
					}
				);
			}
		});			
	});

	$('.description_competency').click(function () {
		var content = $(this).next('label').html();
		var width   = $(window).width()*.7;

	    $.ajax({
	        url: module.get_value('base_url') + "appraisal/appraisal_planning/get_competencies",
	        type:"POST",    
	        data: "master_id="+$(this).attr('master-id'),
	        dataType: "json",
	        beforeSend: function(){
	            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
	        },
	        success: function( data ){

	            $.unblockUI();
	            template_form = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.contents +'</div>',
	                            {
	                                    title: "Description",
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
	    });   
	});	

	if (module.get_value('view') == "detail"){
		$('input[type="text"],input[type="radio"], textarea').attr("disabled", "disabled");	

		window.onload = function(){

			// toggleOn();

			$('.master').each(function() {
				var element = $(this);
	            var compe_val = element.attr('competency-value');
	            var compe_id = $(this).attr('competency-master');

           		get_competency_value(element,compe_id,compe_val);

			});
			$('.development_plan').each(function() {
				var element = $(this);
	            var value = element.attr('resources-value');
	            var value_id = element.val();
	            var competency_id = element.attr('competency');
	            get_resources(element,value_id,value, competency_id);

			});

	}
	

	$('.show_hide').toggle(function() {
	    $(this).closest('table').children('tbody').slideUp();
	    $(this).children('span').text('Show');
	}, function () {
	    $(this).closest('table').children('tbody').slideDown();
	    $(this).children('span').text('Hide');
	});		

		

        $('#attachment-photo').uploadify({
            'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
            'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify3.php',
            'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
            'folder'    : 'uploads/' + module.get_value('module_link'),
            'fileExt'   : '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
            'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
            'auto'      : true,
            'method'    : 'POST',
            'scriptData': {module: module.get_value('module_link'), fullpath: module.get_value('fullpath'), path: "uploads/" + module.get_value('module_link'),field:"attachment-photo",text_id:"test1"},
            'onComplete': function(event, ID, fileObj, response, data)
            {       

                var split_res = response.split('|||||');
                $('#dir_path').val(split_res[0]);
                if(split_res[2] == 'image')
                {
                    var img = '<div class="nomargin image-wrap"><img id="file-photo" src="' + module.get_value('base_url') + split_res[0] +'" width="100px"><div class="delete-image nomargin multi" field="dir_path" upload_id="'+ split_res[1] +'"></div></div>';                           
                }
                else
                {
                    var img = '<div class="nomargin image-wrap"><a id="file-photo" href="' + module.get_value('base_url') + split_res[0] + '" width="100px" target="_blank"><img src="' + module.get_value('base_url') + 'themes/slategray/images/file-icon-md.png"></a><div class="delete-image nomargin multi" field="dir_path" upload_id="'+ split_res[1] +'"></div></div>';                                   
                }
                $('#photo-upload-container').html('');                       
                $('#photo-upload-container').append(img);
            },
            'onError': function (event,ID,fileObj,errorObj) {
                $('#error-photo').html(errorObj.type + ' Error: ' + errorObj.info);
            },
            'onCancel': function ()
            {
                var split_res = $(event.target).attr('rel');
                $( '#dir_path' ).val('');
                $(this).parent('#attachment-photo').remove();                   
            }
        });
    
	}

	if (module.get_value('view') == "edit"){

		window.onload = function(){

			$('#rating_coach').val($('#coach_total_weighted_criteria_score_s').html());
			// toggleOn();
			
			// $('.actual_result').each(function() {

			// 	if ($(this).val() == "") {
			// 		$(this).closest('td').next('td').children('.rating').attr('disabled', true);	
			// 	};
			// })
			$('.add_strength_areas_improvement_row').live('click',function(){

	            var type = $(this).attr('rel');
	            var html = $('.strength_areas_improvement tr.'+type).clone();

	            $(this).parents('table.'+type).find('tbody.'+type).append(html);

	        });

	        $('.delete_strength_areas_improvement_row').live('click',function(){

	            $(this).parent().parent().remove();

	        });


			$('.master').each(function() {
				var element = $(this);
	            var compe_val = element.attr('competency-value');
	            var compe_id = $(this).attr('competency-master');

           		get_competency_value(element,compe_id,compe_val);

			});
			
			$('.development_plan').each(function() {
				
				if ($(this).parents('tr').attr('class') != 'tmp_html') {
					$(this).trigger('change');
					
				};
			});
			
		}
		$('.rating').keydown( numeric_only ); 

        $('.obj_self_rating').live('keyup',function(){
			var perspective = $(this).attr('perspective');
			var key_weight = parseFloat($('.key_weight[perspectiveid='+perspective+']').val());

            var parent = $(this).closest('tr');
			var criteria = $(parent).attr('criteria');
			var ratio_weigth = $(parent).attr('ratio-weigth');
            var self_rating = $(parent).find('.obj_self_rating').val();            
            var target = $(parent).find('.target').val();
            var weight = $(parent).find('.weight').val();
            var achieved = Math.round((self_rating / target) * 100);
            var weight_average = ((achieved * weight) / 100).toFixed(2);

            if (achieved > 150)
            	achieved = 150;
            else if (achieved < 50)
            	achieved = 50;

			weight_average = ((achieved * weight) / 100).toFixed(2);

            if (self_rating == 0) {
            	achieved = 0;
            	weight_average = 0;
            }

            $(parent).find('.self_achieved').val(parseInt(achieved));
            $(parent).find('.self_weight_average').val(weight_average);

			var total_weight_average = 0;
			$('.self_weight_average[perspective='+perspective+']').each(function (index, element) {
				if ($(element).val() != '' && !isNaN($(element).val())) {
					total_weight_average += parseFloat($(element).val());
				}	
			})

			var grand_total_weight_average = 0;
			$('.self_total_weight_average').each(function (index, element) {
				if ($(element).val() != '' && !isNaN($(element).val())) {
					grand_total_weight_average += parseFloat($(element).val());
				}	
			})

			var overall_score = (grand_total_weight_average / 100) * 100;

			var section_rate = get_in_range(parseFloat(overall_score));

			setTimeout(function(){ 
				var none_core_no_question = 0;
				$('.key_weight').each(function (index, element) {
					if (parseFloat($(element).val()) > 0)
						none_core_no_question++;
				});

				//$('#non_core_self_rating_'+perspective+'').html(overall_score.toFixed(2));
				$('#self_total_rating_'+perspective).val(total_weight_average);		
				$('#non_core_self_rating_'+perspective).html((total_weight_average).toFixed(2));	
				$('#self_weighted_score_'+perspective).val((section_rate).toFixed(2));

				var sub_total_self_weighted_score = get_total_score('self_weighted_score');

				var total_section_rating = Math.round((sub_total_self_weighted_score / none_core_no_question) * ratio_weigth);
				//var total_self_weighted_score = Math.round(((sub_total_self_weighted_score / none_core_no_question ) * ratio_weigth)) / 100;		
				var total_self_weighted_score = (section_rate * ratio_weigth) / 100;

				//$('#section_rating_'+criteria+'').html(total_section_rating.toFixed(2));
				$('#section_rating_'+criteria+'').html(overall_score.toFixed(2));
				$('#total_weighted_'+criteria+'').html(total_self_weighted_score.toFixed(2));
				$('#self_total_weighted'+criteria).val(section_rate * ratio_weigth);

				var total_wcs = 0;
				$('.self_total_weighted').each(function (index, element) {
					if ($(element).val() != ' ') {
						total_wcs += Math.round($(element).val()) / 100;
					}	
				})

				$('#total_weighted_criteria_score_s').html(total_wcs.toFixed(2));				
				//$('#rating').val(total_wcs.toFixed(2));	
			}, 3000);
        });

        $('.obj_coach_rating').live('keyup',function(){
			var perspective = $(this).attr('perspective');		
			var key_weight = parseFloat($('.key_weight[perspectiveid='+perspective+']').val());

            var parent = $(this).closest('tr');
			var criteria = $(parent).attr('criteria');
			var ratio_weigth = $(parent).attr('ratio-weigth');            
            var coach_rating = $(parent).find('.obj_coach_rating').val();
            var target = $(parent).find('.target').val();
            var weight = $(parent).find('.weight').val();
			var achieved = 0;
			var weight_average = 0;

            if (coach_rating > 0 && target > 0) {
            	achieved = Math.round((coach_rating / target) * 100);
            	weight_average = ((achieved * weight) / 100).toFixed(2);

	            if (achieved > 150)
	            	achieved = 150;
	            else if (achieved < 50)
	            	achieved = 50;

	            weight_average = ((achieved * weight) / 100).toFixed(2);            	
            }

            $(parent).find('.coach_achieved').val(parseInt(achieved));
            $(parent).find('.coach_weight_average').val(weight_average);

			var total_weight_average = 0;
			$('.coach_weight_average[perspective='+perspective+']').each(function (index, element) {
				if ($(element).val() != ' ') {
					total_weight_average += parseFloat($(element).val());
				}	
			})

			var grand_total_weight_average = 0;
			$('.coach_total_weight_average').each(function (index, element) {
				if ($(element).val() != '' && !isNaN($(element).val())) {
					grand_total_weight_average += parseFloat($(element).val());
				}	
			})

			var overall_score = (grand_total_weight_average / 100) * 100;

			var section_rate = get_in_range(parseFloat(overall_score));

			setTimeout(function(){ 
				var none_core_no_question = 0;
				$('.key_weight').each(function (index, element) {
					if (parseFloat($(element).val()) > 0)
						none_core_no_question++;
				});
				
				$('#coach_total_rating_'+perspective).val(total_weight_average);		
				$('#non_core_coach_rating_'+perspective).html((total_weight_average).toFixed(2));
				$('#coach_weighted_score_'+perspective).val((section_rate).toFixed(2));

				var sub_total_self_weighted_score = get_total_score('coach_weighted_score');
				//var none_core_no_question = $('.none_core_no_question').val();
				var total_section_rating = Math.round((sub_total_self_weighted_score / none_core_no_question) * ratio_weigth);
				//var total_self_weighted_score = ((sub_total_self_weighted_score / none_core_no_question ) * ratio_weigth) / 100;		
				var total_self_weighted_score = (section_rate * ratio_weigth) / 100;

				//$('#coach_rating_'+criteria+'').html(total_section_rating.toFixed(2));
				$('#coach_rating_'+criteria+'').html(overall_score.toFixed(2));
				$('#coach_total_weighted_'+criteria+'').html(total_self_weighted_score.toFixed(2));
				$('#coach_total_weighted'+criteria).val(section_rate * ratio_weigth);

				var total_wcs = 0;
				$('.coach_total_weighted').each(function (index, element) {
					if ($(element).val() != ' ') {
						total_wcs += Math.round($(element).val()) / 100;
					}	
				})

				$('#coach_total_weighted_criteria_score_s').html(total_wcs.toFixed(2));				
				$('#rating').val(total_wcs.toFixed(2));					
			}, 3000);						
        });

/*		$('.obj_self_rating').live('change',function(){

			var perspective = $(this).attr('perspective');
			var total_rating = 0;
			var criteria = $(this).attr('criteria');

			var key_weight = parseFloat($('.key_weight[perspectiveid='+perspective+']').val()) * 0.01;
			var percent_rating = 0;;
			$('.obj_self_rating').each(function(){
				var percent_dis = $(this).attr('percent-distribution') / 100; 
		
				if( $(this).attr('perspective') == perspective ){
					percent_rating += ( parseFloat($(this).val()) * percent_dis ) ;
				}
			});

			total_rating = percent_rating * key_weight;
			$('#non_core_self_rating_'+perspective).html((total_rating).toFixed(2));
			$('#self_total_rating_'+perspective).val((total_rating).toFixed(2));

			var total_section = 0;
			$('.self_rating_field_'+criteria).each(function (index, element) {
				var option = $(element);
				
				if ($(element).val() != ' ') {
					total_section += parseFloat($(element).val());
				}
			});

			$('#self_section_rating'+criteria).val(total_section.toFixed(2));

			var ws = $('#self_total_weighted'+criteria).attr('weighter-score');
			var total_ws = (total_section.toFixed(2) * ws);
			$('#self_total_weighted'+criteria).val(total_ws);

			var total_wcs = 0;
			$('.self_total_weighted').each(function (index, element) {
				if ($(element).val() != ' ') {
					total_wcs += parseFloat($(element).val());
				}	
			})

			$('#total_weighted_criteria_score_s').html(total_wcs.toFixed(2));
		
		});

		$('.obj_coach_rating').live('change',function(){
 
			var perspective = $(this).attr('perspective');
			var total_rating = 0;
			var criteria = $(this).attr('criteria');
			var key_weight = parseFloat($('.key_weight[perspectiveid='+perspective+']').val()) * 0.01;
			var percent_rating = 0;;
			$('.obj_coach_rating').each(function(){
				var percent_dis = parseFloat($(this).attr('percent-distribution')) / 100; 
				
				if( $(this).attr('perspective') == perspective ){
					percent_rating += ( parseFloat($(this).val()) * percent_dis ) ;
				}
			});
			
			total_rating = percent_rating * key_weight;
			
			$('#non_core_coach_rating_'+perspective).html((total_rating).toFixed(2));
			$('#coach_total_rating_'+perspective).val((total_rating).toFixed(2));

			
			var total_section = 0;
			
			$('.coach_rating_field_'+criteria).each(function (index, element) {
				
				var option = $(element);
				var val = option.val();
				if ($(element).val() != ' ') {
					total_section += parseFloat($(element).val());
					
				}
				
			});
			

			$('#section_rating_'+criteria).html(total_section.toFixed(2));
			$('#inp_section_rating'+criteria).val(total_section.toFixed(2));
			
			var ws = $('#total_weighted_'+criteria).attr('weighter-score');
			var total_ws = (total_section.toFixed(2) * ws);
			$('#total_weighted_'+criteria).html(total_ws.toFixed(2));

			var total_wcs = 0;
			$('.weighter_score').each(function (index, element) {
				if ($(element).html() != ' ') {
					total_wcs += parseFloat($(element).html());
				}	
			})

			$('#total_weighted_criteria_score').html(total_wcs.toFixed(2));

		});*/

		$('.self_rating').live('change',function(){

			var competency = $(this).attr('competency');
			var criteria = $(this).attr('criteria');
			var total_section = 0;

			$('.core_self_rating_'+competency).html(parseFloat($(this).val()).toFixed(2));
			var cnt = 0
			$('select[criteria='+criteria+']').each(function (index, element) {
				var option = $(element);

				if (option.hasClass('self_rating')) {
					var val = option.val();
					if (val != '') {
						total_section += parseFloat(val);
					}	
					cnt += 1;
				};
			});
			// console.log(cnt);	

			total_section = (total_section / cnt);
			
			$('#self_section_rating'+criteria).val(total_section.toFixed(2));

			var ws = $('#self_total_weighted'+criteria).attr('weighter-score');

			var total_ws = (total_section.toFixed(2) * ws).toFixed(2);

			$('#self_total_weighted'+criteria).val(total_ws);

			$('#section_rating_'+criteria).html(total_section.toFixed(2));
			$('#total_weighted_'+criteria).html((total_ws / 100).toFixed(2));

			var total_wcs = 0;
			$('.self_total_weighted').each(function (index, element) {
				if ($(element).val() != ' ') {
					total_wcs += Math.round($(element).val()) / 100;
				}	
			})

			$('#total_weighted_criteria_score_s').html(total_wcs.toFixed(2));
			//$('#rating').val(total_wcs.toFixed(2));
		});

		$('.coach_rating').live('change',function(){

			var competency = $(this).attr('competency');
			var criteria = $(this).attr('criteria');
			var total_section = 0;
			var cnt = 0
			$('.core_coach_rating_'+competency).html(parseFloat($(this).val()).toFixed(2));

			$('select[criteria='+criteria+']').each(function (index, element) {
				var option = $(element);

				if (option.hasClass('coach_rating')) {
					var val = option.val();
					if (val != '') {
						total_section += parseFloat(val);
					}	
					cnt += 1;
				};		
			});

			total_section = (total_section / cnt);

			$('#inp_section_rating'+criteria).val(total_section.toFixed(2));

			var ws = $('#coach_total_weighted'+criteria).attr('weighter-score');
			var total_ws = (total_section.toFixed(2) * ws).toFixed(2);

			$('#coach_rating_'+criteria).html(total_section.toFixed(2));
			$('#coach_total_weighted'+criteria).val(total_ws);

			$('#coach_total_weighted_'+criteria).html((total_ws / 100).toFixed(2));

			var total_wcs = 0;
			$('.coach_total_weighted').each(function (index, element) {
				if ($(element).val() != ' ') {
					total_wcs += Math.round($(element).val()) / 100;
				}	
			})

			$('#coach_total_weighted_criteria_score_s').html(total_wcs.toFixed(2));
			$('#rating').val(total_wcs.toFixed(2));
		});


		$('.actual_competency_level').on('change',function(){

			if( $(this).val() != "" ){
                    $(this).parent().find('small').remove();
                    $(this).parent().append('<div style="width:150px"><small>'+$(this).find('option:selected').attr('description')+'</small></div>');
                }
                else{
                    $(this).parent().find('small').remove();
                }

		});


		var table_width_px = $('#main').width();
		$('.add_row').live('click',function() {
			var elem = $(this);
			var id = $(elem).attr("columnid");
			var q_id = $(elem).attr("question");
			var html = '<tr class="additional">' + $('#'+id+'').html() + '</tr>';
			$('#'+q_id+'').before(html);
			$('tr.additional').hover(
				function(){		
					$(this).find('span.del-button').show();
				},
				function(){
					$(this).find('span.del-button').hide();
				}
			);			
		});

		
		$('.delete_row').live('click',function(){
			$(this).closest('tr').remove();
		});

		$('tr.additional').hover(
			function(){		
				$(this).find('span.del-button').show();
			},
			function(){
				$(this).find('span.del-button').hide();
			}
		);



	$('.core_rating').live('change',function(){
		var rate_val = $(this).val();
		var criteria = $(this).attr('criteria');
		
		// $(this).closest('small').next('td').find('.ratingcore').hide();
		$(this).parent().find('.ratingcore').hide();
		// $(this).pa('small').find('.ratingcore').hide();
		$(this).parent().find('.ratingcore'+rate_val).show();

		var total = 0;
		$('select[class="core_rating"] option:selected').each(function (index, element) {
				var option = $(element);
				var val = option.val();
				if (val != '') {
					total += parseFloat(val);
				};
					
 				
			});
		var core_total = parseFloat($("#core_total").val());

		var total_core = total / core_total;
		var weight = $('#sec_rating'+criteria+'').attr('weight');

		$('#sec_rating'+criteria+'').html(total_core.toFixed(2));
			
		$('#inp_sec_rating'+criteria+'').val(total_core.toFixed(2));

		$('#over_rating'+criteria+'').html(((total_core * weight) / 100).toFixed(2));	
		$('#inp_over_rating'+criteria+'').val(((total_core * weight) / 100).toFixed(2));

		var total_score = 0;
			$('.overall_rating').each(function(){
				var or_rating = $(this).html();
				
				if (or_rating != ''){
					total_score += parseFloat(or_rating);
				}
			});
			
			$('#total_score').html(total_score.toFixed(2));
			$('#inp_total_score').val(total_score.toFixed(2));
	});


		$('.rating').live('change',function(){
			var criteria = $(this).attr('criteria');
			var rating = parseFloat($(this).val());
			var weight_ind = parseFloat($(this).closest('td').next('td').children('.weight').val());
			var rating_weight = 0;
			if (rating > 0 && weight_ind){
				rating_weight = rating * weight_ind / 100;
			}

			$(this).closest('td').next('td').next('td').children('.weight_rating').val(rating_weight.toFixed(2))

			var total = 0;
			// $('input[criteria="'+criteria+'"]').filter('.rating').each(function(){
			// 	var val = $(this).val();
			// 	if (val != ''){
			// 		total += parseFloat(val);
			// 	}
			// });
			$('select[criteria="'+criteria+'"] option:selected').each(function (index, element) {
				var option = $(element);
				var val = option.val();
					total += parseFloat(val);
 				
			});

			var total_w_x_r = 0;
			$('input[criteria="'+criteria+'"]').filter('.weight_rating').each(function(){
				var val = $(this).val();
				if (val != ''){
					total_w_x_r += parseFloat(val);
				}
			});

			var weight = $('#sec_rating'+criteria+'').attr('weight');

			$('#sec_rating'+criteria+'').html(total.toFixed(2));
			$('#inp_sec_rating'+criteria+'').val(total.toFixed(2));
			$('#over_rating'+criteria+'').html(((total_w_x_r * weight) / 100).toFixed(2));
			$('#inp_over_rating'+criteria+'').val(((total_w_x_r * weight) / 100).toFixed(2));

			var total_score = 0;	
			$('.overall_rating').each(function(){
				var or_rating = $(this).html();
				
				if (or_rating != ''){
					total_score += parseFloat(or_rating);
				}
			});

			$('#total_score').html(total_score.toFixed(2));
			$('#inp_total_score').val(total_score.toFixed(2));
		});

	$('.weight').keydown( numeric_only ); 
		$('.weight').live('keyup',function(){

			var criteria = $(this).attr('criteria');
			var rating = parseFloat($(this).closest('td').prev('td').children('.rating').val());

			var weight_ind = parseFloat($(this).val());
			var rating_weight = 0;

			if (rating > 0 && weight_ind){
				rating_weight = (rating * weight_ind) / 100;
			}

			$(this).closest('td').next('td').children('.weight_rating').val(rating_weight.toFixed(2))

			var total = 0;

			// $('input[criteria="'+criteria+'"]').filter('.rating').each(function(){
			// 	var val = $(this).val();
			// 	if (val != ''){
			// 		total += parseFloat(val);
			// 	}
			// });

			$('select[criteria="'+criteria+'"] option:selected').each(function (index, element) {
				var option = $(element);
				var val = option.val();
					total += parseFloat(val);
 				
			});

			var total_w_x_r = 0;
			$('input[criteria="'+criteria+'"]').filter('.weight_rating').each(function(){
				var val = $(this).val();
				if (val != ''){
					total_w_x_r += parseFloat(val);
				}
			});
					

			var total_weight = 0
			$('input[criteria="'+criteria+'"]').filter('.weight').each(function() {
					if ($(this).val() != "") {
						total_weight += parseFloat($(this).val());
					}
				
			});
			if (parseFloat(total_weight) != 100  ) {
				// $('#message-container').html(message_growl('error', 'Total weight must be 100%'));	
			};
			
			$('#weight_total').val(total_weight);
			
			var weight = $('#sec_rating'+criteria+'').attr('weight');

			$('#sec_rating'+criteria+'').html(total);
			
			$('#inp_sec_rating'+criteria+'').val(total);

			$('#over_rating'+criteria+'').html(((total_w_x_r * weight) / 100).toFixed(2));	
			$('#inp_over_rating'+criteria+'').val(((total_w_x_r * weight) / 100).toFixed(2));

			var total_score = 0;
			$('.overall_rating').each(function(){
				var or_rating = $(this).html();
				
				if (or_rating != ''){
					total_score += parseFloat(or_rating);
				}
			});
			
			$('#total_score').html(total_score.toFixed(2));
			$('#inp_total_score').val(total_score.toFixed(2));
		});

		$('.add_row_comp1').live('click',function() {
			var elem = $(this).closest('tr');
			var compe = $(this).attr('competency');

			// var html = '<tr class="additional competency_master">' + $('.tmp_html').html() + '</tr>';
			
			$.ajax({
		        url: module.get_value('base_url') + "employee/appraisal/get_form",
		        type:"POST",    
		        data: "competency_id="+compe,
		        dataType: "html",
		        beforeSend: function(){
		            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
		        },
		        success: function( response ){
		            $.unblockUI();
		            var html = '<tr class="additional competency_master">' + response + '</tr>';
		            $('#competencies'+compe).before(html);	
		            $('tr.additional').hover(
							function(){			
								$(this).find('span.del-button').show();
							},
							function(){
								$(this).find('span.del-button').hide();
							}
						);	

		        }
		    }); 


				
		});		
		$('.add_row_others1').live('click',function() {
			var elem = $(this).closest('tr');
			var html = '<tr class="additional competency_master">' + $('.tmp_others').html() + '</tr>';
				

			$('tr.additional').hover(
				function(){		
					$(this).find('span.del-button').show();
				},
				function(){
					$(this).find('span.del-button').hide();
				}
			);		
		});	
		
		$('.development_plan').live('change',function(){

            var element = $(this);
            var value = element.attr('resources-value');
            var competency_id = element.attr('competency');
            var value_id = element.val();

            get_resources(element,value_id,value, competency_id);
 

        });


		$('.add_row_comp2').live('click',function() {
			var elem = $(this).closest('tr');
			var html = '<tr class="additional">' + $(elem).next('tr').html() + '</tr>';
			$('#competencies2').before(html);		
		});			

		var coach_rating_actual = $('#coach_total_weighted_criteria_score_s').html();
		var coach_rating_top = $('#rating').val();
		if ($('#appraisal_status').val() == 5 ||  $('#appraisal_status').val() == 6 || $('#appraisal_status').val() == 7 ||$('#appraisal_status').val() == 8) {
			if (coach_rating_actual != '' && parseFloat(coach_rating_actual) > 0) {
				$('#rating').val(parseFloat(coach_rating_actual));
				$('#rating_coach').val(parseFloat(coach_rating_actual));
			}
		}
	}	
});



function Appraisal(action) 
{
	this.index = function () {
		$('.icon-appraisal').live('click', function(){
			//record_action("edit", $(this).parent().parent().parent().attr("id"), $(this).attr('module_link'));
		});

		$('.jqgrow').die('dblclick');
		$('.jqgrow').live('dblclick', function () {
			record_action("edit", $(this).attr("id"), $(this).attr('module_link'));
		});

		$('.icon-16-document-view').live('click', function(){
			$.ajax({
				url: module.get_value('base_url') + "appraisal/appraisal_planning/get_jd_html",
				type:"POST",	
				data: "record_id="+$(this).attr('pid'),
				dataType: "json",
				beforeSend: function(){
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
				},
				success: function( data ){
					$.unblockUI();
					var width = $(window).width()*.7;
					new Boxy('<div id="boxyhtml" style="width:'+width+'px; overflow-y:auto">' + data.jd_items +'</div>', 
						{
							title:"Job Description",
							modal: true
						}
						);
				}
			});			
		});		
	}

	this.edit = function () {
		$('.icon-conforme').click(function () {					
			conforme($('input[name="record_id"]').val(), 
				function(response) {
					// console.log(response);
				});
		});

/*		$('.icon-final-approval').click(function () {					
			send_approved($('input[name="record_id"]').val(), 
				function(response) {
					console.log(response);
				});
		});*/

		$('.pa_summary').click(function () {
			var content = $(this).next('.prev-appraisal').text();
			var width   = $(window).width()*.7;

			new Boxy('<div id="boxyhtml" style="width:'+width+'px">' + content +'</div>', 
				{		
					title: $(this).attr('atitle'),					
					unloadOnHide: true					
				}
				);
		});

		$('.idp').click(function () {
			var content = $(this).next('.prev-appraisal').html();
			var width   = $(window).width()*.7;

			new Boxy('<div id="boxyhtml" style="width:'+width+'px">' + content +'</div>', 
				{		
					title: $(this).attr('atitle'),					
					unloadOnHide: true					
				}
				);
		});

			
	}
}

function get_competency_value(element,compe_id,compe_val){

	$.ajax({
        url: module.get_value('base_url') + "employee/appraisal/get_competency_value",
        type:"POST",    
        data: "competency_id="+compe_id+"&competency_value="+compe_val,
        dataType: "html",
        beforeSend: function(){
            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
        },
        success: function( response ){
            $.unblockUI();
            if (module.get_value('view') == "detail"){
            	element.parents('tr.competency_master').find('.competency_value_picklist').html(response).attr("disabled", "disabled");
        	}else{
            	element.parents('tr.competency_master').find('.competency_value_picklist').html(response);
        	}
        }
    }); 
}

function get_resources(element,value_id,value,compe_id){
    $.ajax({ 
        url: module.get_value('base_url') + "employee/appraisal/get_resources",
        type:"POST",    
        data: "plan_id="+value_id+"&resources_value="+value+"&master_id="+compe_id,
        dataType: "html",
        beforeSend: function(){
            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
        },
        success: function( response ){
            $.unblockUI();
            if (module.get_value('view') == "detail"){
            	element.closest('td').next('td.resources').html(response);
            	element.closest('td').next('td.resources').children().attr("disabled", "disabled");
			}else{
				element.closest('td').next('td.resources').html(response);
			}
           

        }
    }); 
}

function conforme(record_id, callback)
{
    var width = $(window).width()*.3;
    remarks_boxy = new Boxy.confirm(
        '<div id="boxyhtml" style="width:'+width+'px"><textarea style="height:100px;width:340px;" name="remarks"></textarea></div>',
        function () {
            url = module.get_value('base_url') + module.get_value('module_link') + '/send_conforme/';
            remarks = $('textarea[name="remarks"]').val()
            $.ajax({
                url: url,
                data: 'record_id=' + record_id + '&conformed_remarks=' + remarks,
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    message_growl(response.msg_type, response.msg);
                    
                    if (typeof(callback) == typeof(Function))
                        callback(response);
                }
            });
        },
        {
            title: 'Conforme',
            draggable: false,
            modal: true,
            center: true,
            unloadOnHide: true,
            beforeUnload: function (){
                $('.tipsy').remove();
            }
        });    
}

function send_approved(record_id, callback)
{
    var width = $(window).width()*.3;
    remarks_boxy = new Boxy.confirm(
        '<div id="boxyhtml" style="width:'+width+'px"><textarea style="height:100px;width:340px;" name="remarks_approved"></textarea></div>',
        function () {
            url = module.get_value('base_url') + module.get_value('module_link') + '/send_approved/';
            remarks = $('textarea[name="remarks_approved"]').val()
            $.ajax({
                url: url,
                data: 'record_id=' + record_id + '&approved_remarks=' + remarks,
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    message_growl(response.msg_type, response.msg);
                    
                    if (typeof(callback) == typeof(Function))
                        callback(response);
                }
            });
        },
        {
            title: 'Final Approval',
            draggable: false,
            modal: true,
            center: true,
            unloadOnHide: true,
            beforeUnload: function (){
                $('.tipsy').remove();
            }
        });    
}

function validate_form()
{
/*	$('.required').each(function(){
		if ($(this).is('[readonly]') == false){
			if ($(this).closest('tr').css('display') != "none"){
				if ($(this).val() == ""){
					add_error($(this).attr('cname'),$(this).attr('cname'), "This field is mandatory.");
				}
			}
		}
	});*/

	if ($('#appraisal_status').val() == 1)
		var rating_clas = 'obj_self_rating';
	else
		var rating_clas = 'obj_coach_rating';

    $('.'+rating_clas+'').each(function(){
        var actual_val = $(this).val();
        var fieldlen = $(this).length;
        
        if( actual_val !== "" && fieldlen > 0){
            // remove comma separations
            var integer_val = actual_val.replace(",", "");    
            
            //test if integer
            //var valid = /^[-+]?\d+(\.\d+)?$/.test(actual_val.trim());
            var valid = /^(\d+(\.\d{0,2})?|\.?\d{1,2})$/.test(actual_val.trim())
            if( !valid ){
                console.log(actual_val);
                add_error('target', 'Target', "This field only accept integers.");
                return false;
            }
        }


        fieldval = parseFloat($(this).val());
        weight_val = $(this).closest('tr').find('.weight').val();

        var valid = /^[-+]?\d+(\.\d+)?$/.test( fieldval );
        if( fieldval.toString() != "" && valid){
            if( fieldval < 0.1 && weight_val > 0){
                add_error('target', 'Target', "This field should be greater than 0");
                return false;
            }
        }
    });

    //errors
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
    
    //no error occurred
    return true;
}

function insert_period_id(period_id) 
{	
	if ($('#period_id').size() > 0) {
		$('#period_id').val(period_id);
	} else {		
		$('#record-form').append($('<input type="hidden" id="period_id" name="period_id" />').val(period_id));
	}

	$('.icon-appraisal').each(function(index, elem) {
		$(this).attr('href', $(this).attr('href')+ '/' + period_id);
	});	

	$('.icon-view-log-access').each(function(index, elem) {
		$(this).attr('period_id', period_id);
	});		
}

function get_average(classname){
	var average = 0;
	if (classname){
		var total_points = 0;
		var ctr = 0;
		$('.'+classname+'').each(function(){
			total_points += (parseFloat($(this).val() != '' ? $(this).val() : 0))
			if ($(this).val() != ''){
				ctr++;
			}
		});
		average = total_points / ctr;
	}
	return average;
}

function get_total_score(classname){
	var total_score = 0;
	if (classname){
		$('.'+classname+'').each(function(){
			total_score += (parseFloat($(this).val() != '' ? $(this).val() : 0))
		});
	}

	return total_score;
}

function get_total_ave(classname){
	var total_ave = 0;
	if (classname){
		$('.'+classname+'').each(function(){
			total_ave += (parseFloat($(this).val() != '' ? $(this).val() : 0))
		});
	}

	return total_ave;
}

function get_final_total_score(classname){
	var final_total_score = 0;
	if (classname){
		$('.'+classname+'').each(function(){
			final_total_score += (parseFloat($(this).val() != '' ? $(this).val() : 0))
		});
	}

	return final_total_score;
}

function get_final_total_ave(classname){
	var final_total_ave = 0;
	if (classname){
		$('.'+classname+'').each(function(){
			final_total_ave += (parseFloat($(this).val() != '' ? $(this).val() : 0))
		});
	}

	return final_total_ave;
}

function get_in_range(val){
	var rate = 0;

	if (val < 65)
		rate = 1;
	else if (val >= 65 && val < 80) 
		rate = 2;
	else if (val >= 80 && val < 95) 
		rate = 3;
	else if (val >= 95 && val < 110) 
		rate = 4;
	else if (val >= 110) 
		rate = 5;

	return rate;
}

function ajax_save( on_success, is_wizard , callback, status ){
	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')

		ok_to_save = validate_form();
	}
	else{
		ok_to_save = validate_form();
		//ok_to_save = true;
	}
	var rater = $("input[name=appraiser_id]").val();

	// if ((rater == user.get_value('user_id'))) {
	// 	var total_weight = 0
	//     $('.weight').each(function (index, element){
	//        if ($(element).val() != "") {
	//             total_weight += parseFloat($(element).val());
	//         }  
	//     });

	//     if (parseInt(total_weight) != 100) {
	//          ok_to_save = false;
	//          $('#message-container').html(message_growl('error', 'Total weight must be equal to 100%'));    
	//      };
 // 	}


 	var dept_div_head = $("input[name=division_head_id]").val();

 	if( dept_div_head == user.get_value('user_id')  && $('.appraisal_comment_no').attr('checked') == 'checked' ){

		message_growl('attention', 'Note: Ratee did not agree with the appraisal. Please verify.');

    }


	// if (dept_div_head == user.get_value('user_id')) {
	// 	var dept_div_recommend = $("#employee_appraisal_or_div_dep_comments").val();
	// 	if ((dept_div_recommend.length == 0  || dept_div_recommend == "")) {
	// 		$('#message-container').html(message_growl("error", "COMMENTS OF DEPARTMENT/DIVISION HEAD"));
	// 		ok_to_save = false;
	// 	};
	// };
	
	if( ok_to_save ) {
		// $(".tmp_html input").attr("disabled", true);
		// $(".tmp_html textarea").attr("disabled", true);
		//  $('.rating').attr('disabled', false);
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

		var data = $('#record-form').serialize()+'&status='+status;
		var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"		
		// var period_id = $('#appraisal_period_id').val();
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
				$(".tmp_html input").attr("disabled", false);
				$(".tmp_html textarea").attr("disabled", false);				
				if(  data.record_id != null ){
					//check if new record, update record_id
					if($('#record_id').val() == -1 && data.record_id != ""){
						$('#record_id').val(data.record_id);
						$('#record_id').trigger('change');
						if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
					}
					else{
						$('#record_id').val( data.record_id );
					}
				}

				if( data.msg_type != "error"){					
					switch( on_success ){
						case 'back':
						case 'conformed':
							// go_to_previous_page( data.msg );
							window.location = module.get_value('base_url') + "employee/appraisal/index/" + $('#period_id').val();	
							break;1
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
											show_saving_blockui();
										},								
									success: function () {
										$.unblockUI({
											onUnblock: function() {
												message_growl(data.msg_type, data.msg)
											}
										});										
									}
								});
							}							
							//custom ajax save callback
							if (typeof(callback) == typeof(Function)) callback( data );
							window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + $('#appraisee_id').val() + '/'+ $('#period_id').val();
							break;
						case 'email_approved':							
							if (data.record_id > 0 && data.record_id != '') {
								// Ajax request to send email.                    
								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/send_approved',
									data: 'record_id=' + data.record_id,
									dataType: 'json',
									type: 'post',
									async: false,
									beforeSend: function(){
											show_saving_blockui();
										},								
									success: function () {
										$.unblockUI({
											onUnblock: function() {
												message_growl(data.msg_type, data.msg)
											}
										});										
									}
								});
							}							
							//custom ajax save callback
							if (typeof(callback) == typeof(Function)) callback( data );	
							break;	
						case 'div_review':                           
                            if (data.record_id > 0 && data.record_id != '') {
                                // Ajax request to send email.                    
                                $.ajax({
                                    url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                    data: 'record_id=' + data.record_id + '&status=5',
                                    dataType: 'json',
                                    type: 'post',
                                    async: false,
                                    beforeSend: function(){
                                            show_saving_blockui();
                                        },                              
                                    success: function () {
                                    }
                                });
                            }                           
                            //custom ajax save callback
                            if (typeof(callback) == typeof(Function)) callback( data );
                            window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + $('#appraisee_id').val() + '/'+ $('#period_id').val();
                        break;
                        case 'ratee_discussion':                           
                            if (data.record_id > 0 && data.record_id != '') {
                                // Ajax request to send email.                    
                                $.ajax({
                                    url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                    data: 'record_id=' + data.record_id + '&status=2',
                                    dataType: 'json',
                                    type: 'post',
                                    async: false,
                                    beforeSend: function(){
                                            show_saving_blockui();
                                        },                              
                                    success: function () {
                                    }
                                });
                            }                           
                            //custom ajax save callback
                            if (typeof(callback) == typeof(Function)) callback( data );
                            window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + $('#appraisee_id').val() + '/'+ $('#period_id').val();
                        break;
                        case 'send_final':                           
                            if (data.record_id > 0 && data.record_id != '') {
                                // Ajax request to send email.                    
                                $.ajax({
                                    url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                    data: 'record_id=' + data.record_id + '&status=6',
                                    dataType: 'json',
                                    type: 'post',
                                    async: false,
                                    beforeSend: function(){
                                            show_saving_blockui();
                                        },                              
                                    success: function () {
                                    }
                                });
                            }                           
                            //custom ajax save callback
                            if (typeof(callback) == typeof(Function)) callback( data );
                            window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + $('#appraisee_id').val() + '/'+ $('#period_id').val();
                        break;
                        case 'rater_discussion':                           
                            if (data.record_id > 0 && data.record_id != '') {
                                // Ajax request to send email.                    
                                $.ajax({
                                    url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                    data: 'record_id=' + data.record_id + '&status=3',
                                    dataType: 'json',
                                    type: 'post',
                                    async: false,
                                    beforeSend: function(){
                                            show_saving_blockui();
                                        },                              
                                    success: function () {
                                    }
                                });
                            }                           
                            //custom ajax save callback
                            if (typeof(callback) == typeof(Function)) callback( data );
                            window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + $('#appraisee_id').val() + '/'+ $('#period_id').val();
                        break;
                        case 'rater_review':                           
                            if (data.record_id > 0 && data.record_id != '') {
                                // Ajax request to send email.                    
                                $.ajax({
                                    url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                    data: 'record_id=' + data.record_id + '&status=4',
                                    dataType: 'json',
                                    type: 'post',
                                    async: false,
                                    beforeSend: function(){
                                            show_saving_blockui();
                                        },                              
                                    success: function () {
                                    }
                                });
                            }                           
                            //custom ajax save callback
                            if (typeof(callback) == typeof(Function)) callback( data );
                            window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + $('#appraisee_id').val() + '/'+ $('#period_id').val();



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

function edit_detail(){
	var user_id = $('#employee_id').val();
	var period_id = $('#appraisal_planning_period_id').val();
	window.location = module.get_value('base_url') + "employee/appraisal/edit/" + user_id + "/" + period_id;	
}

function comment_box(criteria_id, question_id) {
	var appraisee = $("#appraisee_id").val();
	var period_id = $("#period_id").val();
	var appraisal_year = $('#appraisal_year').val();
    $.ajax({
        url: module.get_value('base_url') + 'employee/appraisal/get_comments',
        type:"POST",
        data: 'appraisee=' + appraisee + '&criteria_id=' + criteria_id + '&boxy=1' + '&period_id='+period_id + '&appraisal_year='+appraisal_year + '&question_id='+question_id,
        dataType: "json",
        beforeSend: function(){
            $.blockUI({
                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
            });  		
        },
        success: function(data){
            $.unblockUI();
            if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
            if(data.comment_box != ""){
                var width = $(window).width()*.7;
                quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.comment_box +'</div>',
                {
                    title: 'Comments',
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
}

function save_comments( ){
	ok_to_save = true;
	if( ok_to_save ) {		
		var data = $('#comment-form').serialize();
		var saveUrl = module.get_value('base_url')+"employee/appraisal/ajax_save_comment"		

		$.ajax({
			url: saveUrl,
			type:"POST",
			data: data,
			dataType: "json",
			/**async: false, // Removed because loading box is not displayed when set to false **/
			beforeSend: function(){
			    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
			},
			success: function(data){
				$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });

				comment_block = '<li>' +
					'<hr />' +
					'<div>' +
						'<span class="comment-name"><strong>' + data.name + '</strong></span>' +
						'&nbsp;<span class="comment-comment">' + data.comment + '</span>' +
					'</div>' +
					'<div class="comment-date">' + data.created_date + '</div>' +
					'</li>';

				$('#comments-list').prepend(comment_block);
				
				// if( callback != undefined && callback != '' ) eval( callback )
			}
		});
	}
	else{
		return false;
	}
	return true;
}	
function validate_fg1() {

	var ratee = $("#appraisee_id").val();
	var rater = $("input[name=appraiser_id]").val();
	var appraisal_status = $('#appraisal_status').val();
	var raters = $("#rater_id").val();


	if ((ratee == user.get_value('user_id')) && ( appraisal_status == 4 )) {
		var date_ratee = $('input[name="employee_appraisal_or_rates_sign_date"]').val();
		var ratee_comment = $('#employee_appraisal_or_ratees_comments').val();
	
		
/*		if ((ratee_comment.length == 0  || ratee_comment == "")) {
			$('#message-container').html(message_growl("error", "RATEE'S COMMENTS is mandatory"));
			return false;
		};*/

		if ((date_ratee.length == 0  || date_ratee == "")) {
			$('#message-container').html(message_growl('error', 'Date is mandatory'));
			return false;	
		};
	};

	// if ((rater == user.get_value('user_id'))) {
		
	// 	var date_rater = $('input[name="employee_appraisal_or_raters_sign_date"]').val();
	// 	var rater_comment = $('#employee_appraisal_or_raters_comments').val();

	// 	if ((rater_comment.length == 0  || rater_comment == "")) {
	// 		$('#message-container').html(message_growl("error", "COACH / RATER'S COMMENTS is mandatory"));
	// 		return false;
	// 	};

	// 	if ((date_rater.length == 0  || date_rater == "")) {
	// 		$('#message-container').html(message_growl('error', 'Date is mandatory'));
	// 		return false;	
	// 	};

		
	// };


	// if ((rater == user.get_value('user_id'))) {
		// var weight = true;
		// var total_weight = 0
	 //    $('.weight').each(function (index, element){
	 //       if ($(element).val() != "") {
	 //            total_weight += parseFloat($(element).val());
	 //        }  
		//         if ($(element).val() == "") {
		//             weight = false;
		//         } 


	 //    });

	 //    if (parseInt(total_weight) != 100) {
	 //         $('#message-container').html(message_growl('error', 'Total weight must be equal to 100%'));   
	 //         return false;	 
	 //     };
	    

	 //    if ((rater == user.get_value('user_id'))) {
		//     if (weight == false) {
		// 		$('#message-container').html(message_growl('error', 'Weight - This field is mandatory.'));   
		// 	       return false;	

		// 	};
		// }
 	// }

	if ((raters == user.get_value('user_id'))) {
		
		var date_rater = $('#employee_appraisal_or_raters_sign_date'+user.get_value('user_id')).val();

		if ((date_rater.length == 0  || date_rater == "")) {
			$('#message-container').html(message_growl('error', 'Date is mandatory'));
			return false;	
		};

		
	};
	
 	var actual_result = true;
   	$('.actual_result').each(function() {
   		var contributor = $(this).attr('contributor');

   		if (contributor == user.get_value('user_id')) {
   			if ($(this).val() == ""){
				actual_result = false;
			}
   		};
		
	});

	if (actual_result == false) {
		$('#message-container').html(message_growl('error', 'Actual Result - This field is mandatory.'));   
	       return false;	

	};

    //no error occurred
    return true;
}

function validate_fg2() {

	return true;
}

function validate_fg3() {

	var rater = $("input[name=appraiser_id]").val();
	var dept_div_head = $("input[name=division_head_id]").val();

	if ((rater == user.get_value('user_id'))) {
				
		var rater_recommendation = $('#comments_recommendation_rater').val();

		if ((rater_recommendation.length == 0  || rater_recommendation == "")) {
			$('#message-container').html(message_growl("error", "COMMENTS OF IMMEDIATE SUPERIOR"));
			return false;
		};
	};

	// if (dept_div_head == user.get_value('user_id')) {
	// 	var dept_div_recommend = $("#employee_appraisal_or_div_dep_comments").val();
	// 	if ((dept_div_recommend.length == 0  || dept_div_recommend == "")) {
	// 		$('#message-container').html(message_growl("error", "COMMENTS OF IMMEDIATE SUPERIOR"));
	// 		return false;
	// 	};
	// };

	return true;
}