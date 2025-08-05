<?php
// app/Services/SSOSessionHelper.php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class SSOSessionHelper
{
    public static function getUserDetail($key = null)
    {
        $userDetail = Session::get('USER_DETAIL', []);
        
        if ($key) {
            return $userDetail[$key] ?? null;
        }
        
        return $userDetail;
    }

    public static function getUserNIK()
    {
        return self::getUserDetail('NIK') ?? Auth::user()?->nik;
    }

    public static function getUserName()
    {
        return self::getUserDetail('name') ?? Auth::user()?->name;
    }

    public static function getUserPosition()
    {
        return self::getUserDetail('position');
    }

    public static function getUserUnit()
    {
        return self::getUserDetail('unit');
    }

    public static function getUserDivisi()
    {
        return self::getUserDetail('divisi');
    }

    public static function getUserLocation()
    {
        return self::getUserDetail('location');
    }

    public static function getPersonnelArea()
    {
        return self::getUserDetail('pa');
    }

    public static function getPersonnelSubArea()
    {
        return self::getUserDetail('psa');
    }

    public static function getUserPhone()
    {
        return self::getUserDetail('noTelp');
    }

    public static function hasCompleteSessionData()
    {
        $userDetail = self::getUserDetail();
        $requiredFields = ['NIK', 'name', 'position', 'unit'];
        
        foreach ($requiredFields as $field) {
            if (empty($userDetail[$field])) {
                return false;
            }
        }
        
        return true;
    }

    public static function getFormattedUserInfo()
    {
        return [
            'nik' => self::getUserNIK(),
            'name' => self::getUserName(),
            'position' => self::getUserPosition(),
            'unit' => self::getUserUnit(),
            'divisi' => self::getUserDivisi(),
            'location' => self::getUserLocation(),
        ];
    }

    public static function setUserDetail($key, $value)
    {
        $userDetail = Session::get('USER_DETAIL', []);
        $userDetail[$key] = $value;
        Session::put('USER_DETAIL', $userDetail);
    }

    public static function clearUserDetail()
    {
        Session::forget('USER_DETAIL');
    }
}