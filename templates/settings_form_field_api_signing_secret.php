<?php if (\SlackLiveblog\PluginSettings::i()->is_from_env('slack_liveblog_checkbox_field_api_signing_secret')): ?>
  <input type="text" disabled>
  <p class="description" id="tagline-description">You can't edit this field, it was set by your system administrator.</p>
<?php else: ?>
  <input type="text" name="slack_liveblog_settings[slack_liveblog_checkbox_field_api_signing_secret]" value="<?= \SlackLiveblog\PluginSettings::i()->get('slack_liveblog_checkbox_field_api_signing_secret') ?>">
<?php endif ?>
