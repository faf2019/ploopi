<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?
include_once('./modules/system/class_user.php');
include_once './modules/interop-CIECRAM/class_ciecram.php';

// update des userss par fichiers : traitement du fichier

    // déclarations
    $tmp = array();
    
    //init user class
    $user= new user();
    $cie = new ciecram();
    $login=0;
    
    if ($typefile=="ugecam")
    {
        // tableau de lignes du fichier
        $lignes = file($path.'user_ugecam.txt');
    }
    else            
    {
        $lignes = file($path.'user_cram.txt');
        $t = array();
        $t[18]="";$t[19]="";$t[21]="";
    }

    $cpte=0;
    // parcours du tableau
    foreach($lignes as $userl)
    {
        
        if ($typefile=="ugecam")
            $t = explode(';',$userl);
        else
        {
            $t[1]=trim(substr($userl,0,6)); // login
            $t[0]=trim('M'); // sexe
            $t[3]=trim(substr($userl,55,1)); // etat civil
            $t[4]=trim(substr($userl,7,20)); // nom
            $t[5]=trim(substr($userl,57,16)); // nom jeune fille
            $t[6]=trim(substr($userl,27,15)); // prenom
            $t[7]=trim(substr($userl,73,10)); // date de naissance
            $t[8]=trim(substr($userl,196,5)); // code structure
            $t[11]=trim(substr($userl,202,10)); // date entree
            $t[13]=trim(substr($userl,212,10)); // date depart
            $t[16]=trim(substr($userl,83,8)); // adresse
            $t[17]=trim(substr($userl,91,27)); // adresse
            $t[20]=trim(substr($userl,118,35)); // adresse 2
            $t[22]=trim(substr($userl,153,6)); // code postal
            $t[23]=trim(substr($userl,159,29)); // ville    
                    
            //echo $t[1]." - ".$t[3]." - ".$t[4]." - ".$t[5]." - ".$t[6]." - ".$t[7]." - ".$t[8]." - ".$t[11]." - ".$t[13]." - ";
        }   
        
        if (is_numeric($t[1]))
        {
            $sql = "SELECT id FROM ploopi_user where id=".$t[1];
            
            $res = $db->query($sql);
            if (!($fields = $db->fetchrow($res)))
            {
                // a une nouvelle personne
                $cpte++;;
                $db->query("insert into ploopi_user set id=".$t[1]);
            }
            $login=$t[1];
            // open current user
        
            $user->open($login);
            
            $user->fields['sexe']=$t[0];
            $user->fields['login']=$login;
            $user->fields['EtatCivil']=$t[3];
            $user->fields['lastname']=$t[4];
            $user->fields['NomJeuneFille']=$t[5];
            $user->fields['firstname']=$t[6];
            
            if ($user->fields['date_creation']=="")
            {
                $user->fields['date_creation']= date("YmdHis");
                $user->fields['NouvelAgent']="Oui";
            }
                
            $user->fields['DateNaiss']=str_replace(".","/",$t[7]);
            $user->fields['Structure']=$t[8];
            $user->fields['DateEntree']=str_replace(".","/",$t[11]); // date embauche
            $user->fields['DateDepart']=str_replace(".","/",$t[13]);
            $user->fields['address']=$t[16]." ".$t[17]." ".$t[18]." ".$t[19];
            $user->fields['Adresse2']=$t[20]." ".$t[21];
            $user->fields['CodePostal']=$t[22];
            $user->fields['Ville']=$t[23];
            
            // calcul de l'etablissement, un peu empirique
            if ($login>=20000 && $login<30000) 
                $user->fields['Etab']=3;
            else 
            {
             if ($login>=15000 && $login<20000)
                $user->fields['Etab']=4; //CTO
             else
             {
                if ($login>=30000 && $login<50000)
                    $user->fields['Etab']=7;  // IRR + LSC
                else
                {
                    if ($login>=10000 && $login<11000)
                        $user->fields['Etab']=9;
                    else
                    {
                        if ($login>=680000 && $login<690000)
                            $user->fields['Etab']=5;
                        else
                            $user->fields['Etab']=1;
                    }
                }
             }
            } 
            
            if ($user->fields['password']=="")
                $user->fields['password']=md5($cie->GenererPassword($login));
            
            $user->save();
        }
    }
    
    echo $cpte." personnes ont été ajoutées";

?>