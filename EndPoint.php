<?php

//получение всех заявок 
//Сортировка, пагинация, фильтрация 
function get_all($limit, $ofset, $order, $ofset_key, $flter_key, $filter_value){
    $hl=47;
    $hlblock = HL\HighloadBlockTable::getById($hl)->fetch(); 
    $entity = HL\HighloadBlockTable::compileEntity($hlblock); 
    $entity_data = $entity->getDataClass(); 
    $rsData = $entity_data::getList(array(
       "select" => array("*"),
        "order" => array("ID" => "ASC"),
        "limit" => 10, // Ограничение на количество записей
        "offset" => 0, // Смещение для пагинации
        "filter" => array($flter_key =>$filter_value)  // Задаем параметры фильтра выборки

    ));
    while($arData = $rsData->Fetch()){
       $mass_ID[]= $arData["ID"];
    }
    
    foreach($mass_ID as $item){
        $item_objeckt = new Stocks($item["ID"]);
        $mass_objeckt[]= $item_objeckt;
    }
        return $mass_objeckt;
    }

//получение одной заявки
function get_detail($id){
    $hl=47;
    $hlblock = HL\HighloadBlockTable::getById($hl)->fetch(); 
    $entity = HL\HighloadBlockTable::compileEntity($hlblock); 
    $entity_data = $entity->getDataClass(); 
    $rsData = $entity_data::getList(array(
       "select" => array("*"),
        "order" => array("ID" => "ASC"),
        "limit" => 10, // Ограничение на количество записей
        "offset" => 0, // Смещение для пагинации
        "filter" => array("ID"=>$id)  // Задаем параметры фильтра выборки
    ));
    while($arData = $rsData->Fetch()){
        $item_objeckt = new Stocks($arData["ID"]);
     }
     return $item_objeckt;

}

// создание заявки
function post_create($_POST){

    
}
// изменение заявки
function post_update($id, $_POST){



}
// Получение истории статусов по одной заявке 
//Сортировка, пагинация, фильтрация 
function get_history($id){


}

//получение всех заявкок согласующего 
//Сортировка, пагинация, фильтрация 
function get_coordinator($user_id){

}
//получение всех созданых заявок человека
//Сортировка, пагинация, фильтрация 
function get_creater($user_id){


}
//получение всех заявок исполнителя
//Сортировка, пагинация, фильтрация 
function get_executor($user_id){

}



