<?php
/*
Plugin Name: Adgent Tweetedia Widget
Plugin URI: http://www.tweetedia.com
Description: tweetedia(tm) is your complete Twitter Management System. Add our tweetedia(tm) widget to your page and you add the real time stream so your readers can see all the latest Tweets. The interface gives you the tools to find the interesting content on Twitter, add contributors, search Twitter by keyword and hashtag, publish only the tweets you want and black list users. You can manage down to the individual tweet or set the system to 'autopilot' - you have the controls.
Author: Adgent
Author URI: http://www.tweetedia.com
Version: 0.2
*/

if(class_exists('WP_Widget')) {

	class Adgent_Tweetedia_Widget extends WP_Widget
	{
		function Adgent_Tweetedia_Widget()
		{
			$widget_ops = array('classname' => 'adgent_tweetedia_widget', 'description' => 'Add a tweetedia widget to your blog');
			
			$this->WP_Widget('adgent_tweetedia_widget', 'Adgent Tweetedia', $widget_ops);
		}

		function widget($args, $instance)
		{
			extract($args, EXTR_SKIP);

			echo $before_widget;
			
			$feedIdent = empty($instance['feed_ident']) ? '&nbsp;' : apply_filters('adgent_tweetedia_ident', $instance['feed_ident']);
			
			if(!empty($feedIdent)) {
				printf('<script type="text/javascript">
	var tweetediaConfig = {
	ident: \'%s\',
	width: 250,
	params: {
	}
}
</script>
<script type="text/javascript" src="http://www.tweetedia.com/tweetedia-wordpress.js"></script>', $feedIdent);
			} else {
				echo '<!-- No available Adgent Tweetedia Ident found - Ignoring widget -->';
			}
			
			echo $after_widget;
		}

		function update($new_instance, $old_instance)
		{
			$instance = $old_instance;

			$widgetIdent = strip_tags($new_instance['feed_ident_url']);
			
			$config = '';
			
			// To Do: Add cURL or file_get_contents, etc to download pk

			if(ini_get('allow_url_fopen') == true && function_exists('file_get_contents')) {
				$config = file_get_contents($widgetIdent);
			} elseif(function_exists('curl_init') == true) {
				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_URL, $widgetIdent);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				$config = curl_exec($ch);
				curl_close($ch);
			} else {
				trigger_error('You must have file_get_contents with allow_url_fopen, or cURL to download the widget config. Please contact your host.');
				$instance['feed_ident'] = null;
			}

			$instance['feed_ident_url'] = $widgetIdent;
			$instance['feed_ident'] = strip_tags($config);

			return $instance;
		}

		function form($instance)
		{
			$instance = wp_parse_args((array) $instance, array(
			'feed_ident_url' => ''
			));
			
			$feedIdent = strip_tags($instance['feed_ident_url']);
			?>
		<p><label for="<?php echo $this->get_field_id('feed_ident_url'); ?>">Configuration URL: <span style="font-size: 11px;">(To get the Configuration URL, log into your tweetedia account, select your feed and follow the instructions on the Publish page.)</span> <input class="widefat" id="<?php echo $this->get_field_id('feed_ident_url'); ?>" name="<?php echo $this->get_field_name('feed_ident_url'); ?>" type="text" value="<?php echo attribute_escape($feedIdent); ?>" /></label></p>
			<?php
		}
	}

	function Register_Adgent_Tweetedia_Widget()
	{
		register_widget('Adgent_Tweetedia_Widget');
	}

	add_action('init', 'Register_Adgent_Tweetedia_Widget', 1);
}
?>