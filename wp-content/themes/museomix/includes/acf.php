<?php
function my_acf_init() {
	
	acf_update_setting('google_api_key', 'AIzaSyDKqH5424vH-QHEt7gw6xciWwK01RnRXwQ');
}

add_action('acf/init', 'my_acf_init');