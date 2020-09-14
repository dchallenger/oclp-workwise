/*!
* jQuery Wizard Form plugin
* Version 0.1 (05-SEPT-2011)
* @requires jQuery v1.2.3 or later
*
* Copyright (c) 2011 HDI SysTech
*/

;( function( $ ){

	var wizardFormMethods = {
		init : function( options ) {
			
		},
		next : function( ) {
			
		},
		prev : function( ) {
			
		},
	};

	$.fn.wizardForm = function( method ){
		if( wizardFormMethods[method] ){
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		}
		else if ( typeof method === 'object' || ! method ){
			return methods.init.apply( this, arguments );
		}
		else{
			$.error( 'Method ' +  method + ' does not exist on jQuery.wizardForm' );
		}  

		
		
		
		
		
		var current = '';
		
		var settings = {
			'next_button_label' : 'Next',
			'prev_button_label' : 'Prev'
		};
		
		this.each(function() {
			
		});
	}
})( jQuery );