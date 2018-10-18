<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/10/18
 */

namespace FastD\Signaller\Command;

use FastD\Signaller\Sentinel;
use FastD\Signaller\Signaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SignallerCommand
 * @package FastD\Sentinel
 */
class SignallerCommand extends Command
{
    const COMMAND_NAME = 'signaller';

    protected $path;

    public function configure()
    {
        $this->setName(static::COMMAND_NAME);

        $this->addOption('remove', '-r', InputOption::VALUE_OPTIONAL)
            ->addOption('path', '-p', InputOption::VALUE_OPTIONAL)
            ->addOption('list', '-l', InputOption::VALUE_OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Signaller sdk version: <info>' . Signaller::VERSION . '<info>');

        if ($input->hasParameterOption(['--path', '-p'])) {
            $this->path = $input->getOption('path');
        } else {
            $this->path = Sentinel::PATH;
        }

        if ($input->hasParameterOption(['--list', '-l'])) {
            $this->lists($output, $input->getOption('list'));
        }
        if ($input->hasParameterOption(['--remove', '-r'])) {
            $this->remove($output, $input->getOption('remove'));
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $info
     */
    public function lists(OutputInterface $output, $info = 'default')
    {
        if (is_null($info) || 'default' === $info) {
            $this->all($output);
        } else {
            $this->node($output, $info);
        }
    }


    /**
     * @param OutputInterface $output
     * @param $node
     */
    public function remove(OutputInterface $output, $node)
    {
        if (is_null($node)) {
            $output->writeln("<info>$node undefined</info>");
        } elseif (file_exists($file = "{$this->path}/$node.php")) {
            unlink($file);
            $output->writeln("<info>unlink succeed</info>");
        } else {
            $output->writeln("<info>$node no exists</info>");
        }
    }

    /**
     * @param OutputInterface $output
     */
    public function all(OutputInterface $output)
    {
        $services = glob($this->path . '/*.php');

        $output->writeln("all services node:");
        if (empty($services)) {
            $output->writeln("<info>null</info>");
        }
        $table = new Table($output);
        $table->setHeaders(array('server_name', 'protocol', 'ip', 'host', 'port'));

        foreach ($services as $service) {
            if (file_exists($service)) {
                $nodes = include $service;
                $name = basename($service, '.php');

                foreach ($nodes as $node) {
                    $table->addRow(
                        [
                            $name,
                            $node['service_protocol'],
                            $node['ip'],
                            $node['service_host'],
                            $node['service_port'],
                        ]
                    );
                }
            }

        }

        $table->render();
    }

    /**
     * @param OutputInterface $output
     * @param $node
     */
    public function node(OutputInterface $output, $node)
    {
        $nodes = explode(':', $node);
        $node = $nodes[0];
        $config = $nodes[1] ?? null;

        if (file_exists($file = "{$this->path}/$node.php")) {
            $output->writeln("<info>$node:</info>");
            $nodes = include $file;
            $table = new Table($output);
            if (is_null($config)) {
                $table->setHeaders(array('protocol', 'ip', 'host', 'port'));
                foreach ($nodes as $node) {
                    $table->addRow([
                        $node['service_protocol'],
                        $node['ip'],
                        $node['service_host'],
                        $node['service_port']
                    ]);
                }
            } else {
                $table = $this->getNodeInfo($table, $nodes, $config);
            }
            $table->render();
        } else {
            $output->writeln("<info>$node undefined.</info>");
        }
    }

    /**
     * @param Table $table
     * @param $nodes
     * @param $config
     * @return Table
     */
    public function getNodeInfo(Table $table, $nodes, $config)
    {
        switch ($config) {
            case 'route':
                $table->setHeaders(['name', 'method', 'path']);
                foreach ($nodes[0]['routes'] as $name => $route) {
                    $table->addRow([
                        $name,
                        $route[0],
                        $route[1],
                    ]);
                }
                break;
            case 'status':
                $table->setHeaders(['ip', 'start_time', 'connection_num', 'accept_count', 'close_count', 'tasking_num', 'request_count', 'worker_request_count']);
                foreach ($nodes as $node) {
                    $table->addRow([
                        $node['ip'],
                        date('Y-m-d H:i:s', $node['status']['start_time']),
                        $node['status']['connection_num'],
                        $node['status']['accept_count'],
                        $node['status']['close_count'],
                        $node['status']['tasking_num'],
                        $node['status']['request_count'],
                        $node['status']['worker_request_count'],
                    ]);
                }
                break;
        }

        return $table;
    }
}
