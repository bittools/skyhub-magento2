<?php

namespace BitTools\SkyHub\Console\Queue\Catalog\Product\Attribute;

use BitTools\SkyHub\Console\AbstractConsole;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends AbstractConsole
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('skyhub:queue_create:product_attributes')
            ->setDescription('Create queue for product attributes.');

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
        $schedule = $this->context->objectManager()->create(\Magento\Cron\Model\Schedule::class);

        /** @var \BitTools\SkyHub\Cron\Queue\Catalog\Product\Attribute $cron */
        $cron = $this->context
            ->objectManager()
            ->create(\BitTools\SkyHub\Cron\Queue\Catalog\Product\Attribute::class);

        $cron->create($schedule);
    }
}
