jQuery(document).ready(
	function($) {
		$('.skt-date-handler').each(
			function() {
				$(this).append('<a class="button skt-date-now">Now</a>');
			}
		);
		
		$('.skt-date-now').on('click',
			function(e) {
				var date = new Date();
				var container = $(this).closest('.skt-date-handler');
				
				function pad(str) {
					while(str.length < 2) {
						str = '0' + str;
					}
					
					return str;
				}
				
				e.preventDefault();
				container.find('.skt-date-day').val(date.getDate());
				container.find('.skt-date-month').val(date.getMonth() + 1);
				container.find('.skt-date-year').val(date.getFullYear());
				container.find('.skt-date-time').val(
					pad(date.getHours()) + ':' +
					pad(date.getMinutes()) + ':' +
					pad(date.getSeconds())
				);
			}
		);
	}
);