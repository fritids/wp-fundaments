<?php add_action('skt_open_profile_fieldset', 'skt_open_profile_fieldset', 10, 1);
function skt_open_profile_fieldset($legend = '') {
	echo '<fieldset>';
	
	if($legend) {
		echo '<legend>' . htmlentities($legend) . '</legend>';
	}
}

add_action('skt_close_profile_fieldset', 'skt_close_profile_fieldset');
function skt_close_profile_fieldset() {
	echo '</fieldset>';
}

add_action('skt_profile_field', 'skt_profile_field', 10, 2);
function skt_profile_field($fname, $attrs) { ?>
	<p>
		<label>
			<?php echo htmlentities($attrs['label']); ?>
			<?php if(isset($attrs['label'])) {
				unset($attrs['label']);
			} ?>
			
			<?php $GLOBALS['skt_fundaments']->input($fname, $attrs); ?>
		</label>
	</p>
<?php }