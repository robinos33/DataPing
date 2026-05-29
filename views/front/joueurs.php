<?php require_once(__DIR__ . '/header.php'); ?>
<div class="DataPing_div">
    <?php if ($updatedAt !== false): ?>
        <p class="dataping-updated-at">
            Dernière mise à jour : <?php echo esc_html(date('d/m/Y à H:i:s', $updatedAt)); ?>
        </p>
    <?php endif; ?>
    <table class="listeJoueurs sortableTable">
        <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Cl. Off.</th>
            <th>Pts Off.</th>
            <th>Pts Mens.</th>
            <th>↕ Mens.</th>
            <th>↕ Ann.</th>
        </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            foreach ($joueurs->getJoueurs($atts['type']) as $joueur) {
                if (!is_null($joueur->getClassement()->getClassementOfficiel())) {
                    $i++;
                    $class = ($i % 2 == 0) ? 'odd' : 'even';
                    $class .= ' ' . $joueur->getSexe();

                    $progMens = $joueur->getClassement()->getProgressionMensuelle();
                    $progAnn  = $joueur->getClassement()->getProgressionAnnuelle();
                    ?>
                    <tr class="<?php echo esc_attr($class); ?>">
                        <td class="dataping-nom"><?php echo esc_html($joueur->getNom()); ?></td>
                        <td><?php echo esc_html($joueur->getPrenom()); ?></td>
                        <td class="center"><?php echo esc_html($joueur->getClassement()->getClassementOfficiel()); ?></td>
                        <td class="center"><?php echo esc_html($joueur->getClassement()->getPointsOfficiels()); ?></td>
                        <td class="center"><?php echo esc_html($joueur->getClassement()->getPointsMensuels()); ?></td>
                        <td class="center">
                            <?php
                            if ($progMens > 0) {
                                echo '<span class="dataping-badge dataping-badge--up">+' . esc_html($progMens) . '</span>';
                            } elseif ($progMens < 0) {
                                echo '<span class="dataping-badge dataping-badge--down">' . esc_html($progMens) . '</span>';
                            } else {
                                echo '<span class="dataping-badge dataping-badge--neutral">—</span>';
                            }
                            ?>
                        </td>
                        <td class="center">
                            <?php
                            if ($progAnn > 0) {
                                echo '<span class="dataping-badge dataping-badge--up">+' . esc_html($progAnn) . '</span>';
                            } elseif ($progAnn < 0) {
                                echo '<span class="dataping-badge dataping-badge--down">' . esc_html($progAnn) . '</span>';
                            } else {
                                echo '<span class="dataping-badge dataping-badge--neutral">—</span>';
                            }
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
