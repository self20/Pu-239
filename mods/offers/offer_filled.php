<?php

global $CURUSER, $site_config, $cache;

$torrentid = (isset($_POST['torrentid']) ? (int) $_POST['torrentid'] : 0);
if ($torrentid < 1) {
    stderr('Error', 'That ID looks funky!');
}
$res = sql_query("SELECT id FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if (!$arr) {
    stderr('Error', "No torrent with that ID $torrentid");
}
$res = sql_query("SELECT users.username, offers.userid, offers.torrentid, offers.offer FROM offers inner join users on offers.userid = users.id where offers.id = $id") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
$msg = 'Your Offer, [b]' . htmlspecialchars($arr['request']) . '[/b] has been accepted by [b]' . $CURUSER['username'] . '[/b]. You can download the offer from [b][url=details.php?id=' . $torrentid . ']' . $site_config['baseurl'] . '/details.php?id=' . $torrentid . '[/url][/b].  Please do not forget to leave thanks where due.  

If for some reason this is not what you offered, please reset your offer so someone else can fill it by following [b][url=' . $site_config['baseurl'] . "/viewoffers.php?id=$id&offer_reset]this[/url][/b] link.  Do [b]NOT[/b] follow this link unless you are sure that this does not match your offer.";
sql_query('UPDATE offers SET torrentid = ' . $torrentid . ", acceptedby = $CURUSER[id] WHERE id = $id")                                                                             or sqlerr(__FILE__, __LINE__);
sql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject, location) VALUES(0, 0, $arr[userid], " . TIME_NOW . ', ' . sqlesc($msg) . ", 'Request Filled', 1)") or sqlerr(__FILE__, __LINE__);
$cache->increment('inbox_' . $arr['userid']);

if ($site_config['karma'] && isset($CURUSER['seedbonus'])) {
    sql_query('UPDATE users SET seedbonus = seedbonus+' . $site_config['offer_comment_bonus'] . " WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
}
$res         = mysqli_query($GLOBALS['___mysqli_ston'], "SELECT `userid` FROM `voted_offers` WHERE `offerid` = $id AND userid != $arr[userid]") or sqlerr(__FILE__, __LINE__);
$msgs_buffer = [];
if (mysqli_num_rows($res) > 0) {
    $pn_subject = sqlesc('offer ' . $arr['offer'] . ' was just uploaded');
    $pn_msg     = sqlesc('The Offer you voted for [b]' . $arr['offer'] . '[/b] has been accepted by [b]' . $CURUSER['username'] . '[/b]. You can download your offer from 
    [b][url=details.php?id=' . $torrentid . ']' . $site_config['baseurl'] . '/details.php?id=' . $torrentid . '[/url][/b].
      Please do not forget to leave thanks where due.');
    while ($row = mysqli_fetch_assoc($res)) {
        $msgs_buffer[] = '(0, ' . $row['userid'] . ', ' . TIME_NOW . ', ' . $pn_msg . ', ' . $pn_subject . ')';
        $cache->increment('inbox_' . $row['userid']);
    }
    $pn_count = count($msgs_buffer);
    if ($pn_count > 0) {
        sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
    }

    unset($msgs_buffer);
}
((mysqli_free_result($res) || (is_object($res) && ('mysqli_result' == get_class($res)))) ? true : false);
$HTMLOUT .= "<table class='main' width='750px' >" . "<tr><td class='embedded'>\n";
$HTMLOUT .= "<h1>Success!</h1>
<table>
<tr><td>Offer $id (" . htmlspecialchars($arr['offer']) . ") successfully accepted with <a class='altlink' href='details.php?id=" . $torrentid . "'>" . $site_config['baseurl'] . '/details.php?id=' . $torrentid . "</a>.  
<br><br>User <a class='altlink' href='userdetails.php?id=$arr[userid]'><b>$arr[username]</b></a> automatically PMd.  <br><br>
If you have made a mistake in filling in the URL or have realised that your torrent does not actually satisfy this offer
, please reset the offer so someone else can fill it by clicking <a class='altlink' href='viewoffers.php?id=$id&amp;offer_reset'>HERE</a> 
<br><br>Do <b>NOT</b> follow this link unless you are sure there is a problem.<br><br>
<a class='altlink' href='viewoffers.php'>View all offers</a>
</td></tr></table>";
$HTMLOUT .= "</td></tr></table>\n";
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Offer Filled') . $HTMLOUT . stdfoot();
