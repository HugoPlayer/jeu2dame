<?php
require('../db.php');
require('function_game.php');
session_start();

// Cas où un utilisateur non connecté charge la page
if(!isset($_SESSION['id'])){
    header("Location: /play");
}

// Cas pù l'id de la partie se trouve dans l'url (quand la partie est lancée)
if(isset($_GET['id']) AND !empty($_GET['id'])){
    $idpartie = urldecode($_GET['id']);
    if(partie_exist($idpartie)){
        // Vérification du mode de jeu
        $req = $db->prepare("SELECT * FROM partie WHERE id = ?");
        $req->execute(array($idpartie));
        $partie = $req->fetch();
        if($partie['id_gagnant'] == NULL){
            if(!isset($_SESSION['partie']['verified'])){
                if($_SESSION['id'] == $partie['id_joueur1'] || $_SESSION['id'] == $partie['id_joueur2']) {
                    $plateau = convert_array($partie['plateau']);
                    $_SESSION['partie']['id'] = $idpartie;
                    if($_SESSION['id'] == $partie['id_joueur1']){
                        $_SESSION['partie']['id_ennemi'] = $partie['id_joueur2'];
                        $_SESSION['partie']['id_pion'] = 1;
                    } elseif($_SESSION['id'] == $partie['id_joueur2']){
                        $_SESSION['partie']['id_ennemi'] = $partie['id_joueur1'];
                        $_SESSION['partie']['id_pion'] = 2;
                    }
                    if($partie['type'] == 1) {
                        $_SESSION['partie']['titre'] = $_SESSION['username']." VS Pedro [BOT]";
                    } elseif($partie['type'] == 2) {
                        if($partie['id_joueur2'] == NULL){
                            $_SESSION['partie']['titre'] = "En attente d'un second joueur...<br/>Identifiant de la partie: ".$_SESSION['partie']['id'];
                            header("Refresh:5");
                        } else {
                            $req = $db->prepare("SELECT * FROM membres WHERE id = ?");
                            $req->execute(array($_SESSION['partie']['id_ennemi']));
                            $ennemi = $req->fetch();
                            $_SESSION['partie']['titre'] = $_SESSION['username']." VS ".$ennemi['username'];
                            verification_partie();
                        }
                        // Faire une file d'attente if le mode de jeu == 2 et id_joueur2 == NULL
                    } elseif($partie['type'] == 3) {
                        if($partie['id_joueur2'] == NULL){
                            $_SESSION['partie']['titre'] = "En attente d'un second joueur...<br/>Identifiant de la partie: ".$_SESSION['partie']['id'];
                            header("Refresh:5");
                        } else {
                            $req = $db->prepare("SELECT * FROM membres WHERE id = ?");
                            $req->execute(array($_SESSION['partie']['id_ennemi']));
                            $ennemi = $req->fetch();
                            $_SESSION['partie']['titre'] = $_SESSION['username']." VS ".$ennemi['username'];
                            verification_partie();
                        }
                    } else {
                        unset($_SESSION['partie']);
                        header("Location: /play");
                    }
                } else {
                    unset($_SESSION['partie']);
                    header("Location: /play");
                }
            } elseif(isset($_SESSION['partie']['verified']) AND $_SESSION['partie']['verified'] == 0) {
                unset($_SESSION['partie']);
                header("Location: /");
            } else {
                $plateau = convert_array($partie['plateau']);
                if(verif_joueur_actuel($_SESSION['partie']['id'], $_SESSION['id']) == 0){
                    header("Refresh:5");
                }
            }
        } else {
            header("Location: /play/finish_game.php");
        }
    } else {
        unset($_SESSION['partie']);
        header("Location: /play");
    }
} else {
    unset($_SESSION['partie']);
    header("Location: /play");
}

// Cas où le bouton "abandonner" est pressé
if(isset($_POST['formabandon'])){
    $req = $db->prepare("UPDATE partie SET fin_partie = CURRENT_TIMESTAMP, id_gagnant = ? WHERE id = ?");
    $req->execute(array($_SESSION['partie']['id_ennemi'], $_SESSION['partie']['id']));
    header("Location: /play/finish_game.php");
}

// Cas où la case sélectionnée et la nouvelle case sont dans l'url (déplacement d'un joueur)
if(isset($_GET['id'],$_GET['selected_case_l'], $_GET['selected_case_c'], $_GET['newcase_l'], $_GET['newcase_c'])){
    // Vérification du joueur qui joue
    if(verif_joueur_actuel($_SESSION['partie']['id'],$_SESSION['id'])){
        $selected_case_l = intval($_GET['selected_case_l']);
        $selected_case_c = intval($_GET['selected_case_c']);
        $newcase_l = intval($_GET['newcase_l']);
        $newcase_c = intval($_GET['newcase_c']);
        $case_vide = est_vide($plateau,$selected_case_l,$selected_case_c,1,$_SESSION['partie']['id_pion']);
        if($_SESSION['partie']['id_pion'] == 1){
            $case_mangeable = peut_manger($plateau,$selected_case_l,$selected_case_c,$_SESSION['partie']['id_pion'],2);
        } elseif($_SESSION['partie']['id_pion'] == 2){
            $case_mangeable = peut_manger($plateau,$selected_case_l,$selected_case_c,$_SESSION['partie']['id_pion'],1);
        }
        $nb_entree = count($case_mangeable);
        $peut_manger = 0;
        for($i=0;$i<$nb_entree;$i++){
            $nb_entree2 = count($case_mangeable[$i]);
            for($j=0;$j<$nb_entree2;$j++){
                if($case_mangeable[$i][$j] == [$newcase_l,$newcase_c]){
                    $coord_manger = $case_mangeable[$i];
                    $peut_manger = 1;
                    break;
                }
            }
        }
        // Si la nouvelle case est dans le tableau des cases vides possible
        if(in_array([$newcase_l,$newcase_c], $case_vide)){
            pion_deplacement($idpartie,$plateau,$_SESSION['partie']['id_pion'],$selected_case_l,$selected_case_c,$newcase_l,$newcase_c);
            $plateau = convert_array(load_plateau($idpartie));
            if(verif_win($plateau,$_SESSION['partie']['id_pion'])){
                win($_SESSION['partie']['id']);
            } else {
                next_player($idpartie);
                header("Location: game?id=".$_SESSION['partie']['id']);
            }
        } elseif($peut_manger){
            pion_deplacement($idpartie,$plateau,$_SESSION['partie']['id_pion'],$selected_case_l,$selected_case_c,$newcase_l,$newcase_c);
            $plateau = convert_array(load_plateau($idpartie));
            pion_manger($idpartie,$plateau,$coord_manger[1][0],$coord_manger[1][1]);
            $plateau = convert_array(load_plateau($idpartie));
            if(verif_win($plateau,$_SESSION['partie']['id_pion'])){
                win($_SESSION['partie']['id']);
            } else {
                next_player($idpartie);
                header("Location: game?id=".$_SESSION['partie']['id']);
            }
        } else {
            $_SESSION['msg_erreur'] = "Vous ne pouvez pas vous déplacer ici.";
            header("Location: game?id=".$_SESSION['partie']['id']);
        }
    } else {
        header("Location: game?id=".$_SESSION['partie']['id']);
    }
}

function win($id_partie){
    global $db;
    if(partie_exist($id_partie)){
        $req = $db->prepare("UPDATE partie SET fin_partie = current_timestamp(), id_gagnant = ? WHERE id = ?");
        $req->execute(array($_SESSION['id'],$id_partie));
        header("Location: /play/finish_game.php");
    }
}

function verification_partie(){
    /*
    Fonction vérifiant la présence des variables de session et le bon démarrage du jeu
    */
    if(isset($_SESSION['partie']['id'], $_SESSION['partie']['id_ennemi'], $_SESSION['partie']['titre'])){
        $_SESSION['partie']['verified'] = 1;
        header("Refresh");
    } else {
        $_SESSION['partie']['verified'] = 0;
        header("Location: /");
    }
}

?>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Partie en cours - Jeu2Dame</title>
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/game.css">
        <script>
            function selectClick(id_partie,l_case,c_case) {
                console.log("Case sélectionnée : "+l_case+","+c_case);
                window.location.href = "game?id="+id_partie+"&selected_case_l="+l_case+"&selected_case_c="+c_case;
            }
            function moveClick(id_partie,selected_case_l,selected_case_c,newcase_l,newcase_c) {
                console.log("Déplacement de "+selected_case_l+","+selected_case_c+" vers "+newcase_l+","+newcase_c);
                window.location.href = "game?id="+id_partie+"&selected_case_l="+selected_case_l+"&selected_case_c="+selected_case_c+"&newcase_l="+newcase_l+"&newcase_c="+newcase_c;
            }
        </script>
    </head>

    <body>
        <header>
            <a class="header_titre" href="/">Jeu2Dame</a>
            <nav class="navbar_connected">
                <form method="POST" action="">
                    <input id="button_abandon" type="submit" name="formabandon" value="Abandonner la partie"/>
                </form>
            </nav>
        </header>
        <?php if(verif_joueur_actuel($_SESSION['partie']['id'], $_SESSION['id'])): ?>
        <section>
            <div id="game_state">
                <h4>A toi de jouer !</h4>
                <?php 
                if($_SESSION['partie']['id_pion'] == 1){
                    echo "<p>Vous jouez les pions blancs.</p>";
                } elseif($_SESSION['partie']['id_pion'] == 2){
                    echo "<p>Vous jouez les pions noirs.</p>";
                } else {
                    echo "<p>Problème avec le choix des pions</p>";
                }
                
                if(isset($_SESSION['msg_erreur'])){
                    echo "<p>".$_SESSION['msg_erreur']."</p>";
                    unset($_SESSION['msg_erreur']);
                }
                ?>
            </div>
            <div id="fenetre_centre">
                <h2><?php echo $_SESSION['partie']['titre']; ?></h2>
                <div id="plateau">
                    <table>
                        <?php
                        if(isset($_SESSION['partie']['id_ennemi'])){
                            for($i=0;$i<10;$i++){
                                echo "<tr>";
                                for($j=0;$j<10;$j++){
                                    if($plateau[$i][$j] == 1){
                                        $classe = " un";
                                    } elseif($plateau[$i][$j] == 2){
                                        $classe = " deux";
                                    } else {
                                        $classe = "";
                                    }
                                    if(isset($_GET['selected_case_l'],$_GET['selected_case_c']) AND !isset($_GET['next_case_l'],$_GET['next_case_c'])){
                                        if($i%2 == 0 AND $j%2 == 0){
                                            echo "<td id='".$i.$j."' class='fond_blanc".$classe."' onclick='moveClick(".$_SESSION['partie']['id'].",".intval($_GET['selected_case_l']).",".intval($_GET['selected_case_c']).",".$i.",".$j.")'></td>";
                                        } elseif($i%2 == 0 AND $j%2 == 1) {
                                            echo "<td id='".$i.$j."' class='fond_noir".$classe."' onclick='moveClick(".$_SESSION['partie']['id'].",".intval($_GET['selected_case_l']).",".intval($_GET['selected_case_c']).",".$i.",".$j.")'></td>";
                                        } elseif($i%2 == 1 AND $j%2 == 0) {
                                            echo "<td id='".$i.$j."' class='fond_noir".$classe."' onclick='moveClick(".$_SESSION['partie']['id'].",".intval($_GET['selected_case_l']).",".intval($_GET['selected_case_c']).",".$i.",".$j.")'></td>";
                                        } elseif($i%2 == 1 AND $j%2 == 1) {
                                            echo "<td id='".$i.$j."' class='fond_blanc".$classe."' onclick='moveClick(".$_SESSION['partie']['id'].",".intval($_GET['selected_case_l']).",".intval($_GET['selected_case_c']).",".$i.",".$j.")'></td>";
                                        }
                                    } else {
                                        if($i%2 == 0 AND $j%2 == 0){
                                            echo "<td id='".$i.$j."' class='fond_blanc".$classe."' onclick='selectClick(".$_SESSION['partie']['id'].",".$i.",".$j.")'></td>";
                                        } elseif($i%2 == 0 AND $j%2 == 1) {
                                            echo "<td id='".$i.$j."' class='fond_noir".$classe."' onclick='selectClick(".$_SESSION['partie']['id'].",".$i.",".$j.")'></td>";
                                        } elseif($i%2 == 1 AND $j%2 == 0) {
                                            echo "<td id='".$i.$j."' class='fond_noir".$classe."' onclick='selectClick(".$_SESSION['partie']['id'].",".$i.",".$j.")'></td>";
                                        } elseif($i%2 == 1 AND $j%2 == 1) {
                                            echo "<td id='".$i.$j."' class='fond_blanc".$classe."' onclick='selectClick(".$_SESSION['partie']['id'].",".$i.",".$j.")'></td>";
                                        }
                                    }
                                }
                                echo "</tr>";
                            }
                        }
                        ?>
                    </table>
                </div>
            </div>
        </section>
        <?php else: ?>
        <section>
            <div id="game_state">
                <h4>En attente de l'ennemi...</h4>
                <?php 
                if($_SESSION['partie']['id_pion'] == 1){
                    echo "<p>Vous jouez les pions blancs.</p>";
                } elseif($_SESSION['partie']['id_pion'] == 2){
                    echo "<p>Vous jouez les pions noirs.</p>";
                } else {
                    echo "<p>Problème avec le choix des pions</p>";
                }
                
                if(isset($_SESSION['msg_erreur'])){
                    echo "<p>".$_SESSION['msg_erreur']."</p>";
                    unset($_SESSION['msg_erreur']);
                }
                ?>
            </div>
            <div id="fenetre_centre">
                <h2><?php echo $_SESSION['partie']['titre']; ?></h2>
                <div id="plateau">
                    <table>
                        <?php
                        if(isset($_SESSION['partie']['id_ennemi'])){
                            for($i=0;$i<10;$i++){
                                echo "<tr>";
                                for($j=0;$j<10;$j++){
                                    if($plateau[$i][$j] == 1){
                                        $classe = " un";
                                    } elseif($plateau[$i][$j] == 2){
                                        $classe = " deux";
                                    } else {
                                        $classe = "";
                                    }
                                    if($i%2 == 0 AND $j%2 == 0){
                                        echo "<td id='".$i.$j."' class='fond_blanc ".$classe."'></td>";
                                    } elseif($i%2 == 0 AND $j%2 == 1) {
                                        echo "<td id='".$i.$j."' class='fond_noir ".$classe."'></td>";
                                    } elseif($i%2 == 1 AND $j%2 == 0) {
                                        echo "<td id='".$i.$j."' class='fond_noir ".$classe."'></td>";
                                    } elseif($i%2 == 1 AND $j%2 == 1) {
                                        echo "<td id='".$i.$j."' class='fond_blanc ".$classe."'></td>";
                                    }
                                }
                                echo "</tr>";
                            }
                        }
                        ?>
                    </table>
                </div>
            </div>
        </section>
        <?php endif ?>
        <footer>
            <h4>Développé par des gens sympathique</h4>
        </footer>
    </body>
</html>
