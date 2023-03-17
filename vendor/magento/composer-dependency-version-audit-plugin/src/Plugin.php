<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ComposerDependencyVersionAuditPlugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Request;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PrePoolCreateEvent;
use Composer\Repository\ComposerRepository;
use Composer\Repository\FilterRepository;
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

    /**
     * @var array
     */
    private $nonFixedPackages;

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
        $events = [
            Installer\PackageEvents::PRE_PACKAGE_INSTALL => 'packageUpdate',
            Installer\PackageEvents::PRE_PACKAGE_UPDATE => 'packageUpdate'
        ];

        if ((int)explode('.', Composer::VERSION)[0] === 2) {
            $events[PluginEvents::PRE_POOL_CREATE] = 'prePoolCreate';
        }

        return $events;
    }

    /**
     * Get all package installations that use non-fixed version constraints (IE: 2.4.*, ^2.4, etc.)
     * this needs to be done for Composer V1 installs since prePoolCreate event doesn't exist in V1
     *
     * @param Request $request
     * @return array
     */
    private function getNonFixedConstraintList(Request $request): array
    {
        if (!$this->nonFixedPackages) {
            $constraintList = [];
            foreach ($request->getJobs() as $job) {
                if ($job['cmd'] === 'install' &&
                    (strpbrk($job['constraint']->getPrettyString(), "*^-~") ||
                        preg_match('{(?<!^|as|[=>< ,]) *(?<!-)[, ](?!-) *(?!,|as|$)}', $job['constraint']->getPrettyString()
                        )
                    )
                ) {
                    $constraintList[$job['packageName']] = true;
                }
            }
            $this->nonFixedPackages = $constraintList;
        }
        return $this->nonFixedPackages;
    }

    /**
     * Event listener for PrePoolCreate event that is used for composer V2
     *
     * @param PrePoolCreateEvent $event
     */
    public function prePoolCreate(PrePoolCreateEvent $event): void
    {
        if (!$this->nonFixedPackages) {
            $constraintList = [];

            /**
             * get all packages that are in the composer.json under require section, this will be the only time
             * we will be able to get constraints for packages in the require section as this request data isn't
             * shared in the installer event on composer v2
             */
            foreach ($event->getRequest()->getRequires() as $name => $constraint) {
                $prettyString = $constraint->getPrettyString();
                $multiConstraint = preg_match('{(?<!^|as|[=>< ,]) *(?<!-)[, ](?!-) *(?!,|as|$)}', $prettyString);
                if (strpbrk($prettyString, "*^-~") || $multiConstraint){
                    $constraintList[$name] = true;
                }
            }

            /**
             * get all sub packages that are now requirements for new packages to install and store their constraints.
             */
            foreach ($event->getPackages() as $package) {
                foreach ($package->getRequires() as $name => $constraint) {
                    $prettyConstraint = $constraint->getPrettyConstraint();
                    $multiConstraint = preg_match('{(?<!^|as|[=>< ,]) *(?<!-)[, ](?!-) *(?!,|as|$)}', $prettyConstraint);
                    if (strpbrk($prettyConstraint, "*^-~")|| $multiConstraint)
                        $constraintList[$name] = true;
                }
            }

            $this->nonFixedPackages = $constraintList;
        }
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

        if ((int)explode('.', Composer::VERSION)[0] === 1) {
            $this->getNonFixedConstraintList($event->getRequest());
        }

        if(!$isPackageVBE) {
            foreach ($this->composer->getRepositoryManager()->getRepositories() as $repository) {
                $found = $this->versionSelector->findBestCandidate($this->composer, $packageName, $repository);
                $repoUrl = "";
                /** @var RepositoryInterface $repository */
                if ($repository instanceof ComposerRepository) {
                    $repoUrl = $repository->getRepoConfig()['url'];

                } else if ($repository instanceof FilterRepository) {
                    $repoUrl = $repository->getRepository()->getRepoConfig()['url'];
                }
                if ($found) {
                    if ($repoUrl && strpos($repoUrl, self::URL_REPO_PACKAGIST) !== false) {
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

            if ($privateRepoVersion && $publicRepoVersion && version_compare($publicRepoVersion, $privateRepoVersion, '>')) {
                $exceptionMessage = "Higher matching version {$publicRepoVersion} of {$packageName} was found in public repository packagist.org 
                             than {$privateRepoVersion} in private {$privateRepoUrl}. Public package might've been taken over by a malicious entity, 
                             please investigate and update package requirement to match the version from the private repository";

                if ($this->nonFixedPackages && array_key_exists($packageName, $this->nonFixedPackages)) {
                    throw new Exception($exceptionMessage);
                } else {
                    $event->getIO()->writeError('<warning>' . $exceptionMessage . '</warning>');
                }
            }
        }
    }
}
