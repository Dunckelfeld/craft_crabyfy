<?php
/**
 * CraByFy plugin for Craft CMS 3.x
 *
 * Deploys craft fed gatsby frontend to netlify
 *
 * @link      dunckelfeld.de
 * @copyright Copyright (c) 2018 Dunckelfeld
 */

namespace dunckelfeld\crabyfy\models;

use dunckelfeld\crabyfy\CraByFy;

use Craft;
use craft\base\Model;

/**
 * CraByFy Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Dunckelfeld
 * @package   CraByFy
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Netlify Deploy Preview Trigger URL
     *
     * @var string
     */
    public $netlifyDeployPreviewTriggerUrl = '';

    /**
     * Netlify Deploy Live Trigger URL
     *
     * @var string
     */
    public $netlifyDeployLiveTriggerUrl = '';

    /**
     * Netlify Preview URL
     *
     * @var string
     */
    public $netlifyPreviewUrl = '';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['netlifyDeployPreviewTriggerUrl', 'default', 'value' => ''],
            ['netlifyDeployLiveTriggerUrl', 'string'],
            ['netlifyDeployLiveTriggerUrl', 'default', 'value' => ''],
            ['netlifyPreviewUrl', 'default', 'value' => ''],
        ];
    }
}
