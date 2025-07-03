<?php
declare(strict_types=1);

namespace mteu\StreamWriter\Tests\Functional\Writer;

use \stdClass;
use \TYPO3\CMS\Core\Database\ConnectionPool;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class StreamWriterTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'mteu/typo3_stream_writer',
    ];

    protected array $coreExtensionsToLoad = [
        'typo3/cms-core',
        'typo3/cms-backend',
        'typo3/cms-frontend',
    ];

    /**
     * @test
     */
    public function typo3BootsSuccessfullyWithStreamWriterExtension(): void
    {
        // Verify we can access the database
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        self::assertNotNull($connection, 'Database connection should be available');
    }

    /**
     * @test
     */
    public function canCreateGeneralUtilityInstance(): void
    {
        // Test that we can create instances through GeneralUtility
        $instance = GeneralUtility::makeInstance(ConnectionPool::class);
        self::assertInstanceOf(ConnectionPool::class, $instance, 'Should be able to create instances via GeneralUtility');
    }

    /**
     * @test
     */
    public function databaseConnectionIsAvailable(): void
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('pages');

        // Just verify we can create a query builder
        self::assertNotNull($queryBuilder, 'Query builder should be available');

        // Try to execute a simple query (check if pages table exists)
        $result = $queryBuilder
            ->count('uid')
            ->from('pages')
            ->executeQuery()
            ->fetchOne();

        self::assertIsInt($result, 'Should be able to query the pages table');
    }

    /**
     * @test
     */
    public function typo3CoreServicesAreAvailable(): void
    {
        // Test that TYPO3 core services are properly initialized
        self::assertIsArray($GLOBALS['TYPO3_CONF_VARS'], 'TYPO3_CONF_VARS should be available');
        self::assertArrayHasKey('SYS', $GLOBALS['TYPO3_CONF_VARS'], 'SYS configuration should be available');
        self::assertArrayHasKey('EXT', $GLOBALS['TYPO3_CONF_VARS'], 'EXT configuration should be available');

        // Verify that we can resolve paths
        $publicPath = GeneralUtility::getFileAbsFileName('EXT:typo3_stream_writer/ext_emconf.php');
        self::assertNotEmpty($publicPath, 'Should be able to resolve extension paths');
        self::assertFileExists($publicPath, 'Extension file should exist');
    }
}
