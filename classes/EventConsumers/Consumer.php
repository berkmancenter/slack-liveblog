<?php

namespace SlackLiveblog\EventConsumers;

abstract class Consumer {
  protected array $data;
  protected string $slack_channel_id;

  abstract public function consume(): array;

  public function __construct(array $data, string $slack_channel_id) {
    $this->data = $data;
    $this->slack_channel_id = $slack_channel_id;
  }

  protected function get_message_from_blocks($blocks) {
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

  protected function decorate_message($message_text) {
    // Newlines to brs
    $message_text = nl2br($message_text);

    return $message_text;
  }
}
