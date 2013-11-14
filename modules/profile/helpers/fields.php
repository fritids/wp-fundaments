<?php function skt_open_profile_fieldset($legend = '') {
	if(apply_filters('skt_open_profile_fieldset', $legend)) {
		return;
	}
	
	echo '<fieldset>';
	
	if($legend) {
		echo '<legend>' . htmlentities($legend) . '</legend>';
	}
}

function skt_close_profile_fieldset() {
	if(apply_filters('skt_close_profile_fieldset', '')) {
		return;
	}
	
	echo '</fieldset>';
}

function skt_profile_field($fname, $attrs) {
	if(apply_filters('skt_profile_field', $fname, $attrs)) {
		return;
	} ?>
	
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