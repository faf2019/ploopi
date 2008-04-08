<?php
/**
 * ploopi_exception encapsule une erreur (E_WARNING, E_NOTICE, ...) PHP dans un objet héritant de Exception
 */
 
class ploopi_exception extends Exception 
{
    protected $_context = array();

    function __construct($level, $string, $file, $line, $context)
    {
        parent::__construct($string);
        // on modifie la ligne et le fichier pour ne pas avoir la ligne et le fichier d'où l'exception est levée
        $this->file = $file; 
        $this->line = $line;
        $this->_level = $level;
        $this->_context = $context;
    }
    

}
?>