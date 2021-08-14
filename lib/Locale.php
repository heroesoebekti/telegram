<?php

global $sysconf;

T_setlocale(LC_ALL, $sysconf['default_lang']);
_bindtextdomain('messages', __DIR__.DS.'locale');
_bind_textdomain_codeset('messages', 'UTF-8');
_textdomain('messages');


