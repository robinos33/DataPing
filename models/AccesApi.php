<?php

if (!class_exists('AccesApi')) {

    /**
     * @author Robin Aldasoro -
     * Source originale : classe AccesApi VincentBab vincentbab@gmail.com
     */
    class AccesApi {

        /**
         * @var string $appId ID de l'application fourni par la FFTT (ex: AM001)
         */
        protected $appId;

        /**
         * @var string $appKey Mot de passe fourni par la FFTT
         */
        protected $appKey;

        /**
         * @var string $serial Serial de l'utilisateur
         */
        protected $serial;

        /**
         * @var string $ipSource
         */
        protected $ipSource;

        public function __construct($appId, $appKey) {
            $this->appId = $appId;
            $this->appKey = $appKey;

            libxml_use_internal_errors(true);
        }

        public function getAppId() {
            return $this->appId;
        }

        public function getAppKey() {
            return $this->appKey;
        }

        public function setSerial($serial) {
            $this->serial = $serial;

            return $this;
        }

        public function getSerial() {
            return $this->serial;
        }

        public function setIpSource($ipSource) {
            $this->ipSource = $ipSource;

            return $this;
        }

        public function getIpSource() {
            return $this->ipSource;
        }

        public function initialization() {
            return AccesApi::getObject($this->getData('http://www.fftt.com/mobile/pxml/xml_initialisation.php', array()));
        }

        public function getClubsByDepartement($departement) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_club_dep2.php', array('dep' => $departement)), 'club');
        }

        public function getClub($numero) {
            return AccesApi::getObject($this->getData('http://www.fftt.com/mobile/pxml/xml_club_detail.php', array('club' => $numero)), 'club');
        }

        public function getJoueur($licence) {
            $joueur = AccesApi::getObject($this->getData('http://www.fftt.com/mobile/pxml/xml_joueur.php', array('licence' => $licence, 'auto' => 1)), 'joueur');

            if (!isset($joueur['licence'])) {
                return null;
            }

            if (empty($joueur['natio'])) {
                $joueur['natio'] = 'F';
            }

            $joueur['photo'] = "http://www.fftt.com/espacelicencie/photolicencie/{$joueur['licence']}_.jpg";
            $joueur['progmois'] = round($joueur['point'] - $joueur['apoint'], 2); // Progression mensuelle
            $joueur['progann'] = round($joueur['point'] - $joueur['valinit'], 2); // Progression annuelle

            return $joueur;
        }

        public function getJoueurParties($licence) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_partie_mysql.php', array('licence' => $licence, 'auto' => 1)), 'partie');
        }

        public function getJoueurPartiesSpid($licence) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_partie.php', array('numlic' => $licence)), 'resultat');
        }

        public function getJoueurHistorique($licence) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_histo_classement.php', array('numlic' => $licence)), 'histo');
        }

        public function getJoueursByName($nom, $prenom = '') {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_liste_joueur.php', array('nom' => $nom, 'prenom' => $prenom)), 'joueur');
        }

        public function getJoueursByClub($club) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_liste_joueur.php', array('club' => $club)), 'joueur');
        }

        public function getEquipesByClub($club, $type = null) {
            if ($type && !in_array($type, array('M', 'F'))) {
                $type = 'M';
            }

            $teams = AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_equipe.php', array('numclu' => $club, 'type' => $type)), 'equipe');

            foreach ($teams as &$team) {
                $params = array();
                parse_str($team['liendivision'], $params);

                $team['idpoule'] = $params['cx_poule'];
                $team['iddiv'] = $params['D1'];
            }

            return $teams;
        }

        public function getPoules($division) {
            $poules = AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_result_equ.php', array('action' => 'poule', 'D1' => $division)), 'poule');

            foreach ($poules as &$poule) {
                $params = array();
                parse_str($poule['lien'], $params);

                $poule['idpoule'] = $params['cx_poule'];
                $poule['iddiv'] = $params['D1'];
            }

            return $poules;
        }

        public function getPouleClassement($division, $poule = null) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_result_equ.php', array('auto' => 1, 'action' => 'classement', 'D1' => $division, 'cx_poule' => $poule)), 'classement');
        }

        public function getPouleRencontres($division, $poule = null) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_result_equ.php', array('auto' => 1, 'D1' => $division, 'cx_poule' => $poule)), 'tour');
        }

        public function getIndivGroupes($division) {
            $groupes = AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_result_indiv.php', array('action' => 'poule', 'res_division' => $division)), 'tour');

            foreach ($groupes as &$groupe) {
                $params = array();
                parse_str($groupe['lien'], $params);

                if (isset($params['cx_tableau'])) {
                    $groupe['idgroupe'] = $params['cx_tableau'];
                } else {
                    $groupe['idgroupe'] = null;
                }
                $groupe['iddiv'] = $params['res_division'];
            }

            return $groupes;
        }

        public function getGroupeClassement($division, $groupe = null) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_result_indiv.php', array('action' => 'classement', 'res_division' => $division, 'cx_tableau' => $groupe)), 'classement');
        }

        public function getGroupeRencontres($division, $groupe = null) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_result_indiv.php', array('action' => 'partie', 'res_division' => $division, 'cx_tableau' => $groupe)), 'partie');
        }

        public function getOrganismes($type) {
            // Zone / Ligue / Departement
            if (!in_array($type, array('Z', 'L', 'D'))) {
                $type = 'L';
            }

            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_organisme.php', array('type' => $type)), 'organisme');
        }

        public function getEpreuves($organisme, $type) {
            // Equipe / Individuelle
            if (!in_array($type, array('E', 'I'))) {
                $type = 'E';
            }

            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_epreuve.php', array('type' => $type, 'organisme' => $organisme)), 'epreuve');
        }

        public function getDivisions($organisme, $epreuve, $type = 'E') {
            // Equipe / Individuelle
            if (!in_array($type, array('E', 'I'))) {
                $type = 'E';
            }

            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_division.php', array('organisme' => $organisme, 'epreuve' => $epreuve, 'type' => $type)), 'division');
        }

        public function getRencontre($link) {
            $params = array();
            parse_str($link, $params);

            return AccesApi::getObject($this->getData('http://www.fftt.com/mobile/pxml/xml_chp_renc.php', $params), null);
        }

        public function getLicencesByName($nom, $prenom = '') {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_liste_joueur_o.php', array('nom' => strtoupper($nom), 'prenom' => ucfirst($prenom))), 'joueur');
        }

        public function getLicencesByClub($club) {
            return AccesApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_liste_joueur_o.php', array('club' => $club)), 'joueur');
        }

        public function getLicence($licence) {
            return AccesApi::getObject($this->getData('http://www.fftt.com/mobile/pxml/xml_licence.php', array('licence' => $licence)), 'licence');
        }

        public function getData($url, $params = array(), $generateHash = true) {
            if ($generateHash) {
                $params['serie'] = $this->getSerial();
                $params['id'] = $this->getAppId();
                $params['tm'] = date('YmdHis') . substr(microtime(), 2, 3);
                $params['tmc'] = hash_hmac('sha1', $params['tm'], hash('md5', $this->getAppKey(), false));
            }

            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            if ($this->getIpSource()) {
                curl_setopt($curl, CURLOPT_INTERFACE, $this->getIpSource());
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Accept:", // Suprime le header par default de cUrl (Accept: */*)
                "User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Win32)",
                "Content-Type: application/x-www-form-urlencoded",
                "Accept-Encoding: gzip",
                "Connection: Keep-Alive",
            ));
            $data = curl_exec($curl);
            curl_close($curl);

            $xml = simplexml_load_string($data);

            if (!$xml) {
                return false;
            }

            // Petite astuce pour transformer simplement le XML en tableau
            return json_decode(json_encode($xml), true);
        }

        public static function getCollection($data, $key = null) {
            if (empty($data)) {
                return array();
            }

            if ($key) {
                if (!array_key_exists($key, $data)) {
                    return array();
                }
                $data = $data[$key];
            }

            return isset($data[0]) ? $data : array($data);
        }

        public static function getObject($data, $key = null) {
            if ($key && $data !== false) {
                return array_key_exists($key, $data) ? $data[$key] : null;
            } else {
                return empty($data) ? null : $data;
            }
        }

        public static function generateSerial() {
            $serial = '';
            for ($i = 0; $i < 15; $i++) {
                $serial .= chr(mt_rand(65, 90)); //(A-Z)
            }

            return $serial;
        }

    }

}