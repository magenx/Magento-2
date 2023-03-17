<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ComposerDependencyVersionAuditPlugin;

use Composer\DependencyResolver\Request;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Plugin\PrePoolCreateEvent;
use Composer\Semver\Constraint\Constraint;
use Magento\ComposerDependencyVersionAuditPlugin\Utils\Version;
use PHPUnit\Framework\TestCase;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\PackageEvent;
use Composer\Composer;
use Composer\Package\PackageInterface;
use Composer\Repository\ComposerRepository;
use Composer\IO\IOInterface;
use Composer\Repository\RepositoryManager;
use Composer\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Composer\DependencyResolver\Pool;
use Composer\Util\HttpDownloader;
use Composer\Repository\RepositorySet;

/**
 * Test for Class Magento\ComposerDependencyVersionAuditPlugin\Plugin
 */
class PluginTest extends TestCase
{

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var MockObject
     */
    private $eventMock;

    /**
     * @var MockObject
     */
    private $composerMock;
    /**
     * @var MockObject
     */
    private $packageMock;

    /**
     * @var MockObject
     */
    private $installOperationMock;

    /**
     * @var MockObject
     */
    private $poolMock;

    /**
     * @var MockObject
     */
    private $versionSelectorMock;

    /**
     * @var MockObject
     */
    private $repositoryMock1;

    /**
     * @var MockObject
     */
    private $repositoryMock2;

    /**
     * @var HttpDownloader
     */
    private $httpDownloader;

    /**
     * @var MockObject
     */
    private $repositorySetMock;

    /**
     * @var Request
     */
    private $requestMock;

    /**
     * @var NullIO
     */
    private $ioMock;

    /**
     * @var PrePoolCreateEvent
     */
    private $prePoolCreateMock;

    /**#@+
     * Package name constant for test
     */
    const PACKAGE_NAME = 'foo/some-test-package';

    /**
     * Initialize Dependencies
     */
    protected function setUp(): void
    {
        $composerMajorVersion = (int)explode('.', Composer::VERSION)[0];
        $this->io = new NullIO();
        $this->config = Factory::createConfig($this->io);

        if ($composerMajorVersion === 1) {
            $this->repositoryManager = new RepositoryManager($this->io, $this->config);
            $this->poolMock = $this->getMockBuilder(Pool::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['addRepository'])
                ->getMock();

            $this->requestMock = $this->getMockBuilder(Request::class)
                ->onlyMethods(['getJobs'])
                ->disableOriginalConstructor()
                ->getMock();

            $this->eventMock = $this->getMockBuilder(PackageEvent::class)
                ->onlyMethods(['getOperation', 'getComposer', 'getRequest', 'getIO'])
                ->disableOriginalConstructor()
                ->getMock();

            $this->eventMock->expects($this->any())
                ->method('getRequest')
                ->willReturn($this->requestMock);

        } elseif ($composerMajorVersion === 2) {
            $this->httpDownloader = new HttpDownloader($this->io, $this->config);
            $this->repositoryManager = new RepositoryManager($this->io, $this->config, $this->httpDownloader);
            $this->repositorySetMock = $this->getMockBuilder(RepositorySet::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['addRepository'])
                ->getMock();

            $this->eventMock = $this->getMockBuilder(PackageEvent::class)
                ->onlyMethods(['getOperation', 'getComposer', 'getIO'])
                ->disableOriginalConstructor()
                ->getMock();

            $this->requestMock = $this->getMockBuilder(Request::class)
                ->onlyMethods(['getRequires'])
                ->disableOriginalConstructor()
                ->getMock();

            $this->prePoolCreateMock = $this->getMockBuilder(PrePoolCreateEvent::class)
                ->onlyMethods(['getRequest', 'getPackages'])
                ->disableOriginalConstructor()
                ->getMock();

            $this->prePoolCreateMock->expects($this->any())
                ->method('getRequest')
                ->willReturn($this->requestMock);
        }


        $this->packageMock = $this->getMockBuilder(PackageInterface::class)
            ->onlyMethods(['getName', 'getFullPrettyVersion'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->composerMock = $this->getMockBuilder(Composer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPackage', 'getRepositoryManager'])
            ->getMock();

        $this->installOperationMock = $this->getMockBuilder(InstallOperation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPackage'])
            ->getMock();

        $this->versionSelectorMock = $this->getMockBuilder(Version::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBestCandidate'])
            ->getMock();

        $this->repositoryMock1 = $this->getMockBuilder(ComposerRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findPackage', 'addPackage', 'getRepoConfig'])
            ->getMock();

        $this->repositoryMock2 = $this->getMockBuilder(ComposerRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findPackage', 'addPackage', 'getRepoConfig'])
            ->getMock();

        $this->ioMock = $this->getMockBuilder(NullIO::class)
            ->onlyMethods(['writeError'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->composerMock->expects($this->any())
            ->method('getRepositoryManager')
            ->willReturn($this->repositoryManager);

        $this->eventMock->expects($this->any())
            ->method('getComposer')
            ->willReturn($this->composerMock);

        $this->eventMock->expects($this->any())
            ->method('getOperation')
            ->willReturn($this->installOperationMock);

        $this->installOperationMock->expects($this->any())
            ->method('getPackage')
            ->willReturn($this->packageMock);

        $this->packageMock->expects($this->any())
            ->method('getName')
            ->willReturn(self::PACKAGE_NAME);

        $this->plugin = new Plugin($this->versionSelectorMock);
        $this->repositoryManager->addRepository($this->repositoryMock1);
        $this->repositoryManager->addRepository($this->repositoryMock2);
        parent::setUp();
    }

    /**
     * Test valid package install/update
     */
    public function testValidPackageUpdate(): void
    {
        $this->repositoryMock1->expects($this->any())
            ->method('getRepoConfig')
            ->willReturn(['url' => 'https://repo.packagist.org']);

        $this->repositoryMock2->expects($this->any())
            ->method('getRepoConfig')
            ->willReturn(['url' => 'https://someprivaterepo.org']);

        $this->versionSelectorMock->expects($this->any())
            ->method('findBestCandidate')
            ->willReturn($this->packageMock);

        $this->packageMock->expects($this->any())
            ->method('getFullPrettyVersion')
            ->willReturnOnConsecutiveCalls('1.0.1', '1.0.10');

        $constraintMock = $this->getMockBuilder(Constraint::class)
            ->onlyMethods(['getPrettyString'])
            ->disableOriginalConstructor()
            ->getMock();

        $constraintMock->expects($this->any())
            ->method('getPrettyString')
            ->willReturn("1.0.5");

        if ((int)explode('.', Composer::VERSION)[0] === 1) {
            $this->requestMock->expects($this->any())
                ->method('getJobs')
                ->willReturn([
                    ['packageName' => self::PACKAGE_NAME, 'cmd' => 'install', 'fixed' => true, 'constraint' => $constraintMock]
                ]);
        } else {

            $this->requestMock->expects($this->any())
                ->method('getRequires')
                ->willReturn([
                    self::PACKAGE_NAME => $constraintMock
                ]);

            $this->prePoolCreateMock->expects($this->any())
                ->method('getPackages')
                ->willReturn([]);

            $this->plugin->prePoolCreate($this->prePoolCreateMock);
        }

        $this->assertNull($this->plugin->packageUpdate($this->eventMock));
    }

    /**
     * Test invalid package install/update that shows a warning to the user about their requires
     */
    public function testInvalidPackageUpdateWithWarning(): void
    {
        $privateRepoUrl = 'https://example.org';
        $publicRepoVersion ='1.0.5';
        $privateRepoVersion = '1.0.1';

        $this->repositoryMock1->expects($this->any())
            ->method('getRepoConfig')
            ->willReturn(['url' => 'https://repo.packagist.org']);

        $this->repositoryMock2->expects($this->any())
            ->method('getRepoConfig')
            ->willReturn(['url' => $privateRepoUrl]);

        $this->versionSelectorMock->expects($this->any())
            ->method('findBestCandidate')
            ->willReturn($this->packageMock);

        $this->packageMock->expects($this->any())
            ->method('getFullPrettyVersion')
            ->willReturnOnConsecutiveCalls($publicRepoVersion, $privateRepoVersion);

        $constraintMock = $this->getMockBuilder(Constraint::class)
            ->onlyMethods(['getPrettyString'])
            ->disableOriginalConstructor()
            ->getMock();

        $constraintMock->expects($this->any())
            ->method('getPrettyString')
            ->willReturn("1.0.5");

        $packageName = self::PACKAGE_NAME;
        $exceptionMessage = "<warning>Higher matching version {$publicRepoVersion} of {$packageName} was found in public repository packagist.org 
                             than {$privateRepoVersion} in private {$privateRepoUrl}. Public package might've been taken over by a malicious entity, 
                             please investigate and update package requirement to match the version from the private repository</warning>";

        if ((int)explode('.', Composer::VERSION)[0] === 1) {
            $this->requestMock->expects($this->any())
                ->method('getJobs')
                ->willReturn([
                    ['packageName' => self::PACKAGE_NAME, 'cmd' => 'install', 'fixed' => true, 'constraint' => $constraintMock]
                ]);
        } else {

            $this->requestMock->expects($this->any())
                ->method('getRequires')
                ->willReturn([
                    self::PACKAGE_NAME => $constraintMock
                ]);

            $this->prePoolCreateMock->expects($this->any())
                ->method('getPackages')
                ->willReturn([]);

            $this->plugin->prePoolCreate($this->prePoolCreateMock);
        }

        $this->ioMock->expects($this->once())
            ->method('writeError')
            ->with($this->stringContains($exceptionMessage));

        $this->eventMock->expects($this->once())
            ->method('getIO')
            ->willReturn($this->ioMock);

        $this->plugin->packageUpdate($this->eventMock);
    }

    /**
     * Test invalid package install/update that should throw an exception
     */
    public function testInvalidPackageUpdateWithException(): void
    {
        $privateRepoUrl = 'https://example.org';
        $publicRepoVersion ='1.0.5';
        $privateRepoVersion = '1.0.1';

        $this->repositoryMock1->expects($this->any())
            ->method('getRepoConfig')
            ->willReturn(['url' => 'https://repo.packagist.org']);

        $this->repositoryMock2->expects($this->any())
            ->method('getRepoConfig')
            ->willReturn(['url' => $privateRepoUrl]);

        $this->versionSelectorMock->expects($this->any())
            ->method('findBestCandidate')
            ->willReturn($this->packageMock);

        $this->packageMock->expects($this->any())
            ->method('getFullPrettyVersion')
            ->willReturnOnConsecutiveCalls($publicRepoVersion, $privateRepoVersion);

        $constraintMock = $this->getMockBuilder(Constraint::class)
            ->onlyMethods(['getPrettyString'])
            ->disableOriginalConstructor()
            ->getMock();

        $constraintMock->expects($this->any())
            ->method('getPrettyString')
            ->willReturn("1.0.*");

        if ((int)explode('.', Composer::VERSION)[0] === 1) {
            $this->requestMock->expects($this->any())
                ->method('getJobs')
                ->willReturn([
                    ['packageName' => self::PACKAGE_NAME, 'cmd' => 'install', 'fixed' => false, 'constraint' => $constraintMock]
                ]);
        } else {

            $this->requestMock->expects($this->any())
                ->method('getRequires')
                ->willReturn([
                    self::PACKAGE_NAME => $constraintMock
                ]);

            $this->prePoolCreateMock->expects($this->any())
                ->method('getPackages')
                ->willReturn([]);

            $this->plugin->prePoolCreate($this->prePoolCreateMock);
        }

        $packageName = self::PACKAGE_NAME;
        $exceptionMessage = "Higher matching version {$publicRepoVersion} of {$packageName} was found in public repository packagist.org 
                             than {$privateRepoVersion} in private {$privateRepoUrl}. Public package might've been taken over by a malicious entity, 
                             please investigate and update package requirement to match the version from the private repository";
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf($exceptionMessage, self::PACKAGE_NAME));

        $this->plugin->packageUpdate($this->eventMock);
    }

}
