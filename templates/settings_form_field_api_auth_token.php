<?php if (\SlackLiveblog\PluginSettings::i()->is_from_env('settings_form_field_api_auth_token')): ?>
  <input type="text" disabled>
  <p class="description" id="tagline-description">You can't edit this field, it was set by your system administrator.</p>
<?php else: ?>
  <input type="text" name="slack_liveblog_settings[settings_form_field_api_auth_token]" value="<?= \SlackLiveblog\PluginSettings::i()->get('settings_form_field_api_auth_token') ?>">
<?php endif ?>
