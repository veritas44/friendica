<?php
/**
 * DatabaseTest class.
 */

namespace Friendica\Test;

use dba;
use Friendica\Database\DBStructure;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit\DbUnit\DataSet\YamlDataSet;
use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * Abstract class used by tests that need a database.
 */
abstract class DatabaseTest extends TestCase
{

	use TestCaseTrait;

	/**
	 * Get database connection.
	 *
	 * This function is executed before each test in order to get a database connection that can be used by tests.
	 * If no prior connection is available, it tries to create one using the USER, PASS and DB environment variables.
	 *
	 * If it could not connect to the database, the test is skipped.
	 *
	 * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 * @see https://phpunit.de/manual/5.7/en/database.html
	 */
	protected function getConnection()
	{
		if (!dba::$connected) {
			dba::connect('localhost', getenv('USER'), getenv('PASS'), getenv('DB'));

			if (dba::$connected) {
				$app = get_app();
				// We need to do this in order to disable logging
				$app->module = 'install';

				// Create database structure
				DBStructure::update(false, true, true);
			} else {
				$this->markTestSkipped('Could not connect to the database.');
			}
		}

		return $this->createDefaultDBConnection(dba::get_db(), getenv('DB'));
	}

	/**
	 * Get dataset to populate the database with.
	 * @return YamlDataSet
	 * @see https://phpunit.de/manual/5.7/en/database.html
	 */
	protected function getDataSet()
	{
		return new YamlDataSet(__DIR__ . '/datasets/api.yml');
	}
}
