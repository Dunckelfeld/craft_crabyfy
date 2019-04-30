<?php
/**
 * CraByFy plugin for Craft CMS 3.x
 *
 * Deploys craft fed gatsby frontend to netlify
 *
 * @link      dunckelfeld.de
 * @copyright Copyright (c) 2018 Dunckelfeld
 */

namespace dunckelfeld\crabyfy\migrations;

use dunckelfeld\crabyfy\CraByFy;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * CraByFy Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Dunckelfeld
 * @package   CraByFy
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        // crabyfy_deploystatus table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%crabyfy_deploystatus}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%crabyfy_deploystatus}}',
                [
                    'id'          => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid'         => $this->uid(),
                    // Custom columns in the table
                    'siteId'      => $this->integer()->notNull(),
                    'status'      => $this->string(255)->notNull()->defaultValue(''),
                    'deployType'  => $this->string(255)->notNull()->defaultValue(''),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
        // crabyfy_deploystatus table
        // $this->createIndex(
        //     $this->db->getIndexName(
        //         '{{%crabyfy_deploystatus}}',
        //         'status',
        //         true
        //     ),
        //     '{{%crabyfy_deploystatus}}',
        //     'status',
        //     true
        // );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        // crabyfy_deploystatus table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%crabyfy_deploystatus}}', 'siteId'),
            '{{%crabyfy_deploystatus}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        // crabyfy_deploystatus table
        $this->dropTableIfExists('{{%crabyfy_deploystatus}}');
    }
}
