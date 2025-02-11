# Icon Agency Lagoon Integrations

Icon Agency integration with Amazee Lagoon.\
Recommended modules, patches and config for Lagoon.

Contains a Drush `drush deploy:hook` pre-command hook to complement [Lagoon Tools](https://bitbucket.org/iconagency/lagoon-tools/src/master/tasks/drupal/8x-9x/post-rollout) CI/CD.

Tagged releases deploy to packagist \
Untagged as dev

## Usage

```yml
  "require": {
    "amazeeio/drupal_integrations": "^0.3.7",
    "iconagency/drupal_integrations": "^9.1",
  },
  "extra": {
    "enable-patching": true,
    "drupal-scaffold": {
      "allowed-packages": [
        "amazeeio/drupal_integrations",
        "iconagency/drupal_integrations"
      ],
    }
  }
```

## This library

- https://packagist.org/packages/iconagency/drupal_integrations
- https://bitbucket.org/iconagency/lagoon-drupal-integrations

## Icon Agency docs

- https://dev.iconagency.com.au/

## Drupal project

- https://bitbucket.org/iconagency/lagoon-drupal/

## Recommends

- https://github.com/amazeeio/drupal-integrations
