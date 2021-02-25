<?php
namespace ImaraSoft\FileMaker\Models;

use ImaraSoft\FileMaker\Services\FileMakerBase;

class Plan extends FileMakerBase{
    public $Contract_ID;
    public $Plan_Name;
    public $Date_Start;
    public $Term;
    public $Date_Expire;
    public $Upgrade_Text;
    public $Plan_Key;
    public $Plan_ID;
}