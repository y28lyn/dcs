<?php
require_once('includes/connexionBD.php');

function sendJson($data)
{
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    echo json_encode($data);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (isset($_POST['action'])){
        $action = $_POST['action'];
       switch ($action){
        case 'get_TopClient1':
            try {
                $sql = "SELECT gc.NomGrandClient, a.nomAppli AS Application ,SUM(lf.prix) AS ChiffreAffaires FROM grandclients gc JOIN clients c ON gc.GrandClientID = c.GrandClientID JOIN centresactivite ca ON c.CentreActiviteID = ca.CentreActiviteID JOIN ligne_facturation lf ON ca.CentreActiviteID = lf.CentreActiviteID JOIN application a ON lf.IRT = a.IRT WHERE gc.NomGrandClient = 'Client1' GROUP BY gc.GrandClientID,a.nomAppli ORDER BY ChiffreAffaires DESC LIMIT 10;";
                $requete = $pdo->prepare($sql);

                $requete->execute();

                $result = $requete->fetchAll(PDO::FETCH_ASSOC);
                sendJson($result);
            } catch (PDOException $e) {
                echo "Erreur : " . $e->getMessage();
            }
            
            break;

        case 'get_TopClient2':
            try {
                $sql = "SELECT gc.NomGrandClient, a.nomAppli AS Application ,SUM(lf.prix) AS ChiffreAffaires FROM grandclients gc JOIN clients c ON gc.GrandClientID = c.GrandClientID JOIN centresactivite ca ON c.CentreActiviteID = ca.CentreActiviteID JOIN ligne_facturation lf ON ca.CentreActiviteID = lf.CentreActiviteID JOIN application a ON lf.IRT = a.IRT WHERE gc.NomGrandClient = 'Client2' GROUP BY gc.GrandClientID,a.nomAppli ORDER BY ChiffreAffaires DESC LIMIT 10;";
                $requete = $pdo->prepare($sql);

                $requete->execute();

                $result = $requete->fetchAll(PDO::FETCH_ASSOC);
                sendJson($result);
            } catch (PDOException $e) {
                echo "Erreur : " . $e->getMessage();
            }
            
            break;
        case 'get_TopClient3':
            try {
                $sql = "SELECT gc.NomGrandClient, a.nomAppli AS Application ,SUM(lf.prix) AS ChiffreAffaires FROM grandclients gc JOIN clients c ON gc.GrandClientID = c.GrandClientID JOIN centresactivite ca ON c.CentreActiviteID = ca.CentreActiviteID JOIN ligne_facturation lf ON ca.CentreActiviteID = lf.CentreActiviteID JOIN application a ON lf.IRT = a.IRT WHERE gc.NomGrandClient = 'Client3' GROUP BY gc.GrandClientID,a.nomAppli ORDER BY ChiffreAffaires DESC LIMIT 10;";
                $requete = $pdo->prepare($sql);

                $requete->execute();

                $result = $requete->fetchAll(PDO::FETCH_ASSOC);
                sendJson($result);
            } catch (PDOException $e) {
                echo "Erreur : " . $e->getMessage();
            }
            
            break;
        default:
            echo "Action non reconnue";
            break;
       }
    }
} 
else{
    sendJson(array('message' => 'Méthode non autorisée'));
}
?>
