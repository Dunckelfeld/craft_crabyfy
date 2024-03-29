<?php
/**
 * CraByFy plugin for Craft CMS 3.x
 *
 * Deploys craft fed gatsby frontend to netlify
 *
 * @link      dunckelfeld.de
 * @copyright Copyright (c) 2018 Dunckelfeld
 */

namespace dunckelfeld\crabyfy\controllers;

use dunckelfeld\crabyfy\CraByFy;
use dunckelfeld\crabyfy\records\DeployStatus;

use Craft;
use craft\web\Controller;
use craft\services\Sites;
use craft\helpers\UrlHelper;

/**
 * Deploy Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Dunckelfeld
 * @package   CraByFy
 * @since     1.0.0
 */
class DeployController extends Controller
{
    public $enableCsrfValidation = false;

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [
        'index',
        'live-deploy-status-failed',
        'live-deploy-status-succeeded',
        'live-deploy-status-started',
        'preview-deploy-status-failed',
        'preview-deploy-status-succeeded',
        'preview-deploy-status-started',
        'deploy-status-failed',
        'deploy-status-succeeded',
        'deploy-status-started'
    ];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/cra-by-fy/deploy
     *
     * @return mixed
     */
    public function actionIndex()
    {

        $settings = CraByFy::$plugin->getSettings();

        $variables = [
            'netlifyDeployPreviewTriggerUrl' => $settings['netlifyDeployPreviewTriggerUrl'],
            'netlifyDeployLiveTriggerUrl'    => $settings['netlifyDeployLiveTriggerUrl'],
            'previewUrl'                     => $settings['netlifyPreviewUrl'],
            'liveUrl'                        => $settings['netlifyLiveUrl'],
        ];

        return $this->renderTemplate('cra-by-fy', $variables);
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/cra-by-fy/deploy/do-something
     *
     * @return mixed
     */
    public function actionDeployStatusFailed()
    {
        $result = 'actionDeployStatusFailed';
        $this->setStatus('failed', $this->getType());

        return $result;
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/cra-by-fy/deploy/do-something
     *
     * @return mixed
     */
    public function actionDeployStatusSucceeded()
    {
        $result = 'actionDeployStatusSucceeded';
        $this->setStatus('succeeded', $this->getType());

        return $result;
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/cra-by-fy/deploy/do-something
     *
     * @return mixed
     */
    public function actionDeployStatusStarted()
    {
        $result = 'actionDeployStatusStarted';
        $this->setStatus('started', $this->getType());

        return $result;
    }

    /**
     * Get type.
     *
     * @return string
     */
    private function getType()
    {
        $result      = '';
        $postContent = file_get_contents('php://input');
        if ($postContent) {
            $content = json_decode($postContent);
            Craft::debug('netlify post content', 'cra-by-fy');
            Craft::debug($postContent, 'cra-by-fy');
            $result = $content->branch;
        }

        return $result;
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/cra-by-fy/deploy/do-something
     *
     * @return mixed
     */
    public function actionDeployStatus()
    {
        $settings = CraByFy::$plugin->getSettings();

        // return print_r($settings);

        $statuses = array(
            'live'    => $this->getStatus($settings['netlifyDeployLiveBranch']),
            'preview' => $this->getStatus($settings['netlifyDeployPreviewBranch'])
        );
        $json     = json_encode($statuses);

        return $json;
    }

    /**
     * Get status.
     *
     * @param $deployType
     *
     * @return string
     */
    private function getStatus($deployType)
    {

        try {
            $deployStatus = new DeployStatus();
            $deployStatus = DeployStatus::find()
                                        ->where(['deployType' => $deployType])
                                        ->orderBy('dateCreated DESC')
                                        ->limit(1)
                                        ->all();

            if (is_array($deployStatus) && isset($deployStatus[0])) {
                $status = $deployStatus[0]->status;
            } else {
                $status = '-';
            }
        } catch (Exception $e) {
            $status = '-';
        }

        return $status;
    }

    /**
     * Set status.
     *
     * @param $status
     * @param $deployType
     */
    private function setStatus($status, $deployType)
    {
        $deployStatus             = new DeployStatus();
        $deployStatus->status     = $status;
        $deployStatus->deployType = $deployType;
        $deployStatus->siteId     = Craft::$app->getSites()->currentSite->id;
        $deployStatus->save();
    }

}
