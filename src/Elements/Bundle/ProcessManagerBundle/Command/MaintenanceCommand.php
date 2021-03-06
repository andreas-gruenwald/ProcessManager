<?php

/**
 * Elements.at
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (https://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Elements\Bundle\ProcessManagerBundle\Command;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Maintenance;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Templating\EngineInterface;

class MaintenanceCommand extends AbstractCommand
{
    use \Elements\Bundle\ProcessManagerBundle\ExecutionTrait;

    /**
     * @var EngineInterface
     */
    private $templatingEngine;

    protected $loggerInitialized = null;

    public function __construct(EngineInterface $templatingEngine)
    {
        parent::__construct();

        $this->templatingEngine = $templatingEngine;
    }

    protected function configure()
    {
        $this
            ->setName('process-manager:maintenance')
            ->setDescription('Executes regular maintenance tasks (Check Processes, execute cronjobs)')
            ->addOption(
                'monitoring-item-id', null,
                InputOption::VALUE_REQUIRED,
                'Contains the monitoring item if executed via the Pimcore backend'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = ElementsProcessManagerBundle::$maintenanceOptions;
        $monitoringItem = $this->initProcessManager($input->getOption('monitoring-item-id'), $options);
        $this->doUniqueExecutionCheck(null, ['command' => $this->getCommand($options)]);

        \Pimcore\Tool\Console::checkExecutingUser((array)ElementsProcessManagerBundle::getConfig()['general']['additionalScriptExecutionUsers']);

        $maintenance = new Maintenance($this->templatingEngine);
        $maintenance->execute();
    }
}
