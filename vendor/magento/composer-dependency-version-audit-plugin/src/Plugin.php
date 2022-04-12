<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ComposerDependencyVersionAuditPlugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\ComposerRepository;
use Composer\Repository\RepositoryInterface;
use Composer\Package\PackageInterface;
use Exception;
use Magento\ComposerDependencyVersionAuditPlugin\Utils\Version;

/**
 * Composer's entry point for the plugin
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{

    /**#@+
     * URL For Public Packagist Repo
     */
    const URL_REPO_PACKAGIST = 'https://repo.packagist.org';

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var Version
     */
    private $versionSelector;

    /**#@+
     * Constant for VBE ALLOW LIST
     */
    private const VBE_ALLOW_LIST = [
        'vertexinc',
        'yotpo',
        'klarna',
        'amzn',
        'dotmailer',
        'braintree',
        'paypal',
        'gene'
    ];

    /**
     * Initialize dependencies
     * @param Version|null $version
     */
    public function __construct(Version $version = null)
    {
        if ($version) {
            $this->versionSelector = $version;
        } else {
            $this->versionSelector = new Version();
        }
    }

    /**
     * @inheritdoc
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // Declaration must exist
    }

    /**
     * @inheritdoc
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Declaration must exist
    }

    /**
     * @inheritdoc
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
        // Declaration must exist
    }

    /**
     * Event subscriber
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Installer\PackageEvents::PRE_PACKAGE_INSTALL => 'packageUpdate',
            Installer\PackageEvents::PRE_PACKAGE_UPDATE => 'packageUpdate'
        ];
    }

    /**
     * Event listener for Package Install or Update
     *
     * @param PackageEvent $event
     * @return void
     * @throws Exception
     */
    public function packageUpdate(PackageEvent $event): void
    {
        /** @var  OperationInterface */
        $operation = $event->getOperation();
        $this->composer = $event->getComposer();

        /** @var PackageInterface $package  */
        $package = method_exists($operation, 'getPackage')
            ? $operation->getPackage()
            : $operation->getInitialPackage();

        $packageName = $package->getName();
        $privateRepoVersion = '';
        $publicRepoVersion = '';
        $privateRepoUrl = '';
        list($namespace, $project) = explode("/", $packageName);
        $isPackageVBE = in_array($namespace, self::VBE_ALLOW_LIST, true);

        if(!$isPackageVBE) {

            foreach ($this->composer->getRepositoryManager()->getRepositories() as $repository) {

                /** @var RepositoryInterface $repository */
                if ($repository instanceof ComposerRepository) {
                    $found = $this->versionSelector->findBestCandidate($this->composer, $packageName, $repository);
                    $repoUrl = $repository->getRepoConfig()['url'];

                    if ($found) {
                        if (strpos($repoUrl, self::URL_REPO_PACKAGIST) !== false) {
                            $publicRepoVersion = $found->getFullPrettyVersion();
                        } else {
                            $currentPrivateRepoVersion = $found->getFullPrettyVersion();
                            //private repo version should hold highest version of package
                            if (empty($privateRepoVersion) || version_compare($currentPrivateRepoVersion, $privateRepoVersion, '>')) {
                                $privateRepoVersion = $currentPrivateRepoVersion;
                                $privateRepoUrl = $repoUrl;
                            }
                        }
                    }
                }
            }
            if ($privateRepoVersion && $publicRepoVersion && (version_compare($publicRepoVersion, $privateRepoVersion, '>'))) {
                $exceptionMessage = "Higher matching version {$publicRepoVersion} of {$packageName} was found in public repository packagist.org 
                             than {$privateRepoVersion} in private {$privateRepoUrl}. Public package might've been taken over by a malicious entity, 
                             please investigate and update package requirement to match the version from the private repository";
                throw new Exception($exceptionMessage);
            }
        }
    }
}
