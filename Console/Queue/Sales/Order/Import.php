<?php

namespace BitTools\SkyHub\Console\Queue\Sales\Order;

use BitTools\SkyHub\Console\AbstractConsole;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends AbstractConsole
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('skyhub:queue_execute:order')
            ->setDescription('Import next orders from the order\'s queue.');

        parent::configure();
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Magento\Cron\Model\Schedule $schedule */
        $schedule = $this->createSchedule();

        /** @var \BitTools\SkyHub\Cron\Queue\Sales\Order\Queue $cron */
        $cron = $this->context
            ->objectManager()
            ->create(\BitTools\SkyHub\Cron\Queue\Sales\Order\Queue::class);

        $cron->execute($schedule);
    }


    /**
     * @return mixed
     */
    protected function createSchedule()
    {
        return $this->context->objectManager()->create(\Magento\Cron\Model\Schedule::class);
    }
}
