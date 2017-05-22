<?php
class CPTMailChimpAPI {
	private $apiKey;
	private $dataCenter;
	private $apiURI;
	private $hasError = false;

	const API_BASE = 'api.mailchimp.com/3.0/';
	const INLINE_CSS = false;
	const AUTO_FOOTER = true;
	const TRACK_CLICKS = true;

	public function __construct($apiKey){
		$this->apiKey = $apiKey;
		$this->get_api_uri();
	}

	private function get_api_uri(){
		$this->dataCenter = substr($this->apiKey, strpos($this->apiKey,'-')+1);
		$this->apiURI = 'https://'.$this->dataCenter.'.'.self::API_BASE;
	}

	public function create_campaign($listID, $subject, $fromName, $replyEmail, $url){
		$body = array(
			'type'=>'regular',
			'recipients'=>array(
				'list_id'=>$listID
			),
			'settings'=>array(
				'title'=>$subject . ' | ' . date('F j, Y'),
				'subject_line'=>$subject,
				'from_name'=>$fromName,
				'reply_to'=>$replyEmail,
				'auto_footer'=>self::AUTO_FOOTER,
				'inline_css'=>self::INLINE_CSS
			),
			'tracking'=>array(
				'html_clicks'=>self::TRACK_CLICKS
			)
		);
		$args = array(
			'body'=>json_encode($body)
		);
		$response = $this->handle_response(wp_remote_post($this->apiURI.'campaigns', $this->add_mailchimp_authentication($args)));

		$campaignID = $response->id;
		$this->set_campaign_content($campaignID, $url);

		return $campaignID;
	}

	private function set_campaign_content($campaignID, $url){
		$body = array(
			'url'=>$url
		);
		$args = array(
			'body'=>json_encode($body),
			'method'=>'PUT'
		);
		return $this->handle_response(wp_remote_request($this->apiURI.'campaigns/'.$campaignID.'/content', $this->add_mailchimp_authentication($args)));
	}

	public function delete_campaign($campaignID){
		$args = array(
			'method'=>'DELETE'
		);
		return $this->handle_response(wp_remote_request($this->apiURI.'campaigns/'.$campaignID, $this->add_mailchimp_authentication($args)));
	}

	public function send_campaign($campaignID){
		$args = array(); //no parameters for this one, but we need it for to populate with authentication
		$response = $this->handle_response(wp_remote_post($this->apiURI.'campaigns/'.$campaignID.'/actions/send', $this->add_mailchimp_authentication($args)));

		return !$this->hasError;
	}

	public function test_campaign($campaignID, $emails){
		$body = array(
			'test_emails'=>$emails,
			'send_type'=>'html'
		);
		$args = array(
			'body'=>json_encode($body)
		);
		wp_remote_post($this->apiURI.'campaigns/'.$campaignID.'/actions/test', $this->add_mailchimp_authentication($args));
		return true;
	}

	private function add_mailchimp_authentication($args){
		$args['headers'] = array(
				'Authorization' => 'Basic ' . base64_encode( 'user:'. $this->apiKey )
		);
		return $args;
	}

	public function get_list($listID){
		$args = array();
		return $this->handle_response(wp_remote_get($this->apiURI.'lists/'.$listID, $this->add_mailchimp_authentication($args)));
	}

	public function get_subscribers($listID){
		$args = array();
		return $this->handle_response(wp_remote_get($this->apiURI.'lists/'.$listID.'/members', $this->add_mailchimp_authentication($args)));
	}

	private function handle_response($response){
		//TODO clean up this error handling to allow passing error messages back to the user
		if( is_wp_error($response) ){
				error_log('CPT MailChimp API: WordPress Error'. $response->get_error_message());
				$this->hasError = true;
		} else {
			if( FALSE === strpos(wp_remote_retrieve_response_code($response), '2') || 0 != strpos(wp_remote_retrieve_response_code($response), '2') ){
				error_log('CPT MailChimp API: MailChimp Error'. wp_remote_retrieve_body($response));
				$this->hasError = true;
			} else {
				return (wp_remote_retrieve_response_code($response) == 204) ? true : json_decode(wp_remote_retrieve_body($response));
			}
		}
	}

}