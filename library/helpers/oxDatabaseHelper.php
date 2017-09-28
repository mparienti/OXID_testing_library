<?php

/**
 * This file is part of OXID eSales Testing Library.
 *
 * OXID eSales Testing Library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Testing Library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Testing Library. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 */

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\EshopCommunity\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\TestingLibrary\Services\Library\DatabaseHandler;

class oxDatabaseHelper
{
    /** @var DatabaseInterface The database to use */
    protected $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     *
     * @return object
     */
    public function getFieldInformation($tableName, $fieldName)
    {
        $columns = $this->database->metaColumns($tableName);

        foreach($columns as $column) {
            if ($column->name === $fieldName) {

                return $column;
            }
        }

        return null;
    }

    /**
     * @param string $tableName
     */
    public function dropView($tableName)
    {
        if ($this->existsView($tableName)) {
            $generator = oxNew(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);
            $tableNameView = $generator->getViewName($tableName, 0);

            $this->database->execute("DROP VIEW " . $this->database->quoteIdentifier($tableNameView));
        }
    }

    /**
     * @param string $tableName
     *
     * @return bool Does the view with the given name exists?
     */
    public function existsView($tableName)
    {
        $generator = oxNew(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);
        $tableNameView = $generator->getViewName($tableName, 0);
        $sql = "SHOW TABLES LIKE '$tableNameView'";

        return $tableNameView === $this->database->getOne($sql);
    }

    public function adjustTemplateBlocksOxModuleColumn()
    {
        $sql = "ALTER TABLE `oxtplblocks` 
          CHANGE `OXMODULE` `OXMODULE` char(32) 
          character set latin1 collate latin1_general_ci NOT NULL 
          COMMENT 'Module, which uses this template';";

        $this->database->execute($sql);
    }

    public function getDataBaseTables()
    {
        $shopConfigFile = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\ConfigFile::class);
        $databaseHandler = new DatabaseHandler($shopConfigFile);

        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $databaseHandler->getDbName() . "'";

        return DatabaseProvider::getDb()->getAll($sql);
    }
}
