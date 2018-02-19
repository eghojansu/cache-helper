<?php

define('TEMP', dirname(__DIR__) . '/var/');

if (!is_dir(TEMP)) {
    mkdir(TEMP, 0755, true);
}
