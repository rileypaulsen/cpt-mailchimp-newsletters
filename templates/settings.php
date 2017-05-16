<div class="wrap">
	<h1>MailChimp Settings</h1>
	<p>These keys are specific to your MailChimp account. Don’t change them if you don't know what you’re doing.</p>
	<hr>
	<form method="post" action="<?php menu_page_url(self::MENU_SLUG_PREFIX.'settings'); ?>">
		<?php wp_nonce_field( self::NONCE_KEY.'', self::NONCE_KEY.'_nonce' ); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="<?php echo self::API_KEY_FIELD_NAME; ?>">API Key</label></th>
					<td>
						<input type="text" name="<?php echo self::API_KEY_FIELD_NAME; ?>" id="<?php echo self::API_KEY_FIELD_NAME; ?>" class="regular-text code" required value="<?php echo $this->CPT_MAILCHIMP_NEWSLETTERS_MAILCHIMP_API_KEY; ?>"/>
						<p class="description">This key is difficult to acquire on purpose, and shouldn’t be shared anywhere. Anyone with access can control your entire MailChimp account!</p>
						<h4>To generate an API Key:</h4>
						<ol>
							<li>Navigate to your MailChimp account profile</li>
							<li>Go to <b>Extras &gt; API Keys</b></li>
							<li>Click the <b>Create a Key</b> button</li>
							<li>Click in the <b>Label</b> column to name the key</li>
							<li>Copy the generated key here</li>
						</ol>
					</td>
				</tr>

				<tr>
					<th><label for="<?php echo self::LIST_ID_FIELD_NAME; ?>">List ID</label></th>
					<td>
						<input type="text" name="<?php echo self::LIST_ID_FIELD_NAME; ?>" id="<?php echo self::LIST_ID_FIELD_NAME; ?>" class="regular-text code" required value="<?php echo $this->CPT_MAILCHIMP_NEWSLETTERS_MAILCHIMP_LIST_ID; ?>"/>
						<p class="description">This can be found in MailChimp by viewing a list, then navigating to <b>Settings &gt; List name and defaults</b>.</p>
					</td>
				</tr>

				<tr>
					<th><label for="<?php echo self::FROM_NAME_FIELD_NAME; ?>">Sender Name</label></th>
					<td>
						<input type="text" name="<?php echo self::FROM_NAME_FIELD_NAME; ?>" id="<?php echo self::FROM_NAME_FIELD_NAME; ?>" class="regular-text" required value="<?php echo $this->CPT_MAILCHIMP_NEWSLETTERS_FROM_NAME; ?>"/>
						<p class="description">The name that newsletters will be sent from.</p>
					</td>
				</tr>

				<tr>
					<th><label for="<?php echo self::REPLY_EMAIL_FIELD_NAME; ?>">Sender Email</label></th>
					<td>
						<input type="email" name="<?php echo self::REPLY_EMAIL_FIELD_NAME; ?>" id="<?php echo self::REPLY_EMAIL_FIELD_NAME; ?>" class="regular-text" required value="<?php echo $this->CPT_MAILCHIMP_NEWSLETTERS_REPLY_EMAIL; ?>"/>
						<p class="description">The email address that newsletters will be sent from.</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button('Save Settings', 'primary', self::SAVE_FIELD_NAME); ?>
	</form>
</div>