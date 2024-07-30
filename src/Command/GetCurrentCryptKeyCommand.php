<?php
namespace Nathanjosiah\Utilities\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Encryption\Encryptor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetCurrentCryptKeyCommand extends Command
{
    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;

    /**
     * @param Encryptor $encryptor
     */
    public function __construct(
        \Magento\Framework\Encryption\Encryptor $encryptor
    ) {
        parent::__construct();
        $this->encryptor = $encryptor;
    }


    protected function configure()
    {
        $this->setName('nathanjosiah:get-current-crypt-key')
            ->setDescription('Get the active crypt key as it is resolved by the Encryptor');
        $this->addOption(
            'key-version',
            null,
            InputOption::VALUE_REQUIRED,
            'The version of the key to show'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getOption('key-version');
        $keysReflection = new \ReflectionClass($this->encryptor);
        // List of keys
        $keysProperty = $keysReflection->getProperty('keys');
        $keysProperty->setAccessible(true);

        // Current Version
        $keyVersionProperty = $keysReflection->getProperty('keyVersion');
        $keyVersionProperty->setAccessible(true);

        $keys = $keysProperty->getValue($this->encryptor);
        $latestVersion = $keyVersionProperty->getValue($this->encryptor);

        if ($version && $version < 0 || $latestVersion < $version) {
            $output->writeln('Invalid key version. Must be between 0 and current maximum version of "' . $latestVersion . '".');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln($keys[$version ?? $latestVersion]);

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Get all admin permissions including descendants of Magento_Backend::
     *
     * @return array
     */
    private function getAllAdminPermissions()
    {
        $resources = $this->aclResourceProvider->getAclResources();
        $adminPermissions = [];

        $this->extractPermissions($resources, $adminPermissions);

        return $adminPermissions;
    }

    /**
     * Recursively extract permissions
     *
     * @param array $resources
     * @param array &$adminPermissions
     */
    private function extractPermissions(array $resources, array &$adminPermissions)
    {
        foreach ($resources as $resource) {
            $adminPermissions[] = $resource['id'];
            if (isset($resource['children']) && is_array($resource['children'])) {
                $this->extractPermissions($resource['children'], $adminPermissions);
            }
        }
    }
}
