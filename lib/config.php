<?php
    const DEAL_STATUS_B25 = [
        'Новая' => 'C28:UC_K10RHK',
        'Недозвон' => 'C28:PREPARATION',
        'Взят в работу' => 'C28:PREPAYMENT_INVOICE',
        'В работе' => 'C28:EXECUTING',
        'Записан' => 'C28:FINAL_INVOICE',
        'Принят' => 'C28:UC_SJXVNA',
        'Отказ' => 'C28:LOSE',
    ];
    const DEAL_STATUS_B24 = [
        'Новая' => 'C16:NEW',
        'Недозвон' => 'C16:PREPARATION',
        'Взят в работу' => 'C16:PREPAYMENT_INVOIC',
        'В работе' => 'C16:PREPAYMENT_INVOIC',
        'Записан' => 'C16:EXECUTING',
        'Принят' => 'C16:2',
        'Отказ' => 'C16:APOLOGY',
        'Сделка провалена' => 'C16:LOSE',
        'Не вышел на связь' => 'C16:7',
        'Не удалось подобрать СТО' => 'C16:8',
        'База' => 'C16:9',
        'Дубль' => 'C16:10',
        'Сделка успешна' => 'C16:WON',
        'В городе нет СТО' => 'C16:UC_J6UOSA',
        'Не верный номер' => 'C16:UC_PQ3F9B',
    ];


    const HOOK_SELLANTIS_TEST = 'https://domain-test.autocrm.ru/api';

    const API_LOGIN_SELLANTIS = 'name@gmail.com';
    const API_PSW_SELLANTIS = 'password';
    
    const HOOK_B24 = 'https://domain.bitrix24.ru/rest/3504/f6uh1y6s1bchzwvx/';  //вебхук из Б24
    
    const HOOK_SELLANTIS = 'https://domain.autocrm.ru/api';
    const TOKEN = ''; //токен от autocrm.ru
    
    

    const METHOD_B24 = [
        'get_deal' => 'crm.deal.get',
        'deal_update' => 'crm.deal.update'
    ];

    const METHOD_SELLANTIS = [
        'interest' => 'lms/interest'
    ];

?>