<?php
require $_SERVER['DOCUMENT_ROOT']."/exchangeCrm/lib/config.php";
require $_SERVER['DOCUMENT_ROOT']."/exchangeCrm/function.php";

$pathFileJson = $_SERVER['DOCUMENT_ROOT']."/data.json";
if(array_key_exists('id', $_GET) && array_key_exists('name', $_GET))
{
    //addToLog('Началась обработка');
    $param = 'id='.$_GET['id'];
    $url = HOOK_B24.''.METHOD_B24['get_deal'].'?'.$param;
    $data = sendB24($url)->result;
    //addToLog('Данные по ID пользователю:'.$data->UF_CRM_1602367156);
    $url2 = HOOK_B24.'user.get?id='.$data->UF_CRM_1602367156;
    $userData = sendB24($url2)->result;
    //addToLog('Данные по пользователю: '.json_encode($userData, JSON_UNESCAPED_UNICODE));
    //addToLog('Получили всю инфо по передаваемой сделке');
}

//пдготовка данных и ссылки для отправки
$data->name = $_GET['name'];
$data->phone = $_GET['phone'];

// addToLog('ID бренда '.$data->UF_CRM_1663760264107);
// addToLog('код STO OPEL '.$userData[0]->UF_USR_1663760067607);
// addToLog('код STO Пежо '.$userData[0]->UF_USR_1663566068304);
// addToLog('код STO Citoen '.$userData[0]->UF_USR_1663760046752);

//получаю данные по дилеру
switch ($data->UF_CRM_1663760264107) {
    case 1202 : $data->dealer = $userData[0]->UF_USR_1663566068304; break;
    case 1204 : $data->dealer = $userData[0]->UF_USR_1663760046752; break;
    case 1206 : $data->dealer = $userData[0]->UF_USR_1663760067607; break;
}

$fieldsNewInterest = makeArrayForSentCrmSelantis($data, DEAL_STATUS_B24);

addToLog(date("Y-m-d H:i:s"));

$urlSelantis = HOOK_SELLANTIS."/".METHOD_SELLANTIS['interest'];

if(!$data->UF_CRM_1662575437){
    //Первый раз отправляем в sellantis
    //addToLog('Сделка первый раз уходит'); 
      
    $res = sendSelantisCrm($urlSelantis, TOKEN, $fieldsNewInterest);
    
    //отправляем ID созданной сущности в б24

    $fieldB24 = [
        'id' => $_GET['id'],
        'fields' => [
            'UF_CRM_1662575437' => $res->id
        ]
    ];
    $urlB24 = HOOK_B24.''.METHOD_B24['deal_update'];
    addToLog('Записываем ID обращения в сделку. ID='.$res->id);    
    $res2 = sendB24($urlB24, $fieldB24);
}else{
    //Обновление данных в sellantis
    $fieldsNewInterest['id'] = $data->UF_CRM_1662575437;
    //addToLog('Передаем изменения в ID='.$fieldsNewInterest['id']); 
    $urlSelantis = HOOK_SELLANTIS."/".METHOD_SELLANTIS['interest']."/".$fieldsNewInterest['id'];
    //addToLog('Передаем изменения в URL='.$urlSelantis);
    $res = sendSelantisCrm($urlSelantis, TOKEN, $fieldsNewInterest);
}
?>