(function( $, s ){

var container = $( '#wp_pa_box' );

$( document).ready( function() {

	/**
	 * Add Note button
	 */
	$('.add-note', container).click(function(){
		$(this).hide();
		$('.activity-form', container).show();
	});

	/**
	 * Cancel button
	 */
	$('.activity-form a.cancel', container).click(function(){
		$('.add-note', container).show();
		$('#pa_content').val('');
		$('.activity-form', container).hide();
	});

	/**
	 * Submit Note button
	 */
	$('.activity-form a.submit-note', container).click(function(){

		var data = {
			'action': 'pa_add_note',
			'user_id': $('#pa_user_id').val(),
			'post_id': $('#pa_post_id').val(),
			'content': $('#pa_content').val()
		};

		$.ajax({
			type: "POST",
			url: s.admin_ajax,
			data: data,
			dataType: "json",
			cache: !1,
			complete: function(r) {
				var d = r.responseJSON;
				if( typeof d !== 'object' ) {
					alert( 'Sorry, something went wrong! Please reload page and try again.' );
				} else if( !d.success ) {
					alert(d.data);
				} else {
					$('.add-note', container).show();
					$('#pa_content').val('');
					$('.activity-form', container).hide();
					$('.activity-list', container).prepend( '<li class="activity-item">'+ d.data.html + '</li>');
				}
			}
		});

		return false;
	});

	$('a.delete-note', container).on('click', function(){
		var el = $(this),
		    id = $(this).data('id');
		if( typeof id == 'undefined' ) {
			alert( 'Activity ID is undefined!' );
			return false;
		}

		var data = {
			'action': 'pa_delete_note',
			'comment_id': id
		};

		$.ajax({
			type: "POST",
			url: s.admin_ajax,
			data: data,
			dataType: "json",
			cache: !1,
			complete: function(r) {
				var d = r.responseJSON;
				if( typeof d !== 'object' ) {
					alert( 'Sorry, something went wrong! Please reload page and try again.' );
				} else if( !d.success ) {
					alert(d.data);
				} else {
					el.parents('li.activity-item').remove();
				}
			}
		});
		return false;
	});

} );

})( jQuery, __wp_pa_vars );