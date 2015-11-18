<?php

if (!class_exists('joueur')) {

    class Joueur {

        private $nom;
        private $prenom;
        private $sexe;
        private $licence;
        private $club;
        private $classement;

        /**
         * Initialisation du joueur
         * @param array $donneesLicence
         * @param array|null $donneesClassement
         */
        public function __construct($donneesLicence, $donneesClassement) {
            $this->setClassement($donneesClassement);
            $this->setClub($donneesLicence['numclub']);
            $this->setNom($donneesLicence['nom']);
            $this->setPrenom($donneesLicence['prenom']);
            $this->setSexe($donneesLicence['sexe']);
            $this->setLicence($donneesLicence['numclub']);
        }

        public function getNom() {
            return $this->nom;
        }

        public function getPrenom() {
            return $this->prenom;
        }

        public function getSexe() {
            return $this->sexe;
        }

        public function getLicence() {
            return $this->licence;
        }

        public function getClub() {
            return $this->club;
        }

        /**
         *
         * @return Classement
         */
        public function getClassement() {
            return $this->classement;
        }

        public function setNom($nom) {
            $this->nom = $nom;
        }

        public function setPrenom($prenom) {
            $this->prenom = $prenom;
        }

        public function setSexe($sexe) {
            $this->sexe = $sexe;
        }

        public function setLicence($licence) {
            $this->licence = $licence;
        }

        public function setClub($numClub) {
            $club = new Club($numClub);
            $this->club = $club;
        }

        public function setClassement($donneesClassement) {
            $this->classement = new Classement($donneesClassement);
        }

    }

}