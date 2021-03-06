<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
global $site_config, $session;

dbconn();

$numImages = '';
// -------------------- EDIT THESE ----------------- //
$images = [
    'house'        => "{$site_config['pic_baseurl']}captchaImages/01.png",
    'key'          => "{$site_config['pic_baseurl']}captchaImages/02.png",
    'flag'         => "{$site_config['pic_baseurl']}captchaImages/03.png",
    'clock'        => "{$site_config['pic_baseurl']}captchaImages/04.png",
    'bug'          => "{$site_config['pic_baseurl']}captchaImages/05.png",
    'pen'          => "{$site_config['pic_baseurl']}captchaImages/06.png",
    'light bulb'   => "{$site_config['pic_baseurl']}captchaImages/07.png",
    'musical note' => "{$site_config['pic_baseurl']}captchaImages/08.png",
    'heart'        => "{$site_config['pic_baseurl']}captchaImages/09.png",
    'world'        => "{$site_config['pic_baseurl']}captchaImages/10.png",
];
// ------------------- STOP EDITING ---------------- //
$session->set('simpleCaptchaAnswer', null);
$resp = [];
header('Content-Type: application/json');
if (!isset($images) || !is_array($images) || sizeof($images) < 3) {
    $resp['error'] = "There aren't enough images!";
    echo json_encode($resp);
    exit;
}
if (isset($_POST['numImages']) && strlen($_POST['numImages']) > 0) {
    $numImages = intval($_POST['numImages']);
} elseif (isset($_GET['numImages']) && strlen($_GET['numImages']) > 0) {
    $numImages = intval($_GET['numImages']);
}
$numImages = ($numImages > 0) ? $numImages : 5;
$size      = sizeof($images);
$num       = min([
               $size,
               $numImages,
           ]);
$keys = array_keys($images);
$used = [];
for ($i = 0; $i < $num; ++$i) {
    $r = random_int(0, $size - 1);
    while (false !== array_search($keys[$r], $used)) {
        $r = random_int(0, $size - 1);
    }
    array_push($used, $keys[$r]);
}
$selectText = $used[random_int(0, $num - 1)];
$session->set('simpleCaptchaAnswer', hash('sha512', $selectText . $site_config['site']['salt']));
$resp['text']   = $selectText;
$resp['images'] = [];
shuffle($used);
for ($i = 0; $i < sizeof($used); ++$i) {
    array_push($resp['images'], [
        'hash' => hash('sha512', $used[$i] . $site_config['site']['salt']),
        'file' => $images[$used[$i]],
    ]);
}
echo json_encode($resp);
exit;
