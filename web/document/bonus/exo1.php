<?php
/**
 * Created by PhpStorm.
 * User: maps_red
 * Date: 06/10/16
 * Time: 10:20
 */

session_start();

const LIGHTS = 13;
$existings = [];
for ($i = 0; $i < LIGHTS; $i++) {
    $existings[$i] = $i;
}

if (isset($_POST['light'])) {
    $lights = array_keys($_POST['light']);
    $_SESSION['lights'] = array_merge($_SESSION['lights'], $lights);
    $_SESSION['lights'] = array_unique($_SESSION['lights']);
    foreach ($_SESSION['lights'] as $light) {
        unset($existings[$light]);
    }
    $computer = $existings;
    shuffle($computer);
    array_splice($computer, rand(1, 3));
    foreach ($computer as $light) {
        unset($existings[$light]);
        $_SESSION['lights'][$light] = $light;
    }
}


if (isset($_POST['restart'])) {
    $_SESSION['lights'] = [];
    unset($existings);
    echo "<meta http-equiv=\"refresh\" content=\"1; URL=./exo1.php\">";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exercice 1 : Jeu de Nim</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">

</head>
<body>


<div class="container">
    <h1>Exercice 1 : Jeu de Nim : 8pts</h1>
    <div>
        <p>
            Le jeu de Nim se joue à deux joueurs. 13 allumettes sont disposées sur la table.
            À tour de rôle, chacun des deux joueurs peut tirer 1, 2 ou 3 allumettes.
            Le joueur qui tire la dernière allumette gagne la partie.
        </p>
        <p>- Proposer un modèle objet du jeu de Nim<br>
            - Implémentez une partie afin qu’un joueur puisse jouer solo face à l’ordinateur</p>
        <ul>
            <li>On figurera les 13 allumettes par 13 checkbox disposées dans un formulaire.</li>
            <li> Le joueur peut sélectionner de 1 à 3 checkbox</li>
            <li>Un bouton “tirer” envoie le formulaire et retire les allumettes sélectionnées</li>
            <li>Afin de simplifier, on considère que l’ordinateur tire un nombre aléatoire (entre 1 et 3) d’allumettes
                lorsque c’est son tour
            </li>
            <li>À la fin du jeu, afficher “Vous avez gagné” ou “Vous avez perdu”</li>
        </ul>
        <p>
            Bonus : 2pts
            - Implémentez une stratégie gagnante pour l’ordinateur
        </p>
    </div>

    <form method="post">
        <button type="submit" name="restart" class="btn btn-default">Redémarrer le jeu</button>
    </form>

    <div class="informations">
        <hr>
        <?php
        if (isset($lights) && !empty($lights)) {
            echo sprintf("Vous avez choisi les allumettes %s. <br>", implode(",", $lights));
        }
        if (isset($computer) && !empty($computer)) {
            echo sprintf("L'ordinateur a choisi les allumettes %s. <br>", implode(",", $computer));
        } elseif (empty($computer) && isset($lights)) {
            echo "Vous avez gagné !";
        }

        if (empty($existings)) {
            echo "Vous avez perdu !";
        }

        ?>
        <hr>
    </div>

    <form method="post">
        <?php
        if (!empty($existings)) {
            foreach ($existings as $i):
                ?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" class="light" name="light[<?= $i ?>]"> Allumette <?= $i ?>
                    </label>
                </div>
                <?php
            endforeach;
        }
        ?>

        <button type="submit" class="btn btn-default">Tirer</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="exo1/script.js"></script>
</body>
</html>
