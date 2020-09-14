$(document).ready(function(){
	setTimeout( 'init_cst();', 100);
});

function init_cst(){
	if($("#transaction_mode_id").size() > 0){
		$("#transaction_mode_id").change(function(){
			switch( $( this ).val() ){
				case "3":
					$('label[for="amount"]').parent().removeClass('hidden');
					break;
				case "1":
				case "2":
				default:
					$('label[for="amount"]').parent().addClass('hidden');
					$('#amount').val( 0 );
					break;	
			}
		});
		$("#transaction_mode_id").trigger('change');
		$('#multiselect-week').multiselect({show:['blind',250],hide:['blind',250],selectedList: 1});
	}
}

