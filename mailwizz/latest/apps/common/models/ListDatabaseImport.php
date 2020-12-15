<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListDatabaseImport
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

class ListDatabaseImport extends ListImportAbstract
{
    const SERVER_TYPE_MYSQL = 'mysql';

    const SERVER_TYPE_POSTGRESQL = 'pgsql';

    const SERVER_TYPE_SQLSERVER = 'mssql';

    const SERVER_TYPE_ORACLE = 'oci';

    public $server_type = 'mysql';

    public $hostname;

    public $port = 3306;

    public $username;

    public $password;

    public $database_name;

    public $table_name;

    public $email_column = 'email';

    public $ignored_columns;

    protected $_dbConnection;

    protected $_columns;

    public function rules()
    {
        $rules = array(
            array('server_type, hostname, port, username, password, database_name, table_name, email_column', 'required'),
            array('database_name, table_name, email_column', 'match', 'pattern' => '/[a-z0-9_]+/i'),
            array('ignored_columns', 'match', 'pattern' => '/[a-z0-9_,\s]+/i'),
            array('port', 'numerical', 'integerOnly' => true),
            array('server_type', 'in', 'range' => array_keys($this->getServerTypes())),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    public function attributeLabels()
    {
        $labels = array(
            'server_type'       => Yii::t('list_import', 'Server type'),
            'hostname'          => Yii::t('list_import', 'Hostname'),
            'port'              => Yii::t('list_import', 'Port'),
            'username'          => Yii::t('list_import', 'Username'),
            'password'          => Yii::t('list_import', 'Password'),
            'database_name'     => Yii::t('list_import', 'Database name'),
            'table_name'        => Yii::t('list_import', 'Table name'),
            'email_column'      => Yii::t('list_import', 'Email column'),
            'ignored_columns'   => Yii::t('list_import', 'Ignored columns'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'hostname'          => Yii::t('list_import', 'i.e: mysql.databaseclusters.com'),
            'port'              => Yii::t('list_import', 'i.e: 3306'),
            'username'          => Yii::t('list_import', 'i.e: mysqlcluser'),
            'password'          => Yii::t('list_import', 'i.e: superprivatepassword'),
            'database_name'     => Yii::t('list_import', 'i.e: my_blog'),
            'table_name'        => Yii::t('list_import', 'i.e: tbl_subscribers'),
            'email_column'      => Yii::t('list_import', 'email'),
            'ignored_columns'   => Yii::t('list_import', 'i.e: id, date_added, status'),
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'server_type'       => Yii::t('list_import', 'The server type, if not sure choose mysql'),
            'hostname'          => Yii::t('list_import', 'The hostname of your database server, it can also be the ip address'),
            'port'              => Yii::t('list_import', 'The port where your external database server is listening for connections'),
            'username'          => Yii::t('list_import', 'Your username that unique identifies yourself on the database server'),
            'password'          => Yii::t('list_import', 'The password for the username'),
            'database_name'     => Yii::t('list_import', 'Your database name as you see it in a tool like PhpMyAdmin'),
            'table_name'        => Yii::t('list_import', 'Your database table name where your emails are stored, as you see it in a tool like PhpMyAdmin'),
            'email_column'      => Yii::t('list_import', 'The column that identified the email address'),
            'ignored_columns'   => Yii::t('list_import', 'Which columns should we ignore and not import. Separate multiple columns by a comma'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    public function getServerTypes()
    {
        return array(
            self::SERVER_TYPE_MYSQL      => Yii::t('list_import', 'MySQL'),
            //self::SERVER_TYPE_POSTGRESQL => Yii::t('list_import', 'PostgreSQL'),
            //self::SERVER_TYPE_SQLSERVER  => Yii::t('list_import', 'SQL Server'),
            //self::SERVER_TYPE_ORACLE     => Yii::t('list_import', 'Oracle'),
        );
    }

    public function validateAndConnect()
    {
        if (!$this->validate()) {
            return false;
        }

        return $this->getDbConnection() !== null;
    }

    public function getDbConnection()
    {
        if ($this->_dbConnection !== null) {
            return $this->_dbConnection;
        }

        try {

             $this->_dbConnection = new CDbConnection($this->getDbConnectionString(), $this->username, $this->password);
             $this->_dbConnection->active = true;
             $this->_dbConnection->autoConnect = true;
             $this->_dbConnection->emulatePrepare = true;
             $this->_dbConnection->charset = 'utf8';
             $this->_dbConnection->initSQLs = array(
                'SET time_zone="+00:00"',
                'SET NAMES utf8',
                'SET SQL_MODE=""',
             );

        } catch (Exception $e) {

            $this->addError('hostname', $e->getMessage());
            $this->_dbConnection = null;
        }

        return $this->_dbConnection;
    }

    public function getDbConnectionString()
    {
        $driversMap = array(
            self::SERVER_TYPE_MYSQL      => sprintf('mysql:host=%s;port=%d;dbname=%s', $this->hostname, $this->port, $this->database_name),
            //self::SERVER_TYPE_POSTGRESQL => sprintf('pgsql:host=%s;port=%d;dbname=%s', $this->hostname, $this->port, $this->database_name),
            //self::SERVER_TYPE_SQLSERVER  => sprintf('mssql:host=%s;port=%d;dbname=%s', $this->hostname, $this->port, $this->database_name),
            //self::SERVER_TYPE_ORACLE     => sprintf('oci:dbname=//%s:%d/%s', $this->hostname, $this->port, $this->database_name),
        );

        return isset($driversMap[$this->server_type]) ? $driversMap[$this->server_type] : $driversMap[self::SERVER_TYPE_MYSQL];
    }

    public function getColumns()
    {
        if ($this->_columns !== null) {
            return $this->_columns;
        }

        $this->_columns = array();
        $ignore  = explode(',', $this->ignored_columns);
        $ignore  = array_map('trim', $ignore);
        $ignore  = array_map('strtolower', $ignore);

        if ($this->server_type == self::SERVER_TYPE_MYSQL) {
            $_columns = $this->getDbConnection()->createCommand(sprintf('SHOW COLUMNS FROM `%s`', $this->table_name))->queryAll();
            foreach ($_columns as $data) {
                if (!isset($data['Field'])) {
                    continue;
                }
                if (in_array(strtolower($data['Field']), $ignore)) {
                    continue;
                }
                if (isset($data['Extra']) && $data['Extra'] == 'auto_increment') {
                    continue;
                }
                $this->_columns[] = $data['Field'];
            }
        }

        return $this->_columns;
    }

    public function countResults()
    {
        $count = 0;
        if ($this->server_type == self::SERVER_TYPE_MYSQL) {
            $sql   = sprintf('SELECT COUNT(*) AS counter FROM `%s` WHERE LENGTH(`%s`) > 0', $this->table_name, $this->email_column);
            $row   = $this->getDbConnection()->createCommand($sql)->queryRow();
            $count = $row['counter'];
        }
        return $count;
    }

    public function getResults($offset, $limit)
    {
        $results = array();
        if ($this->server_type == self::SERVER_TYPE_MYSQL) {
            $columns = '`' . implode('`, `', $this->getColumns()) . '`';
            $columns = preg_replace('/`' . preg_quote($this->email_column, '/') . '`/', '`' . $this->email_column . '` AS email', $columns);
	        $sql     = sprintf('SELECT %s FROM `%s` WHERE LENGTH(`%s`) > 0 ORDER BY 1 LIMIT %d OFFSET %d', $columns, $this->table_name, $this->email_column, (int)$limit, (int)$offset);
            $results = $this->getDbConnection()->createCommand($sql)->queryAll();
        }
        return $results;
    }
}
