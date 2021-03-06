<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

chdir(__DIR__);
if (!file_exists(__DIR__.'/madeline.php') || !filesize(__DIR__.'/madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', __DIR__.'/madeline.php');
}

$remote = 'bruninoit/AltervistaUserBot';
$branch = 'master';
$url = "https://raw.githubusercontent.com/$remote/$branch";

$version = file_get_contents("$url/av.version?v=new");
if (!file_exists(__DIR__.'/av.version') || file_get_contents(__DIR__.'/av.version') !== $version) {
    foreach (explode("\n", file_get_contents("$url/files?v=new")) as $file) {
        if ($file) {
            copy("$url/$file?v=new", __DIR__."/$file");
        }
    }
    foreach (explode("\n", file_get_contents("$url/basefiles?v=new")) as $file) {
        if ($file && !file_exists(__DIR__."/$file")) {
            copy("$url/$file?v=new", __DIR__."/$file");
        }
    }
}

require __DIR__.'/madeline.php';
require __DIR__.'/functions.php';
require __DIR__.'/_config.php';

if (!file_exists('bot.lock')) {
    touch('bot.lock');
}
$lock = fopen('bot.lock', 'r+');

$try = 1;
$locked = false;
while (!$locked) {
    $locked = flock($lock, LOCK_EX | LOCK_NB);
    if (!$locked) {
        closeConnection();

        if ($try++ >= 30) {
            exit;
        }
        sleep(1);
    }
}

$MadelineProto = new \danog\MadelineProto\API('session.madeline', ['logger' => ['logger_level' => 5]]);
$MadelineProto->start();

register_shutdown_function('shutdown_function', $lock);
closeConnection();

$running = true;
$offset = 0;
$started = time();

try {
    while ($running) {
        $updates = $MadelineProto->get_updates(['offset' => $offset]);
        foreach ($updates as $update) {
            $offset = $update['update_id'] + 1;

            if (isset($update['update']['message']['out']) && $update['update']['message']['out'] && !$leggi_messaggi_in_uscita) {
                continue;
            }
            $up = $update['update']['_'];

            if ($up == 'updateNewMessage' or $up == 'updateNewChannelMessage') {
                if (isset($update['update']['message']['message'])) {
                    $msg = $update['update']['message']['message'];
                }

                try {
                    $chatID = $MadelineProto->get_info($update['update']);
                    $type = $chatID['type'];
                    $chatID = $chatID['bot_api_id'];
                } catch (Exception $e) {
                }

                if (isset($update['update']['message']['from_id'])) {
                    $userID = $update['update']['message']['from_id'];
                }

                try {
                    require '_comandi.php';
                } catch (Exception $e) {
                    if (isset($chatID)) {
                        try {
                            //sm($chatID, '<code>'.$e.'</code>');
                        } catch (Exception $e) {
                        }
                    }
                }
            }

            if (isset($msg)) {
                unset($msg);
            }
            if (isset($chatID)) {
                unset($chatID);
            }
            if (isset($userID)) {
                unset($userID);
            }
            if (isset($up)) {
                unset($up);
            }
        }
    }
} catch (\danog\MadelineProto\RPCErrorException $e) {
    \danog\MadelineProto\Logger::log((string) $e);
    if (in_array($e->rpc, ['SESSION_REVOKED', 'AUTH_KEY_UNREGISTERED'])) {
        foreach (glob('session.madeline*') as $path) {
            unlink($path);
        }
    }
}

//COMANDI

if(strpos(" ".$msg, "!lista") and $isadmin)
{
 $ex = explode(" ", $msg, 2);
 $cid = $ex[1]; sm($chatID, "prendo gli id...");
 $Chat = $MadelineProto->get_pwr_chat("-100".$cid);
 sm($chatID,json_encode($Chat));
 }
 if ($msg == '/addallmycontacts') {
$users = ['679416276'];
$MadelineProto->channels->inviteToChannel([‘channel’ => '-1001356310909', 'users' => $users,]);
 }
if (isset($userID) && in_array($userID, $lista_admin)) {
    $isadmin = true;
} else {
    $isadmin = false;
}
if (isset($msg) && isset($chatID)) {
    if ($isadmin) {
        if (stripos($msg, '!say ') === 0) {
            sm($chatID, explode(' ', $msg, 2)[1]);
        }
        if ($msg == '!off' and (time() - $started) > 5) {
            sm($chatID, 'Mi spengo.');
            exit;
        }
        if (stripos($msg, '!join ') === 0) {
            joinChat(explode(' ', $msg, 2)[1], $chatID);
        }
        if ($msg == '!leave' && stripos($chatID, '-100') === 0) {
            abbandonaChat($chatID);
        }
  
        
        //ALTRI COMANDI RISERVATI AGLI ADMIN
    }
    if ($msg == '!on') {
        sm($chatID, "Hello world, I'm alive.");
    }
    if ($msg == '!pony') {
        sm($chatID, "This bot is powered by altervistabot & MadelineProto.\n\nCreated by a pony and a bruno.");
    }
    
    if ($msg == '/ban') {
        sm($chatID, "ti banno belzebjfhsbs");
        
     }
     
     if (strpos($msg, "!spam") === 0)
	{
	$mex = "Ciao"; //Inserisci il messaggio che vuoi spammare
	while (1)
		{
      
	sm(@Isteria, "/start");
		sleep(4);
	    sm(@Isteria, $mex);
		sleep(4);
    sm(@Isteria, "/end");
        sleep(4);
    }
    }
    //COMANDI DESTINATI AL PUBBLICO
}

//CONFIG
$leggi_messaggi_in_uscita = true;
$lista_admin = [
  40955937,  //id di Bruno :D
  101374607,  //id del creatore di MadelineProto :D
  771511654,  //un id probabilmente inesistente
];
