<?php function skt_open_signup_fieldset($legend = '') {
	if(apply_filters('skt_open_signup_fieldset', $legend)) {
		return;
	}
	
	echo '<fieldset>';
	
	if($legend) {
		echo '<legend>' . htmlentities($legend) . '</legend>';
	}
}

function skt_close_signup_fieldset() {
	if(apply_filters('skt_close_signup_fieldset', '')) {
		return;
	}
	
	echo '</fieldset>';
}

function skt_signup_field($fname, $attrs) {
	if(apply_filters('skt_signup_field', $fname, $attrs)) {
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

add_action('skt_register_button', 'skt_register_button', 10);
function skt_register_button() { ?>
	<input tabindex="4" type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Sign up'); ?>" tabindex="100" />
<?php }