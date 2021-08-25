<?php

namespace Laradevsbd\Zkteco\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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

    public function checkDevice()
    {
        $zk = new ZktecoLib(config('zkteco.ip'),config('zkteco.port'));
        if ($zk->connect()){
            $zk->getAttendance();
            return "success";
        }
        return "fail";
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
        }else{
            return "Device not connected";
        }
    }
}
