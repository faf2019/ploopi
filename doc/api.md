# API Ploopi v1

L’API en lecture seule est accessible sous `/api/v1`. **Système → API** permet
aux administrateurs système de gérer les clés, consulter les compteurs et les
50 derniers appels authentifiés, lire la documentation et télécharger le contrat
OpenAPI 3. La documentation est produite localement, sans service externe.

## Installer ou mettre à jour

Les installations neuves créent les tables en InnoDB. Sur une installation
existante, utiliser **Système → Mise à jour**, vers Ploopi 2.1.0.

Avant la mise à jour, sauvegarder la base et vérifier sa restauration. Prévoir
une fenêtre de maintenance, de l’espace disque pour la reconstruction des tables
et les droits `ALTER` du compte SQL. La conversion peut bloquer des écritures et
les changements de moteur ne sont pas annulables par `ROLLBACK`.

La migration convertit **toutes les tables persistantes de la base configurée**,
y compris les tables des modules métiers ou les tables sans préfixe `ploopi_`.
Vérifier ce périmètre si la base est partagée avec une autre application. Les
vues ne sont pas modifiées. Elle ne change ni les colonnes, ni les index, ni les
encodages. Une table déjà InnoDB est ignorée. En cas d’échec, la migration s’arrête
avant d’avancer la version 2.1.0 ; résoudre l’erreur SQL puis relancer. Les tables
déjà converties sont conservées.

Mettre également à jour les modules Wiki, Actualités et Forms pour déployer leurs
nouveaux fichiers. L’ORM et Forms créent désormais leurs tables en InnoDB.
Reporter aussi la règle de conservation de l’en-tête `Authorization` du nouveau
`.htaccess_modele` dans le `.htaccess` du déploiement, en préservant ses adaptations
locales (notamment avec Apache/FastCGI).

## Clés et droits

Créer une clé avec un libellé, un utilisateur actif, un espace auquel cet
utilisateur est rattaché et des permissions (`core:read`, `wiki:read`,
`news2:read`). La durée est comprise entre 1 et 365 jours, 90 par défaut.

La clé n’est affichée qu’une fois ; seule son empreinte SHA-256 est stockée.
Conserver la clé dans le gestionnaire de secrets du client. La révocation est
immédiate. Le préfixe affiché sert à identifier une clé, pas à s’authentifier.

```sh
curl -H 'Authorization: Bearer VOTRE_CLE' https://votre-ploopi.example/api/v1/me
```

HTTPS est obligatoire, avec une exception pour les appels de test en loopback.
La configuration HTTPS derrière un proxy suit celle du socle (`HTTPS` ou
`HTTP_X_SSL_REQUEST`) : ce dernier en-tête est cru tel quel, le proxy doit donc
l’écraser sur chaque requête et l’application ne doit pas être joignable sans
lui. Aucun cookie n’est accepté comme authentification API ;
aucune clé n’est lue dans les paramètres d’URL.

Les droits et règles IP de l’espace sont recalculés à chaque appel. Une clé
n’accorde pas de rattachement ni de rôle supplémentaire. Désactiver le compte,
le laisser expirer ou retirer son rattachement à l’espace coupe immédiatement l’accès. Les données
de module restent dans l’espace de la clé : les vues transverses du backoffice
n’élargissent pas ce périmètre. Un fournisseur doit compléter ces restrictions
avec ses propres règles de visibilité métier et droits d’action.

## Ressources livrées

| Route GET | Permission | Réponse |
|---|---|---|
| `/me` | `core:read` | Identité minimale du propriétaire |
| `/workspaces` | `core:read` | L’espace de la clé |
| `/modules` | `core:read` | Instances actives rattachées à cet espace |
| `/openapi.json` | `core:read` | Contrat OpenAPI |
| `/modules/{module_id}/pages[/{id}]` | `wiki:read` | Pages du wiki |
| `/modules/{module_id}/articles[/{id}]` | `news2:read` | Actualités publiées |
| `/modules/{module_id}/categories[/{id}]` | `news2:read` | Catégories d’actualités |

`module_id` désigne une **instance**, pas un type de module. Les identifiants
textuels des pages wiki doivent être encodés dans l’URL. Les champs HTML sont
des données ; un client doit les assainir avant tout rendu HTML.

Les collections de modules acceptent `page` (1 à 1 000 000) et `per_page` (1 à
100, 25 par défaut). L’ordre est déterministe, par clé primaire. Elles renvoient :

```json
{"data": [], "meta": {"page": 1, "per_page": 25, "total": 0}}
```

Un détail et `/me` renvoient `{"data": {...}}`. `/workspaces` et `/modules`
renvoient `{"data": [...]}`. Les champs de collections provenant de SQL sont
des chaînes éventuellement nulles ; le contrat décrit les champs exposés.

Les erreurs suivent `{"error": {"code": "…", "message": "…"}}` :
400 paramètre invalide, 401 clé absente/invalide/expirée/révoquée,
403 droits insuffisants, 404 ressource absente ou hors périmètre,
405 méthode autre que GET, 429 limite dépassée, 500 erreur interne.

La limite est de 120 appels par minute et par clé, avec un compteur SQL atomique.
Une réponse 429 contient `Retry-After: 60`. Les réponses ne sont pas mises en cache.
Les appels associés à une clé valide alimentent les compteurs et un journal
(route normalisée, statut, durée). Les secrets, corps et paramètres ne sont pas
journalisés. Les réponses 429 incrémentent les compteurs de la clé sans entrer au
journal. Les entrées de plus de 30 jours sont supprimées par le point d’entrée
`cron` (`modules/system/cron.php`), à planifier chaque minute (voir
`doc/deploiement.md` § 7).

## Étendre à un module métier

Ajouter `install/<module>/files/include/api.php`, puis déployer ce fichier par la
mise à jour habituelle du module (version et changelog à relever). Le socle ne
charge que les fournisseurs des types installés, depuis `modules/`.

```php
<?php
return array(
    'dossiers' => array(
        'summary' => 'Dossiers consultables',
        'fields' => array('id', 'label', 'id_module', 'id_workspace'),
        'action' => 10, // facultatif : droit d’action exigé avant le fournisseur
        'read' => static function ($intModule, $strId)
        {
            return ploopi\api::collection(
                'ploopi_mod_metier_dossier',
                array('id', 'label', 'id_module', 'id_workspace'),
                'id', $intModule, $strId,
                'published = 1' // SQL constant, jamais une expression reçue par HTTP
            );
        }
    )
);
```

Cela ajoute `/modules/{module_id}/dossiers[/{id}]`, la permission `<module>:read`
et ses entrées de documentation. `collection()` impose les filtres `id_module`
et `id_workspace`, borne la pagination et échappe l’identifiant. Le fournisseur
déclare explicitement les colonnes publiques ; aucun `SELECT *` automatique.
Les noms de ressources doivent être uniques entre fournisseurs pour que les
chemins OpenAPI ne se superposent pas.

Pour une visibilité plus complexe (propriété, partage, validation, GED…), écrire
un lecteur dédié réutilisant les règles métier et renvoyant
`array($intStatutHttp, $arrReponse)`. Ne pas exposer une table sans ces contrôles.
Les API d’écriture et le transport d’événements sont des étapes ultérieures.

## Validation

`tests/45_innodb_api.php` teste une conversion MyISAM réelle, la conservation
des données, la reprise et le rollback, puis le contrat HTTP, le cloisonnement,
les permissions, la pagination, les clés et l’administration sur une instance
jetable. Lancer `tests/instance.sh test 45_innodb_api` ou la suite complète
`tests/docker.sh 8.5`.
