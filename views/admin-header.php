<?php if (isset($pluginData)) { ?>
    <h1 class="fftt_plug_title">Données FFTT <span class="fftt_plug_nonoff">(plugin non-officiel)</span> <span class="fftt_plug_version">v<?php echo $pluginData['Version']; ?></span></h1>
    <p class="fftt_plug_author">Par <?php echo $pluginData['Author']; ?></p>
    <?php
}
if (!is_object(getSessionApi()) && $_GET['page'] !== 'parametres_wpApiFFTT') {
    ?>
    <div class="wrap">
        <h1 class="fftt_plug_title">Les équipes </h1>
        <?php echo 'Veuillez rentrer vos paramètres en cliquant sur "Données FFTT" dans le menu'; ?>
    </div>
    <?php
    die();
}


