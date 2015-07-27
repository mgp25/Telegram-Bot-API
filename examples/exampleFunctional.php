<?php

require '../src/Telegram.php';

//                                       //
///////// CONFIG YOUR BOT'S TOKEN /////////
$tg = new telegramBot($token);

$chat_id      = null;
$guessed      = false;
$sendQuestion = false;


// Custom keyboard
$customKeyboard = [
    ['7', '8', '9'],
    ['4', '5', '6'],
    ['1', '2', '3'],
         ['0']
];
$reply_markup = $tg->replyKeyboardMarkup($customKeyboard, true, true);

do
{
  // Get updates the bot has received
  // Offset to confirm previous updates
  $updates = $tg->pollUpdates($offset);
  if ($updates['ok'])
  {
    foreach($updates['result'] as $data)
    {
        if (is_null($chat_id))
          $chat_id = $data['message']['chat']['id'];

        if (!$sendQuestion)
        {
          // sends an action 'typing'
          $tg->sendChatAction($chat_id, 'typing');

          // send message with a custom reply markup
          $tg->sendMessage($chat_id, 'Guess the number', false, null, $reply_markup);
          $sendQuestion = true;
        }

        if (($data['message']['text']) == 5)
        {
          $tg->sendChatAction($chat_id, 'typing');
          $tg->sendMessage($chat_id, 'You did it! :)');

          $tg->sendChatAction($chat_id, 'upload_photo');
          $tg->sendPhoto($chat_id, 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/718smiley.png/220px-718smiley.png');
          $guessed = true;
        }
        else
          $tg->sendMessage($chat_id, 'Wrong number :/ try again', false, null, $reply_markup);
    }
    $offset = $updates['result'][count($updates['result']) - 1]['update_id'] + 1;
  }
}
while(!$guessed);
$offset  = $updates['result'][count($updates['result']) - 1]['update_id'] + 1;
$updates = $tg->pollUpdates($offset);
