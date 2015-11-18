<?php

/**
 * Description of Classement
 *
 * @author robin
 */
class Classement {

    private $rangNational;
    private $rangRegional;
    private $rangDepartemental;
    private $exRangNational;
    private $exPointsMensuels;
    private $pointsOfficiels;
    private $classementOfficiel;
    private $pointsDebutSaison;
    private $pointsMensuels;
    private $progressionMensuelle;
    private $progressionAnnuelle;

    public function __construct($datas) {
        $this->setRangNational($datas['clglob']);
        $this->setRangRegional($datas['rangreg']);
        $this->setRangDepartemental($datas['rangdep']);
        $this->setExRangNational($datas['aclglob']);
        $this->setExPointsMensuels($datas['apoint']);
        $this->setPointsOfficiels($datas['valcla']);
        $this->setClassementOfficiel($datas['clast']);
        $this->setExPointsMensuels($datas['valinit']);
        $this->setPointsMensuels($datas['point']);
        $this->setProgressionMensuelle($datas['progmois']);
        $this->setProgressionAnnuelle($datas['progann']);
    }

    public function getRangNational() {
        return $this->rangNational;
    }

    public function getRangRegional() {
        return $this->rangRegional;
    }

    public function getRangDepartemental() {
        return $this->rangDepartemental;
    }

    public function getExRangNational() {
        return $this->exRangNational;
    }

    public function getPointsOfficiels() {
        return $this->pointsOfficiels;
    }

    public function getClassementOfficiel() {
        return $this->classementOfficiel;
    }

    public function setClassementOfficiel($classementOfficiel) {
        $this->classementOfficiel = $classementOfficiel;
    }

    public function getPointsDebutSaison() {
        return $this->pointsDebutSaison;
    }

    public function getPointsMensuels() {
        return $this->pointsMensuels;
    }

    public function getExPointsMensuels() {
        return $this->exPointsMensuels;
    }

    public function setRangNational($rangNational) {
        $this->rangNational = $rangNational;
    }

    public function setRangRegional($rangRegional) {
        $this->rangRegional = $rangRegional;
    }

    public function setRangDepartemental($rangDepartemental) {
        $this->rangDepartemental = $rangDepartemental;
    }

    public function setExRangNational($exRangNational) {
        $this->exRangNational = $exRangNational;
    }

    public function setPointsOfficiels($pointsOfficiels) {
        $this->pointsOfficiels = $pointsOfficiels;
    }

    public function setPointsDebutSaison($pointsDebutSaison) {
        $this->pointsDebutSaison = $pointsDebutSaison;
    }

    public function setPointsMensuels($pointsMensuels) {
        $this->pointsMensuels = round($pointsMensuels, 2);
    }

    public function setExPointsMensuels($exPointsMensuels) {
        $this->exPointsMensuels = $exPointsMensuels;
    }

    public function getProgressionMensuelle() {
        return $this->progressionMensuelle;
    }

    public function setProgressionMensuelle($progressionMensuelle) {
        $this->progressionMensuelle = $progressionMensuelle;
    }

    public function getProgressionAnnuelle() {
        return $this->progressionAnnuelle;
    }

    public function setProgressionAnnuelle($progressionAnnuelle) {
        $this->progressionAnnuelle = $progressionAnnuelle;
    }

}
