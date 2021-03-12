<?php

namespace ImaraSoft\FileMaker\Services;

use Exception;
use Facade\FlareClient\Http\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use ImaraSoft\FileMaker\Models\Plan;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;

use function GuzzleHttp\Promise\all;

class FileMakerBase{
    private $server;
    private $database;
    private $username;
    private $password;
    
    private $recordId; 

    public function __construct()
    {
        $this->server =  Config::get("filemaker.serverConfig.serverName");
        $this->database =  Config::get("filemaker.serverConfig.database");
        $this->username =  Config::get("filemaker.serverConfig.userName");
        $this->password =  Config::get("filemaker.serverConfig.password");
    }
    public function getRecordID()
    {
        return $this->recordId;
    }
    public function setRecordID($val)
    {
        $this->recordId = $val;
    }
    public function set($data) {
        foreach ($data->fieldData AS $key => $value) {
            $this->{$key} = $value;
        }
        $this->recordId = $data->recordId;
    }
    private function generateToken()
    {
        try
        {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://'.$this->server.'/fmi/data/v1/databases/'.$this->database.'/sessions', 
            ['auth' => [$this->username, $this->password], 'headers' => ['Content-Type' =>'application/json']]);
            return json_decode($response->getBody())->response->token;
        }
        catch(Exception $ex)
        {
            return '';
        }
    }
    private function getFields()
    {
        $properties = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $propertie)
        {
            $propertie->setAccessible(true);
            if ( $propertie->getValue($this) != null)
            {
                $arrayForJSON[$propertie->getName()] = $propertie->getValue($this);
            }
        }
        $jval = json_encode($arrayForJSON);
        $jval = '{"fieldData":'.$jval.'}';
        return $jval;
    }
    public function create()
    {
        $token = $this->generateToken();
        $jval = $this->getFields();
        try
        {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://'.$this->server.'/fmi/data/v1/databases/'.$this->database.'/layouts/'.class_basename($this).'/records', 
            [ 'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' =>'application/json'], 'body' =>$jval],);
            $this->set(json_decode($response->getBody())->response->recordId);
            return json_decode($response->getBody())->response->recordId;
        }
        catch(Exception $ex)
        {
            echo $ex;
            return null;
        }
    }
    public function update()
    {
        $token = $this->generateToken();
        $jval = $this->getFields();
        try
        {
            $client = new \GuzzleHttp\Client();
            $response = $client->patch('https://'.$this->server.'/fmi/data/v1/databases/'.$this->database.'/layouts/'.class_basename($this).'/records/'.$this->recordId, 
            [ 'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' =>'application/json'], 'body' =>$jval]);
            
            return $this;
        }
        catch(Exception $ex)
        {
            echo $ex;
            return null;
        }
    }
    public function delete()
    {
        $token = $this->generateToken();
        try
        {
            $client = new \GuzzleHttp\Client();
            $response = $client->delete('https://'.$this->server.'/fmi/data/v1/databases/'.$this->database.'/layouts/'.class_basename($this).'/records/'.$this->recordId, 
            [ 'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' =>'application/json']],);
            if (json_decode($response->getBody())->messages[0]->code == "0")
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        catch(Exception $ex)
        {
            echo $ex;
            return false;
        }
    }
    public function select()
    {
        $token = $this->generateToken();
        try
        {
            $objcol = collect();
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://'.$this->server.'/fmi/data/v1/databases/'.$this->database.'/layouts/'.class_basename($this).'/records', 
            [ 'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' =>'application/json']],);
            foreach (json_decode($response->getBody()->getContents())->response->data as $record)
            { 
                $reflector = new ReflectionClass(get_class($this));
                $obj = $reflector->newInstance();
                $reflectionMethod = new ReflectionMethod($obj, 'set');
                echo $reflectionMethod->invoke($obj, $record);
                $objcol->push($obj);
            }
            return $objcol;
        }
        catch(Exception $ex)
        {
            echo $ex;
            return null;
        }
    }
    public function selectOne()
    {
        $token = $this->generateToken();
        try
        {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://'.$this->server.'/fmi/data/v1/databases/'.$this->database.'/layouts/'.class_basename($this).'/records/'.$this->recordId, 
            [ 'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' =>'application/json']],);

            foreach (json_decode($response->getBody()->getContents())->response->data as $record)
            {
                $this->set($record);
                return true;
            }
        }
        catch(Exception $ex)
        {
            echo $ex;
            return false;
        }
    }
    public function selectQuery($where)
    {
        $token = $this->generateToken();
        try
        {
            $objcol = collect();
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://'.$this->server.'/fmi/data/v1/databases/'.$this->database.'/layouts/'.class_basename($this).'/_find', 
            [ 'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' =>'application/json'], 'body' => $where],);
            foreach (json_decode($response->getBody()->getContents())->response->data as $record)
            { 
                $reflector = new ReflectionClass(get_class($this));
                $obj = $reflector->newInstance();
                $reflectionMethod = new ReflectionMethod($obj, 'set');
                $reflectionMethod->invoke($obj, $record);
                $objcol->push($obj);
                
            }
            return $objcol;
        }
        catch(Exception $ex)
        {
            return null;
        }
    }
}