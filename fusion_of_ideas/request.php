<?php
    /**
     * Notes:
     *
     * 1.   Input received as POST variables
     * 2.   Output returned as JSON object
     * 3.   id fields assumed to be unsigned integers (anywhere from tinyint to bigint) and cannot be null
     * 4.   name fields assumed to be strings and cannot be empty
     * 5.   Not sure if security is need for this. Added POST input validation, but not mysqli prepared statements
     * 6.   Not sure if primary keys can be edited by client. Assumed they cannot.
     * 7.   Not sure if foreign keys can be edited by client. Assumed they can.
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

    //  Validate request type input
    $filteredPOST = [];
    $filteredPOST['request_type'] = filter_var($_POST['request_type'], FILTER_SANITIZE_STRING);
    if (empty($filteredPOST['request_type'])){
        errorResponse("Invalid request type");
    }

    //  Connect to database
    require('connect.php'); //  file (untracked by git) containing database connection information
    $connection = new mysqli($database['url'], $database['username'], $database['password'], $database['db_name']);
    if($connection->errno){   //  if the connection to database fails, respond to client with error
        errorResponse("(".$connection->errno.") ".$connection->error);
    }

    //  Switch based upon request type
    switch($filteredPOST['request_type']){
        //  Adds a new client
        case 'add_client':
            //  Filter input variables
            $filteredPOST['client_name'] = filter_var($_POST['client_name'], FILTER_SANITIZE_STRING);
            if (empty($filteredPOST['client_name'])){
                errorResponse('Invalid client name');
            }
            //  Create query
            $query_type = "INSERT";
            $query = "INSERT INTO `clients`(`name`) VALUES ('{$filteredPOST['client_name']}');";   //  done
            $id_key_label = 'client_id';
            break;
        //  Edits the name of the client with the given id
        case 'edit_client':
            //  Filter input variables
            $filteredPOST['client_id'] = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['client_id'])){
                errorResponse('Invalid client id');
            }
            $filteredPOST['client_name'] = filter_var($_POST['client_name'], FILTER_SANITIZE_STRING);
            if (empty($filteredPOST['client_name'])){
                errorResponse('Invalid client name');
            }
            //  Create query
            $query_type = "UPDATE";
            $query = "UPDATE `clients` SET `name`='{$filteredPOST['client_name']}' WHERE `id`={$filteredPOST['client_id']};";
            break;
        //  Delete a client, the client's sections, and all links attached to those sections
        case 'delete_client':
            //  Filter input variables
            $filteredPOST['client_id'] = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['client_id'])){
                errorResponse('Invalid client id');
            }
            //  Create query
            $query_type = "DELETE";
            $query = "DELETE c,s,l
                      FROM `clients` AS c
                      LEFT JOIN `sections` AS s ON c.id=s.client_id
                      LEFT JOIN `links` AS l ON s.id=l.section_id
                      WHERE c.id={$filteredPOST['client_id']};";
            break;
        //  Add a section to the client with the given client id, if valid
        case 'add_section':
            //  Filter input variables
            $filteredPOST['client_id'] = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['client_id'])){
                errorResponse('Invalid client id');
            }
            $filteredPOST['section_name'] = filter_var($_POST['section_name'], FILTER_SANITIZE_STRING);
            if (empty($filteredPOST['section_name'])){
                errorResponse('Invalid section name');
            }
            //  Create query
            $query_type = "INSERT";
            $query = "INSERT INTO `sections`(`name`,`client_id`)
                      VALUES (
                        '{$filteredPOST['section_name']}',
                        (SELECT `id` FROM `clients` WHERE `id`={$filteredPOST['client_id']})
                      );";  //  SELECT statement returns null if given client id doesn't exist, causing query to fail
            $id_key_label = 'section_id';
            break;
        //  Edits section name and client id
        case 'edit_section':
            //  Filter input variables
            $filteredPOST['section_id'] = filter_var($_POST['section_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['section_id'])){
                errorResponse('Invalid section id');
            }
            $filteredPOST['section_name'] = filter_var($_POST['section_name'], FILTER_SANITIZE_STRING);
            $filteredPOST['client_id'] = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['section_name']) && empty($filteredPOST['client_id'])){
                errorResponse('No valid section name or client id provided to update section');
            }
            //  If client id is provided, query clients table to check validity
            if(!empty($filteredPOST['client_id'])){
                $result = $connection->query("SELECT id FROM clients WHERE id={$filteredPOST['client_id']}");
                if (!($result->num_rows)){
                    errorResponse("client id does not exist");
                }
            }
            //  Create query
            $query_type = "UPDATE";
            $query = "UPDATE sections SET ";
            if (!empty($filteredPOST['section_name'])){
                $query .= "name='{$filteredPOST['section_name']}'";
            }
            if (!empty($filteredPOST['client_id'])){
                if (!empty($filteredPOST['section_name'])){
                    $query .= ",";
                }
                $query .= "client_id={$filteredPOST['client_id']}";
            }
            $query .= " WHERE sections.id={$filteredPOST['section_id']}";
            $query .= ";";

            //$query = "UPDATE "
            break;
        //  Deletes a section and all the section's links
        case 'delete_section':
            //  Filter input variables
            $filteredPOST['section_id'] = filter_var($_POST['section_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['section_id'])){
                errorResponse('Invalid section id');
            }
            //  Create query
            $query_type = "DELETE";
            $query = "DELETE s,l
                      FROM `sections` AS s
                      LEFT JOIN `links` AS l ON s.id=l.section_id
                      WHERE s.id={$filteredPOST['section_id']};";
            break;
        //  Add a link to the section with the given section id, if valid
        case 'add_link':
            //  Filter input variables
            $filteredPOST['section_id'] = filter_var($_POST['section_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['section_id'])){
                errorResponse('Invalid section id');
            }
            $filteredPOST['link_name'] = filter_var($_POST['link_name'], FILTER_SANITIZE_STRING);
            if (empty($filteredPOST['link_name'])){
                errorResponse('Invalid link name');
            }
            //  Create query
            $query_type = "INSERT";
            $query = "INSERT INTO `links`(`name`,`section_id`)
                      VALUES
                      (
                        '{$filteredPOST['link_name']}',
                        (SELECT `id` FROM `sections` WHERE `id`={$filteredPOST['section_id']})
                      );";  //  SELECT statement returns null if given section id doesn't exist, causing query to fail
            $id_key_label = 'link_id';
            break;
        //  Edits link name and section_id
        case 'edit_link':
            //  Filter input variables
            $filteredPOST['link_id'] = filter_var($_POST['link_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['link_id'])){
                errorResponse('Invalid link id');
            }
            $filteredPOST['link_name'] = filter_var($_POST['link_name'], FILTER_SANITIZE_STRING);
            $filteredPOST['section_id'] = filter_var($_POST['section_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['link_name']) && empty($filteredPOST['section_id'])){
                errorResponse('No valid link name or section id provided to update link');
            }
            //  If section id is provided, query clients table to check validity
            if(!empty($filteredPOST['section_id'])){
                $result = $connection->query("SELECT id FROM sections WHERE id={$filteredPOST['section_id']}");
                if (!($result->num_rows)){
                    errorResponse("section id does not exist");
                }
            }
            //  Create query
            $query_type = "UPDATE";
            $query = "UPDATE `links` SET ";
            if (!empty($filteredPOST['link_name'])){
                $query .= "`name`='{$filteredPOST['link_name']}'";
            }
            if (!empty($filteredPOST['section_id'])){
                if (!empty($filteredPOST['link_name'])){
                    $query .= ",";
                }
                $query .= "`section_id`={$filteredPOST['section_id']}";
            }
            $query .= " WHERE `id`={$filteredPOST['link_id']};";
            break;
        //  Deletes a link
        case 'delete_link':
            //  Filter input variables
            $filteredPOST['link_id'] = filter_var($_POST['link_id'], FILTER_VALIDATE_INT);
            if (empty($filteredPOST['link_id'])){
                errorResponse('Invalid link id');
            }
            //  Create query
            $query_type = "DELETE";
            $query = "DELETE FROM `links` WHERE `id`={$filteredPOST['link_id']};";
            break;
        //  Invalid request
        default:
            errorResponse("Invalid request type");
    }

    //  send query to database
    $result = $connection->query($query);
    if(empty($result)){   //  if the query fails, respond to client with error
        errorResponse("(".$connection->errno.") ".$connection->error);
    }

    //  Send response based upon results
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