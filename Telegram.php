<?php

class telegramBot
{
  const BASE_URL = 'https://api.telegram.org/bot';

  protected $token;

  public function __construct($token)
  {
    $this->token = $token;
    $this->baseURL = self::BASE_URL . $this->token . DIRECTORY_SEPARATOR;
  }

  public function pollUpdates($offset, $timeout = 60, $limit = 100)
  {
    $params = array(
      'offset' => $offset
    );

    if ($timeout != 60)
      $params['timeout'] = $timeout;
    if ($limit != 100)
      $params['limit'] = $limit;

    return $this->sendRequest('getUpdates', $params);
  }

  public function sendMessage($chatID, $text)
  {
    $params = array(
      'chat_id' => $chatID,
      'text'    => $text
    );

    return $this->sendRequest('sendMessage', $params);
  }

  public function forwardMessage($chatID, $fromChatID, $messageID)
  {
    $params = array(
      'chat_id'      => $chatID,
      'from_chat_id' => $fromChatID,
      'message_id'   => $messageID
    );

    return $this->sendRequest('forwardMessage', $params);
  }

  public function sendRequest($method, $params)
  {
    return json_decode(file_get_contents($this->baseURL . $method . '?' . http_build_query($params)), true);
  }

}
