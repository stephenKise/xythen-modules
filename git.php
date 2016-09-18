<?php

function git_getmoduleinfo()
{
    $info = [
        'name' => 'Git Management',
        'author'=> '`&`bStephen Kise`b',
        'version' => '0.2b',
        'category' => 'Administrative',
        'description' =>
            'Manage the git repository.',
        'requires' => [
            'changelog' => '0.1b |Stephen Kise, nope',
        ],
        'download' => 'nope',
        'allowanonymous' => true,
        'override_forced_nav' => true,
    ];
    return $info;
}

function git_install()
{
    module_addhook('superuser');
    return true;
}

function git_uninstall()
{
    return true;
}

function git_dohook($hook, $args)
{
    switch ($hook) {
        case 'superuser':
            global $session;
            $gamelog = db_prefix('gamelog');
            $sql = db_query("SELECT message FROM $gamelog ORDER BY logid+0 DESC LIMIT 1");
            $row = db_fetch_assoc($sql);
            if ($session['user']['superuser'] & SU_MANAGE_MODULES) {
                addnav('Mechanics');
                addnav('Git Pull', 'superuser.php?git=pull');
                require_once('lib/gamelog.php');
                if (httpget('git') == 'pull') {
                    shell_exec('git pull');
                    $output = shell_exec('git log -1 --format="%b (<a href=\"http://github.com/stephenKise/Legend-of-the-Green-Dragon/commit/%h\">%h</a>)"');
                    if ($output != $row['message']) {
                        gamelog($output, get_module_setting('category', 'changelog'));
                    }
                }
            }
            break;
    }
    return $args;
}

function git_run()
{
    $op = httpget('op');
    $gamelog = db_prefix('gamelog');
    page_header();
    switch ($op) {
        case 'pull_modules':
                shell_exec('git submodule foreach git pull --rebase');
                $exec = shell_exec('cd modules && git log -1 --format="%b (<a href=\"http://github.com/stephenKise/xythen-modules/commit/%h\">%h</a>)"');
            break;
        case 'pull_core':
                shell_exec('git pull');
                $exec = shell_exec('git log -1 --format="%b (<a href=\"http://github.com/stephenKise/Legend-of-the-Green-Dragon/commit/%h\">%h</a>)"');
            break;
    }
    output($exec);
    $sql = db_query("SELECT message FROM $gamelog WHERE message = '$exec' ORDER BY logid+0 DESC LIMIT 1");
    if (db_num_rows($sql) != 1) {
        $row = db_fetch_assoc($sql);
        require_once('lib/gamelog.php');
        gamelog($exec, get_module_setting('category', 'changelog'));
    }
    if (httpallpost()) {
        require_once('lib/gamelog.php');
        gamelog(json_encode(httpallpost(), true), get_module_setting('category', 'changelog'));
    }

    page_footer();
}