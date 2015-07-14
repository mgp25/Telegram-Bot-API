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

  /**
   * A simple method for testing your bot's auth token.
   * Returns basic information about the bot in form of a User object.
   *
   * @link https://core.telegram.org/bots/api#getme
   *
   * @return Array
   */
  public function getMe()
  {
    return $this->sendRequest('getMe', null);
  }


  /**
   * Use this method to receive incoming updates using long polling.
   *
   * @link https://core.telegram.org/bots/api#getupdates
   *
   * @param int $offset
   * @param int $limit
   * @param int $timeout
   *
   * @return Array
   */
  public function pollUpdates($offset, $timeout = null, $limit = null)
  {
    $params = compact('offset', 'limit', 'timeout');

    return $this->sendRequest('getUpdates', $params);
  }

  /**
   * Send text messages.
   *
   * @link https://core.telegram.org/bots/api#sendmessage
   *
   * @param int            $chat_id
   * @param string         $text
   * @param bool           $disable_web_page_preview
   * @param int            $reply_to_message_id
   * @param KeyboardMarkup $reply_markup
   *
   * @return Array
   */
  public function sendMessage($chat_id, $text, $disable_web_page_preview = false, $reply_to_message_id = null, $reply_markup = null)
  {
    $params = compact('chat_id', 'text', 'disable_web_page_preview', 'reply_to_message_id', 'reply_markup');

    return $this->sendRequest('sendMessage', $params);
  }

  /**
   * Forward messages of any kind.
   *
   * @link https://core.telegram.org/bots/api#forwardmessage
   *
   * @param int $chat_id
   * @param int $from_chat_id
   * @param int $message_id
   *
   * @return Array
   */
  public function forwardMessage($chat_id, $from_chat_id, $from_chat_id)
  {
    $params = compact('chat_id', 'from_chat_id', 'from_chat_id');

    return $this->sendRequest('forwardMessage', $params);
  }

  /**
   * Send Photos.
   *
   * @link https://core.telegram.org/bots/api#sendphoto
   *
   * @param int            $chat_id
   * @param string         $photo
   * @param string         $caption
   * @param int            $reply_to_message_id
   * @param KeyboardMarkup $reply_markup
   *
   * @return Array
   */
  public function sendPhoto($chat_id, $photo, $caption = null, $reply_to_message_id = null, $replyMarkup = null)
  {
    $data = compact('chat_id', 'photo', 'caption', 'reply_to_message_id', 'reply_markup');

    if (((!is_dir($photo)) && (filter_var($photo, FILTER_VALIDATE_URL) === FALSE)))
      return $this->sendRequest('sendPhoto', $data);

    return $this->uploadFile('sendPhoto', $data);
  }

  private function sendRequest($method, $params)
  {
    if ($method == 'getMe')
      return json_decode(file_get_contents($this->baseURL . $method), true);

    return json_decode(file_get_contents($this->baseURL . $method . '?' . http_build_query($params)), true);
  }

  private function uploadFile($method, $data)
  {
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . mt_rand(0, 9999);
    if (filter_var($data['photo'], FILTER_VALIDATE_URL))
    {
      $url = true;
      file_put_contents($file, file_get_contents($data['photo']));
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime_type = finfo_file($finfo, $file);
      switch($mime_type)
  		{
  			case "image/jpeg":
  				$ext = ".jpg";
  				break;
  			case "image/png":
  				$ext = ".png";
  				break;
        case "image/gif":
          $ext = ".gif";
          break;
      }
      $newFile = $file . $ext;
      rename($file, $newFile);
      $data['photo'] = new CurlFile($newFile, $mime_type, $newFile);
    }
    else
    {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime_type = finfo_file($finfo, $data['photo']);
      $data['photo'] = new CurlFile($data['photo'], $mime_type, $data['photo']);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->baseURL . $method);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = json_decode(curl_exec($ch), true);

    if ($url)
      unlink($newFile);

    return $response;
  }

}
