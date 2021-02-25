<?php

namespace ImaraSoft\FileMaker;

use Faker\Provider\Base;
use Illuminate\Support\ServiceProvider;
use ImaraSoft\FileMaker\Models\PhoneHistory;
use ImaraSoft\FileMaker\Models\Plan;

class FileMakerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $sss = new Plan();
        $sss->setRecordID(2);
        $sss->delete();
        die;
        
        $sss = new Plan();
        $sss->Contract_ID = 123;
        $sss->Plan_Name='Fayiz';
        $sss->Date_Start='12/12/12';
        $sss->Term='2sdf';
        $sss->Date_Expire = '12/12/12';
        $sss->Upgrade_Text='ssfsf';
        $sss->Plan_Key='asdfa';
        $sss->Plan_ID=234;
        $sss->setRecordID(3);
        echo $sss->create();
        die;

        $sss = new Plan();
        $sss->setRecordID(8);
        $sss->selectOne();
        var_dump($sss);
        die;

        $sss = new Plan();
        $sss->setRecordID(8);
        print_r($sss->selectQuery('{"query":[{"Plan_Name": "cool"}]}'));
        die;
    }
    public function register()
    {
        
    }
}