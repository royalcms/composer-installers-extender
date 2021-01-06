<?php

declare(strict_types = 1);

namespace Royalcms\Composer\ComposerInstallersExtender\Installers;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Installers\Installer as InstallerBase;

class Installer extends InstallerBase
{
    /**
     * Package types to installer class map
     *
     * @var array
     */
    private $supportedTypes = [
        'royalcms' => 'RoyalcmsInstaller'
    ];

    /**
     * A list of installer types.
     *
     * @var array
     */
    protected $installerTypes;

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package): string
    {
        $type = $package->getType();
        $frameworkType = $this->findFrameworkType($type);

        if (in_array($frameworkType, array_keys($this->supportedTypes))) {
            $class = __NAMESPACE__ . '\\' . $this->supportedTypes[$frameworkType];
            $installer = new $class($package, $this->composer, $this->io);
            return $installer->getInstallPath($package, $frameworkType);
        }

        $installer = new CustomInstaller($package, $this->composer, $this->io);
        $path = $installer->getInstallPath($package, $package->getType());

        return $path ?: LibraryInstaller::getInstallPath($package);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType): bool
    {
        $frameworkType = $this->findFrameworkType($packageType);

        if (in_array($frameworkType, array_keys($this->supportedTypes))) {
            return parent::supports($packageType);
        }

        return in_array($packageType, $this->getInstallerTypes());
    }

    /**
     * Get a list of custom installer types.
     *
     * @return array
     */
    public function getInstallerTypes(): array
    {
        if (!$this->installerTypes) {
            $extra = $this->composer->getPackage()->getExtra();
            $this->installerTypes = $extra['installer-types'] ?? [];
        }

        return $this->installerTypes;
    }
}
