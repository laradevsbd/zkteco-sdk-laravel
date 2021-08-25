<?php

namespace Laradevsbd\Zkteco\Http\Library;


class ZktecoLib
{
    public $zk;

    public function __construct($ip,$port=4370)
    {
        $this->zk = new \Nurkarim\Zkteco\Http\Library\ZKLib($ip, $port);
        $ret = $this->zk->connect();
    }

    /**
     * @param $id
     * @param $userId
     * @param $name
     * @param $password
     * @param $role
     * @return bool
     */
    public function setUser($id, $userId, $name, $password, $role)
    {
        try {
            if ($this->zk->setUser($id, $userId, $name, $password, $role) == null) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteUser($id)
    {
        $this->zk->deleteUser($id);
    }

    public function getUser()
    {
        return $this->zk->getUser();
    }

    public function getAttendance()
    {
        return $this->zk->getAttendance();
    }

    public function clearUser()
    {
        return $this->zk->clearUser();
    }

    public function clearAdmin()
    {
        return $this->zk->clearAdmin();
    }

    public function clearAttendance()
    {
        return $this->zk->clearAttendance();
    }

    public function restart()
    {
        return $this->zk->restart();
    }

    public function disconnect()
    {
        return $this->zk->disconnect();
    }

    public function connect()
    {
        return $this->zk->connect();
    }

    public function deviceEnable()
    {
        return $this->zk->enableDevice();
    }

    public function deviceDisable()
    {
        return $this->zk->disableDevice();
    }

    public function faceFunctionOn()
    {
        return $this->zk->faceFunctionOn();
    }

    public function serialNumber()
    {
        return $this->zk->serialNumber();
    }

    public function deviceName()
    {
        return $this->zk->deviceName();
    }

    public function pinWidth()
    {
        return $this->zk->pinWidth();
    }

    public function ssr()
    {
        return $this->zk->ssr();
    }

    public function workCode()
    {
        return $this->zk->workCode();
    }

    public function fmVersion()
    {
        return $this->zk->fmVersion();
    }

    public function platform()
    {
        return $this->zk->platform();
    }

}
