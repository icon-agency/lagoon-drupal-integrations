# Icon Agency Lagoon Integrations

Icon Agency integration with Amazee Lagoon.

Opinionated config and structure for development. Recommended base modules and patches.

## Usage

```yml
  "require": {
    "amazeeio/drupal_integrations": "^0.3.6",
    "iconagency/drupal_integrations": "^9.0.6",
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

On deploy you should run `bin/deploy.sh` via whatever methods you please. Generally integrated into Lagoon Tools.

## This library

- https://packagist.org/packages/iconagency/drupal_integrations
- https://bitbucket.org/iconagency/lagoon-drupal-integrations

## Icon Agency docs

- https://dev.iconagency.com.au/

## Drupal project

- https://bitbucket.org/iconagency/lagoon-drupal/

## Recommends

- https://github.com/amazeeio/drupal-integrations
