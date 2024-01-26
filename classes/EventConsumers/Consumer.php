<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\Helpers;
use SlackLiveblog\FrontCore;
use SlackLiveblog\Db;

abstract class Consumer {
  protected array $data;
  protected string $slack_channel_id;
  protected string $slack_api_access_key;

  abstract public function consume();

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
      $files_text = $this->get_files_text($incoming_message['files']);
      if (empty($message_text) === false && empty($files_text) === false) {
        $message_text .= '<br>';
      }
      $message_text .= $files_text;
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
    $urls = [];

    $text_elements = array_map(function ($element) use (&$urls) {
      return $this->format_element_text($element, $urls);
    }, $text_elements);

    $merged_text = implode('', $text_elements);
    $merged_text .= $this->get_embedded_content($urls);

    return $merged_text;
  }

  private function format_element_text($element, &$urls) {
    if (isset($element['url'])) {
      $urls[] = $element['url'];
    }

    if (isset($element['text']) && isset($element['url'])) {
      return '<a href="' . $element['url'] . '" target="blank">' . $element['text'] . '</a>';
    }

    if (isset($element['text'])) {
      return $element['text'];
    }

    if (isset($element['url'])) {
      return '<a href="' . $element['url'] . '" target="blank">' . $element['url'] . '</a>';
    }

    if (isset($element['type']) && $element['type'] === 'emoji') {
      return '&#x' . $element['unicode'];
    }

    return '';
  }

  private function decorate_message($message_text) {
    // Newlines to brs
    $message_text = nl2br($message_text);

    return $message_text;
  }

  private function get_embedded_content($urls) {
    $embedded_text = $this->get_social_media_embedded_elements($urls);
    $inline_images = $this->get_inline_images($urls);

    if (empty($embedded_text) === false) {
      return '<br>' . $embedded_text;
    }

    if (empty($inline_images) === false) {
      return '<br>' . $inline_images;
    }

    return '';
  }

  private function get_files_text($files) {
    $images = [];
    $image_mime_types = [
      'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp', 'image/gif',
    ];

    foreach ($files as $file) {
      if (in_array($file['mimetype'], $image_mime_types)) {
        $images[] = $file;
      }
    }

    $urls = $this->get_images_urls($images);
    $files_text = $this->create_img_elements($urls);

    return $files_text;
  }

  private function get_images_urls($images) {
    return array_filter(array_map(function ($image) {
      return $this->fetch_image_from_slack($image);
    }, $images));
  }

  private function fetch_image_from_slack($image) {
    if (isset($image['filetype']) === false) {
      return false;
    }

    $args = array(
      'headers' => [
        'Authorization' => "Bearer {$this->slack_api_access_key}",
      ],
    );

    $filepath = $this->save_image_locally($image['url_private'], $image['filetype'], $args);

    return $filepath;
  }

  private function get_social_media_embedded_elements($urls) {
    $embedded_text = '';

    foreach ($urls as $url) {
      $embed_code = $this->get_embed_code($url, [
        'twitter' => 'https://publish.twitter.com/oembed?omit_script=true&url=',
        'mastodon' => 'https://mastodon.social/api/oembed?url=',
        'youtube' => 'https://youtube.com/oembed?url=',
      ]);

      if ($embed_code) {
        $embedded_text .= "<div class=\"slack-liveblog-messages-embedded-items-item\">{$embed_code}</div>";
      }
    }

    if ($embedded_text) {
      $embedded_text = "<div class=\"slack-liveblog-messages-embedded-items\">{$embedded_text}</div>";
    }

    return $embedded_text;
  }

  private function get_embed_code($link, $embed_endpoints) {
    $embedded_html = '';

    foreach ($embed_endpoints as $platform => $endpoint) {
      $response = wp_remote_get("{$endpoint}{$link}&maxwidth=800");
      $response_body = json_decode($response['body'], true);

      if (isset($response_body['html'])) {
        $embedded_html.= $response_body['html'];
      }
    }

    return $embedded_html;
  }

  private function get_inline_images($urls) {
    $image_urls = array_filter(array_map(function ($url) {
      $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

      if ($this->is_image_url($extension)) {
        return $this->save_image_locally($url, $extension);
      }

      return null;
    }, $urls));

    $inline_images = $this->create_img_elements($image_urls);

    return $inline_images;
  }

  private function is_image_url($extension) {
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    return in_array($extension, $image_extensions);
  }

  private function save_image_locally($url, $extension, $args = []) {
    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
      return false;
    }

    $image = wp_remote_retrieve_body($response);

    $filename_uuid = Helpers::get_uuid();
    $filename = "{$filename_uuid}.{$extension}";
    $new_file_path = WP_PLUGIN_DIR . "/liveblog-with-slack/files/{$filename}";
    file_put_contents($new_file_path, $image);

    return plugins_url("liveblog-with-slack/files/{$filename}");
  }

  private function create_img_elements($image_urls) {
    return implode('', array_map(function ($image_url) {
      return '<img src="' . $image_url . '">';
    }, $image_urls));
  }

  protected function format_unix_time($unix_timestamp) {
    return "SQL_FUNC:DATE_FORMAT(FROM_UNIXTIME({$unix_timestamp}), '%Y-%m-%d %H:%i:%s.%f')";
  }
}
