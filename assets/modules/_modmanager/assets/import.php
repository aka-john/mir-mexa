<?php
echo '<div style="text-align:center;color:blue;padding:10px;"><b>LOADING ...</b></div>';

$report_status = array(
    3 => 1,
    1 => 2,
    2 => 3,
);
$contract_status = array(
    1 => 0,
    3 => 1,
    2 => 3,
    4 => 2,
);
$contract_type_pdf_header = array(
    0 => 0,
    1 => 1,
    2 => 3,
    3 => 4,
    4 => 6,
    5 => 6,
);
$contract_type = array(
    0 => 'Не указано',//0
    1 => 'SEO',//3
    6 => 'Контекст',//3
    7 => 'SMM',//3
    8 => 'SERM',//3
    9 => 'Услуги в сети',//3
    2 => 'Сайт',//1
    3 => 'Хостинг',//4
    4 => 'Логотип',//6
    5 => 'Дизайн'//6
);
$contract_doc_type = array(
    0 => 1,
    1 => 2,
    2 => 3,
);
$client_org_type = array(
    10 => 0,
    0 => 1,
    1 => 2,
    2 => 3,
    3 => 4,
    4 => 5,
    5 => 6,
    6 => 7,
    7 => 8,
    8 => 9,
    9 => 10,
    11=> 11
);
$client_appointment = array(
    0 => 1,
    1 => 3,
    2 => 4
);
$client_type = array(
    0  => 'Не указано',
    5  => 'Другие услуги',
    1  => 'SEO/SMM/PPC',
    4  => 'Android/Ios',
    7  => 'Визитка/Блог',
    8  => 'Корпоративный',
    9  => 'Интернет-магазин',
    10 => 'Программирование',
    14 => 'Дизайн',
    15 => 'Форма контактов'
);
$client_status = array(
    0 => 1,
    1 => 2,
    2 => 1,
    4 => 1,
    5 => 1,
    6 => 1,
    7 => 1,
    8 => 1,
    9 => 1,
    3 => 1,
);
$client_statu_sale = array(
    0 => 1,
    1 => 0,
    2 => 2,
    4 => 3,
    5 => 4,
    6 => 5,
    7 => 6,
    8 => 7,
    9 => 8,
    3 => 9,
);
$sales_source = array(
    0 => 0,
    1 => 1,
    2 => 2,
    3 => 3,
    4 => 5,
    5 => 4
);
$sales_status = array(
    0 => 1,
    1 => 2,
    2 => 3,
    3 => 4,
    4 => 5,
    5 => 6,
    6 => 7,         
);
$sales_response = array(
    0 => 0,
    1 => 2,
    2 => 1
);
$bill_status = array(
    1 => 0,
    3 => 1,
    2 => 2,
);
$bill_type = array(
    0 => 'Не указано',
    5 => 'Другое',
    1 => 'SEO',
    2 => 'Сайт',
    3 => 'Хостинг',
    4 => 'Интернет услуги',
    6 => 'Поддержка',
    7 => 'ТЗ'
);

function getClientFoundation($client) 
{
    $foundation_org_type = array(
        0 => '-',
        1 => 'ФО',
        2 => 'ФОП',
        3 => 'ТОВ',
        4 => 'ПАТ',
        5 => 'АТ',
        6 => 'OOO',
        7 => 'OAO',
        8 => 'ЧАО',
        9 => 'ЧП',
        10 => 'НВП',
        11=> 'КС'
    );
    
    $org_type = $foundation_org_type[$client['client_org_type']];
    
    switch ($client['client_org_type']) {
        case 0://ФО
            return $client['client_lastname'].' '.$client['client_name'].' '.$client['client_middlename'];
            break;
        case 1://ФОП
            return $org_type.' '.$client['client_lastname'].' '.$client['client_name'].' '.$client['client_middlename'];
            break;
        case 2://ТОВ ПАТ АТ
        case 3:
        case 5:
        case 7:
        case 9:
        case 4:
            if ($client['client_id'] == 897) {
                return ' &mdash; Фінансовий директор '.$org_type.' &laquo;'.$client['client_org_name'].'&raquo; '.$client['client_lastname'].' '.$client['client_name'].' '.$client['client_middlename'];
            }
            return ' &mdash; Директор '.$org_type.' &laquo;'.$client['client_org_name'].'&raquo; '.$client['client_lastname'].' '.$client['client_name'].' '.$client['client_middlename'];
            break;
        case 10:
            return ' &mdash; Директор &laquo;'.$client['client_org_name'].'&raquo; '.$client['client_lastname'].' '.$client['client_name'].' '.$client['client_middlename'];
            break;
        case 6:
            return ' &mdash; Председатель правления '.$org_type.' &laquo;'.$client['client_org_name'].'&raquo; '.$client['client_lastname'].' '.$client['client_name'].' '.$client['client_middlename'];
        break;
        case 8://ЧП
        default:
            return $org_type.' &laquo;'.$client['client_org_name'].'&raquo;';
            break;
    }
}

function generateString($number) {
    $arr = array(
        'a','b','c','d','e','f',
        'g','h','i','j','k','l',
        'm','n','o','p','r','s',
        't','u','v','x','y','z',
        'A','B','C','D','E','F',
        'G','H','I','J','K','L',
        'M','N','O','P','R','S',
        'T','U','V','X','Y','Z',
        '1','2','3','4','5','6',
        '7','8','9','0'
    );
    $pass = "";
    for($i = 0; $i < $number; $i++) {
        $index = rand(0, count($arr) - 1);
        $pass .= $arr[$index];
    }
    return $pass;
}

$salt = 'kDGgH2aicBJ5OphnwM0Mz4H3RpvjVS';

$db = @mysql_connect('localhost' , 'langaner', '19880311');
mysql_query("SET NAMES utf8");
mysql_select_db('crmrebuild', $db);

/* ====================================================================== МЕНЕДЖЕРЫ  ======================================================================*/

/*$sql = "SELECT 
            m.*,
            a.*
        FROM modx_manager_users m 
        JOIN modx_user_attributes a ON a.internalKey = m.id";
$query = mysql_query($sql);

if ($query) {
    while ($row = mysql_fetch_assoc($query)) {
        $sql_user = "INSERT INTO art_manager 
                    (old_id, login, name, password, email, phone, image, created_at, role_id, group_id, comment, gender, blocked, icq, skype, mail_subscribe, menu_type) 
                        VALUES (
                            '".$row['internalKey']."', 
                            '".$row['username']."', 
                            '".$row['fullname']."',
                            AES_ENCRYPT('".generateString(10)."','".$salt."'),
                            '".$row['email']."',
                            '".$row['phone']."',
                            '".$row['photo']."',
                            '".date('Y-m-d H:i:s', time())."',
                            '1',
                            '1',
                            '".$row['comment']."',
                            '".($row['gender'] == 0 ? 1 : 2)."',
                            '0',
                            '',
                            '',
                            '".$row['sign']."',
                            '3'
                    )";
        $query_user = mysql_query($sql_user);
        $id_user = mysql_insert_id();
    }
}*/

/* ====================================================================== КЛИЕНТ/ПРОЕКТЫ/SALE ====================================================================== */

/*$sql = "SELECT * FROM modx_crm_clients";
$query = mysql_query($sql);

if ($query) {
    while ($row = mysql_fetch_assoc($query)) {
        $manager_id = array();
        if ($row['manager_id'] != '') {
            $exp = explode(',', $row['manager_id']);
            foreach ($exp as $key => $value) {
                $manager_id[] = preg_replace('![^\w\d\s]*!','',$value);
            }
        }
        
        $first_manager = 0;
        if (count($manager_id) > 0) {
            $sql_new_manager = "SELECT * FROM art_manager WHERE old_id = ".$manager_id[0];
            $query_new_manager = mysql_query($sql_new_manager); 
            $result = mysql_fetch_assoc($query_new_manager);
            $first_manager = $result['id'];
        }
        
        //sales
        $sql_sale = "INSERT INTO art_sale 
                    (old_id ,created_at, project, cooperation, source, fast_response, budget, timeline, name, phone, email, status, client_status, created_by) 
                        VALUES (
                            '".$row['client_id']."', 
                            '".date('Y-m-d H:i:s', strtotime($row['client_enter_date']))."', 
                            '".$row['client_project']."', 
                            '".$row['cooperation']."', 
                            '".$sales_source[$row['client_source']]."', 
                            '".($row['client_fast_response'] == 0 ? 1 : 2)."', 
                            '".$row['client_budget']."', 
                            '".$row['client_period']."', 
                            '".$row['client_name']."', 
                            '".$row['client_phone']."', 
                            '".$row['client_email']."', 
                            '".$sales_status[$row['client_sales_status']]."', 
                            '".$client_statu_sale[$row['client_status']]."', 
                            '".$first_manager."'
                    )";
        $query_sale = mysql_query($sql_sale);
        $id_sale = mysql_insert_id();

        //если клиент активен то вносим его как нового клиента
        if ($row['client_status'] == 3 && $id_sale) {
            $sql_client = "INSERT INTO art_client 
                        (old_id, name, lastname, middlename, email, created_at, activity, info, city, status, org_type, appointment, org_name, foundation, sale_id) 
                            VALUES (
                                '".$row['client_id']."', 
                                '".$row['client_name']."', 
                                '".$row['client_lastname']."', 
                                '".$row['client_middlename']."', 
                                '".$row['client_email']."', 
                                '".date('Y-m-d H:i:s', strtotime($row['client_open_date']))."', 
                                '".$row['client_activity']."', 
                                '".$row['client_info']."', 
                                '".$row['client_city']."', 
                                '".$client_status[$row['client_status']]."', 
                                '".$client_org_type[$row['client_org_type']]."', 
                                '".$client_appointment[$row['client_appointment']]."', 
                                '".$row['client_org_name']."',
                                '".getClientFoundation($row)."',
                                '".$id_sale."'
                        )";
            $query_client = mysql_query($sql_client);
            $id = mysql_insert_id();
        } else {//иначе оставляем его в таблице sales и завершаем итерацию
            continue;
        }
        
        //менеджеры клиента
        if ($row['client_data'] != '') {
            $data = json_decode($row['client_data'], true);
            if (count($data) > 0) {
                foreach ($data as $key => $value) {
                    $sql_manager = "INSERT INTO art_client_manager 
                        (name, email, phone, skype, client_id) 
                            VALUES (
                                '".$value['client_manager_name']."', 
                                '".$value['client_manager_email']."', 
                                '".$value['client_manager_phone']."', 
                                '".$value['client_manager_skype']."',
                                '".$id."'
                        )";
                    $query_manager = mysql_query($sql_manager);
                }
            }
        }
        
        //реквизиты
        if ($row['client_requisites'] != '') {
            $sql_requisite = "INSERT INTO art_client_detail_requisite 
                (name, requisite, client_id) 
                    VALUES (
                        '".$row['client_name']."_requisite', 
                        '".$row['client_requisites']."',
                        '".$id."'
                )";
            $query_requisite = mysql_query($sql_requisite);
        }
        
        //адрес
        if ($row['client_address'] != '') {
            $sql_requisite = "INSERT INTO art_client_detail_address 
                (name, address, client_id) 
                    VALUES (
                        '".$row['client_name']."_address', 
                        '".$row['client_address']."',
                        '".$id."'
                )";
            $query_requisite = mysql_query($sql_requisite);
        }
        
        //проект
        $sql_project = "INSERT INTO art_project 
                    (old_id ,name, created_at, closed_at, domain, budget, timeline, status, client_id) 
                        VALUES (
                            '".$row['client_id']."', 
                            '".$row['client_project']."', 
                            '".date('Y-m-d H:i:s', strtotime($row['client_open_date']))."', 
                            '".date('Y-m-d H:i:s', strtotime($row['client_close_date']))."', 
                            '".$row['client_domain']."', 
                            '".$row['client_budget']."', 
                            '".$row['client_period']."', 
                            '1', 
                            '".$id."'
                    )";
        $query_project = mysql_query($sql_project);
        $id_project = mysql_insert_id();
        
        //линковка менеджеров с проектом
        if (count($manager_id) > 0) {
            foreach ($manager_id as $key => $value) {
                $sql_new_manager = "SELECT * FROM art_manager WHERE old_id = ".$value;
                $query_new_manager = mysql_query($sql_new_manager); 
                if ($query_new_manager) {
                    $result = mysql_fetch_assoc($query_new_manager);
                } else {
                    $result['id'] = 0;
                }
                
                $sql_p_manager = "INSERT INTO art_manager_to_project 
                            (project_id, manager_id) 
                                VALUES (
                                    '".$id_project."', 
                                    '".$result['id']."' 
                            )";
                $query_p_manager = mysql_query($sql_p_manager);
                $id_p_manager = mysql_insert_id();
            }
        }
    }
}*/

/* ====================================================================== ДОГОВОРЫ ====================================================================== */

/*$sql = "SELECT 
            c.*
        FROM modx_crm_contract c";
$query = mysql_query($sql);

if ($query) {
    while ($row = mysql_fetch_assoc($query)) {
        
        if ($row['manager_id'] != '') {
            $sql_new_manager = "SELECT * FROM art_manager WHERE old_id = ".$row['manager_id'];
            $query_new_manager = mysql_query($sql_new_manager); 
            $result = mysql_fetch_assoc($query_new_manager);
            $row['manager_id'] = $result['id'];
        }
        
        if ($row['client_id'] != '') {
            $sql_new_client = "SELECT * FROM art_client WHERE old_id = ".$row['client_id'];
            $query_new_client = mysql_query($sql_new_client); 
            $result = mysql_fetch_assoc($query_new_client);
            $row['client_id'] = $result['id'];
        }
        
        if ($row['client_id'] != '') {
            $sql_new_client = "SELECT * FROM art_client_detail_requisite WHERE client_id = ".$row['client_id']." LIMIT 1";
            $query_new_client = mysql_query($sql_new_client); 
            $result = mysql_fetch_assoc($query_new_client);
            $row['client_requisit_id'] = $result['id'];
        }
        
        if ($row['client_id'] != '') {
            $sql_new_client = "SELECT id FROM art_project WHERE client_id = ".$row['client_id']." LIMIT 1";
            $query_new_client = mysql_query($sql_new_client); 
            $result = mysql_fetch_assoc($query_new_client);
            $row['project_id'] = $result['id'];
        }
        
        $client_manager_id = 0;
        if ($row['contract_data'] != '') {
            $contract_data = json_decode($row['contract_data'], true);
            if ($contract_data[1] != '') {//поиск ответственного лица у клиента
                $sql_find_manager = "SELECT id, count(*) as cnt FROM art_client_manager WHERE name LIKE '%".$contract_data[1]."%' AND client_id = ".$row['client_id'];
                $query_find_manager = mysql_query($sql_find_manager); 
                $result = mysql_fetch_assoc($query_find_manager);
                
                if ($result['cnt'] > 0) {//если найден
                    $client_manager_id = $result['id'];
                } else {//если нет то заводим нового
                    $sql_client_manager = "INSERT INTO art_client_manager 
                        (name, client_id) 
                            VALUES (
                                '".$contract_data[1]."', 
                                '".$row['client_id']."'
                        )";
                    $query_client_manager = mysql_query($sql_client_manager);
                    $client_manager_id = mysql_insert_id();
                } 
            }  
        }

        //ЗАПОЛНИТЬ ТИП ДОГОВОРА !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $sql_contract = "INSERT INTO art_contract 
                    (old_id ,created_at, closed_at, timeline, status, cooperation, supplier_id, manager_id, created_by, budget, submis_material, client_manager_id, client_requisit_id, project_id) 
                        VALUES (
                            '".$row['contract_id']."', 
                            '".date('Y-m-d H:i:s', strtotime($row['contract_open_date']))."', 
                            '".date('Y-m-d H:i:s', strtotime($row['contract_close_date']))."', 
                            '".$row['contract_period']."', 
                            '".$contract_status[$row['contract_status']]."', 
                            '', 
                            '".$row['contract_tax']."', 
                            '".$row['manager_id']."', 
                            '".$row['manager_id']."', 
                            '".$row['contract_summ']."', 
                            '".$data[0]."', 
                            '".$client_manager_id."', 
                            '".$row['client_requisit_id']."', 
                            '".$row['project_id']."' 
                    )";
        $query_contract = mysql_query($sql_contract);
        $id_contract = mysql_insert_id();
        
        //этапы
        if ($row['contract_data'] != '') {
            //В КАКОМ ТО СЛУЧАЕ СРОКОВ НЕТ, ЕСТЬ ТОЛЬКО ЦЕНА В УЕ !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $contract_data = json_decode($row['contract_data'], true);
            if (count($contract_data[2]) > 0) {
                foreach ($contract_data[2] as $key => $value) {
                    $sql_contract_stage = "INSERT INTO art_contract_stages 
                        (name, timeline, budget, contract_id) 
                            VALUES (
                                '".$value[0]."', 
                                '".$value[1]."',
                                '".$value[2]."',
                                '".$id_contract."'
                        )";
                    $query_contract_stage = mysql_query($sql_contract_stage);
                }
            }
        }
    }
}*/

/* ====================================================================== ДОКУМЕНТЫ ДОГОВОРОВ ====================================================================== */

/*$sql = "SELECT 
            c.*
        FROM modx_crm_contract_upload c";
$query = mysql_query($sql);

if ($query) {
    while ($row = mysql_fetch_assoc($query)) {
        
        if ($row['manager_id'] != '') {
            $sql_new_manager = "SELECT * FROM art_manager WHERE old_id = ".$row['manager_id'];
            $query_new_manager = mysql_query($sql_new_manager); 
            $result = mysql_fetch_assoc($query_new_manager);
            $row['manager_id'] = $result['id'];
        }
        
        if ($row['contract_id'] != '') {
            $sql_new_manager = "SELECT * FROM art_contract WHERE old_id = ".$row['contract_id'];
            $query_new_manager = mysql_query($sql_new_manager); 
            $result = mysql_fetch_assoc($query_new_manager);
            $row['contract_id'] = $result['id'];
        }
        
        $sql_contract_doc = "INSERT INTO art_contract_docs 
                    (name, file, contract_id, manager_id, type, upload_at) 
                        VALUES (
                            '".$row['upload_name']."', 
                            '".$row['upload_file']."', 
                            '".$row['contract_id']."', 
                            '".$row['manager_id']."', 
                            '".$contract_doc_type[$row['upload_type']]."', 
                            '".date('Y-m-d H:i:s', strtotime($row['upload_date']))."' 
                    )";
        $query_contract_doc = mysql_query($sql_contract_doc);
        $id_contract_doc = mysql_insert_id();
          
        //НУЖНО ПЕРЕНОСИТЬ ФАЙЛЫ В ПАПКИ assets/files/contract/contract_id

    }
}*/

/* ====================================================================== СЧЕТА/АКТЫ ====================================================================== */

/*$sql = "SELECT 
            c.*
        FROM modx_crm_bills c";
$query = mysql_query($sql);

if ($query) {
    while ($row = mysql_fetch_assoc($query)) {

        //ЗАПОЛНИТЬ ТИП CЧЕТА !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $sql_bill = "INSERT INTO art_bill 
                    (old_id, number, created_at, payed_at, closed_at, status, type, appointment, contract_id, created_by, manager_id, specialist_id) 
                        VALUES (
                            '".$row['bill_id']."', 
                            '".$row['bill_number']."', 
                            '".date('Y-m-d H:i:s', strtotime($row['bill_date']))."', 
                            '".date('Y-m-d H:i:s', strtotime($row['bill_date_pay']))."', 
                            '', 
                            '".$bill_status[$row['bill_status']]."', 
                            '', 
                            '".$row['bill_appointment']."', 
                            '".$row['contract_id']."', 
                            '".$row['manager_id']."', 
                            '".$row['manager_id']."', 
                            ''
                    )";
        $query_bill = mysql_query($sql_bill);
        $id_bill = mysql_insert_id();
        
        if ($row['bill_data'] != '') {
            $data = json_decode($row['bill_data'], true);
            if (count($data) > 0) {
                foreach ($data as $key => $value) {
                    $sql_bill_structure = "INSERT INTO art_bill_structure 
                        (name, quantity, price, usd_course, commerce, bill_id) 
                            VALUES (
                                '".$value['service_name']."', 
                                '".$value['service_quantity']."', 
                                '".$value['service_summ']."', 
                                '".$value['service_course']."',
                                '".$value['service_commerce']."',
                                '".$id_bill."'
                        )";
                    $query_bill_structur = mysql_query($sql_bill_structure);
                }
            }
        }
        
        $sql_report = "INSERT INTO art_report 
                    (old_id, created_at, closed_at, payed_at, status, bill_id, created_by, client_requisit_id, exclude_contract) 
                        VALUES (
                            '".$id_bill."', 
                            '".date('Y-m-d H:i:s', strtotime($row['bill_date']))."', 
                            '".date('Y-m-d H:i:s', strtotime($row['report_date_close']))."', 
                            '', 
                            '".$report_status[$row['report_status']]."', 
                            '".$id_bill."', 
                            '".$row['manager_id']."', 
                            '', 
                            '".$row['manager_id']."'
                    )";
        $query_report = mysql_query($sql_report);
        $id_report = mysql_insert_id();
        
    }
}*/

echo '<div style="text-align:center;color:red;padding:10px;"><b>IMPORT FROM CRM DB COMPLETED</b></div>';