jQuery(document).ready(
	function($) {
		$('.skt-media-upload-button').on('click',
			function(e) {
				e.preventDefault();
				
				var input = $(this).attr('data-input');
				var frame = wp.media(
					{
						title: 'Use This Media',
						button: {
							text: 'Select Media',
							close: true
						}
					}
				);
				
				frame.on('select',
					function() {
						var attachment = frame.state().get('selection').first();
						var control = $('input[name="' + input + '"]');
						var container = control.closest('.skt-media-handler');
						
						console && console.log(attachment);
						
						if(attachment.attributes.link != '0') {
							container.find('.skt-media-url').html(
								'<a href="' + attachment.attributes.link + '" target="_blank">' + attachment.attributes.title + '</a>'
							);
							
							control.val(attachment.id);
						} else {
							control.val('');
						}
					}
				);
				
				frame.open();
			}
		);
		
		window.send_to_editor = function(html) {
			alert(html);
			tb_remove();
		};
	}
);