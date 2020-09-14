$( document ).ready( function(){
	// Row highlighting, column highlighting is not working yet.
    $( "#module-access" ).delegate( 'td, th','mouseover mouseleave', function(e) {
        if ( e.type == 'mouseover' ) {
          $( this ).parent().addClass( "hover" );
          $( "colgroup" ).eq( $( this ).index() ).addClass( "hover" );
        }
        else {
          $( this ).parent().removeClass( "hover" );
          $( "colgroup" ).eq( $( this ).index() ).removeClass( "hover" );
        }
    });
    
    // Tick all the checkboxes in a column when the action name is clicked
    $( ".action-name" ).click( function() {
        cbClass = ".cb-" + $( this ).text();          
       if ( $( this ).hasClass( "chk" ) ) {
           $( cbClass ).attr( "checked", false );
           $( this ).toggleClass( "chk" );
       } else {
           $( cbClass ).attr( "checked", "checked" );
           $( this ).toggleClass( "chk" );
       }        
    })

    // Tick all the checkboxes in a row when the module name is clicked
    $( ".module-name" ).css("cursor","pointer").click( function() {        
        tdChkBox = $( this ).parents('tr').children( "td" ).children( "input[type=checkbox]" );
        if ( $( this ).hasClass( "chk" ) ) {
            $( this ).toggleClass( "chk" );
            $( tdChkBox ).attr( "checked", "checked" ); 
        } else {
            $( this ).toggleClass( "chk" );
            $( tdChkBox ).attr( "checked", false );
        }
    })
    
    // Tick the checkbox when the user clicks on the td
    $( "#module-access td" ).click( function( event ){        
        if ( event.target.type != "checkbox" ) {
            if ( $( this ).children( "input[type=checkbox]" ).attr( "checked" ) == true ){
                $( this ).children( "input[type=checkbox]" ).attr( "checked", false );
            } else {
                $( this ).children( "input[type=checkbox]" ).attr( "checked", true );
            }            
        }
    })
    
    $( "a[rel=action-checkall]" ).click( function() {
        $( "#module-access tr td input[type=checkbox]" ).attr( "checked", "checked" );    
        return false;
    })
    
    $( "a[rel=action-uncheckall]" ).click( function() {
		$( "#module-access tr td input[type=checkbox]" ).attr( "checked", false );    
        return false;
    });
	


	var trctr = 0;
	$('#module-access tbody tr').each(function(){
		if( trctr % 2 == 0) 
			$(this).addClass("even");
		else
			$(this).addClass("odd");
		trctr++;
	});    

  $('tr.parent span.trigger')
      .css("cursor","pointer")
      .attr("title","Click to expand/collapse")
      .click(function() {        
        id = $(this).parents('tr').attr('id');                
        
        if ($(this).text() == '+') {
          $(this).parents('tr').siblings('.child-'+id).show();
          $(this).text('-');
        } else {          
          $(this).parents('tr').siblings('.' + id).children('th').children('span.trigger').text('+');
          $(this).parents('tr').siblings('.' + id).hide();          
          $(this).text('+');
        }
      });
  
  $('tr[class^=child-]').hide().children('td');
});