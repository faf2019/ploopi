# Licence de Ploopi

Ploopi est un **logiciel libre**. Ce document dit sous quelle licence il est diffusé, ce
qu'elle permet et impose, quelles licences portent les composants tiers, et quels
points restent à régler pour que l'ensemble soit entièrement libre.

> Ce document est une synthèse pratique, pas un avis juridique. En cas de doute sur un
> usage précis, se référer au texte de la licence ([`LICENSE`](../LICENSE)) et, au
> besoin, à un juriste.

---

## 1. En bref

| | |
|---|---|
| Licence | **GNU GPL version 2 ou ultérieure** — identifiant SPDX `GPL-2.0-or-later` |
| Texte | [`LICENSE`](../LICENSE) (GPL v2) ; la GPL v3 est sur <https://www.gnu.org/licenses/gpl-3.0.html> |
| Portée | tout le dépôt, sauf les composants tiers listés au § 4, qui gardent leur licence |
| Titulaires des droits | Ovensia, HeXad et les contributeurs, chacun pour son code (§ 3) |
| Origine | fork de DIMS, diffusé en GPL par la société Netlor |

## 2. La licence de Ploopi

### Pourquoi « version 2 ou ultérieure »

Chaque fichier source porte l'en-tête :

> Ploopi is free software; you can redistribute it and/or modify it under the terms of
> the GNU General Public License as published by the Free Software Foundation; **either
> version 2 of the License, or (at your option) any later version.**

C'est donc la **GPL-2.0-or-later** qui s'applique, et c'est ce que déclare désormais
`composer.json` (il indiquait `GPL-2.0`, identifiant SPDX obsolète et ambigu).

Le « ou ultérieure » n'est pas un détail : plusieurs composants indispensables ne sont
compatibles qu'avec la GPL v3 (bibliothèques sous licence Apache 2.0 comme le SDK AWS,
ou sous GPL v3 comme jscolor et nanogallery2 — voir § 4). Grâce à cette
clause, Ploopi peut être combiné avec eux : **l'ensemble distribué s'utilise de fait sous
les termes de la GPL v3**. Il faut conserver cette clause dans tout nouveau fichier.

### Ce que la licence permet

- utiliser Ploopi pour tout usage, y compris commercial, sans redevance ;
- l'installer pour soi ou pour des clients, l'héberger et le proposer en ligne ;
- l'étudier et le modifier, y compris sans publier ses modifications tant qu'on ne
  **distribue** pas le logiciel ;
- le redistribuer, modifié ou non, gratuitement ou contre rémunération.

### Ce que la licence impose quand on distribue

« Distribuer » = remettre une copie du logiciel à un tiers (archive, image Docker,
installation livrée chez un client qui en reçoit les fichiers…). Dans ce cas :

- fournir le **code source complet** correspondant, ou une offre écrite de le fournir ;
- le distribuer **sous la même licence**, avec le texte de la licence et les mentions
  de droits d'auteur intactes ;
- signaler les fichiers modifiés et la date des modifications ;
- ne pas ajouter de restriction (pas de clause interdisant la redistribution, pas de
  verrou empêchant de réinstaller une version modifiée).

Un **module** écrit pour Ploopi s'exécute dans le même processus et utilise ses classes :
c'est une œuvre dérivée. S'il est distribué, il doit l'être sous une licence compatible
avec la GPL. Un module utilisé uniquement en interne n'a pas à être publié.

### Le cas du service en ligne

La GPL ne s'applique qu'à la distribution : proposer Ploopi **en ligne** (SaaS) à des
utilisateurs n'oblige pas à publier ses modifications. Seule l'AGPL l'imposerait ;
passer Ploopi sous AGPL demanderait l'accord de tous les titulaires de droits et n'est
pas proposé ici.

## 3. Titulaires des droits et contributions

- **DIMS** : Ploopi en est un fork ; DIMS était diffusé sous GPL par la société Netlor.
- **Ovensia** (depuis 2007) et **HeXad** (2009-2010) : auteurs du code d'origine de Ploopi.
- **Contributeurs** : « Contributors hold Copyright (c) to their code submissions » —
  chacun reste titulaire des droits sur son code, qu'il diffuse sous la licence du
  projet.

Une contribution (pull request) est acceptée **sous la licence du projet**
(GPL-2.0-or-later), sans cession de droits. Un nouveau fichier reprend l'en-tête d'un
fichier voisin ; une contribution importante peut ajouter son auteur à la ligne
`Copyright`.

Une cinquantaine de fichiers récents (actions de `news2`, `nanogallery`…) n'ont pas
encore d'en-tête : ils sont couverts par le fichier `LICENSE` et par ce document, mais
l'en-tête est à ajouter au fil des modifications.

## 4. Composants tiers

Les composants tiers gardent leur propre licence. Liste à jour : `composer licenses
--no-dev` pour les dépendances Composer.

### 4.1 Dépendances Composer (installées dans `vendor/`, non versionnées)

| Composant | Usage | Licence | Compatibilité |
|---|---|---|---|
| jQuery, jQuery UI | interface | MIT | ✅ |
| TinyMCE 8 | éditeur riche du socle (`js/editor.js` : messagerie, GED, formulaires, webedit, forms, news2), avec `license_key: 'gpl'` | GPL-2.0-or-later | ✅ (voir § 5) |
| tinymce-i18n (mklkj) | traduction française de TinyMCE | MIT | ✅ |
| HTML Purifier | nettoyage du HTML riche | LGPL-2.1+ | ✅ |
| PhpSpreadsheet | exports tableur | MIT | ✅ |
| PEAR Mail, Net_SMTP, HTTP_Request2… | courriel, HTTP | BSD | ✅ |
| PEAR Net_Socket | sockets | PHP License 3.01 | ⚠️ incompatibilité GPL théorique, sans effet pratique (bibliothèques non modifiées) |
| Horde Text_Diff | différences de versions (wiki) | LGPL-2.1 | ✅ |
| Textile | syntaxe wiki | BSD-3-Clause | ✅ |
| highlight.js | coloration de code | BSD-3-Clause | ✅ |
| SDK AWS | stockage S3 | Apache-2.0 | ✅ (GPL v3) |
| Guzzle, Symfony polyfills, PSR… | dépendances indirectes | MIT | ✅ |
| php-stemmer 4 | racinisation (recherche) | MIT | ✅ |
| PHPUserAgentParser | statistiques | MIT | ✅ |
| array2xml | export XML | Apache-2.0 | ✅ (GPL v3) |
| jscolor 2.5.1 | sélecteur de couleur | GPL-3.0 (ou commerciale) | ✅ (GPL v3) |
| Joomla String 3 | chaînes UTF-8 (requis par php-stemmer) | GPL-2.0-or-later | ✅ |
| Dompdf 3.1 (+ php-font-lib, php-svg-lib, HTML5, CSS-parser) | exports PDF | LGPL-2.1 ; LGPL-2.1+ ; LGPL-3.0+ ; MIT | ✅ |

### 4.2 Composants embarqués dans le dépôt

| Composant | Emplacement | Licence | Compatibilité |
|---|---|---|---|
| Moteur de template (phpBB) | `lib/template/` | GPL-2.0+ | ✅ |
| jsToolbar (Dotclear) | `install/wiki/files/lib/jstoolbar/` (français seul) | GPL-2.0+ | ✅ |
| whatever:hover | `js/csshover.htc` | LGPL-2.1 | ✅ |
| SqlFormatter (Jeremy Dorn) | `install/dbreport/files/lib/` | LGPL | ✅ |
| nanogallery2 2.4.2 | `install/nanogallery/files/include/js/` | GPL-3.0 (ou commerciale) | ✅ (GPL v3) |
| Polices DejaVu et Bitstream Vera | `install/forms/files/fonts/` | licences libres Bitstream Vera / DejaVu | ✅ |
| Icônes de types de fichiers (Tango) | `templates/backoffice/ploopi2/img/documents/mimetypes/` (et ses copies dans `ploopi3` et `ploopi4`) | CC BY 2.0 (Tango, depuis versé au domaine public) | ✅ attribution conservée dans les SVG |
| Chart.js 4.4.9 | `install/dbreport/files/lib/chartjs/` (graphiques de `dbreport`, avec sa licence) | MIT | ✅ |
| Tabler Icons 3.49.0 (sous-ensemble) | `tools/icones/tabler/`, et les icônes qui en sont tirées : `templates/backoffice/ploopi3/img/`, `templates/backoffice/ploopi4/img/` (dont le sprite `icons.svg`), `install/<module>/files/img/`, `img/mimetypes/` | MIT | ✅ |
| Police Open Sans 1.10 (Google) | `templates/backoffice/ploopi2/fonts/`, `templates/backoffice/ploopi3/fonts/` | Apache-2.0 | ✅ (compatible GPL v3) |
| Police Inter 4 (variable, sous-ensembles latin et latin étendu, via Fontsource 5.2.8) | `templates/backoffice/ploopi4/fonts/` (licence dans `LICENSE-Inter.txt`) | SIL OFL 1.1 | ✅ (police distribuée à côté du logiciel, non incorporée au code) |
| Police Nunito ExtraBold (sous-ensemble des seules lettres du logotype, via Google Fonts) | `templates/backoffice/ploopi4/fonts/Nunito-ploopi.woff2` (licence dans `LICENSE-Nunito.txt`) | SIL OFL 1.1 | ✅ (police distribuée à côté du logiciel, non incorporée au code) |

## 5. Points à régler pour rester entièrement libre

Classés par priorité.

1. **CKEditor — réglé.** CKEditor 4 n'était libre que jusqu'à la 4.22, sans correctif
   de sécurité depuis (les versions « LTS » suivantes sont commerciales). TinyMCE 8
   (GPL-2.0-or-later, utilisé sous la clé de licence `'gpl'`, sans module commercial) le
   remplace partout, derrière un point d'entrée unique, `js/editor.js` (`ploopi.editor`).
   Les contenus enregistrés sont repris tels quels.

2. **Polices Verdana — réglé.** Les fichiers `verdana*.ttf` (Microsoft, non
   redistribuables) livrés avec `forms` ont été retirés ; DejaVu Sans les remplace
   (`forms` 3.8.9).

3. **Highcharts — réglé.** Highcharts 3.0.1 (propriétaire, usage commercial payant),
   qui dessinait les graphiques de `dbreport`, est remplacé par
   [Chart.js](https://www.chartjs.org/) 4.4.9 (MIT), livré avec sa licence dans
   `install/dbreport/files/lib/chartjs/` (`dbreport` 2.1.6). Les paramètres de
   graphique enregistrés sont repris tels quels.

4. **soundex-fr-bundle — réglé.** La bibliothèque de recherche phonétique, sous licence
   Creative Commons « pas d'utilisation commerciale », est retirée de `composer.json`.
   La classe `ploopi\phonetique` (`include/classes/phonetique.php`), écrite pour Ploopi
   sous GPL d'après les règles de l'orthographe française, la remplace. Ses codes
   diffèrent : réindexer après la mise à jour (`deploiement.md` § 9).

5. **JpGraph — réglé.** La bibliothèque (QPL, incompatible avec la GPL) qui dessinait
   les graphiques du module `forms` est abandonnée : `formsChart`
   (`install/forms/files/classes/formsChart.php`), écrite pour Ploopi avec GD seul,
   produit les mêmes PNG (`forms` 3.9.0). Les paramètres enregistrés sont repris sans
   migration.

6. **mPDF — réglé.** mPDF (GPL v2 seule : pas de clause « ou ultérieure », donc
   inconciliable avec les composants GPL v3 et Apache du § 4) n'était utilisé que comme
   moteur PDF de PhpSpreadsheet (`ploopi\arr::toexcel`, writer `pdf`). Dompdf (LGPL)
   le remplace, même API, avec ses dépendances (`fpdi`, `psr/log`… quittent
   `composer.lock`).

Tout ce que Ploopi installe ou distribue est désormais sous licence libre et
combinable avec la GPL v3. TinyMCE étant sous GPL-2.0-or-later, il reste compatible avec
Ploopi (GPL-2.0-or-later) ; ne pas activer ses modules commerciaux (« premium »).

## 6. Ajouter une dépendance

Avant d'ajouter une bibliothèque (Composer ou embarquée), vérifier sa licence :

| ✅ Acceptée | ❌ Refusée |
|---|---|
| MIT, BSD (2 ou 3 clauses), ISC, zlib | toute clause « non commercial » (CC BY-NC…) ou « pas de modification » |
| Apache-2.0 | licences propriétaires, même « gratuites » |
| LGPL (2.1 ou 3), MPL-2.0 | GPL-2.0-only (incompatible avec les composants GPL v3) |
| GPL-2.0-or-later, GPL-3.0 | QPL, licences incompatibles avec la GPL |

En cas de double licence (« GPL ou commerciale »), c'est la branche GPL qui s'applique.
Mettre à jour le tableau du § 4 dans la même pull request.

```sh
composer licenses --no-dev        # inventaire des dépendances installées
```

## 7. En-tête des fichiers

À placer en tête de tout nouveau fichier PHP (adapter l'année et le titulaire : un
contributeur indique son propre nom) :

```php
<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
    along with Ploopi.  If not, see <https://www.gnu.org/licenses/>.
*/
```

Pour un script shell ou un fichier court, une ligne suffit :
`# SPDX-License-Identifier: GPL-2.0-or-later`.
