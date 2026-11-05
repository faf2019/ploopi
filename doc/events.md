# Événements métier avec RabbitMQ

Ploopi 2.2.0 ajoute une outbox InnoDB, un relais RabbitMQ et un consommateur PHP
facultatif. **Système → Événements** affiche l’état de la capture, les compteurs,
les cent derniers événements et permet de reprendre les publications en échec.
Le contenu des messages et les identifiants de connexion ne sont pas affichés.

## Installation

Cette évolution dépend du passage à InnoDB de Ploopi 2.1.0. Sauvegarder la base,
installer les dépendances verrouillées par `composer.lock`, puis effectuer
**Système → Mise à jour**, vers 2.2.0. Mettre à jour les modules Wiki et Actualités
pour déployer leurs producteurs. PHP doit disposer de l’extension `sockets` ;
elle est ajoutée aux images Docker du dépôt.

La capture est **désactivée par défaut**. L’activer seulement après la migration
avec `PLOOPI_EVENTS_ENABLED=1`. Le transport utilise `php-amqplib/php-amqplib`,
sans extension AMQP native. Aucun appel au broker n’a lieu pendant une requête
métier : son indisponibilité n’empêche pas les écritures tant que l’outbox SQL
reste disponible.

### Docker

Après installation de Ploopi et migration de la base, ajouter dans le fichier
`.env` ignoré par Git les identifiants d’un compte dédié :

```dotenv
PLOOPI_RABBITMQ_USER=ploopi
PLOOPI_RABBITMQ_PASSWORD=<secret à générer>
```

```sh
# Développement : le dépôt, sa configuration et ses documents restent partagés.
docker compose -f docker-compose.yml -f docker-compose.events.yml up --build -d

# Production : réutilise l’image et les volumes du déploiement de production.
docker compose -f docker-compose.prod.yml -f docker-compose.events.prod.yml up --build -d
```

Les surcharges ajoutent RabbitMQ 4.4 et le worker publisher. Les ports AMQP et
d’administration restent sur le réseau Docker interne. Un volume nommé conserve
les données du broker ; le hostname reste stable lors des recréations. Ne pas
supprimer ce volume avec `down -v` si ses messages doivent être conservés.

Le publisher redémarre automatiquement. Avant l’installation initiale de Ploopi
ou la migration de sa base, il quitte avec un statut d’indisponibilité ; une fois
ces prérequis remplis, le redémarrage suivant démarre le traitement. Les processus
et les connexions sont recréés, ils ne sont pas conservés par une image.

Le consommateur local reste facultatif. Après avoir déclaré les gestionnaires
locaux décrits plus bas, ajouter `--profile events-consumer` à la commande Compose.
Pour des intégrations externes, laisser le consommateur PHP arrêté et créer une
file dédiée à chaque application abonnée.

### Broker existant et TLS

| Variable | Valeur par défaut / rôle |
|---|---|
| `PLOOPI_EVENTS_ENABLED` | `0` : capture automatique et `events::record()` inactifs |
| `PLOOPI_EVENTS_SOURCE` | `urn:ploopi` ; définir une URI propre à chaque instance |
| `PLOOPI_RABBITMQ_HOST` | `rabbitmq` |
| `PLOOPI_RABBITMQ_PORT` | `5672` ; utiliser `5671` pour un listener TLS habituel |
| `PLOOPI_RABBITMQ_USER` | Obligatoire pour les workers |
| `PLOOPI_RABBITMQ_PASSWORD` | Secret du compte AMQP |
| `PLOOPI_RABBITMQ_PASSWORD_FILE` | Fichier contenant le secret, prioritaire sur la variable |
| `PLOOPI_RABBITMQ_VHOST` | `/` ; préférer un vhost dédié en production |
| `PLOOPI_RABBITMQ_EXCHANGE` | `ploopi.events`, exchange topic durable |
| `PLOOPI_RABBITMQ_QUEUE` | `ploopi.events`, file quorum durable |
| `PLOOPI_RABBITMQ_TLS` | `0` sur réseau Docker interne ; `1` active TLS |
| `PLOOPI_RABBITMQ_CA_FILE` | Autorité personnalisée, facultative avec TLS |

Avec TLS, la chaîne et le nom du certificat sont toujours vérifiés. Aucune option
ne désactive ces vérifications. Ne pas envoyer d’identifiants AMQP en clair sur
un réseau non maîtrisé. Les surcharges livrées visent le broker interne ; adapter
les variables des services web/publisher/consumer pour un broker externe.

Utiliser un compte AMQP et des permissions limités au vhost et aux noms
d’exchanges/files nécessaires. Le worker déclare sa topologie ; il lui faut les
droits correspondants. Les secrets peuvent être fournis par Docker secrets et
`PLOOPI_RABBITMQ_PASSWORD_FILE` sans être copiés dans le dépôt.

## Contrat des messages

Les messages utilisent CloudEvents 1.0 en JSON structuré,
`content_type=application/cloudevents+json`, `delivery_mode=2` et
`message_id` égal à l’identifiant conservé dans l’outbox. Le type sert de routing key.

```json
{
  "specversion": "1.0",
  "id": "1f2e3d4c5b6a79800112233445566778",
  "source": "urn:ploopi:organisation",
  "type": "news.article.updated.v1",
  "time": "2026-10-21T16:00:00Z",
  "datacontenttype": "application/json",
  "ploopimodule": 12,
  "ploopiworkspace": 2,
  "ploopiuser": 7,
  "data": {"id": 42, "changed_fields": ["published"]}
}
```

Les types livrés sont `user.created.v1`, `user.updated.v1`, `user.deleted.v1`,
`wiki.page.created.v1`, `wiki.page.updated.v1`, `wiki.page.deleted.v1`,
`news.article.created.v1`, `news.article.updated.v1` et `news.article.deleted.v1`.
Pour une clé composite, `data.id` est un objet des champs de clé. Le producteur
n’embarque ni mot de passe, ni valeurs personnelles, ni contenu des pages/articles.
Les modifications techniques, comme la dernière connexion d’un utilisateur, sont
exclues. Une sauvegarde inchangée ne produit pas d’événement ; une suppression
qui ne retire aucune ligne non plus.

Ces producteurs concernent les classes métier instrumentées. Une écriture SQL
directe, ou un `data_object` générique sur la même table, ne produit pas
automatiquement d’événement. Ils ne couvrent pas encore tous les modules ni la
GED. Le nom `updated` signale une modification : il ne constitue pas une décision
métier plus précise, comme une validation ou une publication.

Chaque source doit identifier une instance de Ploopi. L’identité d’un événement
est le couple **source + id** ; elle reste identique lors de toutes ses reprises.
L’ordre global, l’absence de doublons et une livraison exactement une fois ne sont
pas garantis, notamment avec plusieurs publishers, des reprises ou des partitions
réseau. Les consommateurs doivent gérer les mises à jour devenues obsolètes.

## Garanties et exploitation

- Le changement SQL instrumenté et l’insertion en outbox se valident ou s’annulent
  ensemble. Les transactions imbriquées utilisent des savepoints et ne valident
  jamais la transaction de leur appelant.
- Les appels SQL en transaction ne sont pas rejoués seuls après un deadlock ou une
  perte de connexion : cela casserait leur atomicité. L’exception remonte à
  l’opération métier, qui doit être reprise dans son ensemble si nécessaire.
- Le publisher réserve un événement avec un bail de 120 secondes, puis libère la
  transaction SQL avant de joindre RabbitMQ. Il attend un publisher confirm et
  refuse les retours « sans route » ou les NACK.
- Après une erreur, le message reste en base, avec backoff exponentiel à partir
  de 5 secondes, plafonné à une heure. Après dix réservations/tentatives, il passe
  en `failed` et nécessite une reprise explicite. Un worker interrompu est repris
  après expiration de son bail.
- Un broker qui confirme un message avant que le publisher ne puisse confirmer
  ce succès en SQL peut provoquer une republication : c’est une livraison
  **au moins une fois**. L’état `published` signifie « accepté et routé par
  RabbitMQ », pas « traitement métier terminé ».

Les files principales, de reprise et d’échec sont des files quorum durables. Les
dead letters utilisent le transfert `at-least-once` de RabbitMQ. Un seul nœud
reste un seul point de panne : pour tolérer la perte d’un nœud, exploiter un cluster
RabbitMQ avec réplication adaptée et sauvegarder les données.

```sh
php bin/events.php publish --watch --limit=100
php bin/events.php publish --limit=100     # un seul lot, utile depuis cron
php bin/events.php status
php bin/events.php retry --id=<id_outbox>
php bin/events.php prune --days=30
```

La commande ponctuelle `publish` renvoie 1 si une publication du lot a échoué.
`prune` ne supprime que les événements déjà publiés ; aucun événement en attente
ou en échec n’est supprimé. La reprise d’une publication en `failed` conserve son
identifiant et sa charge utile, puis remet son compteur de tentatives à zéro.

Les méthodes transactionnelles du connecteur Ploopi doivent encadrer les écritures
concernées ; ne pas ouvrir de transaction par du SQL brut et ne pas exécuter de DDL
à l’intérieur. Les opérations de fichiers, courriels et services externes ne sont
pas annulables par une transaction SQL. Une indexation configurée dans une base
distincte ne participe pas non plus à cette transaction. Les objets ORM doivent être rechargés
après l’annulation d’une transaction englobante.

## Produire un événement depuis un module métier

Pour une opération explicite, partager la transaction et la connexion Ploopi :

```php
ploopi\db::get()->transaction(function () use ($objDossier)
{
    if ($objDossier->save() === false) throw new RuntimeException('Échec de sauvegarde');
    ploopi\events::record('metier.dossier.validated.v1',
        array('id' => $objDossier->fields['id']),
        array('module' => $objDossier->fields['id_module'],
              'workspace' => $objDossier->fields['id_workspace']));
});
```

Une classe dérivée de `data_object` peut aussi déclarer `$strEventPrefix` et
`$arrEventFields` pour ses événements de création/modification/suppression, comme
les classes Wiki et Actualités livrées. Le contrat de payload reste explicite et
ne sérialise jamais automatiquement toute la ligne. Choisir des types versionnés,
éviter les données sensibles et limiter les messages à 1 Mio.

## Consommer depuis un module ou un système externe

Chaque application abonnée doit déclarer une file durable distincte et la lier
à `ploopi.events` avec les routing keys souhaitées, par exemple
`news.article.*.v1`. Deux workers sur la même file se partagent les livraisons ;
ils ne reçoivent pas chacun une copie. Une file créée après publication ne reçoit
pas automatiquement les anciens messages.

Un module Ploopi déclare ses gestionnaires dans
`install/<module>/files/include/events.php`, déployé vers `modules/` :

```php
<?php
return array(
    'news.article.updated.v1' => static function (array $arrEvent)
    {
        $intArticle = (int) $arrEvent['data']['id'];
        // Écriture métier via ploopi\db::get(), dans la transaction du consommateur.
        // Ne pas appeler commit() ici et ne pas faire confiance aux messages comme autorisation utilisateur.
    }
);
```

```sh
php bin/events.php consume --watch --queue=metier.actualites --binding=news.article.*.v1
```

Le consommateur local déduplique par file/source/id dans `ploopi_event_inbox`,
exécute tous les gestionnaires concernés dans la même transaction SQL et n’envoie
l’ACK qu’après commit. Un ACK perdu provoque une redélivrance sans refaire les
écritures validées. Un échec annule aussi l’entrée inbox, puis le message passe
par une file de reprise différée de cinq secondes, trois fois au plus. Après
le quatrième essai, il va dans `<file>.dead`. Un message invalide ou sans
gestionnaire va directement dans cette file : lier la file (`--binding`) aux
seuls types traités. `consume` refuse de démarrer (code 78) quand aucun module
ne déclare de gestionnaire.

Pour retraiter une dead letter après correction, la republier avec son enveloppe
source/id intacte et sans l’en-tête `x-ploopi-attempt`, puis acquitter sa copie
dans la file d’échec après confirmation de la nouvelle publication. Cette
opération relève de l’exploitation RabbitMQ ; le bouton « Reprendre » de Ploopi
concerne les échecs de publication de l’outbox.

Les effets externes d’un gestionnaire doivent posséder leur propre clé
d’idempotence ou être relayés par une outbox dédiée. L’inbox n’est pas purgée
automatiquement : conserver les identités tant qu’une copie d’un message peut
encore être relivrée. Les reprises ne doivent pas créer de boucle de production
du même événement.

## Validation

`tests/46_events.php` vérifie les écritures et événements atomiques, les savepoints,
les erreurs SQL, le catalogue du socle, la confidentialité et le tableau de bord.
Avec un broker réel, elle vérifie la connexion indisponible puis rétablie, les
confirmations, une livraison répétée, le rollback des gestionnaires, les reprises
vers la file d’échec, le routage obligatoire et deux publishers concurrents.

```sh
tests/instance.sh test 46_events             # SQL ; transport déclaré non évalué sans broker
tests/docker.sh 8.5 --events                 # suite complète + RabbitMQ 4.4 jetable
```

Pour un runner existant, définir `PLOOPI_TEST_RABBITMQ_HOST` et les variables de
connexion AMQP du tableau avant le lancement de la suite. Un broker configuré
mais indisponible fait échouer ses contrôles ; il n’est pas assimilé à un succès.
