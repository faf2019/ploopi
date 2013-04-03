<?php

/*

    SOUNDEX FR

    �douard BERG� � 12.2007 v1.2

---------------------------------------------------------------------------------------------------------
Cet algorithme et son application en PHP sont tous deux distribu�s sous la licence

        Creative Commons Paternit� - Pas d'Utilisation Commerciale 2.0
            http://creativecommons.org/licenses/by-nc/2.0/fr/

Vous �tes libres :
    - de reproduire, distribuer et communiquer cette cr�ation au public
    - de modifier cette cr�ation

Selon les conditions suivantes :
    - Paternit�. Vous devez citer le nom de l'auteur original de la mani�re indiqu�e
        par l'auteur de l'oeuvre ou le titulaire des droits qui vous conf�re cette
        autorisation (mais pas d'une mani�re qui sugg�rerait qu'ils vous soutiennent
        ou approuvent votre utilisation de l'oeuvre).
        Demande de l'auteur: Citer "�douard BERG�" et/ou " http://www.roudoudou.com"
    - Pas d'Utilisation Commerciale. Vous n'avez pas le droit d'utiliser cette
        cr�ation � des fins commerciales sauf autorisation de l'auteur.
---------------------------------------------------------------------------------------------------------

Pourquoi un nouveau Soundex? Il en existe d�j� beaucoup de variantes non? Oui mais...

- Ils g�n�ralisent trop les sons, provocants beaucoup de faux positifs.
- Leur conversion phon�tique est limit�e alors que le fran�ais est complexe, r�sultant beaucoup de manqu�s.

L'objectif est donc clair. Je veux...

- Moins de faux positifs.
- Plus de r�ussite.
- De plus, je trouve pratique d'avoir une conversion qui reste "lisible".
- Une certaine correction des erreurs orthographiques les plus courantes.

Commen�ant mes d�veloppements avec 200 mots de r�f�rence, relire cette liste � chaque fois �tait
ridiculement improductif et source d'erreurs. Je suis alors pass� � la vitesse sup�rieure en me

constituant un corpus de test de 7.000 mots (choisis en "feuilletant" un dictionnaire), ainsi
qu'une page de test pour automatiser les v�rifications et la d�tection d'�ventuelles r�gressions.

Pour terminer la validation de mon algorithme, j'ai t�l�charg� une version fran�aise du
dictionnaire myspell pour l'ajouter � ma page de test. Cette ultime v�rification me permit
de corriger quelques imperfections. Au final, cet algorithme a �t� test� avec plus de 70.000 mots.

Attention, il n'est pas parfait!  Mais j'ai enfin eu ce que je voulais, avec tr�s peu de concessions,
� savoir un algorithme qui se comporte bien avec des mots fran�ais. L'occasion pour moi de remercier
quelques personnes:

- Fr�d�ric Brouard pour son article sur les soundex.
http://sqlpro.developpez.com/cours/soundex/

- Florent Bruneau de qui j'ai repris quelques morceaux de code. Lui m�me inspir� par Fr�d�ric Brouard.
http://blog.mymind.fr/post/2007/03/15/Soundex-Francais

- Christophe Pythoud et Vazkor (Jean-Claude M.) pour leur immense travail sur le dictionnaire myspell.
http://perso.latribu.com/rocky2/index.html (Le chien n'est pas m�chant)


Une nouvelle version?

Je n'ai pas encore d'axe de travail. Soit je me sers de mon corpus de test pour compacter le code produit
tout en gardant l'unicit� de sens, soit j'utiliserai un dictionnaire. Mais on n'en est pas encore l�.

�douard BERG�
*/

function phonetique($sIn)
{
$accents = array('�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E','�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A',
                '�' => 'A', '�' => 'A', '�' => 'E','�' => 'I', '�' => 'I', '�' => 'I', '�' => 'I',
                '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'OEU',
                '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'U','�' => 'N', '�' => 'S', '�' => 'E');

$min2maj = array('�' => '�', '�' => '�', '�' => '�', '�' => '�','�' => '�', '�' => '�', '�' => '�', '�' => 'A',
                '�' => 'A', '�' => '�', '�' => '�', '�' => '�', '�' => '�', '�' => '�', '�' => '�', '�' => '�',
                '�' => '�', '�' => '�', '�' => '�', '�' => '�','�' => '�', '�' => '�', '�' => '�',
                '�' => '�', '�' => '�', '�' => '�', '�' => '�','�' => '�', '�' => '�', '�' => 'S');


//$sIn = utf8_decode($sIn);                     // Selon votre impl�mentation, vous aurez besoin de d�coder ce qui arrive pour les caract�res sp�ciaux
$sIn = strtr( $sIn, $min2maj);                  // minuscules accentu�es ou compos�es en majuscules simples
$sIn = strtr( $sIn, $accents);                  // majuscules accentu�es ou compos�es en majuscules simples
$sIn = strtoupper( $sIn );                      // on passe tout le reste en majuscules
$sIn = preg_replace( '`[^A-Z]`', '', $sIn );    // on garde uniquement les lettres de A � Z

$sBack=$sIn;                                    // on sauve le code (utilis� pour les mots tr�s courts)

$sIn = preg_replace( '`O[O]+`', 'OU', $sIn );   // pr� traitement OO... -> OU
$sIn = preg_replace( '`SAOU`', 'SOU', $sIn );   // pr� traitement SAOU -> SOU
$sIn = preg_replace( '`OES`', 'OS', $sIn );     // pr� traitement OES -> OS
$sIn = preg_replace( '`CCH`', 'K', $sIn );      // pr� traitement CCH -> K
$sIn = preg_replace( '`CC([IYE])`', 'KS$1', $sIn ); // CCI CCY CCE
$sIn = preg_replace( '`(.)\1`', '$1', $sIn );   // supression des r�p�titions

// quelques cas particuliers
if ($sIn=="CD") return($sIn);
if ($sIn=="BD") return($sIn);
if ($sIn=="BV") return($sIn);
if ($sIn=="TABAC") return("TABA");
if ($sIn=="FEU") return("FE");
if ($sIn=="FE") return($sIn);
if ($sIn=="FER") return($sIn);
if ($sIn=="FIEF") return($sIn);
if ($sIn=="FJORD") return($sIn);
if ($sIn=="GOAL") return("GOL");
if ($sIn=="FLEAU") return("FLEO");
if ($sIn=="HIER") return("IER");
if ($sIn=="HEU") return("E");
if ($sIn=="HE") return("E");
if ($sIn=="OS") return($sIn);
if ($sIn=="RIZ") return("RI");
if ($sIn=="RAZ") return("RA");

// pr�-traitements
$sIn = preg_replace( '`OIN[GT]$`', 'OIN', $sIn );                                   // terminaisons OING -> OIN
$sIn = preg_replace( '`E[RS]$`', 'E', $sIn );                                       // supression des terminaisons infinitifs et participes pluriels
$sIn = preg_replace( '`(C|CH)OEU`', 'KE', $sIn );                                   // pr� traitement OEU -> EU
$sIn = preg_replace( '`MOEU`', 'ME', $sIn );                                        // pr� traitement OEU -> EU
$sIn = preg_replace( '`OE([UI]+)([BCDFGHJKLMNPQRSTVWXZ])`', 'E$1$2', $sIn );        // pr� traitement OEU OEI -> E
$sIn = preg_replace( '`^GEN[TS]$`', 'JAN', $sIn );                                  // pr� traitement GEN -> JAN
$sIn = preg_replace( '`CUEI`', 'KEI', $sIn );                                       // pr� traitement accueil
$sIn = preg_replace( '`([^AEIOUYC])AE([BCDFGHJKLMNPQRSTVWXZ])`', '$1E$2', $sIn );   // pr� traitement AE -> E
$sIn = preg_replace( '`AE([QS])`', 'E$1', $sIn );                                   // pr� traitement AE -> E
$sIn = preg_replace( '`AIE([BCDFGJKLMNPQRSTVWXZ])`', 'AI$1', $sIn );                // pr�-traitement AIE(consonne) -> AI
$sIn = preg_replace( '`ANIEM`', 'ANIM', $sIn );                                     // pr� traitement NIEM -> NIM
$sIn = preg_replace( '`(DRA|TRO|IRO)P$`', '$1', $sIn );                             // P terminal muet
$sIn = preg_replace( '`(LOM)B$`', '$1', $sIn );                                     // B terminal muet
$sIn = preg_replace( '`(RON|POR)C$`', '$1', $sIn );                                 // C terminal muet
$sIn = preg_replace( '`PECT$`', 'PET', $sIn );                                      // C terminal muet
$sIn = preg_replace( '`ECUL$`', 'CU', $sIn );                                       // L terminal muet
$sIn = preg_replace( '`(CHA|CA|E)M(P|PS)$`', '$1N', $sIn );                         // P ou PS terminal muet
$sIn = preg_replace( '`(TAN|RAN)G$`', '$1', $sIn );                                 // G terminal muet


// sons YEUX
$sIn = preg_replace( '`([^VO])ILAG`', '$1IAJ', $sIn );
$sIn = preg_replace( '`([^TRH])UIL(AR|E)(.+)`', '$1UI$2$3', $sIn );
$sIn = preg_replace( '`([G])UIL([AEO])`', '$1UI$2', $sIn );
$sIn = preg_replace( '`([NSPM])AIL([AEO])`', '$1AI$2', $sIn );
$convMIn  = array("DILAI","DILON","DILER","DILEM","RILON","TAILE","GAILET","AILAI","AILAR",
"OUILA","EILAI","EILAR","EILER","EILEM","REILET","EILET","AILOL" );
$convMOut = array( "DIAI", "DION","DIER", "DIEM", "RION", "TAIE", "GAIET", "AIAI", "AIAR",
"OUIA", "AIAI", "AIAR", "AIER", "AIEM",  "RAIET", "EIET", "AIOL" );
$sIn = str_replace( $convMIn, $convMOut, $sIn );
$sIn = preg_replace( '`([^AEIOUY])(SC|S)IEM([EA])`', '$1$2IAM$3', $sIn );   // IEM -> IAM
$sIn = preg_replace( '`^(SC|S)IEM([EA])`', '$1IAM$2', $sIn );               // IEM -> IAM

// MP MB -> NP NB
$convMIn  = array( 'OMB', 'AMB', 'OMP', 'AMP', 'IMB', 'EMP','GEMB','EMB', 'UMBL','CIEN');
$convMOut = array( 'ONB', 'ANB', 'ONP', 'ANP', 'INB', 'ANP','JANB','ANB', 'INBL','SIAN');
$sIn = str_replace( $convMIn, $convMOut, $sIn );

// Sons en K
$sIn = preg_replace( '`^ECHO$`', 'EKO', $sIn );     // cas particulier �cho
$sIn = preg_replace( '`^ECEUR`', 'EKEUR', $sIn );   // cas particulier �c�ur�
// Chol�ra Ch�ur mais pas chocolat!
$sIn = preg_replace( '`^CH(OG+|OL+|OR+|EU+|ARIS|M+|IRO|ONDR)`', 'K$1', $sIn );              //En d�but de mot
$sIn = preg_replace( '`(YN|RI)CH(OG+|OL+|OC+|OP+|OM+|ARIS|M+|IRO|ONDR)`', '$1K$2', $sIn );  //Ou devant une consonne
$sIn = preg_replace( '`CHS`', 'CH', $sIn );
$sIn = preg_replace( '`CH(AIQ)`', 'K$1', $sIn );
$sIn = preg_replace( '`^ECHO([^UIPY])`', 'EKO$1', $sIn );
$sIn = preg_replace( '`ISCH(I|E)`', 'ISK$1', $sIn );
$sIn = preg_replace( '`^ICHT`', 'IKT', $sIn );
$sIn = preg_replace( '`ORCHID`', 'ORKID', $sIn );
$sIn = preg_replace( '`ONCHIO`', 'ONKIO', $sIn );
$sIn = preg_replace( '`ACHIA`', 'AKIA', $sIn );         // retouche ACHIA -> AKIA
$sIn = preg_replace( '`([^C])ANICH`', '$1ANIK', $sIn ); // ANICH -> ANIK    1/2
$sIn = preg_replace( '`OMANIK`', 'OMANICH', $sIn );     // cas particulier  2/2
$sIn = preg_replace( '`ACHY([^D])`', 'AKI$1', $sIn );
$sIn = preg_replace( '`([AEIOU])C([BDFGJKLMNPQRTVWXZ])`', '$1K$2', $sIn ); // voyelle, C, consonne sauf H
$convPrIn  = array('EUCHA','YCHIA','YCHA','YCHO','YCHED','ACHEO','RCHEO','RCHES',
'ECHN','OCHTO','CHORA','CHONDR','CHORE','MACHM','BRONCHO','LICHOS','LICHOC');
$convPrOut = array('EKA',  'IKIA', 'IKA', 'IKO',  'IKED','AKEO','RKEO',  'RKES',
'EKN',  'OKTO', 'KORA', 'KONDR' ,'KORE' ,'MAKM', 'BRONKO', 'LIKOS', 'LIKOC');
$sIn = str_replace( $convPrIn, $convPrOut, $sIn );

// Weuh (perfectible)
$convPrIn  = array( 'WA','WO', 'WI','WHI','WHY','WHA','WHO');
$convPrOut = array( 'OI', 'O','OUI','OUI','OUI','OUA', 'OU');
$sIn = str_replace( $convPrIn, $convPrOut, $sIn );

// Gueu, Gneu, Jeu et quelques autres
$convPrIn  = array( 'GNES','GNET','GNER','GNE',  'GI', 'GNI','GNA','GNOU','GNUR','GY','OUGAIN',
'AGEOL', 'AGEOT','GEOLO','GEOM','GEOP','GEOG','GEOS','GEORG','GEOR','NGEOT','UGEOT','GEOT','GEOD','GEOC','GEO','GEA','GE',
'QU', 'Q',  'CY', 'CI', 'CN','ICM','CEAT','CE',
'CR', 'CO', 'CUEI','CU', 'VENCA','CA', 'CS','CLEN','CL', 'CZ', 'CTIQ',
'CTIF','CTIC','CTIS','CTIL','CTIO','CTI', 'CTU', 'CTE','CTO','CTR','CT', 'PH', 'TH',
'OW', 'LH', 'RDL', 'CHLO', 'CHR', 'PTIA');
$convPrOut = array( 'NIES','NIET','NIER', 'NE',  'JI',  'NI','NIA','NIOU','NIUR','JI','OUGIN',
'AJOL',  'AJOT','JEOLO','JEOM','JEOP','JEOG','JEOS','JORJ','JEOR','NJOT','UJOT','JEOT','JEOD','JEOC', 'JO','JA' ,'JE',
'K', 'K',  'SI', 'SI', 'KN','IKM', 'SAT','SE',
'KR', 'KO', 'KEI','KU', 'VANSA','KA', 'KS','KLAN','KL', 'KZ', 'KTIK',
'KTIF','KTIS','KTIS','KTIL','KSIO','KTI', 'KTU', 'KTE','KTO','KTR','KT', 'F',  'T',
'OU',  'L',  'RL',  'KLO',  'KR', 'PSIA');
$sIn = str_replace( $convPrIn, $convPrOut, $sIn );

$sIn = preg_replace( '`GU([^RLMBSTPZN])`', 'G$1', $sIn ); // Gueu !
$sIn = preg_replace( '`GNO([MLTNRKG])`', 'NIO$1', $sIn ); // GNO ! Tout sauf S pour gnos
$sIn = preg_replace( '`GNO([MLTNRKG])`', 'NIO$1', $sIn ); // bis -> gnognotte! Si quelqu'un sait le faire en une seule regexp...


// TI -> SI v2.0
$convPrIn  = array( 'BUTIE','BUTIA','BATIA','ANTIEL','RETION','ENTIEL','ENTIAL','ENTIO','ENTIAI','UJETION','ATIEM','PETIEN',
'CETIE','OFETIE','IPETI','LBUTION','BLUTION','LETION','LATION','SATIET');
$convPrOut = array( 'BUSIE','BUSIA','BASIA','ANSIEL','RESION','ENSIEL','ENSIAL','ENSIO','ENSIAI','UJESION','ASIAM','PESIEN',
'CESIE','OFESIE','IPESI','LBUSION','BLUSION','LESION','LASION','SASIET');
$sIn = str_replace( $convPrIn, $convPrOut, $sIn );
$sIn = preg_replace( '`(.+)ANTI(AL|O)`', '$1ANSI$2', $sIn ); // sauf antialcoolique, antialbumine, antialarmer, ...
$sIn = preg_replace( '`(.+)INUTI([^V])`', '$1INUSI$2', $sIn ); // sauf inutilit�, inutilement, diminutive, ...
$sIn = preg_replace( '`([^O])UTIEN`', '$1USIEN', $sIn ); // sauf soutien, ...
$sIn = preg_replace( '`([^DE])RATI[E]$`', '$1RASI$2', $sIn ); // sauf xxxxxcratique, ...
// TIEN TION -> SIEN SION v3.1
$sIn = preg_replace( '`([^SNEU]|KU|KO|RU|LU|BU|TU|AU)T(IEN|ION)`', '$1S$2', $sIn );


// H muet
$sIn = preg_replace( '`([^CS])H`', '$1', $sIn );    // H muet
$sIn = str_replace( "ESH", "ES", $sIn );            // H muet
$sIn = str_replace( "NSH", "NS", $sIn );            // H muet
$sIn = str_replace( "SH", "CH", $sIn );             // ou pas!

// NASALES
$convNasIn  = array( 'OMT','IMB', 'IMP','UMD','TIENT','RIENT','DIENT','IEN',
'YMU','YMO','YMA','YME', 'YMI','YMN','YM', 'AHO','FAIM','DAIM','SAIM','EIN','AINS');
$convNasOut = array( 'ONT','INB', 'INP','OND','TIANT','RIANT','DIANT', 'IN',
'IMU','IMO','IMA','IME', 'IMI','IMN','IN',  'AO', 'FIN','DIN', 'SIN','AIN','INS');
$sIn = str_replace( $convNasIn, $convNasOut, $sIn );
// AIN -> IN v2.0
$sIn = preg_replace( '`AIN$`', 'IN', $sIn );
$sIn = preg_replace( '`AIN([BTDK])`', 'IN$1', $sIn );
// UN -> IN
$sIn = preg_replace( '`([^O])UND`', '$1IND', $sIn ); // aucun mot fran�ais ne commence par UND!
$sIn = preg_replace( '`([JTVLFMRPSBD])UN([^IAE])`', '$1IN$2', $sIn );
$sIn = preg_replace( '`([JTVLFMRPSBD])UN$`', '$1IN', $sIn );
$sIn = preg_replace( '`RFUM$`', 'RFIN', $sIn );
$sIn = preg_replace( '`LUMB`', 'LINB', $sIn );
// EN -> AN
$sIn = preg_replace( '`([^BCDFGHJKLMNPQRSTVWXZ])EN`', '$1AN', $sIn );
$sIn = preg_replace( '`([VTLJMRPDSBFKNG])EN([BRCTDKZSVN])`', '$1AN$2', $sIn ); // deux fois pour les motifs recouvrants malentendu, pendentif, ...
$sIn = preg_replace( '`([VTLJMRPDSBFKNG])EN([BRCTDKZSVN])`', '$1AN$2', $sIn ); // si quelqu'un sait faire avec une seule regexp!
$sIn = preg_replace( '`^EN([BCDFGHJKLNPQRSTVXZ]|CH|IV|ORG|OB|UI|UA|UY)`', 'AN$1', $sIn );
$sIn = preg_replace( '`(^[JRVTH])EN([DRTFGSVJMP])`', '$1AN$2', $sIn );
$sIn = preg_replace( '`SEN([ST])`', 'SAN$1', $sIn );
$sIn = preg_replace( '`^DESENIV`', 'DESANIV', $sIn );
$sIn = preg_replace( '`([^M])EN(UI)`', '$1AN$2', $sIn );
$sIn = preg_replace( '`(.+[JTVLFMRPSBD])EN([JLFDSTG])`', '$1AN$2', $sIn );
// EI -> AI
$sIn = preg_replace( '`([VSBSTNRLPM])E[IY]([ACDFRJLGZ])`', '$1AI$2', $sIn );

// Histoire d'�
$convNasIn  = array( 'EAU', 'EU',  'Y', 'EOI', 'JEA','OIEM','OUANJ','OUA','OUENJ');
$convNasOut = array(   'O',  'E',  'I',  'OI', 'JA' ,'OIM' ,'OUENJ', 'OI','OUANJ');
$sIn = str_replace( $convNasIn, $convNasOut, $sIn );
$sIn = preg_replace( '`AU([^E])`', 'O$1', $sIn ); // AU sans E qui suit

// Les retouches!
$sIn = preg_replace( '`^BENJ`', 'BINJ', $sIn );             // retouche BENJ -> BINJ
$sIn = preg_replace( '`RTIEL`', 'RSIEL', $sIn );            // retouche RTIEL -> RSIEL
$sIn = preg_replace( '`PINK`', 'PONK', $sIn );              // retouche PINK -> PONK
$sIn = preg_replace( '`KIND`', 'KOND', $sIn );              // retouche KIND -> KOND
$sIn = preg_replace( '`KUM(N|P)`', 'KON$1', $sIn );         // retouche KUMN KUMP
$sIn = preg_replace( '`LKOU`', 'LKO', $sIn );               // retouche LKOU -> LKO
$sIn = preg_replace( '`EDBE`', 'EBE', $sIn );               // retouche EDBE pied-b�uf
$sIn = preg_replace( '`ARCM`', 'ARKM', $sIn );              // retouche SCH -> CH
$sIn = preg_replace( '`SCH`', 'CH', $sIn );                 // retouche SCH -> CH
$sIn = preg_replace( '`^OINI`', 'ONI', $sIn );              // retouche d�but OINI -> ONI
$sIn = preg_replace( '`([^NDCGRHKO])APT`', '$1AT', $sIn );  // retouche APT -> AT
$sIn = preg_replace( '`([L]|KON)PT`', '$1T', $sIn );        // retouche LPT -> LT
$sIn = preg_replace( '`OTB`', 'OB', $sIn );                 // retouche OTB -> OB (hautbois)
$sIn = preg_replace( '`IXA`', 'ISA', $sIn );                // retouche IXA -> ISA
$sIn = preg_replace( '`TG`', 'G', $sIn );                   // retouche TG -> G
$sIn = preg_replace( '`^TZ`', 'TS', $sIn );                 // retouche d�but TZ -> TS
$sIn = preg_replace( '`PTIE`', 'TIE', $sIn );               // retouche PTIE -> TIE
$sIn = preg_replace( '`GT`', 'T', $sIn );                   // retouche GT -> T
$sIn = str_replace( "ANKIEM", "ANKILEM", $sIn );            // retouche tranquillement
$sIn = preg_replace( "`(LO|RE)KEMAN`", "$1KAMAN", $sIn );   // KEMAN -> KAMAN
$sIn = preg_replace( '`NT(B|M)`', 'N$1', $sIn );            // retouche TB -> B  TM -> M
$sIn = preg_replace( '`GSU`', 'SU', $sIn );                 // retouche GS -> SU
$sIn = preg_replace( '`ESD`', 'ED', $sIn );                 // retouche ESD -> ED
$sIn = preg_replace( '`LESKEL`','LEKEL', $sIn );            // retouche LESQUEL -> LEKEL
$sIn = preg_replace( '`CK`', 'K', $sIn );                   // retouche CK -> K

// Terminaisons
$sIn = preg_replace( '`USIL$`', 'USI', $sIn );              // terminaisons USIL -> USI
$sIn = preg_replace( '`X$|[TD]S$|[DS]$`', '', $sIn );       // terminaisons TS DS LS X T D S...  v2.0
$sIn = preg_replace( '`([^KL]+)T$`', '$1', $sIn );          // sauf KT LT terminal
$sIn = preg_replace( '`^[H]`', '', $sIn );                  // H pseudo muet en d�but de mot, je sais, ce n'est pas une terminaison
$sBack2=$sIn;                                               // on sauve le code (utilis� pour les mots tr�s courts)
$sIn = preg_replace( '`TIL$`', 'TI', $sIn );                // terminaisons TIL -> TI
$sIn = preg_replace( '`LC$`', 'LK', $sIn );                 // terminaisons LC -> LK
$sIn = preg_replace( '`L[E]?[S]?$`', 'L', $sIn );           // terminaisons LE LES -> L
$sIn = preg_replace( '`(.+)N[E]?[S]?$`', '$1N', $sIn );     // terminaisons NE NES -> N
$sIn = preg_replace( '`EZ$`', 'E', $sIn );                  // terminaisons EZ -> E
$sIn = preg_replace( '`OIG$`', 'OI', $sIn );                // terminaisons OIG -> OI
$sIn = preg_replace( '`OUP$`', 'OU', $sIn );                // terminaisons OUP -> OU
$sIn = preg_replace( '`([^R])OM$`', '$1ON', $sIn );         // terminaisons OM -> ON sauf ROM
$sIn = preg_replace( '`LOP$`', 'LO', $sIn );                // terminaisons LOP -> LO
$sIn = preg_replace( '`NTANP$`', 'NTAN', $sIn );            // terminaisons NTANP -> NTAN
$sIn = preg_replace( '`TUN$`', 'TIN', $sIn );               // terminaisons TUN -> TIN
$sIn = preg_replace( '`AU$`', 'O', $sIn );                  // terminaisons AU -> O
$sIn = preg_replace( '`EI$`', 'AI', $sIn );                 // terminaisons EI -> AI
$sIn = preg_replace( '`R[DG]$`', 'R', $sIn );               // terminaisons RD RG -> R
$sIn = preg_replace( '`ANC$`', 'AN', $sIn );                // terminaisons ANC -> AN
$sIn = preg_replace( '`KROC$`', 'KRO', $sIn );              // terminaisons C muet de CROC, ESCROC
$sIn = preg_replace( '`HOUC$`', 'HOU', $sIn );              // terminaisons C muet de CAOUTCHOUC
$sIn = preg_replace( '`OMAC$`', 'OMA', $sIn );              // terminaisons C muet de ESTOMAC (mais pas HAMAC)
$sIn = preg_replace( '`([J])O([NU])[CG]$`', '$1O$2', $sIn );// terminaisons C et G muet de OUC ONC OUG
$sIn = preg_replace( '`([^GTR])([AO])NG$`', '$1$2N', $sIn );// terminaisons G muet ANG ONG sauf GANG GONG TANG TONG
$sIn = preg_replace( '`UC$`', 'UK', $sIn );                 // terminaisons UC -> UK
$sIn = preg_replace( '`AING$`', 'IN', $sIn );               // terminaisons AING -> IN
$sIn = preg_replace( '`([EISOARN])C$`', '$1K', $sIn );      // terminaisons C -> K
$sIn = preg_replace( '`([ABD-MO-Z]+)[EH]+$`', '$1', $sIn ); // terminaisons E ou H sauf pour C et N
$sIn = preg_replace( '`EN$`', 'AN', $sIn );                 // terminaisons EN -> AN (difficile � faire avant sans avoir des soucis) Et encore, c'est pas top!
$sIn = preg_replace( '`(NJ)EN$`', '$1AN', $sIn );           // terminaisons EN -> AN
$sIn = preg_replace( '`^PAIEM`', 'PAIM', $sIn );            // PAIE -> PAI
$sIn = preg_replace( '`([^NTB])EF$`', '\1', $sIn );         // F muet en fin de mot

$sIn = preg_replace( '`(.)\1`', '$1', $sIn );               // supression des r�p�titions (suite � certains remplacements)

// cas particuliers, bah au final, je n'en ai qu'un ici
$convPartIn  = array( 'FUEL');
$convPartOut = array( 'FIOUL');
$sIn = str_replace( $convPartIn, $convPartOut, $sIn );

// Ce sera le seul code retourn� � une seule lettre!
if ($sIn=='O') return($sIn);

// seconde chance sur les mots courts qui ont souffert de la simplification
if (strlen($sIn)<2)
{
    // Sigles ou abr�viations
    if (preg_match("`[BCDFGHJKLMNPQRSTVWXYZ][BCDFGHJKLMNPQRSTVWXYZ][BCDFGHJKLMNPQRSTVWXYZ][BCDFGHJKLMNPQRSTVWXYZ]*`",$sBack))
        return($sBack);

    if (preg_match("`[RFMLVSPJDF][AEIOU]`",$sBack))
    {
        if (strlen($sBack)==3)
            return(substr($sBack,0,2));// mots de trois lettres suppos�s simples
        if (strlen($sBack)==4)
            return(substr($sBack,0,3));// mots de quatre lettres suppos�s simples
    }

    if (strlen($sBack2)>1) return $sBack2;
}

if (strlen($sIn)>1)
    return substr($sIn,0,16); // Je limite � 16 caract�res mais vous faites comme vous voulez!
else
    return '';
}
