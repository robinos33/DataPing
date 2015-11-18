<?php

class ConstantesApiFFTT {

    const WP_API_FFTT_ID_APPLICATION = 'wp_Api_FFTT_id_application';
    const WP_API_FFTT_MOT_DE_PASSE = 'wp_Api_FFTT_mot_de_passe';
    const WP_API_FFTT_NUM_CLUB = 'wp_Api_FFTT_num_club';

}

/**
 * Autoloading des models
 */
function autoload_fft_models() {
    $repertoireModels = __DIR__ . '/models/';
    $models = glob($repertoireModels . "*.php");
    foreach ($models as $model) {
        require_once $model;
    }
}

function getSessionApi() {
    return $_SESSION['api'];
}

function setSessionApi($api) {
    $_SESSION['api'] = $api;
}
