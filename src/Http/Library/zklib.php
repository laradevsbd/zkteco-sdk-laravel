<?php
namespace Laradevsbd\Zkteco\Http\Library;

class ZKLib
{
    public $ip;
    public $port;
    public $zkclient;

    public $data_recv = '';
    public $session_id = 0;
    public $userdata = array();
    public $attendancedata = array();
    public $timeout_sec = 60; //5
    public $timeout_usec = 5000000;
    public function __construct($ip, $port)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->zkclient = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $timeout = array('sec' => 60, 'usec' => 500000);
        $this->setTimeout($this->timeout_sec, $this->timeout_usec);
      // socket_set_option($this->zkclient, SOL_SOCKET, SO_RCVTIMEO, $timeout);

        include_once("zkconst.php");
        include_once("zkconnect.php");
        include_once("zkversion.php");
        include_once("zkos.php");
        include_once("zkplatform.php");
        include_once("zkworkcode.php");
        include_once("zkssr.php");
        include_once("zkpin.php");
        include_once("zkface.php");
        include_once("zkserialnumber.php");
        include_once("zkdevice.php");
        include_once("zkuser.php");
        include_once("zkattendance.php");
        include_once("zktime.php");
    }


    function createChkSum($p)
    {
        /*This function calculates the chksum of the packet to be sent to the
        time clock

        Copied from zkemsdk.c*/

        $l = count($p);
        $chksum = 0;
        $i = $l;
        $j = 1;
        while ($i > 1) {
            $u = unpack('S', pack('C2', $p['c' . $j], $p['c' . ($j + 1)]));

            $chksum += $u[1];

            if ($chksum > USHRT_MAX)
                $chksum -= USHRT_MAX;
            $i -= 2;
            $j += 2;
        }

        if ($i)
            $chksum = $chksum + $p['c' . strval(count($p))];

        while ($chksum > USHRT_MAX)
            $chksum -= USHRT_MAX;

        if ($chksum > 0)
            $chksum = -($chksum);
        else
            $chksum = abs($chksum);

        $chksum -= 1;
        while ($chksum < 0)
            $chksum += USHRT_MAX;

        return pack('S', $chksum);
    }

    function createHeader($command, $chksum, $session_id, $reply_id, $command_string)
    {
        /*This function puts a the parts that make up a packet together and
        packs them into a byte string*/
        $buf = pack('SSSS', $command, $chksum, $session_id, $reply_id) . $command_string;

        $buf = unpack('C' . (8 + strlen($command_string)) . 'c', $buf);

        $u = unpack('S', $this->createChkSum($buf));

        if (is_array($u)) {
            foreach ($u as $key=> $val){
                $u = $u[$key];
            }
        }
        $chksum = $u;

        $reply_id += 1;

        if ($reply_id >= USHRT_MAX)
            $reply_id -= USHRT_MAX;

        $buf = pack('SSSS', $command, $chksum, $session_id, $reply_id);

        return $buf . $command_string;

    }

    function checkValid($reply)
    {
        /*Checks a returned packet to see if it returned CMD_ACK_OK,
        indicating success*/
        $u = unpack('H2h1/H2h2', substr($reply, 0, 8));

        $command = hexdec($u['h2'] . $u['h1']);
        if ($command == CMD_ACK_OK)
            return TRUE;
        else
            return FALSE;
    }

    public function setTimeout($sec = 0, $usec = 0)
    {
        if ($sec != 0) {
            $this->timeout_sec = $sec;
        }
        if ($usec != 0) {
            $this->timeout_usec = $usec;
        }
        $timeout = array('sec' => $this->timeout_sec, 'usec' => $this->timeout_usec);
        socket_set_option($this->zkclient, SOL_SOCKET, SO_RCVTIMEO, $timeout);
    }

    public function ping($timeout = 1)
    {
        $time1 = microtime(true);
        $pfile = fsockopen($this->ip, $this->port, $errno, $errstr, $timeout);
        if (!$pfile) {
            return 'down';
        }
        $time2 = microtime(true);
        fclose($pfile);
        return round((($time2 - $time1) * 1000), 0);
    }

    private function reverseHex($input)
    {
        $output = '';
        for ($i = strlen($input); $i >= 0; $i--) {
            $output .= substr($input, $i, 2);
            $i--;
        }
        return $output;
    }

    private function encodeTime($time)
    {
        $str = str_replace(array(":", " "), array("-", "-"), $time);
        $arr = explode("-", $str);
        $year = @$arr[0] * 1;
        $month = ltrim(@$arr[1], '0') * 1;
        $day = ltrim(@$arr[2], '0') * 1;
        $hour = ltrim(@$arr[3], '0') * 1;
        $minute = ltrim(@$arr[4], '0') * 1;
        $second = ltrim(@$arr[5], '0') * 1;
        $data = (($year % 100) * 12 * 31 + (($month - 1) * 31) + $day - 1) * (24 * 60 * 60) + ($hour * 60 + $minute) * 60 + $second;
        return $data;
    }

    private function decodeTime($data)
    {
        $second = $data % 60;
        $data = $data / 60;
        $minute = $data % 60;
        $data = $data / 60;
        $hour = $data % 24;
        $data = $data / 24;
        $day = $data % 31 + 1;
        $data = $data / 31;
        $month = $data % 12 + 1;
        $data = $data / 12;
        $year = floor($data + 2000);
        $d = date("Y-m-d H:i:s", strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second));
        return $d;
    }

    public function connect()
    {
        return zkconnect($this);
    }

    public function disconnect()
    {
        return zkdisconnect($this);
    }

    public function shutdownDevice()
    {
        return shutdownDevice($this);
    }

    public function changeSpeed()
    {
        return zkChangeSpeed($this,$speed = 0); //Change transfer speed of the device. 0 = slower. 1 = faster.
    }

    public function version()
    {
        return zkversion($this);
    }


    public function osversion()
    {
        return zkos($this);
    }

    /*
    public function extendFormat() {
        return zkextendfmt($this);
    }

    public function extendOPLog(index=0) {
        return zkextendoplog($this, index);
    }
    */

    public function platform()
    {
        return zkplatform($this);
    }

    public function fmVersion()
    {
        return zkplatformVersion($this);
    }

    public function workCode()
    {
        return zkworkcode($this);
    }

    public function ssr()
    {
        return zkssr($this);
    }

    public function pinWidth()
    {
        return zkpinwidth($this);
    }

    public function faceFunctionOn()
    {
        return zkfaceon($this);
    }

    public function serialNumber()
    {
        return zkserialnumber($this);
    }

    public function deviceName()
    {
        return zkdevicename($this);
    }

    public function disableDevice()
    {
        return zkdisabledevice($this);
    }

    public function enableDevice()
    {
        return zkenabledevice($this);
    }

    public function getUser()
    {
        return zkgetuser($this);
    }

    public function setUser($uid, $userid, $name, $password, $role)
    {
        return zksetuser($this, $uid, $userid, $name, $password, $role);
    }

    public function clearUser()
    {
        return zkclearuser($this);
    }

    public function clearAdmin()
    {
        return zkclearadmin($this);
    }

    public function getAttendance()
    {
        return zkgetattendance($this);
    }

    public function clearAttendance()
    {
        return zkclearattendance($this);
    }

    public function setTime($t)
    {
        return zksettime($this, $t);
    }

    public function getTime()
    {
        return zkgettime($this);
    }
    public function deleteUser($id)
    {
        return deleteUser($this, $id);
    }
   public function restart()
    {
        return restart($this);
    }

    public function execCommand($command, $command_string = '', $offset_data = 8)
    {
        $chksum = 0;
        $session_id = $this->session_id;
        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->received_data, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);
        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);
        socket_sendto($this->socket, $buf, strlen($buf), MSG_EOR, $this->ip, $this->port);
        try {
            socket_recvfrom($this->socket, $this->received_data, 1024, 0, $this->ip, $this->port);
            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->received_data, 0, 8));
            $this->session_id =  hexdec($u['h6'] . $u['h5']);
            return substr($this->received_data, $offset_data);
        } catch (ErrorException $e) {
            return false;
        } catch (exception $e) {
            return false;
        }
    }

    public function writeLCD($rank, $text)
    {
        $command = CMD_WRITE_LCD;
        $byte1 = chr((int) ($rank % 256));
        $byte2 = chr((int) ($rank >> 8));
        $byte3 = chr(0);
        $command_string = $byte1 . $byte2 . $byte3 . ' ' . $text;
        return $this->execCommand($command, $command_string);
    }

    public function clearLCD()
    {
        $command = CMD_CLEAR_LCD;
        return $this->execCommand($command);
    }

    public function testVoice()
    {
        $command = CMD_TESTVOICE;
        $command_string = chr(0) . chr(0);
        return $this->execCommand($command, $command_string);
    }

    public function getVersion()
    {
        $command = CMD_VERSION;
        return $this->execCommand($command);
    }

    public function getOSVersion($net = true)
    {
        $command = CMD_OPTIONS_RRQ;
        $command_string = '~OS';
        $return = $this->execCommand($command, $command_string);
        if ($net) {
            $arr = explode("=", $return, 2);
            return $arr[1];
        } else {
            return $return;
        }
    }

    public function setOSVersion($osVersion)
    {
        $command = CMD_OPTIONS_WRQ;
        $command_string = '~OS=' . $osVersion;
        return $this->execCommand($command, $command_string);
    }

    public function getPlatform($net = true)
    {
        $command = CMD_OPTIONS_RRQ;
        $command_string = '~Platform';
        $return = $this->execCommand($command, $command_string);
        if ($net) {
            $arr = explode("=", $return, 2);
            return $arr[1];
        } else {
            return $return;
        }
    }

    public function setPlatform($patform)
    {
        $command = CMD_OPTIONS_RRQ;
        $command_string = '~Platform=' . $patform;
        return $this->execCommand($command, $command_string);
    }

    public function getFirmwareVersion($net = true)
    {
        $command = CMD_OPTIONS_RRQ;
        $command_string = '~ZKFPVersion';
        $return = $this->execCommand($command, $command_string);
        if ($net) {
            $arr = explode("=", $return, 2);
            return $arr[1];
        } else {
            return $return;
        }
    }

    public function setFirmwareVersion($firmwareVersion)
    {
        $command = CMD_OPTIONS_WRQ;
        $command_string = '~ZKFPVersion=' . $firmwareVersion;
        return $this->execCommand($command, $command_string);
    }

    public function getWorkCode($net = true)
    {
        $command = CMD_OPTIONS_RRQ;
        $command_string = 'WorkCode';
        $return = $this->execCommand($command, $command_string);
        if ($net) {
            $arr = explode("=", $return, 2);
            return $arr[1];
        } else {
            return $return;
        }
    }

    public function setWorkCode($workCode)
    {
        $command = CMD_OPTIONS_WRQ;
        $command_string = 'WorkCode=' . $workCode;
        return $this->execCommand($command, $command_string);
    }

    public function getSSR($net = true)
    {
        $command = CMD_OPTIONS_PRQ;
        $command_string = '~SSR';
        $return = $this->execCommand($command, $command_string);
        if ($net) {
            $arr = explode("=", $return, 2);
            return $arr[1];
        } else {
            return $return;
        }
    }

    public function setSSR($ssr)
    {
        $command = CMD_OPTIONS_WRQ;
        $command_string = '~SSR=' . $ssr;
        return $this->execCommand($command, $command_string);
    }

    public function getPinWidth($net = true)
    {
        $command = CMD_GET_PINWIDTH;
        $command = CMD_OPTIONS_PRQ;
        $command_string = '~PIN2Width';
        $return = $this->execCommand($command, $command_string);
        if ($net) {
            $arr = explode("=", $return, 2);
            return $arr[1];
        } else {
            return $return;
        }
    }

    public function setPinWidth($pinWidth)
    {
        $command = CMD_OPTIONS_WRQ;
        $command_string = '~PIN2Width=' . $pinWidth;
        return $this->execCommand($command, $command_string);
    }

    public function getFaceFunctionOn($net = true)
    {
        $command = CMD_OPTIONS_RRQ;
        $command_string = 'FaceFunOn';
        $return = $this->execCommand($command, $command_string);
        if ($net) {
            $arr = explode("=", $return, 2);
            return $arr[1];
        } else {
            return $return;
        }
    }

    public function setFaceFunctionOn($faceFunctionOn) //$faceFunctionOn= 1 = available; 2 = not available.
    {
        $command = CMD_OPTIONS_WRQ;
        $command_string = 'FaceFunOn=' . $faceFunctionOn;
        return $this->execCommand($command, $command_string);
    }

    public function getSerialNumber($net = true)
    {
        $command = CMD_OPTIONS_RRQ;
        $command_string = '~SerialNumber';
        $return = $this->execCommand($command, $command_string);
        if ($net) {
            $arr = explode("=", $return, 2);
            return $arr[1];
        } else {
            return $return;
        }
    }

    public function setSerialNumber($serialNumber)
    {
        $command = CMD_OPTIONS_WRQ;
        $command_string = '~SerialNumber=' . $serialNumber;
        return $this->execCommand($command, $command_string);
    }

    public function getDeviceName($net = true)
    {
        $command = CMD_OPTIONS_RRQ;
        $command_string = '~DeviceName';
        $return = $this->execCommand($command, $command_string);
        if ($net) {
            $arr = explode("=", $return, 2);
            return $arr[1];
        } else {
            return $return;
        }
    }

    public function setDeviceName($deviceName)
    {
        $command = CMD_OPTIONS_WRQ;
        $command_string = '~DeviceName=' . $deviceName;
        return $this->execCommand($command, $command_string);
    }

    public function zkGetTime()
    {
        // resolution = 1 minute
        $command = CMD_GET_TIME;
        return $this->decodeTime(hexdec($this->reverseHex(bin2hex($this->execCommand($command)))));
    }

    public function zkSetTime($t)
    {
        // resolution = 1 second
        $command = CMD_SET_TIME;
        $command_string = pack('I', $this->encodeTime($t));
        return $this->execCommand($command, $command_string);
    }
    public function enableClock($mode = 0)
    {
        $command = CMD_ENABLE_CLOCK;
        $command_string = chr($mode);
        return $this->execCommand($command, $command_string);
    }

    public function startVerify($uid)
    {
        $command = CMD_STARTVERIFY;
        $byte1 = chr((int) ($uid % 256));
        $byte2 = chr((int) ($uid >> 8));
        $command_string = $byte1 . $byte2;
        return $this->execCommand($command, $command_string);
    }

    public function startEnroll($uid, $finger)
    {
        $command = CMD_STARTENROLL;
        $byte1 = chr((int) ($uid % 256));
        $byte2 = chr((int) ($uid >> 8));
        $command_string = $byte1 . $byte2 . chr($finger);
        return $this->execCommand($command, $command_string);
    }

    public function cancelCapture()
    {
        $command = CMD_CANCELCAPTURE;
        return $this->execCommand($command);
    }

    public function setUserTemplate($data)
    {
        $command = CMD_USERTEMP_WRQ;
        $command_string = $data;
        // $length = ord(substr($command_string, 0, 1)) + ord(substr($command_string, 1, 1)) * 256;
        return $this->execCommand($command, $command_string);
        /*
        $chksum = 0;
        $session_id = $this->session_id;
        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->received_data, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);
        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);
        socket_sendto($this->socket, $buf, strlen($buf), 0, $this->ip, $this->port);
        try {
            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->received_data, 0, 8));
            $this->session_id = hexdec($u['h6'] . $u['h5']);
            return substr($this->received_data, 8);
        } catch (ErrorException $e) {
            return FALSE;
        } catch (exception $e) {
            return FALSE;
        }
        */
    }

    public function deleteUserTemp($uid, $finger)
    {
        $command = CMD_DELETE_USERTEMP;
        $byte1 = chr((int) ($uid % 256));
        $byte2 = chr((int) ($uid >> 8));
        $command_string = $byte1 . $byte2 . chr($finger);
        return $this->execCommand($command, $command_string);
    }

    public function testUserTemplate($uid, $finger)
    {
        $command = CMD_TEST_TEMP;
        $byte1 = chr((int) ($uid % 256));
        $byte2 = chr((int) ($uid >> 8));
        $command_string = $byte1 . $byte2 . chr($finger);
        $u =  unpack('H2h1/H2h2', $this->execCommand($command, $command_string));
        $ret = hexdec($u['h2'] . $u['h1']);
        return ($ret == CMD_ACK_OK) ? 1 : 0;
    }



    public function getUserTemplateAll($uid)
    {
        $template = array();
        $j = 0;
        for ($i = 5; $i < 10; $i++, $j++) {
            $template[$j] = $this->getUserTemplate($uid, $i);
        }
        for ($i = 4; $i >= 0; $i--, $j++) {
            $template[$j] = $this->getUserTemplate($uid, $i);
        }
        return $template;
    }

    public function getUserTemplate($uid, $finger)
    {
        $template_data = '';
        $this->user_data = array();
        $command = CMD_USERTEMP_RRQ;
        $byte1 = chr((int) ($uid % 256));
        $byte2 = chr((int) ($uid >> 8));
        $command_string = $byte1 . $byte2 . chr($finger);
        $chksum = 0;
        $session_id = $this->session_id;
        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->received_data, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);
        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);
        socket_sendto($this->socket, $buf, strlen($buf), 0, $this->ip, $this->port);
        try {
            socket_recvfrom($this->socket, $this->received_data, 1024, 0, $this->ip, $this->port);
            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->received_data, 0, 8));
            $bytes = $this->getSizeTemplate();
            if ($bytes) {
                while ($bytes > 0) {
                    socket_recvfrom($this->socket, $received_data, 1032, 0, $this->ip, $this->port);
                    array_push($this->user_data, $received_data);
                    $bytes -= 1024;
                }
                $this->session_id =  hexdec($u['h6'] . $u['h5']);
                socket_recvfrom($this->socket, $received_data, 1024, 0, $this->ip, $this->port);
            }
            $template_data = array();
            if (count($this->user_data) > 0) {
                for ($x = 0; $x < count($this->user_data); $x++) {
                    if ($x == 0) {
                        $this->user_data[$x] = substr($this->user_data[$x], 8);
                    } else {
                        $this->user_data[$x] = substr($this->user_data[$x], 8);
                    }
                }
                $user_data = implode('', $this->user_data);
                $template_size = strlen($user_data) + 6;
                $prefix = chr($template_size % 256) . chr(round($template_size / 256)) . $byte1 . $byte2 . chr($finger) . chr(1);
                $user_data = $prefix . $user_data;
                if (strlen($user_data) > 6) {
                    $valid = 1;
                    $template_data = array($template_size, $uid, $finger, $valid, $user_data);
                }
            }
            return $template_data;
        } catch (ErrorException $e) {
            return false;
        } catch (exception $e) {
            return false;
        }
    }

    public function getUserData()
    {
        $uid = 1;
        $command = CMD_USERTEMP_RRQ;
        $command_string = chr(5);
        $chksum = 0;
        $session_id = $this->session_id;
        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($this->received_data, 0, 8));
        $reply_id = hexdec($u['h8'] . $u['h7']);
        $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);
        socket_sendto($this->socket, $buf, strlen($buf), 0, $this->ip, $this->port);
        try {
            socket_recvfrom($this->socket, $this->received_data, 1024, 0, $this->ip, $this->port);
            $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($this->received_data, 0, 8));
            $bytes = $this->getSizeUser();
            if ($bytes) {
                while ($bytes > 0) {
                    socket_recvfrom($this->socket, $received_data, 1032, 0, $this->ip, $this->port);
                    array_push($this->user_data, $received_data);
                    $bytes -= 1024;
                }
                $this->session_id =  hexdec($u['h6'] . $u['h5']);
                socket_recvfrom($this->socket, $received_data, 1024, 0, $this->ip, $this->port);
            }
            $users = array();
            $retdata = "";
            if (count($this->user_data) > 0) {
                for ($x = 0; $x < count($this->user_data); $x++) {
                    if ($x > 0) {
                        $this->user_data[$x] = substr($this->user_data[$x], 8);
                    }
                    if ($x > 0) {
                        $retdata .= substr($this->user_data[$x], 0);
                    } else {
                        $retdata .= substr($this->user_data[$x], 12);
                    }
                }
            }
            return $retdata;
        } catch (ErrorException $e) {
            return false;
        } catch (exception $e) {
            return false;
        }
    }

}

?>
