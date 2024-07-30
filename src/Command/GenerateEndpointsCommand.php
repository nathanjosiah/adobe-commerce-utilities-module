<?php
namespace Nathanjosiah\Utilities\Command;

use Magento\Framework\Acl\AclResource\ProviderInterface as AclResourceProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Webapi\Model\Config as RestConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEndpointsCommand extends Command
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var RestConfig
     */
    private $restConfig;

    /**
     * @var AclResourceProvider
     */
    private $aclResourceProvider;

    /**
     * GenerateRestEndpointsList constructor.
     *
     * @param State $appState
     * @param RestConfig $restConfig
     * @param AclResourceProvider $aclResourceProvider
     */
    public function __construct(
        State $appState,
        RestConfig $restConfig,
        AclResourceProvider $aclResourceProvider
    ) {
        $this->appState = $appState;
        $this->restConfig = $restConfig;
        $this->aclResourceProvider = $aclResourceProvider;
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('nathanjosiah:generate-admin-rest-endpoints')
            ->setDescription('Generate a list of all admin REST endpoints');
        $this->addOption(
            'inverse',
            'i',
            InputOption::VALUE_NONE,
            'Only show non-admin endpoints'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(Area::AREA_ADMINHTML);

        $routes = $this->restConfig->getServices()['routes'];
        $adminPermissions = $this->getAllAdminPermissions();
        $endpoints = [];
        $inverse = $input->getOption('inverse');

        foreach ($routes as $route => $methods) {
            foreach ($methods as $method => $routeConfig) {
                if (isset($routeConfig['resources']) && is_array($routeConfig['resources'])) {
                    foreach ($routeConfig['resources'] as $resource => $na) {
                        $found = in_array($resource, $adminPermissions, true);
                        if ($found && !$inverse || !$found && $inverse) {
                            $endpoints[] = strtoupper($method) . ' ' . $route;
                            break;
                        }
                    }
                }
            }
        }

        if (!empty($endpoints)) {
            $output->writeln("List of all admin REST endpoints:");
            foreach ($endpoints as $endpoint) {
                $output->writeln($endpoint);
            }
        } else {
            $output->writeln("No admin REST endpoints found.");
        }

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
