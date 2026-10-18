# Ploopi

[![tests](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/faf2019/8992326b9a700fd3832d1f3ff85bb8cf/raw/ploopi-tests.json)](tests/README.md)
[![dépendances](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/faf2019/8992326b9a700fd3832d1f3ff85bb8cf/raw/ploopi-dependances.json)](tests/README.md#badge-du-readme-facultatif)
[![licence](https://img.shields.io/badge/licence-GPL--2.0--or--later-blue)](doc/licence.md)
[![PHP](https://img.shields.io/badge/PHP-8.1%E2%80%938.5-777bb4?logo=php&logoColor=white)](doc/deploiement.md)
[![base](https://img.shields.io/badge/base-MariaDB%20%7C%20MySQL-003545?logo=mariadb&logoColor=white)](doc/deploiement.md)

Ploopi est une plateforme web libre écrite en PHP : portail, gestion de contenus,
GED, wiki, formulaires, planning, réservations, annuaire, requêtes sur la base…
Tout le fonctionnel est apporté par des modules installables depuis le backoffice.

Site historique : http://www.ploopi.org/

> **Version de développement.**

## Essayer en cinq minutes (Docker)

```sh
git clone https://github.com/faf2019/ploopi.git
cd ploopi
docker compose up --build -d
# puis ouvrir http://localhost:8080/
```

Guide pas à pas : [`docker/LISEZMOI.md`](docker/LISEZMOI.md).

## Documentation

| | |
|---|---|
| [Déploiement](doc/deploiement.md) | installation manuelle (Apache + PHP 8.1–8.5 + MariaDB), configuration, mises à jour, sauvegardes |
| [Développement](doc/developpement.md) | environnement DDEV, modules, tests, scripts de `tools/`, conventions |
| [Licence](doc/licence.md) | GPL-2.0-or-later, composants tiers, contributions |
| [Référence technique](doc/reference.md) | architecture et API du socle et des modules |

Captures d'écran : [`screenshots/`](screenshots/).

## Licence

Ploopi est un logiciel libre, distribué sous **GNU GPL version 2 ou ultérieure**
(`GPL-2.0-or-later`) : voir [`LICENSE`](LICENSE) et [`doc/licence.md`](doc/licence.md).

Copyright © 2007-2026 Ovensia, HeXad et les contributeurs. Ploopi est un fork de DIMS
(Netlor, GPL).
