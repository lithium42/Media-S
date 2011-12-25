<form id="commentform" class="contactform" method="post" action="">
	<?php wp_nonce_field( $this->tag, $this->tag.'_nonce' ); ?>
	<?php if ( count( $this->messages ) > 0 ) : ?>
	<div id="contact_response" class="<?php echo ( count( $this->messages['error'] ) > 0 ? 'error' : 'ok' ); ?>">
		<?php if ( isset( $this->messages['ok'] ) ) : ?>
			<p><?php esc_html_e( $this->messages['ok'] ); ?></p>
		<?php elseif ( is_array( $this->messages['error'] ) ) : ?>
		<ul>
			<li><?php echo implode( '</li><li>', $this->messages['error'] ); ?></li>
		</ul>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<p class="content_name">
		<input type="text" tabindex="1" size="22" value="<?php if ( isset( $contact['name'] ) ) { esc_html_e( $contact['name'] ); } ?>" id="contact_name" name="contact[name]" />
		<label for="contact_name">
			<small><?php _e( 'Name' ); ?> (<?php _e( 'required' ); ?>)</small>
		</label>
	</p>
	<p class="content_email">
		<input type="text" tabindex="2" size="22" value="<?php if ( isset( $contact['email'] ) ) { esc_html_e( $contact['email'] ); } ?>" id="contact_email" name="contact[email]" />
		<label for="contact_email">
			<small><?php _e( 'Email' ); ?> (<?php _e( 'required' ); ?>)</small>
		</label>
	</p>
	<p class="content_phone">
		<input type="text" tabindex="3" size="22" value="<?php if ( isset( $contact['phone'] ) ) { esc_html_e( $contact['phone'] ); } ?>" id="contact_phone" name="contact[phone]" />
		<label for="contact_phone">
			<small><?php _e( 'Phone' ); ?></small>
		</label>
	</p>
	<p class="content_message">
		<textarea tabindex="4" rows="10" cols="100%" id="contact_message" name="contact[message]"><?php if ( isset( $contact['message'] ) ) { esc_html_e($contact['message']); } ?></textarea>
	</p>
	<p class="content_submit">
		<input type="submit" value="<?php _e( 'Send Message' ); ?>" tabindex="5" id="submit" name="contact[submit]" />
	</p>
</form>