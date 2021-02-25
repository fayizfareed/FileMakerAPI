<?php
namespace ImaraSoft\FileMaker\Models;

use Faker\Provider\Base;
use ImaraSoft\FileMaker\Services\FileMakerBase;

class ContractDetails extends FileMakerBase{
    public $MobileNumberTrim;
    public $Networks;
    public $NetworkDateChanged;
    public $ServiceType;
    public $MobileNumber;
    public $SimCardNumber;
    public $SimCardNumberDateChange;
    public $SimSerialNumber;
    public $SimSerialNumberDateChange;
    public $PUKCode;
    public $PUKCodeDateChange;
    public $ActivationDate;
    public $CompanyID;
    public $DateDisconnected;
    public $TelstraAccountNumber;
    public $CUDIDuserdetails;
    public $Contract_ID;
    public $UserName;
    public $listofServiceTypes;

    public function GetFMContracts($listofServiceTypes)
    {
        $query = '{"query":[{"CompanyID": "'.$this->CompanyID.'"},{"Networks": "'.$this->Networks.'"},{"'.$this->listofServiceTypes.'"}]}';
        return $this->selectQuery($query);
    }
}