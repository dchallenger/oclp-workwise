var loan = false;
$(document).ready(function(){
	if( module.get_value('view') == 'edit' ){
		$('#loan_id').change(function(){
			if( $(this).val() != "" ){
				var loan_id = $(this).val();
				$.ajax({
                    url: module.get_value('base_url')+module.get_value('module_link')+"/get_loan_default",
                    data: 'loan_id='+loan_id,
                    type: "post",
                    success: function(response)
                    {
                		loan = response;
                		$('#debit_account_id').val(response.debit);
                		$('#debit_account_id').trigger("liszt:updated");
                		$('#credit_account_id').val(response.credit);
                		$('#credit_account_id').trigger("liszt:updated");
                		$('#description').val(response.loan);
                		$('#interest').val(response.interest);
                		$('#interest_type_id').val(response.interest_type_id); 

                		calculate_beginning_balance();
                		calculate_system_interest();
                    }
                });
			}
		});

		$('#amount').keyup( calculate_beginning_balance );
		$('#amount').keyup( calculate_system_amortization );
		$('#amount').keyup( calculate_system_interest );
		
		$('#interest').keyup( calculate_beginning_balance );
		$('#interest').keyup( calculate_system_interest );
		$('#interest_type_id').change( calculate_beginning_balance );
		
		$('#no_payments').keyup( calculate_beginning_balance );
		$('#no_payments').keyup( calculate_system_amortization );
		$('#no_payments').keyup( calculate_system_interest );
		
		if( $('#record_id').val() == "-1" ){
			$('#no_payments').keyup(function(){
				calc_payments_remaining();	
			});
			$('#multiselect-week').bind("multiselectclick", function(event, ui){ calc_payments_remaining(); });	
		}
		else{		
			var beginning_balance = $('#beginning_balance').val();
			beginning_balance = parseFloat( beginning_balance );
			$('#beginning_balance').val( addCommas( beginning_balance.toFixed(2) ) );
			
			var running_balance = $('#running_balance').val();
			running_balance = parseFloat( running_balance );
			$('#running_balance').val( addCommas( running_balance.toFixed(2) ) );
			
			var system_amortization = $('#system_amortization').val();
			system_amortization = parseFloat( system_amortization );
			$('#system_amortization').val( addCommas( system_amortization.toFixed(2) ) );
			
			var system_interest = $('#system_interest').val();
			system_interest = parseFloat( system_interest );
			$('#system_interest').val( addCommas( system_interest.toFixed(2) ) );

			$.ajax({
                url: module.get_value('base_url')+module.get_value('module_link')+"/get_loan_default",
                data: 'loan_id='+$('#loan_id').val(),
                type: "post",
                success: function(response)
                {
            		loan = response;
                }
            });
		}
	}
});

function calc_payments_remaining(){
	if($('#no_payments').val() == ''){
		var months = 0;
	}
	else{
		var months = parseFloat( $('#no_payments').val() );
	}
	var weeks = $('#multiselect-week').multiselect('getChecked');
	$('#no_payments_remaining').val( months );
}

function calculate_beginning_balance(){
	if( $('#amount').val() == '' || $('#interest').val() == "" || $('#interest_type_id').val() == '' || $('#no_payments').val() == '' ){
		return false;
	}

	var beginning_balance = 0;
	var amount = parseFloat( remove_commas( $('#amount').val() ) );
	var interest = parseFloat( remove_commas( $('#interest').val() ) );
	var term = parseFloat( remove_commas( $('#no_payments').val() ) );
	var interest_type_id = $('#interest_type_id').val();
	var monthly_amortization = amount / term;

	switch(loan.loan_mode_id){
		case '1': //simple
			if( interest_type_id == 2 ){
				interest = ( amount * interest /100 );
			}
			interest = interest * term;
			break;
		case '2': //diminishing
		case '3': //diminishing with equal payments
			var monthly_interest = new Array();
			var ctr = 0;
			var diminishing_amount = amount;
			while( ctr < term){
				monthly_interest[ctr] = diminishing_amount * interest / 100;
				diminishing_amount = diminishing_amount - monthly_amortization;
				ctr++;
			}	

			interest = 0;
			for(var i in monthly_interest){
				interest = interest + parseFloat(monthly_interest[i]);
			}

			break;
	}

	beginning_balance = parseFloat( amount ) + parseFloat( interest );
	$('#beginning_balance').val( addCommas( beginning_balance.toFixed(2) ) );
	$('#running_balance').val( addCommas( beginning_balance.toFixed(2) ) );
	
	if( $('#record').val() == "" ) $('#running_balance').val( $('#beginning_balance').val() );
}

function calculate_system_amortization(){
	if($('#amount').val() != "" && $('#no_payments').val() != ""){
		var amount = remove_commas( $('#amount').val() );
		var no_payments = remove_commas( $('#no_payments').val() );
		var system_amortization = parseFloat( amount ) / parseFloat( no_payments );
		$('#system_amortization').val( addCommas( system_amortization.toFixed(2) ) );
	}

	return false;
}

function calculate_system_interest(){
	if( $('#amount').val() == '' || $('#interest').val() == "" || $('#interest_type_id').val() == '' || $('#no_payments').val() == '' ){
		return false;
	}

	var amount = parseFloat( remove_commas( $('#amount').val() ) );
	var interest = parseFloat( remove_commas( $('#interest').val() ) );
	var interest_type_id = $('#interest_type_id').val();
	var term = parseFloat( remove_commas( $('#no_payments').val() ) );
	var monthly_amortization = amount / term;
	switch(loan.loan_mode_id){
		case '1': //simple
		case '2': //diminishing
			if( interest_type_id == 2 ){
				interest = ( amount * interest /100 );
			}
			break;
		case '3': //diminishing
			var monthly_interest = new Array();
			var ctr = 0;
			var diminishing_amount = amount;
			while( ctr < term){
				monthly_interest[ctr] = diminishing_amount * interest / 100;
				diminishing_amount = diminishing_amount - monthly_amortization;
				ctr++;
			}	

			interest = 0;
			for(var i in monthly_interest){
				interest = interest + parseFloat(monthly_interest[i]);
			}
			interest = interest / term;
			break;
	}
	$('#system_interest').val( addCommas( interest.toFixed(2) ) );
}