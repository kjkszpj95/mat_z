<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
CModule::IncludeModule("iblock");
use Bitrix\Highloadblock as HL; 
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
class  Stocks {
    // id hl
    protected $hl=47;
    protected $hl_MV=48;
    protected $hl_STATUS=49;
    protected $hl_History=50;
    public $strocks_id;
    public $UF_USER_UPDATE;
    public $UF_COORDINATOR;
    public $UF_EXECUTOR;
    public $UF_USER_CREATED;
    public $UF_STASUS;
    public $UF_DISCLAIMER;
    public $UF_DATE_UPDATE;
    public $UF_DATE_CREATE;
    public $UF_NOTE;
    public $MATERIAL_VALUES;
    

    public function __construct($strocks_id= null) {
        $this->strocks_id =$strocks_id; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
$hlblock = HL\HighloadBlockTable::getById($this->hl)->fetch(); 
$entity = HL\HighloadBlockTable::compileEntity($hlblock); 
$entity_data = $entity->getDataClass(); 
$rsData = $entity_data::getList(array(
   "select" => array("*"),
   "order" => array("ID" => "ASC"),
   "filter" => array("ID"=>$strocks_id)  // Задаем параметры фильтра выборки
));
while($arData = $rsData->Fetch()){
    foreach ($arData as $key => $value) {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
    }

}
$hlblock_MV = HL\HighloadBlockTable::getById($this->hl_MV)->fetch(); 
$entity_MV = HL\HighloadBlockTable::compileEntity($hlblock_MV); 
$entity_data_MV = $entity_MV->getDataClass(); 
$rsData_MV = $entity_data_MV::getList(array(
   "select" => array("*"),
   "order" => array("ID" => "ASC"),
   "filter" => array("UF_APPLICATION"=>$strocks_id)  // Задаем параметры фильтра выборки
));
while($arData_MV = $rsData_MV->Fetch()){
    $this->MATERIAL_VALUES[] = $arData_MV;
}

}
    // Обращение  в hl, для получения данных пользователя заявки
     public function get_user_created(){
        $rsUser = CUser::GetByID($this->UF_USER_CREATED);
        $arUser = $rsUser->Fetch();
        return $arUser;
        }
  // Обращение  в hl, для получения данных исполнителя заявки
  public function get_user_executor(){
        $rsUser = CUser::GetByID($this->UF_EXECUTOR);
        $arUser = $rsUser->Fetch();
        return $arUser;
        
    // todo реализовать  для получения данных исполнителя заявки
        }
 // Обращение  в hl, для добавления исполнителя 
 public function set_user_executor($user_id){
    $hlblock = HL\HighloadBlockTable::getById($this->hl)->fetch(); 
    $entity = HL\HighloadBlockTable::compileEntity($hlblock); 
    $entity_data_class = $entity->getDataClass();
    $this->UF_EXECUTOR=$user_id;
       $data = array(
        "UF_EXECUTOR"=>$this->UF_EXECUTOR
       );
        $entity_data_class::update($this->strocks_id, $data);

    // todo реализовать  для получения данных исполнителя заявки

        }
// получение статуса заявки 
public function get_status(){
$hlblock = HL\HighloadBlockTable::getById($this->hl_STATUS)->fetch(); 
$entity = HL\HighloadBlockTable::compileEntity($hlblock); 
$entity_data = $entity->getDataClass(); 
$rsData = $entity_data::getList(array(
   "select" => array("*"),
   "order" => array("ID" => "ASC"),
   "filter" => array("ID"=>$this->UF_STASUS)  // Задаем параметры фильтра выборки
));

while($arData_status = $rsData->Fetch()){
        $UF_STASUS_detail = $arData_status;
}
    return  $UF_STASUS_detail;
    // todo реализовать получение статуса заявки 
}

public function create_status($status_id, $user_id){

    $hlblock = HL\HighloadBlockTable::getById($this->hl)->fetch(); 
    $entity = HL\HighloadBlockTable::compileEntity($hlblock); 
    $entity_data_class = $entity->getDataClass(); 
    $objDateTime = \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime("now"));
    $this->UF_DATE_UPDATE= $objDateTime;
       $data = array(
        "UF_STASUS"=>$status_id,
        "UF_DATE_UPDATE"=>$this->UF_DATE_UPDATE,
        "UF_USER_UPDATE"=>$user_id,
        "UF_DISCLAIMER"=>$this->UF_DISCLAIMER
       );
        $entity_data_class::update($this->strocks_id, $data);
        $this->create_history($user_id, $this->UF_STASUS, $status_id);
        $this->UF_USER_UPDATE= $user_id;
        }

protected function create_history($user_id, $status_before, $status_after){

    $hlblock_HISTORY = HL\HighloadBlockTable::getById($this->hl_History)->fetch(); 
    $entity_HISTORY= HL\HighloadBlockTable::compileEntity($hlblock_HISTORY); 
    $entity_data_class_HISTORY = $entity_HISTORY->getDataClass(); 
    $objDateTime = \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime("now"));
    $this->UF_DATE_UPDATE->setDate= $objDateTime;
    $data = array(
        "UF_DATE_UPDATE"=>$this->UF_DATE_UPDATE,
      "UF_USER_UPDATE"=>$user_id,
      "UF_STATUS_BEFORE"=>$status_before,
      "UF_STATUS_AFTER"=>$status_after,
      "UF_APPLICATION"=>$this->strocks_id,
     );
     $result = $entity_data_class_HISTORY::add($data);



    }

public function get_histiry(){

$hlblock_History = HL\HighloadBlockTable::getById($this->hl_History)->fetch(); 
$entity_HISTORY = HL\HighloadBlockTable::compileEntity($hlblock_History); 
$entity_data_class_HISTORY = $entity_HISTORY->getDataClass(); 
$rsData_HISTORY = $entity_data_class_HISTORY::getList(array(
   "select" => array("*"),
   "order" => array("ID" => "ASC"),
   "filter" => array("UF_APPLICATION"=>$this->strocks_id)  // Задаем параметры фильтра выборки
));
while($arData_HISTORY = $rsData_HISTORY->Fetch()){
    $HISTORY[] = $arData_HISTORY;
}

    return $HISTORY;

 }



public function create_material($id){
    $entity_MV = HL\HighloadBlockTable::compileEntity($this->hl_MV); 
    $entity_data_class_MV = $entity_MV->getDataClass();
    foreach($this->MATERIAL_VALUES as $key=> $item){
        $data = array(
            "UF_NAME"=>$item["UF_NAME"],
              "UF_MEASURE"=>$item["UF_MEASURE"],
              "UF_SUM"=>$item["UF_SUM"],
              "UF_APPLICATION"=>$id,
           );
           $id_MV = $entity_data_class_MV::add($data);
           $this->MATERIAL_VALUES[$key]["ID"]=$id_MV->getId();
    }
}

public function create_strock(){
$entity = HL\HighloadBlockTable::compileEntity($this->hl); 
$entity_data_class = $entity->getDataClass(); 
   // Массив полей для добавления
    // $MATERIAL_VALUES;
   $data = array(
    "UF_COORDINATOR"=>$this->UF_COORDINATOR,
      "UF_USER_CREATED"=>$this->UF_USER_CREATED,
      "UF_STASUS"=>$this->UF_STASUS,
      "UF_DATE_CREATE"=>$this->UF_DATE_CREATE,
      "UF_NOTE"=>$this->UF_NOTE
   );
   $id = $entity_data_class::add($data);
 $this->strocks_id=$id->getId();
  	$mv = $this->create_material($id->getId());

 }



    // присваиваются в другом эндпоинте 
    //"UF_EXECUTOR"=>date("d.m.Y")
   // "UF_DISCLAIMER"=>date("d.m.Y")
    //"UF_DATE_UPDATE"=>date("d.m.Y")
}
