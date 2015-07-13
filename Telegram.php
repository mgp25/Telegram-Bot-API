<?php

class telegramBot
{
  const BASE_URL = 'https://api.telegram.org/bot';

  protected $token;

  public function __construct($token)
  {
    $this->token = $token;
    $this->baseURL = BASE_URL . $this->token . DIRECTORY_SEPARATOR;
  }

  function pollUpdates($offset, $timeout = 60, $limit = 100)
  {
    return json_decode(file_get_contents($this->baseURL . "getUpdates?offset=$offset&timeout=$timeout&limit=$limit"), true);
  }

  function sendMessage($chatID, $text)
  {
    return json_decode(file_get_contents($this->baseURL . "sendMessage?chat_id=$chatID&text=" . urlencode($text)), true);
  }

  function forwardMessage($chatID, $fromChatID, $messageID)
  {
    return json_decode(file_get_contents($this->baseURL . "forwardMessage?chat_id=$chatID&from_chat_id=$fromChatID&message_id=$messageID"), true);
  }

}
