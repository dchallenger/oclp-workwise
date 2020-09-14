$(document).ready(function(){
	if(module.get_value('view') == "edit"){
		$('.td_parent').live('mouseover', function(){
			$(this).children('.decrease_year').show();
			$(this).children('.increase_year').show();
		});

		$('.td_parent').live('mouseout', function(){
			$(this).children('.decrease_year').hide();
			$(this).children('.increase_year').hide();
		});

		$('.increase_year, .decrease_year').live('mouseover', function(){
			$(this).css('cursor', 'pointer');
		});

		$('.increase_year').live('click', function(){
			var new_count = parseInt($(this).siblings('input').val()) + 1;
			$(this).siblings('input').val(new_count);
			var msg = new_count + (new_count < 2 ? ' Year ' : ' Years');
			$(this).siblings('span').replaceWith('<span class="succeeding_year" rel="'+new_count+'">'+msg+'</span>');
		});

		$('.decrease_year').live('click', function(){
			var new_count = parseInt($(this).siblings('input').val()) - 1;
			if(new_count > 0)
			{
				$(this).siblings('input').val(new_count);
				var msg = new_count + (new_count < 2 ? ' Year ' : ' Years');
				$(this).siblings('span').replaceWith('<span class="succeeding_year" rel="'+new_count+'">'+msg+'</span>');
			}
		});

		$('.icon-16-delete').live('click', function(){
			$(this).parent().parent().remove();
		});
	}
});

function add_leave()
{
	$.ajax({
		url: module.get_value('base_url')+'admin/leave_setup/add_leave',
		dataType: 'html',
		type: 'post',
		success: function(response) {
			$('.leave-table').append(response);
		}
	});
}