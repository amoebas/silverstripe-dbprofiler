jQuery(document).ready(function() {
 	$("#profile-query-data").tablesorter();  
	
	jQuery('a.showonclick').click(function() {
		var id = this.id.replace( 'click-', '' );
		jQuery('#showonclickarea').html( jQuery('#' + id).html() );
		jQuery('#showonclickarea').removeClass( 'hidden' );
  		
  		var pos = jQuery('#' + id).offset();  
		jQuery('#showonclickarea').css( {"left": (pos.left) + "px", "top":pos.top + "px"} );
		jQuery('#showonclickarea').show();
		return false;
	});

	jQuery('a.doit').click(function() {
		jQuery('#showonclickarea').html( 'Loading...' );
		url = jQuery(this).attr('href');
		jQuery.get( url,
			function(data) {
				jQuery('#showonclickarea').html( data );
		});
		return false;
	});

	jQuery('a.doit').click(function() {
		jQuery('table.tablesorter tbody td').each(function(){
			jQuery(this).removeClass('viewing');
		});
		jQuery(this).parent().parent().children().each(function(){
			jQuery(this).addClass('viewing');
		});
		return false;
	});
});