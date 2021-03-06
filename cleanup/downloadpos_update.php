<?php
/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function downloadpos_update($data)
{
    global $site_config, $queries, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $dt          = TIME_NOW;
    $res         = sql_query('SELECT id, modcomment FROM users WHERE downloadpos > 1 AND downloadpos < ' . $dt) or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Download ban expired.';
        $msg     = "Your Download ban has expired and has been auto-removed by the system.\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment     = $arr['modcomment'];
            $modcomment     = get_date($dt, 'DATE', 1) . " - Download ban Removed By System.\n" . $modcomment;
            $modcom         = sqlesc($modcomment);
            $msgs_buffer[]  = '(0,' . $arr['id'] . ',' . $dt . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ' )';
            $users_buffer[] = '(' . $arr['id'] . ', \'1\', ' . $modcom . ')';
            $cache->update_row('user' . $arr['id'], [
                'downloadpos' => 1,
                'modcomment'  => $modcomment,
            ], $site_config['expires']['user_cache']);
            $cache->increment('inbox_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($data['clean_log'] && $count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer))                                                                                         or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, downloadpos, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE downloadpos = VALUES(downloadpos), modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log("Download Possible Cleanup: Completed. Removed Download ban from $count members");
        }
        unset($users_buffer, $msgs_buffer, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Download possible clean-------------------- Downloadpos cleanup Complete using $queries queries --------------------");
    }
}
