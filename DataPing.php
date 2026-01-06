<?php
/*
  Plugin Name: DataPing
  Plugin URI: http://robin-aldasoro.com/docs/wordpress-plugins/DataPing.zip
  Description: Ce plugin affiche les données accessibles via l'API de la FFTT
  Version: 0.3.0
  Author: Robin Aldasoro
  Author URI: robin-aldasoro.com
  License: GPLv2
 */

require_once('Utils.php');

class DataPing
{

    /**
     * Types possibles  de listes de joueurs  à insérer dans les shortcodes
     * @var array
     */
    private $typeListeJoueurs = array(
        'M', 'F', 'MF'
    );

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('init', array($this, 'dataping_style_scripts'));
        add_shortcode('equipe', array($this, 'equipes_front'));
        add_shortcode('joueurs', array($this, 'joueurs_front'));

        // Hooks pour exposer les données en cache aux autres plugins
        add_filter('dataping_get_joueurs', array($this, 'get_joueurs_data'), 10, 1);
        add_filter('dataping_get_equipes', array($this, 'get_equipes_data'), 10, 1);
        add_filter('dataping_get_classement_poule', array($this, 'get_classement_poule_data'), 10, 2);
        add_filter('dataping_get_rencontres_poule', array($this, 'get_rencontres_poule_data'), 10, 2);
    }

    public function add_admin_menu()
    {
        add_menu_page('DataPing', 'DataPing', 'manage_options', 'parametres_DataPing', array($this, 'admin_module'));
        add_submenu_page('parametres_DataPing', 'Equipes', 'Equipes', 'manage_options', 'equipes_DataPing', array($this, 'equipes_admin'));
        add_submenu_page('parametres_DataPing', 'Joueurs', 'Joueurs', 'manage_options', 'joueurs_DataPing', array($this, 'joueurs_admin'));
    }

    public function admin_module()
    {
        $this->_getLayout('admin');
    }

    public static function getPluginData()
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $datas = get_plugin_data(__FILE__);
        return $datas;
    }

    public function dataping_style_scripts()
    {
        // Styles
        wp_register_style('admin-css', plugins_url('/assets/DataPing.css', __FILE__), array(), '1.0');
        wp_enqueue_style('admin-css');
        // Javascript
        wp_register_script('dataping-js', plugins_url('/assets/DataPing.js', __FILE__), array('jquery'), '1.0', true);
        wp_register_script('table-sorter', plugins_url('/assets/tablesorter/jquery.tablesorter.min.js', __FILE__), array('jquery'), '1.0', true);
        wp_register_script('table-sorter-pager', plugins_url('/assets/tablesorter/jquery.tablesorter.pager.js', __FILE__), array('jquery', 'table-sorter'), '1.0', true);
        wp_enqueue_script('dataping-js');
        wp_enqueue_script('table-sorter');
        wp_enqueue_script('table-sorter-pager');
    }

    public function register_settings()
    {
        register_setting('DataPing_settings', ConstantesDataPing::DATAPING_ID_APPLICATION);
        register_setting('DataPing_settings', ConstantesDataPing::DATAPING_MOT_DE_PASSE);
        register_setting('DataPing_settings', ConstantesDataPing::DATAPING_NUM_CLUB);

        add_settings_section('DataPing_section', 'Paramètres du plugin', array($this, 'section_html'), 'DataPing_settings');
        add_settings_field(ConstantesDataPing::DATAPING_ID_APPLICATION, 'Id Application', array($this, 'id_application_html'), 'DataPing_settings', 'DataPing_section');
        add_settings_field(ConstantesDataPing::DATAPING_MOT_DE_PASSE, 'Mot de passe Application', array($this, 'mot_de_passe_html'), 'DataPing_settings', 'DataPing_section');
        add_settings_field(ConstantesDataPing::DATAPING_NUM_CLUB, 'Numéro de club', array($this, 'equipe_num_html'), 'DataPing_settings', 'DataPing_section');
    }

    public function section_html()
    {
        echo '<p>Entrez les paramètres de l\'application fournis par la FFTT</p>';
        echo '<p>Si vous n\'en avez pas, vous devrez faire la demande suivante en suivant la procédure décrite ici : <a target="_blank" href="http://www.fftt.com/actus/ouverture_interfaces_smartping_2015_06_30-1362.html">http://www.fftt.com/actus/ouverture_interfaces_smartping_2015_06_30-1362.html</a></p>';
    }

    public function id_application_html()
    {
        ?>
        <input type="text" name="DataPing_id_application"
               value="<?php echo esc_attr(get_option(ConstantesDataPing::DATAPING_ID_APPLICATION)); ?>"/>
        <?php
    }

    public function mot_de_passe_html()
    {
        ?>
        <input type="text" name="DataPing_mot_de_passe"
               value="<?php echo esc_attr(get_option(ConstantesDataPing::DATAPING_MOT_DE_PASSE)); ?>"/>
        <?php
    }

    public function equipe_num_html()
    {
        ?>
        <input type="text" name="DataPing_num_club"
               value="<?php echo esc_attr(get_option(ConstantesDataPing::DATAPING_NUM_CLUB)); ?>"/>
        <?php
    }

    public function getForm()
    {
        echo '<form action="options.php" method="POST" name="DataPing_settings" class="DataPing_settings_form">';
        do_settings_sections('DataPing_settings');
        settings_fields('DataPing_settings');
        echo '<div>' . submit_button('Valider la saisie') . '</div>';
        echo '</form>';
    }

    public function equipes_admin()
    {
        $this->_getLayout('equipes');
    }

    public function joueurs_admin()
    {
        $this->_getLayout('joueurs');
    }

    private function _getLayout($view){
        include_once(__DIR__ . '/views/admin/layout.php');
    }

    public function equipes_front($atts, $content)
    {
        $api = AccesFFTTApi::getInstance();
        if (!is_object($api)) {
            return __('Problème lors de la récupération des résultats', 'dataping');
        }

        // Normalise et valide les attributs du shortcode
        $atts = shortcode_atts(array('iddiv' => '', 'idpoule' => ''), (array) $atts, 'equipe');
        $atts['iddiv'] = (string) $atts['iddiv'];
        $atts['idpoule'] = (string) $atts['idpoule'];
        if ($atts['iddiv'] === '' || $atts['idpoule'] === '') {
            return __('Poule ou division incorrecte', 'dataping');
        }

        $listeEquipesM = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'M');
        $listeEquipesF = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'F');
        $listeEquipes = array_merge((array) $listeEquipesM, (array) $listeEquipesF);

        ob_start();
        require __DIR__ . '/views/front/equipes.php';
        return ob_get_clean();
    }

    /**
     * Méthode qui gère les liste de joueurs coté front
     * @param type $atts type: M | F | MF
     * @param type $content
     * @return string
     */
    public function joueurs_front($atts, $content)
    {
        $atts = shortcode_atts(array('type' => 'MF'), (array) $atts, 'joueurs');
        if (in_array($atts['type'], $this->getTypeListeJoueurs(), true)) {
            $listeJoueurs = array();
            $joueurs = new Joueurs($atts['type']);
            ob_start();
            require __DIR__ . '/views/front/joueurs.php';
            return ob_get_clean();
        }
        return __('Erreur de paramètres du shortcode', 'dataping');
    }

    private function getTypeListeJoueurs()
    {
        return $this->typeListeJoueurs;
    }

    /**
     * Hook pour récupérer les données des joueurs en cache
     * Usage: $joueurs = apply_filters('dataping_get_joueurs', 'MF');
     * @param string $type Type de joueurs ('M', 'F', ou 'MF')
     * @return array Tableau d'objets Joueur
     */
    public function get_joueurs_data($type = 'MF')
    {
        if (!in_array($type, $this->typeListeJoueurs, true)) {
            $type = 'MF';
        }
        $joueurs = new Joueurs($type);
        return $joueurs->getJoueurs($type);
    }

    /**
     * Hook pour récupérer les données des équipes en cache
     * Usage: $equipes = apply_filters('dataping_get_equipes', 'M');
     * @param string $type Type d'équipes ('M' ou 'F', null pour toutes)
     * @return array Tableau d'équipes
     */
    public function get_equipes_data($type = null)
    {
        $api = AccesFFTTApi::getInstance();
        if (!is_object($api)) {
            return array();
        }

        if ($type === 'M' || $type === 'F') {
            return $api->getEquipesByClub(ParametresDataPing::getNumClub(), $type);
        }

        // Retourne toutes les équipes (M et F)
        $equipesM = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'M');
        $equipesF = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'F');
        return array_merge((array) $equipesM, (array) $equipesF);
    }

    /**
     * Hook pour récupérer le classement d'une poule en cache
     * Usage: $classement = apply_filters('dataping_get_classement_poule', null, array('division' => 'D1', 'poule' => 'A'));
     * @param mixed $value Valeur par défaut (ignorée)
     * @param array $params Paramètres avec 'division' et 'poule'
     * @return array Classement de la poule
     */
    public function get_classement_poule_data($value, $params)
    {
        $api = AccesFFTTApi::getInstance();
        if (!is_object($api) || !isset($params['division']) || !isset($params['poule'])) {
            return array();
        }
        return $api->getPouleClassement($params['division'], $params['poule']);
    }

    /**
     * Hook pour récupérer les rencontres d'une poule en cache
     * Usage: $rencontres = apply_filters('dataping_get_rencontres_poule', null, array('division' => 'D1', 'poule' => 'A'));
     * @param mixed $value Valeur par défaut (ignorée)
     * @param array $params Paramètres avec 'division' et 'poule'
     * @return array Rencontres de la poule
     */
    public function get_rencontres_poule_data($value, $params)
    {
        $api = AccesFFTTApi::getInstance();
        if (!is_object($api) || !isset($params['division']) || !isset($params['poule'])) {
            return array();
        }
        return $api->getPouleRencontres($params['division'], $params['poule']);
    }
}

new DataPing();

