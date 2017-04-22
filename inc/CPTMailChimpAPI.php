<?php
class CPTMailChimpAPI {
	private $apiKey;

	const INLINE_CSS = false;

	public function __construct($apiKey){
		$this->apiKey = $apiKey;
	}

	public function create_campaign($title, $url, $listID){
		//TODO create the campaign
		return 123456;
	}

	public function delete_campaign($campaignID){
		//TODO delete the campaign
		return true;
	}

	public function send_campaign($campaignID){
		//TODO send the campaign
		return true;
	}

	public function test_campaign($campaignID, $emails){
		//TODO send the test
		return true;
	}
}