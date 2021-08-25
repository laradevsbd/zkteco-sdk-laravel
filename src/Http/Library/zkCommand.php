<?php
define('CMD_CONNECT', 1000);
define('CMD_EXIT', 1001);
define('CMD_ENABLEDEVICE', 1002);
define('CMD_DISABLEDEVICE', 1003);
define('CMD_RESTART', 1004);
define('CMD_POWEROFF', 1005);
define('CMD_SLEEP', 1006);
define('CMD_RESUME', 1007);
define('CMD_TEST_TEMP', 1011);
define('CMD_TESTVOICE', 1017);
define('CMD_VERSION', 1100);
define('CMD_CHANGE_SPEED', 1101);

define('CMD_ACK_OK', 2000);
define('CMD_ACK_ERROR', 2001);
define('CMD_ACK_DATA', 2002);
define('CMD_PREPARE_DATA', 1500);
define('CMD_DATA', 1501);

define('CMD_USER_WRQ', 8);
define('CMD_USERTEMP_RRQ', 9);
define('CMD_USERTEMP_WRQ', 10);
define('CMD_OPTIONS_RRQ', 11);
define('CMD_OPTIONS_WRQ', 12);
define('CMD_ATTLOG_RRQ', 13);
define('CMD_CLEAR_DATA', 14);
define('CMD_CLEAR_ATTLOG', 15);
define('CMD_DELETE_USER', 18);
define('CMD_DELETE_USERTEMP', 19);
define('CMD_CLEAR_ADMIN', 20);
define('CMD_ENABLE_CLOCK', 57);
define('CMD_STARTVERIFY', 60);
define('CMD_STARTENROLL', 61);
define('CMD_CANCELCAPTURE', 62);
define('CMD_STATE_RRQ', 64);
define('CMD_WRITE_LCD', 66);
define('CMD_CLEAR_LCD', 67);

define('CMD_GET_TIME', 201);
define('CMD_SET_TIME', 202);

define('USHRT_MAX', 65535);

define('LEVEL_USER', 0);          // 0000 0000
define('LEVEL_ENROLLER', 2);      // 0000 0010
define('LEVEL_MANAGER', 12);      // 0000 1100
define('LEVEL_SUPERMANAGER', 14); // 0000 1110

?>