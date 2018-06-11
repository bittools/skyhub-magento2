<?php

namespace BitTools\SkyHub\Console\Queue\Sales\Order\Status;

use BitTools\SkyHub\Console\AbstractConsole;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Execute extends AbstractConsole
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('skyhub:queue_execute:order_status')
            ->setDescription('Execute order status queue.');

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

        /** @var \BitTools\SkyHub\Cron\Queue\Sales\Order\Status $cron */
        $cron = $this->context
            ->objectManager()
            ->create(\BitTools\SkyHub\Cron\Queue\Sales\Order\Status::class);

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
