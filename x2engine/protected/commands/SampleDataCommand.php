<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2017 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/

Yii::import('application.components.util.*');

/**
 * Sample/dummy data exporter.
 * 
 * A command for exporting non-application (human-entered) data into an SQL 
 * script for use as sample data. Requires the "mysqldump" utility to be 
 * installed on the system.
 * 
 * The SQL generated by this script can be used as an alternate method for 
 * exporting data, reinstalling and importing data into the fresh installation.
 * Note, however that it does not save custom modules or any of the tables 
 * listed in $tblsExclude for these reasons:
 * 
 * - x2_auth tables: there is no easy, reliable way of distinguishing 
 * 		user-entered data in this table from default application data.
 * - x2_sessions/x2_temp_files: This data is entirely ephemeral
 * - x2_timezones/x2_timezone_points: This is static data inserted during 
 * 		installation and doesn't need to be exported.
 * 
 * Note also that any files in the uploads folder will also need to be backed up,
 * if the data is to be re-used elsewhere; references to files on the server 
 * will otherwise point to nonexistent files.
 * @package application.commands
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class SampleDataCommand extends CConsoleCommand {

    public $pdo;

    /**
     * Format a string with a value such that it can be used in an SQL statement
     * 
     * @param type $x
     * @return str
     */
    public function sqlValue($val) {
        if ($val === null) {
            return "NULL";
        } else if (is_int($val)) {
            return "$val";
        } else if (is_bool($val)) {
            return (string) ((int) $val);
        } else {// string
            return $this->pdo->quote($val);
        }
    }

    /**
     * Exports the database content into dummy data files
     * 
     * @param array $args
     * @param PDOException $e
     * @return type 
     */
    public function actionExport($args) {

        if (!copy("./data/install_timestamp", "./data/dummy_data_date")) {
            die("Error: actionExport: failed to copy install_timestamp to dummy_data_date");
        }

        // [edition] => [array of table names]
        $tblEditions = require(realpath(Yii::app()->basePath . '/data/nonFreeTables.php'));
        $allEditions = array_keys($tblEditions);
        $nonFreeEditions = array_diff($allEditions, array('opensource'));
        $specTemplate = array_fill_keys($allEditions, array());
        $this->pdo = Yii::app()->db->pdoInstance;
        $conf = realpath(Yii::app()->basePath . '/config/X2Config.php');
        if ($conf) {
            if ((include $conf) !== 1) {
                die('Configuration import failed.');
            }
        } else {
            die("Configuration file not found. This script must be run in protected/data.\n");
        }
        $getTbls = $this->pdo->prepare("SHOW TABLES IN `$dbname`");
        $getTbls->execute();
        try {
            $allTbls = array_map(function($tr)use($dbname) {
                return $tr["Tables_in_$dbname"];
            }, $getTbls->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage() . "\n");
        }

        /**
         * The command for exporting data:
         */
        $command = "mysqldump -tc -u $user -p$pass $dbname ";

        // Ignore pattern for lines in output of mysqldump:
        $lPat = '/^(\/\*|\-\-|\s*$';
        // Export current app's data as "dummy" (usage example) data
        $lPat.='|(?:UN)?LOCK TABLES)/';
        $out = FileUtil::rpath(Yii::app()->basePath . '/data/dummy_data%s.sql');

        /**
         * Update the list of tables for each edition with the default tables:
         */
        $nonFreeTbls = array_reduce($allEditions,
                function($a, $e)use($tblEditions) {
            return array_merge($tblEditions[$e], $a);
        }, array());
        $tblEditions['opensource'] = array_diff($allTbls, $nonFreeTbls);

        /**
         * Declare the export specification arrays
         *
         * Here it's specified what data will be exported and how.
         * Each of these arrays follows the basic pattern of $specTemplate:
         * [edition] => [array of table names or ([table name] =>[spec])]
         */
        /**
         * These will be excluded from data export altogether
         */
        $tblsExclude = $specTemplate;
        // These will be excluded for open source and above:
        $tblsExclude['opensource'] = array_merge(array(
            'x2_admin',
            'x2_auth_assignment',
            'x2_auth_item',
            'x2_auth_item_child',
            'x2_doc_folders',
            'x2_modules',
	    'x2_mobile_layouts',
            'x2_sessions',
            'x2_temp_files',
            'x2_timezone_points',
            'x2_timezones',
            'x2_tips',
                ), $tblEditions['pro'], $tblEditions['pla']);
        // These for professional edition:
        $tblsExclude['pro'] = array_merge(array(
            'x2_forwarded_email_patterns',
            'x2_charts',
            'x2_reports_2',
                ), $tblEditions['pla']);
        // These for platform/platinum edition:
        $tblsExclude['pla'] = array(
            'x2_forwarded_email_patterns'
        );

        /**
         * These will be included, but with specific criteria
         */
        $tblsWhere = $specTemplate;
        $tblsWhere['opensource'] = array(
            'x2_dropdowns' => 'id>=1000',
            'x2_fields' => 'custom=1',
            'x2_form_layouts' => 'id>=1000',
            'x2_media' => '(id>11 AND id<1000) OR (id>1006 AND id<2000) OR id>2002', // Quit messing with my head, guys! I mean it! -- keep the "id" field following a simple and consistent pattern in protected/modules/media/data/install.sql
            'x2_profile' => 'id>2',
            'x2_users' => 'id>2',
            'x2_social' => 'id>1',
            'x2_docs' => 'id>52 OR id<52' // exclude the sample quote template, which is default
        );

        /**
         * Update statements will be generated for these tables on which there's no way
         * of inserting it at install time without running into duplicate primary key
         * errors (because it's a record inserted by the installer itself). In each table:
         * 'pk' =>  primary key (string for single-column or array for multi-column)
         * 'fields' => array of fields to update or "*" to update all fields. Must include primary key.
         * 'where' => records for which to generate update statements
         */
        $tblsChangeDefault = $specTemplate;
        $tblsChangeDefault['opensource'] = array(
            'x2_profile' => array(
                'pk' => 'id',
                'fields' => '*',
                'where' => '`id`=1'
            ),
            'x2_users' => array(
                'pk' => 'id',
                'fields' => array('id', 'firstName', 'lastName', 'officePhone',
                    'cellPhone', 'showCalendars',
                    'recentItems', 'topContacts'),
                'where' => '`id`=1'
            )
        );

        /**
         * Switch the order of output generation so that foreign key constraints don't 
         * fail during insertion. List dependencies here.
         */
        $insertFirst = $specTemplate;
        $insertFirst['opensource'] = array(
            'x2_action_meta_data' => array('x2_actions'),
            'x2_role_to_permission' => array('x2_roles'),
            'x2_role_to_user' => array('x2_roles'),
            'x2_list_criteria' => array('x2_lists'),
            'x2_list_items' => array('x2_lists'),
            'x2_role_to_workflow' => array('x2_workflow_stages', 'x2_roles', 'x2_workflows'),
            'x2_workflow_stages' => array('x2_workflows'),
            'x2_action_text' => array('x2_actions'),
            'x2_actions' => array('x2_workflows', 'x2_workflow_stages'),
        );
        /**
         * This array stores tables to be executed "next"
         */
        $insertNext = $specTemplate;

        /**
         * The resulting SQL to be written to files 
         */
        $allSql = $specTemplate;

        /**
         * Assemble the array of combined export specs.
         * 
         * Note that since the "where" conditions are put in the array last, they'll
         * take precedence (so if it's listed in both $tblsExclude and $tblsWhere, 
         * only $tblsWhere will apply).
         */
        $allTbls = array();
        foreach ($allEditions as $edition) {
            $allTbls[$edition] = array_fill_keys($tblEditions[$edition], true);
            foreach ($tblsExclude[$edition] as $tbl)
                    $allTbls[$edition][$tbl] = false;
            foreach ($tblsWhere[$edition] as $tbl => $where)
                    $allTbls[$edition][$tbl] = $where;
        }

        // The update statement that will be used for updating records post-insertion:
        $updateStatement = "UPDATE `%s` SET %s WHERE %s;";

        foreach ($nonFreeEditions as $edition)
                $allSql[$edition][] = "/* @edition:$edition */";

        /**
         * Generate SQL for the data:
         */
        foreach ($allTbls as $edition => $tbls) {

            /**
             * Generate insertion statements 
             */
            $eTbls = $tbls;
            while (count($eTbls) > 0) {
                $tblsTmp = $eTbls;
                foreach ($tblsTmp as $tbl => $where) {
                    if ($where != false) {
                        // This table is to be included in the data export
                        if (array_key_exists($tbl, $insertFirst[$edition])) {
                            // This table depends on other tables being ready with data
                            $skip = False;
                            foreach ($insertFirst[$edition][$tbl] as $tblFirst)
                            // Check to see if the table has been accounted for already
                                    if (array_key_exists($tblFirst, $eTbls)) {
                                    $skip = True;
                                    break;
                                }
                            if ($skip)
                            // Not all dependencies of this table have been resolved yet.
                                    continue;
                        }
                        $output = array();
                        $tblCommand = "$command $tbl" . ($where !== true ? " --where='" . $where . "' "
                                            : ' ');
                        exec($tblCommand, $output);
                        foreach ($output as $line) {
                            if (!preg_match($lPat, $line)) {
                                $allSql[$edition][] = $line;
                            }
                        }
                    }
                    unset($eTbls[$tbl]);
                }
            }

            /**
             * Generate update statements 
             */
            foreach ($tblsChangeDefault[$edition] as $tbl => $how) {
                $colSel = $how['fields'];
                if (is_array($how['fields']))
                        $colSel = '`' . implode('`,`', $how['fields']) . '`';
                $query = $this->pdo->prepare("SELECT $colSel FROM `$tbl` WHERE {$how['where']}");
                $query->execute();
                $recs = $query->fetchAll(PDO::FETCH_ASSOC);
                $pk = $how['pk'];
                if (!is_array($pk)) $pk = array($pk);
                foreach ($recs as $rec) {
                    // Generate a "where" clause criterion to refer to this record by its primary key
                    $whereSelector = array();
                    foreach ($pk as $c) {
                        $whereSelector[] = "`$c`=" . $this->sqlValue($rec[$c]);
                    }
                    // Exclude the primary key from the columns to be updated:
                    foreach ($pk as $col) unset($rec[$col]);
                    $fieldsSet = array();

                    foreach ($rec as $col => $val)
                            $fieldsSet[] = "`$col`=" . $this->sqlValue($val);

                    $allSql[$edition][] = sprintf($updateStatement, $tbl,
                            implode(',', $fieldsSet),
                            implode(' AND ', $whereSelector));
                }
            }
        }

        // Create dummy data files
        foreach ($allSql as $edition => $sqls)
                file_put_contents(sprintf($out,
                            $edition == 'opensource' ? '' : "-$edition"),
                    implode("\n/*&*/\n", $sqls));
    }

    /**
     * Hunts through the database for in-the-future timestamps and reports them
     *
     * @param type $args 
     */
    public function actionFutureTimes($args) {
        $dateFields = require(realpath(Yii::app()->basePath . '/data/dateFields.php'));
        $maxFuture = array(
            'table' => null,
            'column' => null,
            'key' => null,
            'date' => 0
        );
        $useFile = array_pop($args);
        if ($useFile)
                $time = (int) file_get_contents(realpath(Yii::app()->basePath . '/data/dummy_data_date'));
        else $time = time();
        $minFuture = array_merge(array(), $maxFuture);
        $minFuture['date'] = PHP_INT_MAX;
        $time = time();
        $futureFields = require(realpath(Yii::app()->basePath . '/data/futureFields.php'));
        $futureTables = array_keys($futureFields);

        foreach ($dateFields as $table => $cols) {
            $pk = Yii::app()->db->schema->getTable($table)->primaryKey;

            $pastCols = $cols;
            // Exclude fields that are permitted to be in the future:
            if (in_array($table, $futureTables))
                    $pastCols = array_diff($pastCols, $futureFields[$table]);

            $select = array_merge(is_array($pk) ? $pk : array($pk), $pastCols);
            $where = '`' . implode("`>$time OR `", $pastCols) . "`>$time";
            $dates = Yii::app()->db->createCommand()
                    ->select($select)
                    ->from($table)
                    ->where($where)
                    ->queryAll();
            if (!empty($dates)) echo implode("\t", $pastCols) . "\t($table)\n";
            foreach ($dates as $record) {
                $line = '';
                foreach ($select as $col) {
                    $line .= ($record[$col] == null ? "NULL" : $record[$col]) . "\t";
                }
                foreach ($pastCols as $dateField) {
                    $date = $record[$dateField];
                    if ($date > $maxFuture['date']) {
                        $maxFuture['table'] = $table;
                        $maxFuture['column'] = $dateField;
                        $maxFuture['key'] = var_export($pk, true);
                        $maxFuture['date'] = $date;
                    }
                    if ($date > $time && $date < $minFuture['date']) {
                        $minFuture['table'] = $table;
                        $minFuture['column'] = $dateField;
                        $minFuture['key'] = var_export($pk, true);
                        $minFuture['date'] = $date;
                    }
                }
                echo "$line\n";
            }
        }
        echo "\nRecord furthest in the future:\n";
        print_r($maxFuture);
        echo strftime('%c', $maxFuture['date']);
        echo "\nRecord least far in the future:\n";
        print_r($minFuture);
        echo strftime('%c', $minFuture['date']);
    }

    /**
     * "Compress" all sample data timestamps
     *
     * Brings all timestamps closer to "now" using a logarithmic scale. This is
     * to bring really far-apart events closer together while avoiding too much
     * "clumping" of events around the installation timestamp.
     *
     * @param array $newDisp The new furthest time into the past that any event
     *  is allowed to go.
     */
    public function actionSquashtime($dtnew) {
        $newDisp = (int) trim($dtnew);
        echo "Finding the oldest event in the sample data...\n";
        $dateFields = require(realpath(Yii::app()->basePath . '/data/dateFields.php'));
        $installTimestamp = (integer) file_get_contents(implode(DIRECTORY_SEPARATOR,
                                array(
                    Yii::app()->basePath, 'data', 'dummy_data_date'
        )));
        $now = $installTimestamp;
        $min = $now;
        foreach ($dateFields as $table => $columns) {
            $newMin = Yii::app()->db->createCommand()
                    ->select(count($columns) > 1 ? 'LEAST(MIN(`' . implode('`),MIN(`',
                                            $columns) . '`))' : 'MIN(`' . reset($columns) . '`)')
                    ->from($table)
                    ->queryScalar();
            if (!empty($newMin) && $newMin < $min) {
                $min = $newMin;
                echo "Older timestamp $newMin found in table $table\n";
            }
        }
        echo "min: $min\nnow: $now\n";
        $oldDisp = $installTimestamp - $min;

        $yn = $this->prompt("The oldest record is $oldDisp seconds in the "
                . "past. Are you sure you want to proceed with adjusting all "
                . "timestamps logarithmically such that the old maximum time "
                . "displacement into the past $oldDisp becomes the new, $newDisp?");
        if (!preg_match('/^y(es)?$/i', trim($yn))) Yii::app()->end();

        foreach ($dateFields as $table => $columns) {
            foreach ($columns as $column) {
                list($setClause, $params) = $this->timeCompressSql($column,
                        $installTimestamp, $oldDisp, $newDisp);
                $sqlRun = "UPDATE `$table` " . $setClause;
                Yii::app()->db->createCommand($sqlRun)
                        ->execute($params);
                echo 'Ran "' . strtr($sqlRun, $params) . "\"\n";
            }
        }
    }

    /**
     * Generates update SQL for a timestamp column to "compress" times
     * 
     * @param string $column Attribute/column name to be changed
     * @param type $ti Timestamp of installation ("now")
     * @param type $dtMax Furthest time into the past that events go
     * @param type $dtMaxNew New furthest time into the past that events can go
     * @return type
     */
    public function timeCompressSql($column, $ti, $dtMax, $dtMaxNew) {
        $sql = "SET `$column`=(:ti1-:dtMaxNew*LOG2(1+(:ti2-`$column`)/:dtMax)) "
                . "WHERE `$column` < :ti3";
        $params = array(
            ':ti1' => $ti,
            ':ti2' => $ti,
            ':ti3' => $ti,
            ':dtMaxNew' => $dtMaxNew,
            ':dtMax' => $dtMax
        );
        return array($sql, $params);
    }

}

?>
