<?php wp_nonce_field('extra-checkout-field' . $id, 'extra-checkout-field' . $id . '-nonce'); ?>
<input type="hidden" name="extra_checkout_step_step_id" value="<?php echo $id; ?>" />
<input type="hidden" name="extra_checkout_step_name" value="<?php echo $name; ?>" />
<input type="hidden" name="action" value="extra_checkout_fields_save_data" />
