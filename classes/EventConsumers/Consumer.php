<?php

namespace SlackLiveblog\EventConsumers;

abstract class Consumer {
  protected array $data;
  protected string $channel_id;

  abstract public function consume(): array;

  public function __construct(array $data, string $channel_id) {
    $this->data = $data;
    $this->channel_id = $channel_id;
  }
}
