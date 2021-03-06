<?php

function checkdir(&$dirs)
{
    foreach ($dirs as $dir => $x) {
        if (is_dir($dir)) {
            $fn = $dir . uniqid(time()) . '.tmp';
            if (@file_put_contents($fn, '1')) {
                unlink($fn);
                $dirs[$dir] = 1;
            } else {
                $dirs[$dir] = 0;
            }
        } else {
            $dirs[$dir] = 0;
        }
    }
}

function permissioncheck()
{
    global $root, $public;
    $dirs = [
        $root                      => 0,
        $root . 'dir_list/'        => 0,
        $root . 'cache/'           => 0,
        $root . 'torrents/'        => 0,
        $root . 'uploads/'         => 0,
        $root . 'include/backup/'  => 0,
        $root . 'sqlerr_logs/'     => 0,
        $public . 'install/'       => 0,
        $public . 'install/extra/' => 0,
        $root . 'include/'         => 0,
    ];
    checkdir($dirs);
    $continue = true;
    $out      = '<fieldset><legend>Directory check</legend>';
    foreach ($dirs as $dir => $state) {
        if (!$state) {
            $continue = false;
        }
        $out .= '<div class="' . ($state ? 'readable' : 'notreadable') . '">' . $dir . '</div>';
    }
    if (!$continue) {
        $out .= '<div class="info" style="text-align:center;">It looks like you need to chmod some directories!<br>all directories marked in red should be chmoded 0777<br><br><input type="button" value="Reload" onclick="window.location.reload()" /></div>';
    }
    $out .= '</fieldset>';
    if ($continue) {
        $out .= '
                <div style="text-align:center;">
                    <input type="button" onclick="onClick(2)" value="Next step" />
                </div>';
    }
    $out .= '
    <script>
        var processing = 1;
    </script>';

    return $out;
}
