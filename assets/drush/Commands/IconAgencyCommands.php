<?php

namespace Drush\Commands\iconagency;

use Consolidation\SiteAlias\SiteAlias;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drush\SiteAlias\SiteAliasManagerAwareInterface;
use Drush\Commands\DrushCommands;
use Drush\Drush;

/**
 * Drush integration for IconAgency Lagoon.
 */
class IconAgencyCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

  /**
   * Hook into the drush hook:deploy command and add site requirements.
   *
   * @hook pre-command deploy:hook
   */
  public function deploy() {
    $self = $this->siteAliasManager()->getSelf();
    $redispatchOptions = Drush::redispatchOptions();

    if (!getenv('LAGOON_ENVIRONMENT')) {
      $this->logger()->notice("IconAgency post-deployments skipped for non-lagoon deployments.");
      return;
    }

    $this->logger()->notice("IconAgency post-deployments start.");

    if (getenv('LAGOON_ENVIRONMENT_TYPE') === 'production') {
      $this->production($self, $redispatchOptions);
    } else {
      $this->development($self, $redispatchOptions);
    }

    $this->all($self, $redispatchOptions);
    $this->processManager()->drush($self, 'cache:rebuild', [], $redispatchOptions)->mustRun();

    $this->logger()->success("IconAgency post-deployments complete.");
  }


  /**
   * Production post deployment tasks
   *
   * @param \Consolidation\SiteAlias\SiteAlias $site
   *   Site to run commands on.
   * @param array $options
   *   Command options.
   *
   * @return void
   */
  private function production(SiteAlias $site, array $options = []): void {
    $manager = $this->processManager();

    if (getenv('FASTLY_API_TOKEN') && getenv('FASTLY_API_SERVICE') && !getenv('CI')) {

      $fastlyModules = [
        'fastly',
        'fastlypurger',
        'http_cache_control',
        'purge',
        'purge_ui',
        'purge_processor_lateruntime',
        'purge_processor_cron',
        'purge_queuer_coretags',
        'purge_drush'
      ];

      $fastlyConfig = [
        'partial' => true,
        'source' => '../vendor/iconagency/drupal_integrations/config'
      ];

      $this->logger()->info("Enabling Fastly.");
      $manager->drush($site, 'pm:enable', $fastlyModules, $options)->mustRun();
      $manager->drush($site, 'config:import', [], $options + $fastlyConfig)->mustRun();
    }

    $manager->drush($site, 'pm:uninstall', ['stage_file_proxy'], $options)->mustRun();
    $manager->drush($site, 'config:delete', ['stage_file_proxy.settings', 'origin'], $options)->run();
  }

  /**
   * Development post deployment tasks
   *
   * @param \Consolidation\SiteAlias\SiteAlias $site
   *   Site to run commands on.
   * @param array $options
   *   Command options.
   *
   * @return void
   */
  private function development(SiteAlias $site, array $options = []): void {
    $manager = $this->processManager();

    $manager->drush($site, 'pm:enable', ['stage_file_proxy'], $options)->mustRun();
    $config = ['stage_file_proxy.settings', 'origin', $this->productionUrl()];
    $manager->drush($site, 'config:set', $config, $options)->mustRun();
  }

  /**
   * All post deployment tasks
   *
   * @param \Consolidation\SiteAlias\SiteAlias $site
   *   Site to run commands on.
   * @param array $options
   *   Command options.
   *
   * @return void
   */
  private function all(SiteAlias $site, array $options = []): void {
    $manager = $this->processManager();

    $manager->drush($site, 'pm:enable', ['lagoon_logs'], $options)->mustRun();
    $manager->drush($site, 'pm:uninstall', ['dblog'], $options)->run();
  }

  /**
   * Get the Lagoon production URL.
   * 
   * @return string 
   */
  private function productionUrl(): ?string {
    $manager = $this->processManager();

    $process = $manager->process([
      'bash',
      '-c',
      '. /lagoon-tools/tasks/common/get_production_url && echo $(get_production_url)'
    ]);

    $process->run();

    return $process->isSuccessful() ? trim($process->getOutput()) : getenv('LAGOON_PRODUCTION_URL');
  }
}
