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

<?php wp_nonce_field( self::NONCE_KEY.'', self::NONCE_KEY.'_nonce' ); ?>

<?php if( empty($newsletterSent) ): ?>
	<?php if('publish' == $post->post_status && !empty($campaignID)): ?>
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
	<h4>Current Status</h4>
	This newsletter was sent on:<br><i><time><?php echo date(self::DATE_STRING, $newsletterSent); ?></time></i>.
<?php endif; ?>