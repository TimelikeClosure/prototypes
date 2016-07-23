<?php
    /**
     * Notes:
     *
     * 1.   Input received as POST variables
     * 1.   Output returned as JSON object
     * 1.   id fields assumed to be unsigned integers (anywhere from tinyint to bigint) and cannot be null
     * 1.   name fields assumed to be strings and cannot be empty
     * 1.   Not sure if security is need for this. Added POST input validation, but not mysqli prepared statements
     * 1.   Not sure if primary keys can be edited by client. Assumed they cannot.
     * 1.   Not sure if foreign keys can be edited by client. Assumed they can.
     */


    /**
     * errorResponse - returns the given error message to the client and halts execution
     * @param {string} $error_msg
     */
    function errorResponse($error_msg){
        $output = [
            'success' => false,
            'error_msg' => $error_msg
        ];
        echo json_encode($output);
        exit();
    }

    //  validate inputs
    $filteredPOST = [];
    $filteredPOST['request_type'] = filter_var($_POST['request_type'], FILTER_SANITIZE_STRING);
    if (empty($filteredPOST['request_type'])){
        errorResponse("Invalid request type");
    }

    //  connect to database
    require('connect.php'); //  file (untracked by git) containing database connection information
    $connection = new mysqli($database['url'], $database['username'], $database['password'], $database['db_name']);
    if($connection->errno){   //  if the connection to database fails, respond to client with error
        errorResponse("(".$connection->errno.") ".$connection->error);
    }

    //  switch based upon request type
    switch($filteredPOST['request_type']){
        //  add a new client - QA
        case 'add_client':
            //  filter input variables
            $filteredPOST['client_name'] = filter_var($_POST['client_name'], FILTER_SANITIZE_STRING);
            if (empty($filteredPOST['client_name'])){
                errorResponse('Invalid client name');
            }
            //  create query
            $query_type = "INSERT";
            $query = "INSERT INTO `clients`(`name`) VALUES ('{$filteredPOST['client_name']}');";   //  done
            $id_key_label = 'client_id';
            break;
        //  edits the name of the client with the given id - QA
        case 'edit_client':
            //  filter input variables
            $filteredPOST['client_id'] = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['client_id']) && $filteredPOST['client_id'] !== 0){
                errorResponse('Invalid client id');
            }
            //  create query
            $query_type = "UPDATE";
            $query = "UPDATE `clients` SET `name`='{$filteredPOST['client_id']}';";
            break;
        //  delete a client, the client's sections, and all links attached to those sections - QA
        case 'delete_client':
            //  filter input variables
            $filteredPOST['client_id'] = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['client_id']) && $filteredPOST['client_id'] !== 0){
                errorResponse('Invalid client id');
            }
            //  create query
            $query_type = "DELETE";
            $query = "DELETE c,s,l
                      FROM `clients` AS c
                      LEFT JOIN `sections` AS s ON c.id=s.client_id
                      LEFT JOIN `links` AS l ON s.id=l.section_id
                      WHERE c.id={$filteredPOST['client_id']};";
            break;
        //  add a section to the client with the given client id, if valid - QA
        case 'add_section':
            //  filter input variables
            $filteredPOST['client_id'] = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['client_id']) && $filteredPOST['client_id'] !== 0){
                errorResponse('Invalid client id');
            }
            $filteredPOST['section_name'] = filter_var($_POST['section_name'], FILTER_SANITIZE_STRING);
            if (empty($filteredPOST['section_name'])){
                errorResponse('Invalid section name');
            }
            //  create query
            $query_type = "INSERT";
            $query = "INSERT INTO `sections`(`name`,`client_id`)
                      VALUES (
                        '{$filteredPOST['section_name']}',
                        (SELECT `id` FROM `clients` WHERE `id`={$filteredPOST['client_id']})
                      );";  //  SELECT statement returns null if given client id doesn't exist, causing query to fail
            $id_key_label = 'section_id';
            break;
        //  edits section name and client id - QA
        case 'edit_section':
            //  filter input variables
            $filteredPOST['section_name'] = filter_var($_POST['section_name'], FILTER_SANITIZE_STRING);
            $filteredPOST['client_id'] = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['section_name']) && empty($filteredPOST['client_id']) && $filteredPOST['client_id'] !== 0){
                errorResponse('No valid section name or client id provided to update section');
            }
            //  create query
            $query_type = "UPDATE";
            $query = "UPDATE `sections` SET ";
            if (!empty($filteredPOST['section_name'])){
                $query .= "`name`='{$filteredPOST['section_name']}'";
            }
            if (!empty($filteredPOST['client_id']) || $filteredPOST['client_id'] === 0){
                if (!empty($filteredPOST['section_name'])){
                    $query .= ",";
                }
                $query .= "`client_id`=(SELECT `id` FROM `clients` WHERE `id`={$filteredPOST['client_id']})";
            }
            $query .= ";";
            break;
        //  deletes a section and all the section's links - QA
        case 'delete_section':
            //  filter input variables
            $filteredPOST['section_id'] = filter_var($_POST['section_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['section_id']) && $filteredPOST['section_id'] !== 0){
                errorResponse('Invalid section id');
            }
            //  create query
            $query_type = "DELETE";
            $query = "DELETE s,l
                      FROM `sections` AS s
                      LEFT JOIN `links` AS l ON s.id=l.section_id
                      WHERE s.id={$filteredPOST['section_id']};";
            break;
        //  add a link to the section with the given section id, if valid
        case 'add_link':
            //  filter input variables
            $filteredPOST['section_id'] = filter_var($_POST['section_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['section_id']) && $filteredPOST['section_id'] !== 0){
                errorResponse('Invalid section id');
            }
            $filteredPOST['link_name'] = filter_var($_POST['link_name'], FILTER_SANITIZE_STRING);
            if (empty($filteredPOST['link_name'])){
                errorResponse('Invalid link name');
            }
            //  create query
            $query_type = "INSERT";
            $query = "INSERT INTO `links`(`section_id`,`name`)
                      VALUES
                      (
                        '{$filteredPOST['link_name']}',
                        (SELECT `id` FROM `sections` WHERE `id`={$filteredPOST['section_id']})
                      );";  //  SELECT statement returns null if given section id doesn't exist, causing query to fail
            $id_key_label = 'link_id';
            break;
        //  edits link name and section_id - QA
        case 'edit_link':
            //  filter input variables
            $filteredPOST['link_name'] = filter_var($_POST['link_name'], FILTER_SANITIZE_STRING);
            $filteredPOST['section_id'] = filter_var($_POST['section_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['link_name']) && empty($filteredPOST['section_id']) && $filteredPOST['section_id'] !== 0){
                errorResponse('No valid link name or section id provided to update link');
            }
            //  create query
            $query_type = "UPDATE";
            $query = "UPDATE `links` SET ";
            if (!empty($filteredPOST['link_name'])){
                $query .= "`name`='{$filteredPOST['link_name']}'";
            }
            if (!empty($filteredPOST['section_id']) || $filteredPOST['section_id'] === 0){
                if (!empty($filteredPOST['link_name'])){
                    $query .= ",";
                }
                $query .= "`section_id`=(SELECT `id` FROM `sections` WHERE `id`={$filteredPOST['section_id']})";
            }
            $query .= ";";
            break;
        //  deletes a link - QA
        case 'delete_link':
            //  filter input variables
            $filteredPOST['link_id'] = filter_var($_POST['link_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['link_id']) && $filteredPOST['link_id'] !== 0){
                errorResponse('Invalid link id');
            }
            //  create query
            $query_type = "DELETE";
            $query = "DELETE FROM `links` WHERE `id`={$filteredPOST['link_id']};";
            break;
        //  invalid request
        default:
            errorResponse("Invalid request type");
    }

    //  send query to database
    $result = $connection->query($query);
    if(empty($result)){   //  if the query fails, respond to client with error
        errorResponse("(".$connection->errno.") ".$connection->error);
    }

    //  send response based upon results
    $output = ["success" => true];
    switch($query_type){
        case "INSERT":
            $output['affected_rows'] = $connection->affected_rows;
            $output[$id_key_label] = $connection->insert_id;
            break;
        case "UPDATE":
            $output['affected_rows'] = $connection->affected_rows;
            break;
        case "DELETE":
            $output['affected_rows'] = $connection->affected_rows;
            break;
    }
    $connection->close();
    echo json_encode($output);
?>