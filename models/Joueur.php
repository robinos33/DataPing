<?php

if (!class_exists('joueur')) {

    class Joueur {

        private $nom;
        private $prenom;
        private $sexe;
        private $licence;
        private $club;
        private $classement;
        private $categorie;
        private $etranger;

        /**
         * Initialisation du joueur
         * @param array $donneesLicence
         * @param array|null $donneesClassement
         */
        public function __construct($donneesLicence, $donneesClassement) {
            $this->setClassement($donneesClassement);
            $this->setClub($donneesLicence['club'] ?? '');
            $this->setNom($donneesLicence['nom'] ?? '');
            $this->setPrenom($donneesLicence['prenom'] ?? '');
            $this->setSexe($donneesLicence['sexe'] ?? '');
            $this->setLicence($donneesLicence['licence'] ?? '');
            $this->setCategorie($donneesClassement['cat'] ?? '');
            $this->setEtranger($donneesClassement['natio'] ?? 'F');
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
            // Ne pas créer d'objet Club pour éviter des appels API inutiles
            // Tous les joueurs sont du même club, on stocke juste le numéro
            $this->club = $numClub;
        }

        public function setClassement($donneesClassement) {
            $this->classement = new Classement($donneesClassement);
        }

        public function getCategorie() {
            return $this->categorie;
        }

        public function setCategorie($categorie) {
            $this->categorie = $categorie;
        }

        public function getEtranger() {
            return $this->etranger;
        }

        public function setEtranger($natio) {
            // Si natio == 'E', c'est un joueur étranger
            $this->etranger = ($natio === 'E');
        }

        public function isEtranger() {
            return $this->etranger;
        }

    }

}