<?php
/**
 * Plugin Name:       CPT MailChimp Newsletters
 * Plugin URI:        http://rileypaulsen.com
 * Description:       Allows sending a custom post type’s “Single” template as a MailChimp campaign. Use whatever desired means to generate the template file.
 * Version:           1.0.0
 * Author:            Riley Paulsen
 * Author URI:        http://rileypaulsen.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cpt-mailchimp-newsletters
 * Domain Path:       /languages
 */
if( !class_exists('CPTMailChimpNewsletters') ){
	require_once('secret.php');
	require_once('inc/CPTMailChimpAPI.php');

	class CPTMailChimpNewsletters {
		private $noticeType;
		private $noticeStatus;
		private $noticeData;

		const SENT_KEY = 'newsletter-sent';
		const DATE_STRING = 'F j, Y @ g:i a';
		const POST_TYPE = 'newsletter';
		const NONCE_KEY = 'cpt_mailchimp_newsletter_integration';
		const CAMPAIGN_KEY = 'cpt_mailchimp_newsletter_campaign_id';
		const CAPABILITY = 'edit_post';

		public function __construct() {
			date_default_timezone_set( "America/Indiana/Indianapolis" );
			add_action( 'init', array( $this, 'init' ) );
		}

		public function init(){
			add_filter( 'manage_'.self::POST_TYPE.'_posts_columns' , array($this, 'add_custom_columns') );
			add_action( 'manage_posts_custom_column' , array($this, 'custom_columns'), 10, 2 );
			add_filter( 'single_template', array($this,'load_newsletter_template') );
			add_action( 'save_post', array($this, 'save') );
			add_action( 'publish_'.self::POST_TYPE, array($this, 'setup_campaign'), 10, 2 );
			add_action( 'before_delete_post', array($this, 'delete_campaign'), 10, 2 );
			add_action( 'admin_notices', array($this, 'display_notice') );
			add_filter( 'redirect_post_location', array($this, 'persist_notice') );
			add_action( 'add_meta_boxes', array($this, 'meta_box') );
		}

		public function add_custom_columns($columns){
			unset($columns['date']);
			$custom = [
				'sent'=>'Sent'
			];
			return array_merge($columns, $custom);
		}

		public function custom_columns($column, $post_id){
			switch ( $column ) {
				case 'sent':
					$sent = ($sent = get_post_meta($post_id, self::SENT_KEY, true)) ? date(self::DATE_STRING, $sent) : '<i>Pending</i>';
					echo $sent;
					break;
				default:
					break;
			}
		}

		//if there's no single-newsletter.php in the theme folder, use the one from the plugin
		public function load_newsletter_template($template){
			global $post;

			if ($post->post_type == self::POST_TYPE){
				$plugin_path = plugin_dir_path( __FILE__ ).'templates/';
				$template_name = 'single-'.self::POST_TYPE.'.php';

				//check to see if the theme has a file that matches the desired template
				if( (!empty($template) && $template === locate_template($template_name)) || !file_exists($plugin_path . $template_name)) {
					return $template;
				}

				//use the file from the plugin if one isn't in the theme
				return $plugin_path . $template_name;
			}
			return $template;
		}

		public function save($post_id){
			// Verify that the nonce is valid.
			if ( !isset( $_POST[self::NONCE_KEY.'_nonce']) || !wp_verify_nonce( $_POST[self::NONCE_KEY.'_nonce'], self::NONCE_KEY) ) {
				return $post_id;
			}

			/*
			* If this is an autosave, our form has not been submitted,
			* so we don't want to do anything.
			*/
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			// Check the user's permissions.
			if ( self::POST_TYPE != $_POST['post_type'] || !current_user_can( self::CAPABILITY, $post_id) ) {
				return $post_id;
			}

			if( isset($_POST['newsletter-test'], $_POST['newsletter-preview-emails']) ){
				$this->notice = 'a cool message';
				$this->noticeStatus = 'error';
				$this->send_test($post_id);
			}

			if( isset($_POST['newsletter-campaign']) ){
				$this->send_campaign($post_id);
			}

		}

		public function setup_campaign($post_id, $post){
			$api = new CPTMailChimpAPI(CPT_MAILCHIMP_NEWSLETTERS_MAILCHIMP_API_KEY);
			$campaignID = $api->create_campaign($post->post_title, get_permalink($post_id), CPT_MAILCHIMP_NEWSLETTERS_MAILCHIMP_LIST_ID);
			update_post_meta($post_id, self::CAMPAIGN_KEY, $campaignID);
		}

		public function delete_campaign($post_id){
			global $post_type;
			if( self::POST_TYPE !== $post_type ){
				return;
			}
			$api = new CPTMailChimpAPI(CPT_MAILCHIMP_NEWSLETTERS_MAILCHIMP_API_KEY);
			$campaignID = $api->delete_campaign($this->get_campaign_id($post_id));
		}

		private function send_test($post_id){
			$emails = $this->parse_emails($_POST['newsletter-preview-emails']);

			if( empty($emails) ){
				return;
			}

			$api = new CPTMailChimpAPI(CPT_MAILCHIMP_NEWSLETTERS_MAILCHIMP_API_KEY);
			$result = $api->test_campaign($this->get_campaign_id($post_id), $emails);

			$this->noticeType = 'test';
			$this->noticeStatus = ( $result ) ? 'success' : 'error';
			$this->noticeData = count($emails);
		}

		private function send_campaign($post_id){
			$api = new CPTMailChimpAPI(CPT_MAILCHIMP_NEWSLETTERS_MAILCHIMP_API_KEY);
			$result = $api->send_campaign($this->get_campaign_id($post_id));

			$this->noticeType = 'campaign';
			$this->noticeStatus = ( $result ) ? 'success' : 'error';
			update_post_meta($post_id, self::SENT_KEY, time());
		}

		private function parse_emails($rawEmails){
			return array_filter(array_map('trim', explode(',', $rawEmails)), function($email){
				return filter_var($email, FILTER_VALIDATE_EMAIL);
			});
		}

		private function get_campaign_id($post_id){
			return get_post_meta($post_id, self::CAMPAIGN_KEY, true);
		}

		public function display_notice(){
			if( !isset($_GET['newsletter-notice-status'], $_GET['newsletter-notice-type']) ){
				return;
			}
			$status = $_GET['newsletter-notice-status'];
			if( $_GET['newsletter-notice-type'] == 'test' ){
				$count = $_GET['newsletter-notice-data'];
				$notice = ($status == 'success') ? '<b>Test newsletter sent to '.$count.' email(s)!</b> Look for them shortly.' : 'There was a problem sending the test email. <i>Please try again.</i>';
			} else {
				$notice = ($status == 'success') ? '<b>Newsletter sent successfully!</b> Look for it shortly.' : 'There was a problem sending the newsletter. <i>Please try again.</i>';
			}
			?>
			<div class="notice notice-<?php echo $status; ?>">
				<p><?php echo $notice; ?></p>
			</div>
			<?php
		}

		public function persist_notice($loc){
			$args = [];
			if( isset($this->noticeType) ){
				$args['newsletter-notice-type'] = $this->noticeType;
			}
			if( isset($this->noticeStatus) ){
				$args['newsletter-notice-status'] = $this->noticeStatus;
			}
			if( isset($this->noticeData) ){
				$args['newsletter-notice-data'] = $this->noticeData;
			}
			return add_query_arg( $args, $loc );
		}

		public function meta_box(){
			add_meta_box( 'cpt-mailchimp-newsletters-meta-box', __( 'Newsletter Controls', 'textdomain' ), array($this,'meta_box_content'), 'newsletter', 'side' );
		}

		public function meta_box_content($post){
			// Add an nonce field so we can check for it later.
			wp_nonce_field( self::NONCE_KEY.'', self::NONCE_KEY.'_nonce' );
			$newsletterSent = get_post_meta($post->ID, self::SENT_KEY, true);
			?>
			<style type="text/css">
				#cpt-mailchimp-newsletters-meta-box {

				}
				#cpt-mailchimp-newsletters-meta-box h4 {
					margin:0 0 .15em;
					font-size: 14px;
				}
				#cpt-mailchimp-newsletters-meta-box hr {
					margin:20px 0;
					display: block;
				}
				#cpt-mailchimp-newsletters-meta-box label {
					font-style:italic;
					font-size:11px;
					margin-bottom:.15em;
					display: block;
				}
				#cpt-mailchimp-newsletters-meta-box .button {
					margin-top:.5em;
				}
				#cpt-mailchimp-newsletters-meta-box textarea {
					display: block;
					width:100%;
					height:60px;
					font-size: 12px;
					margin-bottom:.5em;
					padding:5px;
				}
				#cpt-mailchimp-newsletters-meta-box .mailchimp {
					width:25px;
					height:auto;
					display:block;
					margin:0 auto 0;
				}
				#cpt-mailchimp-newsletters-meta-box .mailchimp img {
					width:100%;
					display: block;
				}
			</style>
			<?php if( empty($newsletterSent) ): ?>
				<?php if('publish' == $post->post_status): ?>
					<p><i>MailChimp Campaign ID: <b><?php echo $this->get_campaign_id($post->ID); ?></b></i></p>
					<hr>
					<h4>Send Preview Email</h4>
					<label for="newsletter-preview-emails">Enter email addresses (comma separated)</label>
					<textarea name="newsletter-preview-emails" id="newsletter-preview-emails"></textarea>
					<button type="submit" class="button" name="newsletter-test">Send Test</button>
					<hr>
					<h4>Send Newsletter</h4>
					<label for="list">Send newsletter to MailChimp subscribers</label>
					<button type="submit" class="button button-primary" name="newsletter-campaign" onclick="return confirm('Are you sure you want to send the campaign?');">Send Newsletter</button>
				<?php else: ?>
					<p><i>Publish the newsletter above to enable previewing and sending.</i></p>
				<?php endif; ?>
			<?php else: ?>
				<h4>Status</h4>
				This newsletter was sent on:<br><i><time><?php echo date(self::DATE_STRING, $newsletterSent); ?></time></i>.
			<?php endif; ?>
			<?php
		}
	}
	new CPTMailChimpNewsletters();
}