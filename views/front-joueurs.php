<?php require_once(__DIR__ . '/front-header.php'); ?>
<div class="fftt_plug_div">
    <table class="listeJoueurs sortableTable">
        <thead>
        <th width="135">Nom</th>
        <th width="135">Prénom</th>
        <th>Class. <br />Off.</th>
        <th>Points <br />Off.</th>
        <th>Points <br />Mens.</th>
        <th>Progr. <br />Mens.</th>
        <th>Progr. <br />Ann.</th>
        </thead>
        <tbody>
            <?php
            $i = 0;
            foreach ($joueurs->getJoueurs($atts['type']) as $joueur) {
                if (!is_null($joueur->getClassement()->getClassementOfficiel())) {
                    //Affichage pair/impair
                    $i++;
                    if ($i % 2 == 0) {
                        $class = 'odd';
                    } else {
                        $class = 'even';
                    }
                    ?>
                    <tr class="<?php echo $class ?>">
                        <td><?php echo $joueur->getNom(); ?></td>
                        <td><?php echo $joueur->getPrenom(); ?></td>
                        <td class="center"><?php echo $joueur->getClassement()->getClassementOfficiel(); ?></td>
                        <td class="center"><?php echo $joueur->getClassement()->getPointsOfficiels(); ?></td>
                        <td class="center"><?php echo $joueur->getClassement()->getPointsMensuels(); ?></td>
                        <td class="center">
                            <?php
                            $color = '';
                            if ($joueur->getClassement()->getProgressionMensuelle() > 0) {
                                $color = 'vert';
                            } elseif ($joueur->getClassement()->getProgressionMensuelle() < 0) {
                                $color = 'rouge';
                            }
                            echo '<span class="' . $color . '">' . $joueur->getClassement()->getProgressionMensuelle() . '</span>';
                            ?>
                        </td>
                        <td class="center">
                            <?php
                            $color = '';
                            if ($joueur->getClassement()->getProgressionAnnuelle() > 0) {
                                $color = 'vert';
                            } elseif ($joueur->getClassement()->getProgressionAnnuelle() < 0) {
                                $color = 'rouge';
                            }
                            echo '<span class="' . $color . '">' . $joueur->getClassement()->getProgressionAnnuelle() . '</span>';
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>
