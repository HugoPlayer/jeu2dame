<?php
session_start();

require('../db.php');
 
if(!isset($_SESSION['id']) || !isset($_SESSION['admin'])) {
    header("Location: /");
}

$table_membres = $db->query('SELECT * FROM membres ORDER BY id');
$table_liste_ami = $db->query('SELECT * FROM liste_ami ORDER BY id');
$table_requete_attente = $db->query('SELECT * FROM requete_attente ORDER BY id');
$table_partie = $db->query('SELECT * FROM partie ORDER BY id');

?>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Accès réglementé - Panel d'administration - Jeu2Dame</title>
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/admin.css">
    </head>
    <body>
        <header>
            <a class="header_titre" href="/">Jeu2Dame</a>
            <?php if(isset($_SESSION['id'])): ?>
            <nav class="navbar_connected">
                <?php if(isset($_SESSION['admin'])): ?>
                <button disabled class="header_button" onclick="window.location.href = '/admin';">Panel d'administration</button>
                <?php endif ?>
                <button class="header_button" onclick="window.location.href = '/accounts/profil';">Profil</button>
                <button class="header_button" onclick="window.location.href = '/logout';">Déconnexion</button>
                <h4 id="username_hello"><?php echo('Bonjour ' . $_SESSION['username']); ?></h4>
            </nav>
            <?php else: ?>
            <nav>
                <button onclick="window.location.href = '/register';">Inscription</button>
                <button onclick="window.location.href = '/login';">Connexion</button>
            </nav>
            <?php endif ?>
        </header>
        <section>
            <div id="fenetre_titre">
                <h1>Panel d'administration - Votre ip : <?php echo $_SERVER['REMOTE_ADDR']; ?></h1>
            </div>
            <div id="fenetre_centre">
                <div class="module">
                    <h2>Base de données - Membres</h2>
                    <div class="module_scrollbar">
                        <table>
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>username</th>
                                    <th>mail</th>
                                    <th>pswd</th>
                                    <th>creation_date</th>
                                    <th>avatar</th>
                                    <th>confirmkey</th>
                                    <th>mail_confirm</th>
                                    <th>tfa_code</th>
                                    <th>admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($m = $table_membres->fetch()){ ?>
                                <tr>
                                    <td><?= $m['id'] ?></td>
                                    <td><?= $m['username'] ?></td>
                                    <td><?= $m['mail'] ?></td>
                                    <td><?= $m['pswd'] ?></td>
                                    <td><?= $m['creation_date'] ?></td>
                                    <td><?= $m['avatar'] ?></td>
                                    <td><?= $m['confirmkey'] ?></td>
                                    <td><?= $m['mail_confirm'] ?></td>
                                    <td><?= $m['tfa_code'] ?></td>
                                    <td><?= $m['admin'] ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="module" id="module_mid">
                    <div class="module_mid">
                        <h2>Base de données - Liste d'ami</h2>
                        <div class="module_scrollbar">
                            <table>
                                <thead>
                                    <tr>
                                        <th>id</th>
                                        <th>id_joueur1</th>
                                        <th>id_joueur2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($a = $table_liste_ami->fetch()){ ?>
                                    <tr>
                                        <td><?= $a['id'] ?></td>
                                        <td><?= $a['id_joueur1'] ?></td>
                                        <td><?= $a['id_joueur2'] ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="module_mid">
                        <h2>Base de données - Requêtes en attente</h2>
                        <div class="module_scrollbar">
                            <table>
                                <thead>
                                    <tr>
                                        <th>id</th>
                                        <th>type</th>
                                        <th>date_requete</th>
                                        <th>id_joueur1</th>
                                        <th>id_joueur2</th>
                                        <th>valider_user1</th>
                                        <th>valider_user2</th>
                                        <th>newmail</th>
                                        <th>mailconfirm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($r = $table_requete_attente->fetch()){ ?>
                                    <tr>
                                        <td><?= $r['id'] ?></td>
                                        <td><?= $r['type'] ?></td>
                                        <td><?= $r['date_requete'] ?></td>
                                        <td><?= $r['id_joueur1'] ?></td>
                                        <td><?= $r['id_joueur2'] ?></td>
                                        <td><?= $r['valider_user1'] ?></td>
                                        <td><?= $r['valider_user2'] ?></td>
                                        <td><?= $r['newmail'] ?></td>
                                        <td><?= $r['mailconfirm'] ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="module">
                    <h2>Base de données - Partie</h2>
                        <div class="module_scrollbar">
                            <table>
                                <thead>
                                    <tr>
                                        <th>id</th>
                                        <th>type</th>
                                        <th>id_joueur1</th>
                                        <th>id_joueur2</th>
                                        <th>id_joueur_tour</th>
                                        <th>plateau</th>
                                        <th>debut_partie</th>
                                        <th>fin_partie</th>
                                        <th>id_gagnant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($p = $table_partie->fetch()){ ?>
                                    <tr>
                                        <td><?= $p['id'] ?></td>
                                        <td><?= $p['type'] ?></td>
                                        <td><?= $p['id_joueur1'] ?></td>
                                        <td><?= $p['id_joueur2'] ?></td>
                                        <td><?= $p['id_joueur_tour'] ?></td>
                                        <td><?= $p['plateau'] ?></td>
                                        <td><?= $p['debut_partie'] ?></td>
                                        <td><?= $p['fin_partie'] ?></td>
                                        <td><?= $p['id_gagnant'] ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>


        </section>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathiques</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
    </body>
</html>
