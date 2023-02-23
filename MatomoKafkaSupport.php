<?php
/*
 * Plugin Name: Matomo Plugin adding support for WordPress
 * Description: This plugin was initially developed as a Matomo plugin and now wants to add some additional support for WordPress
 * Author: Matomo
 * Author URI: https://matomo.org
 * Version: 1.0.0
 *
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MatomoKafkaSupport;

use Piwik\Common;
use Piwik\Tracker\Cache;


class MatomoKafkaSupport extends \Piwik\Plugin {

	public function __construct()
    {
        parent::__construct();

    }


	public function isTrackerPlugin()
    {
        return true;
    }

}