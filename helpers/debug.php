<?php /**
 * Helper functions for debugging objects
 *
 * @package wp-fundaments
 */

function skt_debug($obj) {
	if(!isset($GLOBALS['skt_debug_objects'])) {
		$GLOBALS['skt_debug_objects'] = array();
	}
	
	$GLOBALS['skt_debug_objects'][] = $obj;
}

add_action('wp_footer', 'skt_debug_print');
add_action('admin_footer', 'skt_debug_print');
function skt_debug_print() {
	if(isset($GLOBALS['skt_debug_objects']) && is_array($GLOBALS['skt_debug_objects'])) {
		foreach($GLOBALS['skt_debug_objects'] as $i => $obj) {
			$top = ($i * 24) + 100;
			$rgb = array(
				rand(0, 255),
				rand(0, 255),
				rand(0, 255)
			);
			
			$zindex = 9999 + $i; ?>
			
			<div class="skt-debug" id="skt-debug-<?php echo $i; ?>" style="position: fixed; top: <?php echo $top; ?>px; left: 0; border-left: 16px solid rgb(<?php echo implode(', ', $rgb); ?>); max-width: 100%; max-height: 100%; overflow: scroll; background: rgba(0, 0, 0, 0.75); color: #999; font-face: monotype; padding: 0; z-index: <?php echo $zindex; ?>;">
				<pre style="margin: 10px;"><?php print_r($obj); ?></pre>
			</div>
			<script>
				jQuery(document).ready(
					function($) {
						var bug = $(
							'#skt-debug-<?php echo $i; ?>'
						);
						
						bug.data(
							'width', bug.width() + 20
						).data(
							'height', bug.height() + 20
						).hover(
							function() {
								if($(this).data('animating')) {
									return;
								}
								
								$('.skt-debug').not(bug).css('opacity', 0.5).data('animating', true);
								$(this).data('animating', true).animate(
									{
										width: $(this).data('width')
									},
									100,
									function() {
										$(this).animate(
											{
												height: $(this).data('height')
											},
											100,
											function() {
												$(this).data('animating', false);
											}
										);
									}
								);
							},
							function() {
								if($(this).data('animating')) {
									return;
								}
								
								$(this).animate(
									{
										height: 16
									},
									100,
									function() {
										$(this).animate(
											{
												width: 0
											},
											100,
											function() {
												$(this).data('animating', false);
												$('.skt-debug').not(bug).css('opacity', 1).data('animating', false);
											}
										);
									}
								);
							}
						).css(
							{
								width: 16,
								height: 16
							}
						);
					}
				)
			</script>
			<?php $GLOBALS['skt_fundaments_debug_divs'] = $i + 1;
		}
	}
}