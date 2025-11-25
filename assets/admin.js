document.addEventListener('DOMContentLoaded', function () {
	var mainform, isSubmitting = false;
	jQuery(document).ready(function () {
		mainform = jQuery('#xmlsf-settings-form');
		mainform.on('submit',function(){
			isSubmitting = true
		})
		mainform.data('initial-state', mainform.serialize());
		jQuery(window).on('beforeunload', function(event) {
			if (!isSubmitting && mainform.length && mainform.serialize() != mainform.data('initial-state')){
				event.preventDefault();
				return "<?php echo translate( 'The changes you made will be lost if you navigate away from this page.' ); ?>";
			}
		});
	});
}, false );