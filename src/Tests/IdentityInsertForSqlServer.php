<?php /*

SLO Cloud - A Cloud-Based SLO Reporting Tool for Higher Education

This is a peer-reviewed, open-source, public project made possible by the Open Innovation in Higher Education project.

Copyright (C) 2015 Jesse Lawson, San Bernardino Community College District

Contributors:
Jesse Lawson
Jason Brady

THIS PROJECT IS LICENSED UNDER GPLv2. YOU MAY COPY, DISTRIBUTE AND MODIFY THE SOFTWARE AS LONG AS YOU TRACK
CHANGES/DATES OF IN SOURCE FILES AND KEEP ALL MODIFICATIONS UNDER GPL. YOU CAN DISTRIBUTE YOUR APPLICATION USING A
GPL LIBRARY COMMERCIALLY, BUT YOU MUST ALSO DISCLOSE THE SOURCE CODE.

GNU General Public License Version 2 Disclaimer:

---

This file is part of SLO Cloud

SLO Cloud is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later.

SLO Cloud is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free
Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or
visit http://opensource.org/licenses/GPL-2.0

---

*/

namespace SLOCloud\Tests;

use PHPUnit_Extensions_Database_DataSet_ITable;
use PHPUnit_Extensions_Database_DataSet_ITableMetaData;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Extensions_Database_Operation_Insert;

/**
 * We override one method in the class in order to allow identity inserts for SQL Server
 * @package SLOCloud\Tests
 */
class IdentityInsertForSqlServer extends PHPUnit_Extensions_Database_Operation_Insert
{
    protected function buildOperationQuery(
        PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData,
        PHPUnit_Extensions_Database_DataSet_ITable $table,
        PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
    ) {
        $columnCount = count($table->getTableMetaData()->getColumns());

        if ($columnCount > 0) {
            $placeHolders = implode(', ', array_fill(0, $columnCount, '?'));

            $columns = '';
            foreach ($table->getTableMetaData()->getColumns() as $column) {
                $columns .= $connection->quoteSchemaObject($column).', ';
            }

            $columns = substr($columns, 0, -2);

            $tableName = $connection->quoteSchemaObject($table->getTableMetaData()->getTableName());
            $query = "
                BEGIN TRY
                    SET IDENTITY_INSERT {$tableName} ON;
                END TRY
                BEGIN CATCH
                END CATCH
                INSERT INTO {$tableName}
                ({$columns})
                VALUES
                ({$placeHolders});
                BEGIN TRY
                    SET IDENTITY_INSERT {$tableName} OFF;
                END TRY
                BEGIN CATCH
                END CATCH
            ";

            return $query;
        } else {
            return false;
        }
    }
}
