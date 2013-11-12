jQuery(document).ready(
	function($) {
		function skt_delete_row(e) {
			var deleteRow = $(this).closest('tr');
			var table = deleteRow.closest('table');
			var prefix = table.attr('data-prefix');
			var rows;
			
			e.preventDefault();
			deleteRow.remove();
			
			rows = table.find('tbody tr').not('.skt-new-row').not('.skt-empty-row');
			rows.each(
				function(count) {
					var row = $(this);
					
					row.find('td').each(
						function() {
							var input = $(this).find(':input');
							var name = input.attr('name');
							var iPrefix = prefix + '__';
							var end;
							
							if(typeof(name) == 'undefined') {
								return;
							}
							
							if(name.substr(0, iPrefix.length) == iPrefix) {
								end = name.indexOf('__', iPrefix.length);
								name = prefix + '__' + count + '__' + name.substr(end + 2);
							}
							
							input.attr('name', name);
						}
					);
				}
			);
			
			$('input[name="' + prefix + '__count"]').val(rows.length);
		}
		
		$('.skt-fieldset-table').each(
			function() {
				var table = $(this);
				var prefix = table.attr('data-prefix');
				
				table.parent().find('.skt-add-row').on('click',
					function(e) {
						var count = table.find('tbody').find('tr').not('.skt-new-row').not('.skt-empty-row').length;
						var clone = table.find('.skt-new-row').last().clone();
						
						e.preventDefault();
						clone.find('td').each(
							function() {
								var input = $(this).find(':input');
								var name = input.attr('name');
								var iPrefix = prefix + '__-1__';
								
								if(typeof(name) == 'undefined') {
									return;
								}
								
								if(name.substr(0, iPrefix.length) == iPrefix) {
									name = prefix + '__' + count + '__' + name.substr(iPrefix.length);
								}
								
								input.attr('name', name).attr('id', 'id' + name);
							}
						);
						
						clone.removeClass('skt-new-row');
						clone.find('.skt-delete-row').on('click', skt_delete_row);
						table.find('tbody').append(clone.show());
						
						$('input[name="' + prefix + '__count"]').val(count + 1);
						table.find('.skt-empty-row').remove();
					}
				);
				
				table.find('.skt-new-row').hide();
				table.find('tbody tr').not('.skt-new-row').not('.skt-empty-row').find('.skt-delete-row').on('click', skt_delete_row);
			}
		);
	}
);