<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\Helpers;
use SlackLiveblog\FrontCore;
use SlackLiveblog\Db;

abstract class Consumer {
  protected array $data;
  protected string $slack_channel_id;
  protected string $slack_api_access_key;

  abstract public function consume(): array;

  public function __construct(array $data, string $slack_channel_id) {
    $this->data = $data;
    $this->slack_channel_id = $slack_channel_id;
    $this->slack_api_access_key = $this->get_slack_api_access_key();
  }

  protected function get_message_text($incoming_message) {
    $message_text = '';

    if (isset($incoming_message['blocks'])) {
      $message_text = $this->get_message_from_blocks($incoming_message['blocks']);
    }

    if (isset($incoming_message['files'])) {
      $message_text .= $this->get_files_text($incoming_message['files']);
    }

    $message_text = $this->decorate_message($message_text);

    return $message_text;
  }

  private function get_slack_api_access_key() {
    $channel = FrontCore::$channels->get_channel(['slack_id' => $this->slack_channel_id]);
    $workspace = Db::i()->get_row('workspaces', ['access_token'], ['id' => $channel->workspace_id]);

    return $workspace->access_token;
  }

  private function get_message_from_blocks($blocks) {
    $text_elements = $blocks[0]['elements'][0]['elements'];
    $text_elements = array_map(function ($element) {
      if (isset($element['text']) && isset($element['url'])) {
        return "<a href=\"{$element['url']}\" target=\"blank\">{$element['text']}</a>";
      }

      if (isset($element['text'])) {
        return $element['text'];
      }

      if (isset($element['url'])) {
        return "<a href=\"{$element['url']}\" target=\"blank\">{$element['url']}</a>";
      }
    }, $text_elements);

    $merged_text = implode('', $text_elements);

    return $merged_text;
  }
  private function decorate_message($message_text) {
    // Newlines to brs
    $message_text = nl2br($message_text);

    return $message_text;
  }

  private function get_files_text($files) {
    $files_text = '';
    $images = [];
    $image_mime_types = [
      'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
    ];

    foreach ($files as $file) {
      if (in_array($file['mimetype'], $image_mime_types)) {
        $images[] = $file;
      }
    }

    $image_urls = $this->get_images_urls($images);
    $images_text = '';
    foreach ($image_urls as $image_url) {
      $images_text .= '<img src="' . $image_url . '">';
    }
    $files_text .= $images_text;

    if ($files_text) {
      $files_text = "<br>{$files_text}";
    }

    return $files_text;
  }

  private function get_images_urls($images) {
    $image_urls = [];

    foreach ($images as $image) {
      $image_url = $this->fetch_image($image);

      if ($image_url) {
        $image_urls[] = $image_url;
      }
    }

    return $image_urls;
  }

  private function fetch_image($image) {
    if (isset($image['filetype']) === false) {
      return false;
    }

    $args = array(
      'headers' => [
        'Authorization' => "Bearer {$this->slack_api_access_key}",
      ],
    );

    $response = wp_remote_get($image['url_private'], $args);

    if (is_wp_error($response)) {
      return false;
    }

    $filename_uuid = Helpers::get_uuid();
    $filename = "{$filename_uuid}.{$image['filetype']}";
    $new_file_path = WP_PLUGIN_DIR . "/liveblog-with-slack/files/{$filename}";
    file_put_contents($new_file_path, wp_remote_retrieve_body($response));

    return plugins_url("liveblog-with-slack/files/{$filename}");
  }
}
