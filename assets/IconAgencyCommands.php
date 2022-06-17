<?php

namespace Drush\Commands\iconagency;

use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drush\Attributes as CLI;
use Drush\Boot\DrupalBootLevels;
use Drush\Commands\DrushCommands;
use Drush\SiteAlias\SiteAliasManagerAwareInterface;
use Drush\Drush;

/**
 * Deployment commands for the Lagoon stack.
 */
final class IconAgencyCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

  /**
   * Drupal Module Extension service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * Drupal Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Drupal app root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * Hook into the drush hook:deploy command.
   *
   * @hook post-command deploy:hook
   * @bootstrap full
   */
  #[CLI\Hook(type: 'post-command', target: 'deploy:hook'), CLI\Bootstrap(level: DrupalBootLevels::FULL)]
  public function postDeployHook(): void {
    $this->moduleExtensionList = \Drupal::service('extension.list.module');
    $this->moduleHandler = \Drupal::service('module_handler');
    $this->fileSystem = \Drupal::service('file_system');
    $this->appRoot = \Drupal::getContainer()->getParameter('app.root');

    switch (getenv('LAGOON_ENVIRONMENT_TYPE')) {
      case 'development':
        $this->importDevConfig();
        $this->enableStageFileProxy();
        break;

      case 'production':
        $this->importCdnConfig();
        $this->uninstallStageFileProxy();
        break;

      default:
        $this->logger()->error(dt('LAGOON_ENVIRONMENT_TYPE not recognised.'));
        return;
    }

    $this->enableLagoonLogs();
    $this->uninstallDevModules();

    $this->logger()->success(dt('Lagoon hooks complete.'));
  }

  /**
   * Import partial config from ../config/dev if exists.
   */
  private function importDevConfig(): void {
    if (!$yml_path = $this->fileSystem->realpath($this->appRoot . '/../config/dev')) {
      return;
    }

    $yml = $this->fileSystem->scanDirectory($yml_path, '/\.yml$/i');
    if (empty($yml)) {
      return;
    }

    $this->run('config:import', [], [
      'partial' => TRUE,
      'source' => $yml_path,
    ]);
  }

  /**
   * Import partial config from ./config/cdn.
   */
  private function importCdnConfig(): void {
    $enable = [
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

    if (!getenv('FASTLY_API_TOKEN') || !getenv('FASTLY_API_SERVICE') || getenv('CI')) {
      return;
    }

    if (!$yml_path = $this->fileSystem->realpath($this->appRoot . '/../vendor/iconagency/drupal_integrations/config/cdn')) {
      return;
    }

    $yml = $this->fileSystem->scanDirectory($yml_path, '/\.yml$/i');
    if (empty($yml)) {
      return;
    }

    if (!$this->run('pm:enable', $enable)) {
      return;
    }

    $this->run('config:import', [], [
      'partial' => TRUE,
      'source' => $yml_path,
    ]);
  }

  /**
   * Enable Stage File Proxy.
   */
  private function enableStageFileProxy(): void {
    if (!$url = $this->getProductionUrl()) {
      return;
    }

    $this->run('pm:enable', ['stage_file_proxy']);
    $this->run('config:set', ['stage_file_proxy.settings', 'origin', $url]);
  }

  /**
   * Enable Lagoon Logs.
   */
  private function enableLagoonLogs(): void {
    $this->run('pm:enable', ['lagoon_logs']);
  }

  /**
   * Uninstall Stage File Proxy.
   */
  private function uninstallStageFileProxy(): void {
    $this->run('pm:uninstall', ['stage_file_proxy']);
  }

  /**
   * Uninstall Development Modules.
   */
  private function uninstallDevModules(): void {
    $uninstall = array_filter([
      'kint',
      'devel_kint_extras',
      'devel_generate',
      'webprofiler',
      'devel',
      'dblog',
      'update',
      'automated_cron',
    ], [$this->moduleExtensionList, 'exists']);

    if (!empty($uninstall)) {
      $this->run('pm:uninstall', $uninstall);
    }
  }

  /**
   * Get the Lagoon production URL.
   */
  private function getProductionUrl(): ?string {
    /** @var \Drush\SiteAlias\ProcessManager $manager */
    $manager = $this->processManager();

    $process = $manager->process([
      'bash',
      '-c',
      '. /lagoon-tools/tasks/common/get_production_url && echo $(get_production_url)',
    ]);

    $process->run();

    return $process->isSuccessful() ? trim($process->getOutput()) : (getenv('LAGOON_PRODUCTION_URL') ?: NULL);
  }

  /**
   * Execute task with some conditional filtering.
   */
  private function run(string $action, array $config, array $options = []): bool {
    /** @var \Drush\SiteAlias\ProcessManager $manager */
    $manager = $this->processManager();

    $options = [...Drush::redispatchOptions(), ...$options];

    $process = $manager->drush($this->siteAliasManager()->getSelf(), $action, $config, $options);
    $process->run($process->showRealtime());

    return $process->isSuccessful();
  }

}
