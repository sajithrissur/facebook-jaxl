<?php

define('FACEBOOKBOT_PHP', TRUE);


// Include XMPP Engine 'JAXL'
// Path might be alter in time.
// require_once ROOT_DIR.'/JAXL/jaxl.php';
require_once 'jaxl.php';
require_once 'xmpp/xmpp_msg.php';


function sendMessage($client)
{

    $msg = new XMPPMsg(array('to'=>'userid@chat.facebook.com'), 'test message');
    $client->send($msg);

    _info("test messages sent");
}


$user = 'userid'; 
// $user = $argv[1];    // User name or facebook id
$jidSuffix = '@chat.facebook.com';  // Facebook chat account suffix
$appKey = 'appIDhere';    // Taken from developer.facebook.com
// $appKey = $argv[2];  // Facebook app token
$accessToken = 'access token from facebook developer page';  // Facebook user token - tried both app token tool on developer.facebook.com and token provided after user login both posses xmpp-login permission
// $accessToken = $argv[3];

$client = new JAXL( array(  // (required) credentials
                            'jid' => $user.$jidSuffix,
                            'fb_app_key' => $appKey,
                            'fb_access_token' => $accessToken,

                            // force tls (facebook require this now)
                            'force_tls' => true,
                            // (required) force facebook oauth
                            'auth_type' => 'X-FACEBOOK-PLATFORM',

                            // (optional)
                            //'resource' => 'resource',

                            'log_level' => JAXL_INFO,
                            'priv_dir'  => '.'
                        ));

$client->add_cb('on_auth_success', function()
{
    global $client;
    _info("got on_auth_success cb, jid ".$client->full_jid->to_string());
    $client->set_status("available!", "dnd", 10);

    // Here is the part where i tried to send message. In addition, i tried to call this function wherever i can on the code.
    sendMessage($client);
});

$client->add_cb('on_auth_failure', function($reason)
{
    global $client;
    $client->send_end_stream();
    _info("got on_auth_failure cb with reason $reason");

});

$client->add_cb('on_chat_message', function($stanza)
{
    global $client;

    // echo back incoming message stanza
    $stanza->to = $stanza->from;
    $stanza->from = $client->full_jid->to_string();
    $client->send($stanza);

    _info("echo message sent");

    sendMessage($client);
});

$client->add_cb('on_disconnect', function()
{
    _info("got on_disconnect cb");
});

$client->start();

echo "done\n";

?>