<?php
/**
 * CraByFy plugin for Craft CMS 3.x
 *
 * Deploys craft fed gatsby frontend to netlify
 *
 * @link      dunckelfeld.de
 * @copyright Copyright (c) 2018 Dunckelfeld
 */

namespace dunckelfeld\crabyfy\services;

use dunckelfeld\crabyfy\CraByFy;

use Craft;
use craft\base\Component;

/**
 * Deploy Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Dunckelfeld
 * @package   CraByFy
 * @since     1.0.0
 */
class Deploy extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     CraByFy::$plugin->deploy->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (CraByFy::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }

    public function deployPreview() {
      // Sets our destination URL
      $settings = CraByFy::$plugin->getSettings();
      $endpoint_url = $settings['netlifyDeployPreviewTriggerUrl'];
      Craft::debug('deploying preview:', 'cra-by-fy');
      if(!empty($endpoint_url)) {
        $response = $this->curl($endpoint_url);
      } else {
        $response = 'No live deployment url set. Go to <a href="/admin/settings/plugins/cra-by-fy">Settings</a>';
      }

      return $response;
    }

    public function deployLive() {
      // Sets our destination URL
      $settings = CraByFy::$plugin->getSettings();
      $endpoint_url = $settings['netlifyDeployLiveTriggerUrl'];
      Craft::debug('deploying live:', 'cra-by-fy');
      // return false;
      if(!empty($endpoint_url)) {
        $response = $this->curl($endpoint_url);
      } else {
        $response = 'No live deployment url set. Go to <a href="/admin/settings/plugins/cra-by-fy">Settings</a>';
      }

      return $response;
    }

    private function curl($url) {
        // Sets our options array so we can assign them all at once
        $options = [
          CURLOPT_URL        => $url,
          CURLOPT_POSTFIELDS => '',
        	CURLOPT_POST       => true
        ];

        Craft::debug($url, 'cra-by-fy');
        // Initiates the cURL object
        $curl = curl_init();

        // Assigns our options
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($curl, CURLOPT_STDERR, $verbose);
        curl_setopt_array($curl, $options);

        Craft::debug($curl, 'cra-by-fy');
        // Executes the cURL POST
        $results = curl_exec($curl);

        if ($results === FALSE) {
           return printf("cUrl error (#%d): %s<br>\n", curl_errno($curl), htmlspecialchars(curl_error($curl)));
        }

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Craft::debug('getting curl', 'cra-by-fy');
        Craft::debug($verboseLog, 'cra-by-fy');

        // Be kind, tidy up!
        curl_close($curl);

        return "started";
    }
}
