<?php

class ploopiException extends Exception
{
    private $arrTrace;

    /**
     * Détermine si l'origine de l'exception est une erreur, ne sert plus pour le moment
     *
     * @var boolean
     */
    private $booIsError;

    /**
    * Constructeur
    */
    public function __construct($strMsg, $intCode = E_USER_ERROR, $strFile = null, $intLine = null, $arrContext = null, $booIsError = false)
    {
        // Récupération du trace
        $this->arrTrace = $this->getTrace();

        // Appel direct, on ajoute la dernière erreur à la pile d'erreur
        if ($arrContext == null) $this->arrTrace = array_merge(array(array('file' => $this->file, 'line' => $this->line)), $this->arrTrace);

        if (!is_null($strFile)) $this->file = $strFile;
        if (!is_null($intLine)) $this->line = $intLine;

        $this->booIsError = $booIsError;

        parent::__construct($strMsg, $intCode);

        // Compteur global du nombre d'erreurs

        ploopiError::writeLog($intCode, $strMsg, $this->arrTrace);
    }

    /**
     * Affichage de l'erreur
     *
     * @param boolean $booKill true si le script doit être arrêté
     */

    public function show($booKill = false)
    {
        ploopiError::show($this->code, $this->message, $this->arrTrace);

        // critical error or kill
        if ($this->code == E_ERROR || $this->code == E_PARSE || $this->code == E_USER_ERROR || $booKill) ploopiKernel::kill();
    }
}

