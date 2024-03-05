<?php
require_once('includes/connexionBD.php');

function sendJson($data)
{
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    echo json_encode($data);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (isset($_POST['action'])){
        $action = $_POST['action'];
        if ($action == 'getFirst'){
            $query = "SELECT * FROM users";
            $result = $conn->query($query);
            $users = array();
            while ($row = $result->fetch_assoc()){
                $users[] = $row;
            }
            sendJson($users);
        }
    }
} 
?>
