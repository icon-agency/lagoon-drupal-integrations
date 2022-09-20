# Icon Agency Lagoon Integrations

Icon Agency integration with Amazee Lagoon.\
Recommended modules, patches and config for Lagoon.

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

## Recommends

- https://github.com/amazeeio/drupal-integrations

## Waiting

"drupal/fastly": "3.14",
"drupal/stage_file_proxy": "^1.3",

- https://www.drupal.org/project/stage_file_proxy/issues/3289828
- https://www.drupal.org/files/issues/2022-07-22/fastly.3.x-dev.rector.patch
