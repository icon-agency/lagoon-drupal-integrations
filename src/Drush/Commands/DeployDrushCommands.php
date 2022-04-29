<?php

namespace IconAgency\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Boot\DrupalBootLevels;
use Drush\Commands\DrushCommands;
use Drush\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;

/**
 * Deployment commands for the Lagoon stack.
 */
final class DeployDrushCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

  /**
   * Hook into the drush hook:deploy command.
   */
  #[
    CLI\Hook(type: 'post-command', target: 'deploy:hook'),
    CLI\Bootstrap(level: DrupalBootLevels::FULL)
  ]
  public function postDeployHook(): void {
    $this->deploy(getenv('LAGOON_ENVIRONMENT_TYPE'));
  }

  /**
   * Execute deploy commands for Lagoon.
   *
   * @param string|bool $environment
   *   Current environment.
   */
  #[
    CLI\Command(name: 'iconagency:deploy'),
    CLI\Argument(name: 'environment', description: 'eg production, development.'),
    CLI\Help(description: 'Run CD logic on environment.'),
    CLI\Bootstrap(level: DrupalBootLevels::FULL)
  ]
  public function deploy(string|bool $environment): void {
    if (!$environment) {
      return;
    }

    if ($environment === 'production') {
      $this->production();
    }
    else {
      $this->development();
    }

    $this->all();
  }

  /**
   * Production deployment tasks.
   */
  private function production(): void {
    $site = $this->siteAliasManager()->getSelf();

    if (getenv('FASTLY_API_TOKEN') && getenv('FASTLY_API_SERVICE') && !getenv('CI')) {
      $fastlyModules = [
        'fastly',
        'fastlypurger',
        'http_cache_control',
        'purge',
        'purge_ui',
        'purge_drush',
        'purge_processor_cron',
        'purge_queuer_coretags',
        'purge_processor_lateruntime',
      ];

      $fastlyOptions = [
        'yes' => TRUE,
        'partial' => TRUE,
        'source' => realpath(dirname(__FILE__) . '../assets/config/fastly'),
      ];

      $this->processManager()->drush($site, 'pm:enable', $fastlyModules, ['yes' => TRUE])->mustRun();
      $this->processManager()->drush($site, 'config:import', [], $fastlyOptions)->mustRun();
    }

    $this->processManager()->drush($site, 'pm:uninstall', ['stage_file_proxy'], ['yes' => TRUE])->mustRun();
    $this->processManager()->drush($site, 'config:delete', ['stage_file_proxy.settings', 'origin'], ['yes' => TRUE])->run();
  }

  /**
   * Development deployment tasks.
   */
  private function development(): void {
    $site = $this->siteAliasManager()->getSelf();

    $this->processManager()->drush($site, 'pm:enable', ['stage_file_proxy'], ['yes' => TRUE])->mustRun();

    $config = ['stage_file_proxy.settings', 'origin', $this->productionUrl()];
    $this->processManager()->drush($site, 'config:set', $config, ['yes' => TRUE])->mustRun();
  }

  /**
   * All deployment tasks.
   */
  private function all(): void {
    $site = $this->siteAliasManager()->getSelf();

    $this->processManager()->drush($site, 'pm:enable', ['lagoon_logs'], ['yes' => TRUE])->mustRun();
    $this->processManager()->drush($site, 'pm:uninstall', ['dblog'], ['yes' => TRUE])->run();
  }

  /**
   * Get the Lagoon production URL.
   */
  private function productionUrl(): ?string {
    $process = $this->processManager()->process([
      'bash',
      '-c',
      '. /lagoon-tools/tasks/common/get_production_url && echo $(get_production_url)',
    ]);

    $process->run();

    return $process->isSuccessful() ? trim($process->getOutput()) : getenv('LAGOON_PRODUCTION_URL');
  }

}
