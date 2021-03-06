<?php
/**
 * CraByFy plugin for Craft CMS 3.x
 *
 * Deploys craft fed gatsby frontend to netlify
 *
 * @link      dunckelfeld.de
 * @copyright Copyright (c) 2018 Dunckelfeld
 */

namespace dunckelfeld\crabyfy;

use dunckelfeld\crabyfy\services\Deploy as DeployService;
use dunckelfeld\crabyfy\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\events\ModelEvent;
use craft\fields\Matrix;
use craft\services\Elements;
use craft\elements\Entry;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

use craft\events\ElementEvent;
use yii\base\Event;


/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Dunckelfeld
 * @package   CraByFy
 * @since     1.0.0
 *
 * @property  DeployService $deploy
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class CraByFy extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * CraByFy::$plugin
     *
     * @var CraByFy
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * CraByFy::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'deploy' => \dunckelfeld\crabyfy\services\Deploy::class,
        ]);

        // // Register our site routes
        // Event::on(
        //     UrlManager::class,
        //     UrlManager::EVENT_REGISTER_SITE_URL_RULES,
        //     function (RegisterUrlRulesEvent $event) {
        //         $event->rules['siteActionTrigger1'] = 'cra-by-fy/deploy';
        //     }
        // );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cpActionTrigger0'] = 'cra-by-fy/deploy';
                $event->rules['cpActionTrigger1'] = 'cra-by-fy/deploy/deploy-live';
                $event->rules['cpActionTrigger2'] = 'cra-by-fy/deploy/deploy-status-failed';
                $event->rules['cpActionTrigger3'] = 'cra-by-fy/deploy/deploy-status-succeeded';
                $event->rules['cpActionTrigger4'] = 'cra-by-fy/deploy/deploy-status-started';
                $event->rules['cpActionTrigger8'] = 'cra-by-fy/deploy/deploy-status';
            }
        );


        // Temporarily disabling triggering the automatic preview deploy,
        // since it's causing performance issues. ~seamofreality

        // Event::on(
        //     Elements::class,
        //     Elements::EVENT_AFTER_SAVE_ELEMENT,
        //     function (ElementEvent $event) {
        //         Craft::debug('saving an Entry', 'cra-by-fy');
        //         $settings = CraByFy::$plugin->getSettings();
        //         if($settings['deployPreviewOnSave'] == "yes") {
        //           $this->deploy->deployPreview($event);
        //        }
        //     }
        // );

        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function (RegisterCpNavItemsEvent $event) {
                Craft::debug('creating nav items', 'cra-by-fy');
                Craft::debug($event, 'cra-by-fy');
                Craft::debug($event->navItems, 'cra-by-fy');

                $settings          = CraByFy::$plugin->getSettings();
                $previewUrl        = $settings['netlifyPreviewUrl'];
                $event->navItems[] = [
                    'url'    => '/admin/actions/cra-by-fy/deploy',
                    'id'     => 'nav-crabify',
                    'label'  => 'CraByFy',
                    'icon'   => '@dunckelfeld/crabyfy/icon.svg',
                    'subnav' => [
                        'deploy-live' => [
                            'url'   => '/admin/actions/cra-by-fy/deploy/deploy-live',
                            'id'    => 'nav-live-deploy',
                            'label' => 'Deploy Live',
                        ],
                        'preview-url' => [
                            'url'    => $previewUrl,
                            'id'     => 'nav-preview-url',
                            'target' => '_blank',
                            'label'  => 'Preview',
                        ]
                    ]
                ];
            }
        );

        Craft::$app->getView()->hook('cp.layouts.base', function (array &$context) {
            return $this->deployButtonAssets();
        });

        Craft::$app->getView()->hook('cp.entries.edit.details', function (array &$context) {
            return $this->previewButton($context['entry']['uri']);
        });

        /**
         * Logging in Craft involves using one of the following methods:
         *
         * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
         * Craft::info(): record a message that conveys some useful information.
         * Craft::warning(): record a warning message that indicates something unexpected has happened.
         * Craft::error(): record a fatal error that should be investigated as soon as possible.
         *
         * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
         *
         * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
         * the category to the method (prefixed with the fully qualified class name) where the constant appears.
         *
         * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
         * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
         *
         * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
         */
        Craft::info(
            Craft::t(
                'cra-by-fy',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered sidebar HTML, which will be inserted into the content
     * block on the sidebar page.
     *
     * @return string The rendered sidebar HTML
     */
    protected function deployButtonAssets(): string
    {
        return Craft::$app->view->renderTemplate(
            'cra-by-fy/deployButtonAssets'
        );
    }

    /**
     * Returns the rendered sidebar HTML, which will be inserted into the content
     * block on the sidebar page.
     *
     * @return string The rendered sidebar HTML
     */
    protected function previewButton($uri): string
    {

        $settings  = CraByFy::$plugin->getSettings();
        $variables = [
            'previewLink' => $settings['netlifyPreviewUrl'] . $uri . '?preview=1',
        ];

        return Craft::$app->view->renderTemplate(
            'cra-by-fy/previewButton', $variables
        );
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'cra-by-fy/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
