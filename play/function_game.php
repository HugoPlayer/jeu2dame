<?php 
require('../db.php');

function next_player($id_partie){
    /*
    Fonction permettant le changement de joueur qui est en train de jouer.
    Valide
    */
    global $db;
    if(partie_exist($id_partie)){
        $req = $db->prepare("SELECT * FROM partie WHERE id = ?");
        $req->execute(array($id_partie));
        $partie = $req->fetch();
        $req_update = $db->prepare("UPDATE partie SET id_joueur_tour = ? WHERE id = ?");
        if($partie['id_joueur_tour'] == $partie['id_joueur1']){
            $req_update->execute(array($partie['id_joueur2'],$id_partie));
            return 1;
        } elseif($partie['id_joueur_tour'] == $partie['id_joueur2']) {
            $req_update->execute(array($partie['id_joueur1'],$id_partie));
            return 1;
        }
    }
    return 0;
}

function pion_deplacement($id_partie,$tab,$equipe,$selected_case_l,$selected_case_c,$newcase_l,$newcase_c){
    /*
    Fonction déplaçant un pion.
    */
    if(partie_exist($id_partie)){
        $tab[$selected_case_l][$selected_case_c] = 0;
        $tab[$newcase_l][$newcase_c] = $equipe;
        save_plateau($id_partie,convert_str($tab));
        return 1;
    }
    return 0;
}

function pion_manger($id_partie,$tab,$l,$c){
    /*
    Fonction permettant de manger un pion.
    */
    $tab[$l][$c] = 0;
    save_plateau($id_partie,convert_str($tab));
}

function est_vide($tableau,$ligne_depart,$colonne_depart,$rayon,$equipe){
    /*
    Fonction vérifiant si le coup demandé va sur une case vide.
    Renvoie un tableau de coordonée des cases vides autour du joueur en fonction du rayon.
    Valide
    */
    $vide = array();
    for($i=1;$i<=$rayon;$i++){
        if($tableau[$ligne_depart][$colonne_depart] == $equipe){
            if($ligne_depart-$i >= 0 && $colonne_depart-$i >= 0){
                if($tableau[$ligne_depart-$i][$colonne_depart-$i] == 0){
                    $vide[] = [$ligne_depart-$i,$colonne_depart-$i];
                }
            }
            if($ligne_depart-$i >= 0 && $colonne_depart+$i < 10){
                if ($tableau[$ligne_depart-$i][$colonne_depart+$i] == 0){
                    $vide[] = [$ligne_depart-$i,$colonne_depart+$i];
                }
            }
            if($ligne_depart+$i < 10 && $colonne_depart-$i >= 0){
                if($tableau[$ligne_depart+$i][$colonne_depart-$i] == 0){
                    $vide[] = [$ligne_depart+$i,$colonne_depart-$i];
                }
            }
            if($ligne_depart+$i < 10 && $colonne_depart+$i < 10){
                if($tableau[$ligne_depart+$i][$colonne_depart+$i] == 0){
                    $vide[] = [$ligne_depart+$i,$colonne_depart+$i];
                }
            }
        }
    }
    return $vide;
}

function peut_manger($tableau,$ligne_depart,$colonne_depart,$equipe,$equipe_ennemi){
    /*
    Fonction vérifiant si le coup demandé peut manger un pion.
    Renvoie un tableau de coordonnée.
    Valide
    */
    $vide = est_vide($tableau,$ligne_depart,$colonne_depart,2,$equipe);
    $valeur = array();
    if($ligne_depart-2 >= 0 && $colonne_depart-2 >= 0){
        if($tableau[$ligne_depart-1][$colonne_depart-1] == $equipe_ennemi && in_array([$ligne_depart-2,$colonne_depart-2],$vide)){
            $valeur[] = [[$ligne_depart-2,$colonne_depart-2],[$ligne_depart-1,$colonne_depart-1]];
        }
    }
    if($ligne_depart-2 >= 0 && $colonne_depart+2 < 10){
        if($tableau[$ligne_depart-1][$colonne_depart+1] == $equipe_ennemi && in_array([$ligne_depart-2,$colonne_depart+2],$vide)){
            $valeur[] = [[$ligne_depart-2,$colonne_depart+2],[$ligne_depart-1,$colonne_depart+1]];
        }
    }
    if($ligne_depart+2 < 10 && $colonne_depart-2 >= 0){
        if($tableau[$ligne_depart+1][$colonne_depart-1] == $equipe_ennemi && in_array([$ligne_depart+2,$colonne_depart-2],$vide)){
            $valeur[] = [[$ligne_depart+2,$colonne_depart-2],[$ligne_depart+1,$colonne_depart-1]];
        }
    }
    if($ligne_depart+2 < 10 && $colonne_depart+2 < 10){
        if($tableau[$ligne_depart+1][$colonne_depart+1] == $equipe_ennemi && in_array([$ligne_depart+2,$colonne_depart+2],$vide)){
            $valeur[] = [[$ligne_depart+2,$colonne_depart+2],[$ligne_depart+1,$colonne_depart+1]];
        }
    }
    return $valeur;
}

function verif_joueur_actuel($id_partie,$id_joueur_actuel){
    /*
    Fonction vérifiant si le joueur entré doit jouer.
    Renvoie 1 si c'est bien au tour du joueur entré de jouer, sinon 0.
    Valide
    */
    global $db;
    if(partie_exist($id_partie)){
        $req = $db->prepare("SELECT * FROM partie WHERE id = ?");
        $req->execute(array($id_partie));
        $partie = $req->fetch();
        if($partie['id_joueur_tour'] == $id_joueur_actuel){
            return 1;
        }
    }
    return 0;
}

function verif_win($tableau,$joueur){
    /*
    Fonction vérifiant s'il reste des pions du joueur entré.
    Renvoie 1 s'il ne reste plus de pion, sinon 0.
    Valide
    */
    if($joueur == 1){
        $joueur = 2;
    } elseif($joueur == 2){
        $joueur = 1;
    }
    for($i=0;$i<10;$i++){
        for($j=0;$j<10;$j++){
            if($tableau[$i][$j] == $joueur){
                return 0;
            }
        }
    }
    return 1;
}

function convert_str($tableau){
    /* 
    Fonction convertissant le plateau sous forme de tableau en chaine de 
    caractère (compatible avec la db).
    Valide
    */
    $plateau_string = "";
    for($i=0;$i<10;$i++){
        for($j=0;$j<10;$j++){
            $plateau_string .= $tableau[$i][$j];
        }
    }
    return $plateau_string;
}

function convert_array($string){
    /* 
    Fonction convertissant le plateau sous forme de chaine de 
    caractère en tableau.
    Valide
    */
    $plateau_array = array();
    $id = 0;
    for($i=0;$i<10;$i++){
        for($j=0;$j<10;$j++){
            $plateau_array[$i][$j] = substr($string, $id, 1);
            $id += 1;
        }
    }
    return $plateau_array;
}

function partie_exist($id){
    /*
    Fonction vérifiant l'existance d'une partie dans la db.
    Valide
    */
    global $db;
    $req = $db->prepare("SELECT * FROM partie WHERE id = ?");
    $req->execute(array($id));
    $exist = $req->rowCount();
    if($exist){
        return 1;
    }
    return 0;
}

function save_plateau($id,$plateau){
    /*
    Fonction enregistrant le plateau de jeu dans la db.
    Valide
    */
    global $db;
    if(partie_exist($id)){
        $req = $db->prepare("UPDATE partie SET plateau = ? WHERE id = ?");
        $req->execute(array($plateau, $id));
    }
}

function load_plateau($id){
    /*
    Fonction chargeant le plateau de jeu depuis la db.
    Valide
    */
    global $db;
    if(partie_exist($id)){
        $req = $db->prepare("SELECT * FROM partie WHERE id = ?");
        $req->execute(array($id));
        $partie = $req->fetch();
        $plateau = $partie['plateau'];
        return $plateau;
    }
    return 0;
}

?>