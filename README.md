[![Issues](https://img.shields.io/github/issues/laradevsbd/zkteco-sdk-laravel.svg?style=flat-square)](https://github.com/nurkarim/zkteco-sdk-laravel/issues)
[![Stars](https://img.shields.io/github/stars/laradevsbd/zkteco-sdk-laravel.svg?style=flat-square)](https://github.com/nurkarim/zkteco-sdk-laravel/stargazers)
[![Forks](https://img.shields.io/github/forks/laradevsbd/zkteco-sdk-laravel.svg?style=flat-square)](https://github.com/nurkarim/zkteco-sdk-laravel/network/members)

## Zkteco Laravel SDK ##
This package easy to use functions to ZKTeco Device activities with **laravel** framework. 

**Requires:** Laravel >= 5.0

**License:** MIT

### About SDK

**Laravel** ZKLibrary is PHP Library for ZK Time & Attendance Devices. This library is design to reading and writing data to
attendance device (fingerprint, face recognition or RFID) using UDP protocol. This library is useful to comunicate
between web server and attendance device directly without any addition program. This library is implemented in the form
of class. So that you can create an object and use it functions.

Web server must be connected to the attendance device via Local Area Network (LAN). The UDP port that is used in this
communication is 4370. You can not change this port without changing firmware of the attendance device. So, you just use
it.

The format of the data are: binary, string, and number. The length of the parameter and return value must be vary.

### This package is compatible with Laravel `5.* 6.* 7.* 8.*` ###

## Installation ##

Begin by installing this package through Composer. Just run following command to terminal-

    composer require laradevsbd/zkteco-sdk

Once this operation completes, the final step is to add the service provider. Open config/app.php, and add a new item to
the providers array.

    
    'providers' => [

            // .........................
            Laradevsbd\Zkteco\ZktecoServiceProvider::class,

        ]
    

If you want to change Zkteco  settings , you need to publish its config file(s). For that you need to set ip address in the terminal-

    php artisan vendor:publish

# Usages

##### Create an object of ZktecoLib class.

        use Laradevsbd\Zkteco\Http\Library\ZktecoLib;
    
    //  1 s't parameter is string $ip Device IP Address
    //  2 nd  parameter is integer $port Default: 4370
    
        $zk = new ZktecoLib(config('zkteco.ip'));
    
    //  or you can use with port
    //    $zk = new ZktecoLib(config('zkteco.ip'), 8080);
        
### ZktecoLib Method

* Connect

      //    this return bool
            $zk->connect()
* Disconnect

       // this is return bool
       $zk->disconnect()
  
* Device Enable

       // this is return bool//mixed
       $zk->deviceEnable()

* Device Disable

       // this is return bool//mixed
       $zk->deviceDisable()

* Face Function On

       // this is return bool//mixed
       $zk->faceFunctionOn()

* Device Restart

       // this is return bool//mixed
       $zk->restart()
  
* Device Serial Number

        //    get device serial number
       $zk->serialNumber()

* Device Name

        //    get device name
       $zk->deviceName()

* Device PIN Width

        //    get device pin width
       $zk->pinWidth()

* Device SSR

        //    get device ssr
       $zk->ssr()

* Device Work Code

        //    get device work code
       $zk->workCode()

* Device Firmware  Version

        //    get device fmVersion
       $zk->fmVersion()

* Device Platform

        //    get device platform
       $zk->platform()


* Get Attendance

       //    return array[]
       $zk->getAttendance()

* Clear Attendance

       //   return bool/mixed
       $zk->clearAttendance()

* Clear Admin

       //    remove all admin
       //    return bool|mixed
       $zk->clearAdmin()
    
* Clear User

       //    remove all users
       //    return bool|mixed
       $zk->clearUser()
  
* Get User

        //    get User
        //    this return array[]
        $zk->getUser()
  
* Delete User

        //    parameter integer $uid
        //    return bool|mixed
        $zk->deleteUser()
  
* Set/Add User

        //    1 s't parameter int $uid Unique ID (max 65535)
        //    2 nd parameter int|string $userid ID in DB (same like $uid, max length = 9, only numbers - depends device setting)
        //    3 rd parameter string $name (max length = 24)
        //    4 th parameter int|string $password (max length = 8, only numbers - depends device setting)
        //    5 th parameter int $role Default Util::LEVEL_USER
        //    return bool|mixed
        $zk->setUser()

* User Role
  
        The role of user. The length of $role is 1 byte. Possible value of $role are:
        
        0 = LEVEL_USER
        2 = LEVEL_ENROLLER
        12 = LEVEL_MANAGER
        14 = LEVEL_SUPERMANAGER

## Example

        use Laradevsbd\Zkteco\Http\Library\ZktecoLib;
        
        class ZktecoController extends Controller
        {

            public function index()
            {
                $zk = new ZktecoLib(config('zkteco.ip'),config('zkteco.port'));
                if ($zk->connect()){
                $attendance = $zk->getAttendance();
                return view('zkteco::app',compact('attendance'));
                }
            
            }

            public function addUser()
            {
                $zk = new ZktecoLib(config('zkteco.ip'),config('zkteco.port'));
                if ($zk->connect()){
                    $role = 14; //14= super admin, 0=User :: according to ZKtecho Machine
                    $users = $zk->getUser();
                    $total = end($users);
                    $lastId=$total[3]+1;
                    $zk->setUser($lastId, '11', 'super', '234', $role);
                    return "Add user success";
                }
                else{
                     return "Device not connected";
                }
            }

          

        }
