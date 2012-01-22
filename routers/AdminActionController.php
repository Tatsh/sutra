<?php
/**
 * Handles /admin requests and some sub pages.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraRouters
 * @link http://www.example.com/
 *
 * @version 1.0
 */
class AdminActionController extends MoorActionController {
  /**
   * Clear the caches.
   *
   * @return void
   */
  public function clearCache() {
    sAuthorization::requireAdministratorPrivileges();

    $cache = sCache::getInstance();
    $cwd = getcwd();
    $filename = $cache->get('sTemplate::'.$cwd.'::latest_compiled_js_filename');

    $cache->clear();

    $cache->set('sTemplate::'.$cwd.'::latest_compiled_js_filename', $filename);
    $cache->set('sTemplate::'.$cwd.'::latest_compiled_js_filename_should_be_deleted', TRUE);

    $new_compilation = new CompiledJavascriptFile;
    $new_compilation->setFilename('files/js-'.fCryptography::randomString(32).'.js');
    $new_compilation->store();

    sMessaging::add(__('Cleared caches.'), '/admin');
    fURL::redirect('/admin');
  }

  /**
   * Get the pages to list at /admin. Classes that implement the
   *   AdminActionControllerLink interface are called.
   *
   * Classes that implement the AdminActionControllerLink cannot be
   *   auto-loaded and should be manually included.
   *
   * @return array Array with structure: category => array of pages.
   */
  private static function getAdminPages() {
    $pages = array();

    foreach (get_declared_classes() as $class) {
      $reflect = new ReflectionClass($class);
      if ($reflect->implementsInterface('AdminActionControllerLink')) {
        $obj = new $class;
        $category = $class->getCategory();

        if (!isset($pages[$category])) {
          $pages[$category] = array();
        }

        $pages[$category][] = $class->getPages();
      }
    }

    return $pages;
  }

  /**
   * Handles GET requests at /admin.
   *
   * @todo How other classes will plug/hook here.
   *
   * @return void
   */
  public function index() {
    sAuthorization::requireAdministratorPrivileges();

    $items = array(
      __('Cache') => array(
        'admin/clear-cache' => __('Clear Caches'),
      ),
      __('Site Statistics (TODO)') => array(
        'admin/statistics/search-engine-referrers' => __('Search Engine Referrers'),
        'admin/statistics/top-referrers' => __('Top Referrers'),
      ),
    );

    $content = sTemplate::buffer('admin', array(
      'items' => $items,
    ));

    sTemplate::render(array(
      'title' => __('Administrator'),
      'content' => $content,
    ));
  }
}
