# Icon Agency Lagoon Integrations

Icon Agency integration with Amazee Lagoon.\
Recommended modules, patches and config for Lagoon.

Contains a Drush `drush deploy:hook` pre-command hook to complement [Lagoon Tools](https://bitbucket.org/iconagency/lagoon-tools/src/master/tasks/drupal/8x-9x/post-rollout) CI/CD.

## Usage

```yml
  "require": {
    "iconagency/drupal_integrations": "^10.0.0",
  },
  "extra": {
    "enable-patching": true,
    "drupal-scaffold": {
      "allowed-packages": [
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
