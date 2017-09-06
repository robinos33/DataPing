<?php

if (!class_exists('joueurs')) {

    /**
     * Description of joueurs
     *
     * @author robin
     */
    class Joueurs {

        private $_api;
        /**
         * Tableau contenant les joueurs du club
         * @var array Joueur
         */
        private $joueurs;

        public function __construct() {
            $this->_api = AccesFFTTApi::getInstance();
            $licencies = $this->_api->getLicencesByClub(ParametresDataPing::getNumClub());
            $this->setJoueursFromApi($licencies);
        }

        public function getJoueurs($sexe) {
            $joueurs = array();
            switch ($sexe) {
                default:
                case 'MF':
                    $joueurs = $this->joueurs;
                    break;
                case 'F':
                case 'M':
                    foreach ($this->joueurs as $joueur) {
                        if ($joueur->getSexe() === $sexe) {
                            $joueurs[] = $joueur;
                        }
                    }
                    break;
            }
            return $joueurs;
        }

        /**
         * Construction de la liste des joueurs
         * @param array $listeJoueurs
         */
        public function setJoueursFromApi($listeJoueurs) {
            foreach ($listeJoueurs as $joueur) {
                $donneesLicence = $this->_api->getLicence($joueur['licence']);
                $donneesClassement = $this->_api->getJoueur($joueur['licence']);
                if (!is_null($donneesLicence) && !is_null($donneesClassement)) {
                    $this->joueurs[] = new Joueur($donneesLicence, $donneesClassement);
                }
            }
        }

    }

}