<?php declare(strict_types=1);

use bz\Lang_Switch\Lang_Switch;
use bz\Lang_Switch\Lang_Switch_Options;

require_once 'bz\Lang_Switch\Lang_Switch.php';
require_once 'bz\Lang_Switch\Lang_Switch_Options.php';

$lang_config = array(
    //'file_name' => 'locale.json'
);

$bz_lang_switch = new Lang_Switch(new Lang_Switch_Options($lang_config));

function jsonHeader()
{
    header('Content-Type: application/json; charset=UTF-8');
}
if ($bz_lang_switch->response['file']) {
    //$bz_lang_switch->debug_preview();
    if (!headers_sent()){
        jsonHeader();
    }

    echo $bz_lang_switch->response['file'];
}