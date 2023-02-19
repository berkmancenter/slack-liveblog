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
}
