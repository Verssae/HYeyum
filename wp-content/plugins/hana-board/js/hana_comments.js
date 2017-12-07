jQuery(document).ready(function($) {
    if( typeof $(".hana-comment-delete-link") != 'undefined' ) {
      $(".hana-comment-delete-link").click(function(e){
        $deleteButton=$(this);
        if( confirm(hana_comment.msg.confirm) ) {
          jQuery.ajax({
    				type: 'post',
    				url: hana_comment.ajaxurl,
    				data: 'comment_id='+$deleteButton.data('commentId'),
    				dataType: 'json',
    				beforeSend: function() {
    				},
    				complete: function() {
    				},
    				success: function(data) {
    					console.log(hana_comment.msg);
    					if ( data.error == 1 ) {
    					  alert(hana_comment.msg.error_permission);
    					} else if( data.error == 2) {
      					  alert(hana_comment.msg.error_have_children);    						
    					}else {
    						$('#comment-'+$deleteButton.data('commentId')).remove();
    					}
    				}
    			});
        }

      });
    }
});