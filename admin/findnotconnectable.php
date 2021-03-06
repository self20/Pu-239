<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'torrenttable_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $lang;

$lang    = array_merge($lang, load_language('non_con'));
$HTMLOUT = '';
if (isset($_GET['action1']) && 'list' == htmlsafechars($_GET['action1'])) {
    $res2 = sql_query("SELECT userid, seeder, torrent, agent FROM peers WHERE connectable='no' ORDER BY userid DESC") or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= "<h3><a href='staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable&amp;action1=sendpm'>{$lang['non_con_sendall']}</a></h3>
    <h3><a href='staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable'>{$lang['non_con_view']}</a></h3>
    <h1>{$lang['non_con_peers']}</h1>
    {$lang['non_con_this']}<br><p><span class='has-text-danger'>*</span> {$lang['non_con_means']}<br>";
    $result = sql_query("SELECT DISTINCT userid FROM peers WHERE connectable = 'no'");
    $count  = mysqli_num_rows($result);
    $HTMLOUT .= "$count {$lang['non_con_unique']}</p>";
    @((mysqli_free_result($result) || (is_object($result) && ('mysqli_result' == get_class($result)))) ? true : false);
    if (0 == mysqli_num_rows($res2)) {
        $HTMLOUT .= "<p><b>{$lang['non_con_all']}</b></p>\n";
    } else {
        $HTMLOUT .= "<table >\n";
        $HTMLOUT .= "<tr><td class='colhead'>{$lang['non_con_name']}</td><td class='colhead'>{$lang['non_con_tor']}</td><td class='colhead'>{$lang['non_con_client']}</td></tr>\n";
        while ($arr2 = mysqli_fetch_assoc($res2)) {
            $r2 = sql_query('SELECT username FROM users WHERE id=' . sqlesc($arr2['userid'])) or sqlerr(__FILE__, __LINE__);
            $a2 = mysqli_fetch_assoc($r2);
            $HTMLOUT .= "<tr><td><a href='userdetails.php?id=" . (int) $arr2['userid'] . "'>" . htmlsafechars($a2['username']) . "</a></td><td><a href='details.php?id=" . (int) $arr2['torrent'] . "&amp;dllist=1#seeders'>" . (int) $arr2['torrent'] . '</a>';
            if ('yes' == $arr2['seeder']) {
                $HTMLOUT .= "<span class='has-text-danger'>*</span>";
            }
            $HTMLOUT .= '</td><td>' . htmlsafechars($arr2['agent']) . "</td></tr>\n";
        }
        $HTMLOUT .= "</table>\n";
    }
}
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $dt  = TIME_NOW;
    $msg = htmlsafechars($_POST['msg']);
    if (!$msg) {
        stderr('Error', 'Please Type In Some Text');
    }
    $query = sql_query("SELECT DISTINCT userid FROM peers WHERE connectable='no'");
    while ($dat = mysqli_fetch_assoc($query)) {
        $subject = 'Connectability';
        sql_query('INSERT INTO messages (sender, receiver, added, msg, subject) VALUES (0, ' . sqlesc($dat['userid']) . ', ' . sqlesc(TIME_NOW) . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')') or sqlerr(__FILE__, __LINE__);
    }
    sql_query('INSERT INTO notconnectablepmlog (user, date) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($dt) . ')') or sqlerr(__FILE__, __LINE__);
    header('Refresh: 0; url=staffpanel.php?tool=findnotconnectable');
}
if (isset($_GET['action1']) && 'sendpm' == htmlsafechars($_GET['action1'])) {
    $HTMLOUT .= "<table class='main' width='750' ><tr><td class='embedded'>
<div>
<h1>{$lang['non_con_mass']}</h1>
<form method='post' action='staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable'>";
    if (isset($_GET['returnto']) || isset($_SERVER['HTTP_REFERER'])) {
        $HTMLOUT .= "<input type='hidden' name='returnto' value='" . (isset($_GET['returnto']) ? htmlsafechars($_GET['returnto']) : htmlsafechars($_SERVER['HTTP_REFERER'])) . "' />";
    }
    $receiver = '';
    // default message
    $body = "{$lang['non_con_body']}";
    $HTMLOUT .= "<table>
<tr>
<td>{$lang['non_con_sendall']}<br>
<table style='border: 0;' width='100%'>
<tr>
<td style='border: 0;'>&#160;</td>
<td style='border: 0;'>&#160;</td>
</tr>
</table>
</td>
</tr>
<tr><td><textarea name='msg' cols='120' rows='15'>$body</textarea></td></tr>

<tr><td colspan='2'><input type='submit' value='Send' class='button is-small'/></td></tr>
</table>
<input type='hidden' name='receiver' value='$receiver'/>
</form>
</div></td></tr></table>
<br>
NOTE: No HTML Code Allowed. (NO HTML)
";
}
if ('' == isset($_GET['action1'])) {
    $getlog = sql_query('SELECT * FROM `notconnectablepmlog` ORDER BY date DESC LIMIT 20') or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= "<h1>{$lang['non_con_uncon']}</h1>
    <h3><a href='staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable&amp;action1=sendpm'>{$lang['non_con_sendall']}</a></h3>
    <h3><a href='staffpanel.php?tool=findnotconnectable&amp;action=findnotconnectable&amp;action1=list'>{$lang['non_con_list']}</a></h3><p>
    <br>{$lang['non_con_please1']}<br></p>
    <table >\n
    <tr><td class='colhead'>{$lang['non_con_by']}</td>
    <td class='colhead'>{$lang['non_con_date']}</td><td class='colhead'>{$lang['non_con_elapsed']}</td></tr>";
    while ($arr2 = mysqli_fetch_assoc($getlog)) {
        $r2      = sql_query('SELECT username FROM users WHERE id=' . sqlesc($arr2['user'])) or sqlerr(__FILE__, __LINE__);
        $a2      = mysqli_fetch_assoc($r2);
        $elapsed = get_date($arr2['date'], '', 0, 1);
        $HTMLOUT .= "<tr><td class='colhead'><a href='userdetails.php?id=" . (int) $arr2['user'] . "'>" . htmlsafechars($a2['username']) . "</a></td><td class='colhead'>" . get_date($arr2['date'], '') . "</td><td>$elapsed</td></tr>";
    }
    $HTMLOUT .= '</table>';
}
echo stdhead() . $HTMLOUT . stdfoot();
die();
