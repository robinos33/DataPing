<?php

/**
 * Description of Classement
 * Classe simplifiée pour ne contenir que les données essentielles
 *
 * @author robin
 */
class Classement {

    private $pointsMensuels;
    private $pointsOfficiels;
    private $progressionAnnuelle;
    private $progressionMensuelle;
    private $classementOfficiel;

    public function __construct($datas) {
        $this->setPointsMensuels($datas['point'] ?? 0);
        $this->setPointsOfficiels($datas['valcla'] ?? 0);
        $this->setProgressionAnnuelle($datas['progann'] ?? 0);
        $this->setProgressionMensuelle($datas['progmois'] ?? 0);
        $this->setClassementOfficiel($datas['clast'] ?? '');
    }

    public function getPointsMensuels() {
        return $this->pointsMensuels;
    }

    public function setPointsMensuels($pointsMensuels) {
        $this->pointsMensuels = round($pointsMensuels, 2);
    }

    public function getPointsOfficiels() {
        return $this->pointsOfficiels;
    }

    public function setPointsOfficiels($pointsOfficiels) {
        $this->pointsOfficiels = round($pointsOfficiels, 2);
    }

    public function getProgressionAnnuelle() {
        return $this->progressionAnnuelle;
    }

    public function setProgressionAnnuelle($progressionAnnuelle) {
        $this->progressionAnnuelle = round($progressionAnnuelle, 2);
    }

    public function getProgressionMensuelle() {
        return $this->progressionMensuelle;
    }

    public function setProgressionMensuelle($progressionMensuelle) {
        $this->progressionMensuelle = round($progressionMensuelle, 2);
    }

    public function getClassementOfficiel() {
        return $this->classementOfficiel;
    }

    public function setClassementOfficiel($classementOfficiel) {
        $this->classementOfficiel = $classementOfficiel;
    }

}
