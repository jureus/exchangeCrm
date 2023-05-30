<?php

    function addToLog($text)
    {
        $path = $_SERVER['DOCUMENT_ROOT']."/exchangeCrm/log.txt";
        file_put_contents($path, PHP_EOL . $text, FILE_APPEND);
    }

    function getFileDataJson($url)
    {
        $data_file = file_get_contents($url);
        if($data_file){
            $arr = json_decode($data_file, true);
            return $arr;
        }else{
            return false;
        }
    }

    function writeJsonFile($arr, $url)
    {
        $data_file = file_get_contents($url);
        if($data_file){
            $arrTotlaJson = getFileDataJson($url);
                $arrTotlaJson[] = $arr;
        }else{
            $arrTotlaJson[] = $arr;
        }
        $newJsonfile = json_encode($arrTotlaJson, JSON_UNESCAPED_UNICODE);
        if(is_writable($url)){
            file_put_contents($url, $newJsonfile, LOCK_EX);
        }else{
            echo "Файл недостуен для записи";
        }  
    }

    function sendB24($url, array $fields = null)
    {
        if($fields){
          $postData = json_encode($fields, JSON_UNESCAPED_UNICODE);
        }
        
        $reqHeader = ['Content-Type: application/json'];
        //Запускаю сеанс передачи
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $reqHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if($fields){
          curl_setopt($curl, CURLOPT_POST, 1);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
  
        //Получаю ответ
        $response = curl_exec($curl);
        //Завершаю сеанс
        curl_close($curl);
        //Возвращаю результат функции
        //addToLog('Ответ от б24: '.$response);
        return json_decode($response); 
    }

    function sendSelantisCrm($url, $token, $fields = null)
    {
        //$login = null, $pwd = null
        //$token = base64_encode($login.':'.$pwd);
        //'Authorization: Basic <'.$token.'>'
        if(!$fields){
            $reqHeader = [
                'Connection: Keep-Alive',
                'Accept: application/json',
                'Authorization: Bearer '.$token
            ];
            
        }else{
            $postData = json_encode($fields, JSON_UNESCAPED_UNICODE);
            //addToLog('Данные: '.$postData);

            $reqHeader = [
                'Connection: Keep-Alive',
                'Accept: application/json',
                'Authorization: Bearer '.$token,
                'Content-Type: application/json; charset=utf-8/json',
                'Content-Length:'.strlen($postData)
            ];
            
        }
        $fp = null;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $reqHeader);
        if($fields && !array_key_exists('id', $fields)){
            //addToLog('Выбран метод POST');
            curl_setopt($ch, CURLOPT_POST, 1); //используем если поста запрос
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); //используем если пост запрос
        }elseif($fields && $fields['id']){
            addToLog('Выбран метод PUT');
            $fp = tmpfile();
            fwrite($fp, $postData);
            fseek($fp, 0);
            curl_setopt($ch, CURLOPT_PUT, 1); //используем если поста запрос
            curl_setopt($ch, CURLOPT_INFILE, $fp); //используем если пост запрос
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($postData)); //используем если пост запрос
        }else{
            //addToLog('Выбран метод GET');
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $res = curl_exec($ch);
        addToLog("Ответ selantis: ".$res);
        $data = json_decode($res);
        if($fp){
            fclose($fp);
        }
        curl_close($ch);
        
        return $data;
    }

    function makeArrayForSentCrmSelantis( $data, $status)
    {
        
        $fieldsNew = [
            'request_type_id'=> 267,
            'source_id' => 4788,
            'channel_id' => 1,
            'type' => 'lead',
            'may_process_personal_data' => true,
            'client_confirm_communication' => true,
            'executor_category_id' => 5,
            'first_name' => $data->name,
            'phone' => $data->phone
        ];
    

        switch ($data->STAGE_ID){
            case $status['Новая'] : $fieldsNew['status'] = 0; break;
            case $status['Взят в работу'] : $fieldsNew['status'] = 0; break;
            case $status['В работе'] : $fieldsNew['status'] = 0; break;
            case $status['Отказ'] : $fieldsNew['status'] = 0; break;
            case $status['Записан'] : $fieldsNew['status'] = 0; break;
            case $status['Принят'] : $fieldsNew['status'] = 0; break;
            case $status['Недозвон'] : $fieldsNew['status'] = 0; break;
            default: $fieldsNew['status'] = 0;
        }
        addToLog('Статус = '.$fieldsNew['status']);
        addToLog('ID сделки = '.$data->ID);
        
        $modelName = '';
        if($data->UF_CRM_1584889606847 && $data->UF_CRM_1584889620408 && $data->UF_CRM_1584889643877){
            $modelName = $data->UF_CRM_1584889606847.", ".
                        $data->UF_CRM_1584889620408.", ".
                        $data->UF_CRM_1584889643877;
            $fieldsNew['model_name'] = $modelName;
        }

        if($data->UF_CRM_1584889606847 && 
        $data->UF_CRM_1602367834 && 
        $data->UF_CRM_1602367875 &&
        $data->UF_CRM_1602367860 &&
        $data->UF_CRM_1602338731131){
            $comment = 'Причина обращения: '.
            $data->UF_CRM_1594811257076."; Автосервис: ".
            $data->UF_CRM_1602367834."; Адрес СТО: ".
            $data->UF_CRM_1602367875." ".
            $data->UF_CRM_1602367860."; Время записи: ".
            urldecode($data->UF_CRM_1602338731131);

            if($modelName){
                $comment = $comment ." Модель: ".$modelName;
                $fieldsNew['comment'] = $comment;
            }else{
                $fieldsNew['comment'] = $comment;
            }
        }

        //формирую ID  дилера
        if($data->dealer){
            $fieldsNew['dealer_id'] = $data->dealer;
        }
        addToLog('ID  диллера сформировано = '.$fieldsNew['dealer_id']);
        //формирую ID Бренда
        if($data->UF_CRM_1663760264107){
            switch ($data->UF_CRM_1663760264107) {
                case 1202 : $fieldsNew['brand_id'] = 4; break;
                case 1204 : $fieldsNew['brand_id'] = 5; break;
                case 1206 : $fieldsNew['brand_id'] = 1; break;
            }
        }
        //addToLog('ID Бренда сформировано = '.$fieldsNew['brand_id']);
        //формию время записи
        if($data->UF_CRM_1602338731131){
            $fieldsNew['credit_visit_datetime'] = urldecode($data->UF_CRM_1602338731131);

            $dateArr = explode(' ', urldecode($data->UF_CRM_1602338731131));
            $date = $dateArr[0];
            $arrDate = explode('.', urldecode($date));
            $timeArr = explode(':', $dateArr[1]);
            $time1 = $timeArr[0];
            $time2 = $time1 +1;
            $fieldsNew['service__serviceDateFrom'] = $arrDate[0].".".$arrDate[1].".20".$arrDate[2]." ".$time1.":".$timeArr[1];
            $fieldsNew['service__serviceDateTo'] = $arrDate[0].".".$arrDate[1].".20".$arrDate[2]." ". $time2. ":".$timeArr[1];
        }
         //addToLog('Время записи сформировано = '.$fieldsNew['credit_visit_datetime']);
         //addToLog('Время записи сформировано = '.$fieldsNew['service__serviceDateFrom']);
         //addToLog('Время записи сформировано = '.$fieldsNew['service__serviceDateTo']);

        //формирую ID кампании
        if($data->UF_CRM_1663565964839){
            switch ($data->UF_CRM_1663565964839) {
                case 1192 : $fieldsNew['campaign_id'] = 18; break;
                case 1194 : $fieldsNew['campaign_id'] = 21; break;
                case 1200 : $fieldsNew['campaign_id'] = 29; break;
                case 1232 : $fieldsNew['campaign_id'] = 33; break;
                case 1234 : $fieldsNew['campaign_id'] = 36; break;
                case 1236 : $fieldsNew['campaign_id'] = 39; break;
            }
        }
        // addToLog('ID campany b24 = '.$data->UF_CRM_1663565964839);
        // addToLog('ID campany сформировано = '.$fieldsNew['campaign_id']);
    
        if($data->UF_CRM_1594803660292){
            //$fieldsNew['timezone'] = $data->UF_CRM_1594803660292;
        }
        return $fieldsNew;
    }
?>
