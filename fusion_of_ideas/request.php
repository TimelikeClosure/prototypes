<?php

    $output = [
        "success" => null
    ];

    //  validate inputs

    //  connect to database
    $connection = new mysqli('url', 'username', 'password', 'database');
    if($connection->errno){   //  if the connection to database fails, respond to client with error
        $output['success'] = false;
        $output['error_msg'] = "(".$connection->errno.") ".$connection->error;
        echo json_encode($output);
        exit();
    }

    //  switch based upon request type
    switch($_POST['request_type']){
        case 'add_client':
            $query_type = "INSERT";
            $query = "INSERT INTO `clients`(`name`) VALUES ('{$_POST['client_name']}');";
            $id_label = 'client_id';
            break;
        case 'edit_client':
            $query_type = "UPDATE";
            $query = "UPDATE `clients` SET `name`='{$_POST['client_name']}';";
            break;
        case 'delete_client':
            $query_type = "DELETE";
            $query = "DELETE c,s,l
                      FROM `clients` AS c
                      LEFT JOIN `sections` AS s ON c.id=s.client_id
                      LEFT JOIN `links` AS l ON s.id=l.section_id
                      WHERE c.id={$_POST['client_id']};";
            break;
        case 'add_section':
            $query_type = "INSERT";
            $query = "INSERT INTO `sections`(`client_id`,`name`) VALUES ({$_POST['client_id']},'{$_POST['section_name']}');";
            $id_label = 'section_id';
            break;
        case 'edit_section':
            $query_type = "UPDATE";
            $query = "UPDATE `sections` SET `client_id`='{$_POST['client_id']}',`name`='{$_POST['section_name']}';";
            break;
        case 'delete_section':
            $query_type = "DELETE";
            $query = "DELETE FROM s,l
                      USING `sections` AS s JOIN `links` AS l ON s.id=l.section_id
                      WHERE s.id={$_POST['section_id']};";
            break;
        case 'add_link':
            $query_type = "INSERT";
            $query = "INSERT INTO `links`(`section_id`,`name`) VALUES ({$_POST['section_id']},'{$_POST['link_name']}');";
            $id_label = 'link_id';
            break;
        case 'edit_link':
            $query_type = "UPDATE";
            $query = "UPDATE `links` SET `section_id`={$_POST['section_id']},`name`='{$_POST['link_name']}';";
            break;
        case 'delete_link':
            $query_type = "DELETE";
            $query = "DELETE FROM `links` WHERE `id`={$_POST['link_id']};";
            break;
        default:
            $output['success'] = false;
            $output['error_msg'] = "Invalid request type";
            echo json_encode($output);
            exit();
    }

    //  send query to database
    $result = $connection->query($query);
    if(!$result){   //  if the query fails, respond to client with error
        $output['success'] = false;
        $output['error_msg'] = "(".$connection->errno.") ".$connection->error;
        echo json_encode($output);
        exit();
    }

    //  send response based upon results
    switch($query_type){
        case "INSERT":
            $output['success'] = true;
            $output[$id_label] = $connection->insert_id;
            break;
        case "UPDATE":

            break;
        case "DELETE":

            break;
    }
    echo json_encode($output);
?>