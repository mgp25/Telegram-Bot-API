<?php

require '../src/Telegram.php';

$token = ''; // HERE YOUR TOKEN
// You will need a file called quotes (without extension)
// from there it will take the quotes and send them to your subscribers :)

$tg = new telegramBot($token);

$offset = 0;

  $response = $tg->pollUpdates($offset, 60);
  $response = json_decode($response, true);

  if ($response['ok'])
  {
    foreach($response['result'] as $data)
    {
        $chatID = $data['message']['chat']['id'];
        switch ($data['message']['text'])
        {
          case '/start':
            addContact($chatID);
            break;
          case '/remove':
            deleteContact($chatID);
            break;
        }
    }
    $offset = $response['result'][count($response['result']) - 1]['update_id'] + 1;
  }
  $chats = allChats();
  $quotes = readQuotes();

  $i = 0;
  for ($i; $i < count($chats); $i++)
  {
    $tg->sendMessage($chats[$i]['chat_id'], $quotes[$chats[$i]['init']]);
    updateInit($chats[$i]['chat_id']);

    if ($chats[$i]['init'] == count($quotes) - 1)
      resetInit($chats[$i]['chat_id']);
  }


function addContact($chatID)
{
  $contactsDB = __DIR__ . DIRECTORY_SEPARATOR . 'contacts.db';
  if (!file_exists($contactsDB))
  {
    $db = new \PDO("sqlite:" . $contactsDB, null, null, array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $db->exec('CREATE TABLE contacts (`chat_id` TEXT, `init` INT)');
  }
  else {
    $db = new \PDO("sqlite:" . $contactsDB, null, null, array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  }

  $sql = 'SELECT chat_id FROM contacts WHERE chat_id = :chat_id';
  $query = $db->prepare($sql);
  $query->execute(
      array(
          ':chat_id' => $chatID
      )
  );
  $chat = $query->fetchAll();
  if ($chat[0]['chat_id'] == null)
  {
    $sql = 'INSERT INTO contacts (`chat_id`, `init`) VALUES (:chat_id, :init)';
    $query = $db->prepare($sql);

    $query->execute(
      array(
        ':chat_id' => $chatID,
        ':init' => 0
      )
    );
  }
}

function deleteContact($chatID)
{
  $contactsDB = __DIR__ . DIRECTORY_SEPARATOR . 'contacts.db';
  $cDB = new \PDO("sqlite:" . $contactsDB, null, null, array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  $sql = "DELETE FROM contacts WHERE chat_id = :chat_id";
  $query = $cDB->prepare($sql);
  $query->execute(array(':chat_id' => $chatID));
}

function allChats()
{
  $contactsDB = __DIR__ . DIRECTORY_SEPARATOR . 'contacts.db';
  $db = new \PDO("sqlite:" . $contactsDB, null, null, array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

  $sql = 'SELECT chat_id, init FROM contacts';
  $query = $db->prepare($sql);
  $query->execute();

  $chats = $query->fetchAll();

  return $chats;
}

function readQuotes()
{
  $quotesFile = __DIR__ . DIRECTORY_SEPARATOR . 'quotes';
  $quotes = array_filter(explode("\n", file_get_contents($quotesFile)));

  return $quotes;
}

function updateInit($chatID)
{
  $contactsDB = __DIR__ . DIRECTORY_SEPARATOR . 'contacts.db';
  $db = new \PDO("sqlite:" . $contactsDB, null, null, array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

  $sql = "UPDATE contacts SET init = init + 1 WHERE chat_id = :chat_id";
  $query = $db->prepare($sql);
  $query->execute(
    array(
      ':chat_id' => $chatID
    )
  );
}

function resetInit($chatID)
{
  $contactsDB = __DIR__ . DIRECTORY_SEPARATOR . 'contacts.db';
  $db = new \PDO("sqlite:" . $contactsDB, null, null, array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

  $sql = "UPDATE contacts SET init = 0 WHERE chat_id = :chat_id";
  $query = $db->prepare($sql);
  $query->execute(
    array(
      ':chat_id' => $chatID
    )
  );
}
