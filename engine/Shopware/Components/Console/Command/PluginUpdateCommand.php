<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Console\Command;

use Shopware\Components\Plugin\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class PluginUpdateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:plugin:update')
            ->setDescription('Updates a plugin.')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'The plugin to be updated.'
            )
            ->setHelp(<<<EOF
The <info>%command.name%</info> updates a plugin.
EOF
            );
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Disable error reporting for shopware menu legacy hack
        set_error_handler(function($errno, $errstr) {
            if ($errno === E_RECOVERABLE_ERROR
                    && stripos($errstr, 'Argument 1 passed to Shopware\Models\Menu\Repository::findOneBy() must be of the type array') === 0) {

                return true;
            }

            return false;
        });

        /** @var Installer $installer */
        $installer  = $this->container->get('shopware.plugin_installer');
        $pluginName = $input->getArgument('plugin');

        try {
            $plugin = $installer->getPluginByName($pluginName);
        } catch (\Exeption $e) {
            $output->writeln(sprintf('Unknown plugin: %s.', $pluginName));
            return 1;
        }

        if (!$plugin->getUpdateVersion()) {
            $output->writeln(sprintf('The plugin %s is up to date.', $pluginName));
            return 1;
        }

        $installer->updatePlugin($plugin);

        $output->writeln(sprintf('Plugin %s has been updated successfully.', $pluginName));
    }
}
