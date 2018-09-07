<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Learning\FirstUnit\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductReviewListCommand extends Command
{
    /**
     * Module list
     *
     * @var ModuleListInterface
     */
    private $moduleList;

    protected $_appState;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(\Magento\Framework\App\State $appState)
    {
        $this->_appState = $appState;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('product:review:list')
            ->setDescription('Review list for a product')
            ->setDefinition([
                new InputArgument(
                    'product_id',
                    InputArgument::OPTIONAL,
                    'Product Id'
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_appState->setAreaCode('adminhtml');

        $product_id = $input->getArgument('product_id');

        if (empty($product_id)) {
            $output->writeln('<info>Product Id is empty!<info>');
            exit();
        }

        if (!is_numeric($product_id)) {
            $output->writeln('<info>Product Id must be a number!<info>');
            exit();
        }

	    $data = $this->getReviews($product_id);
        if ( is_array($data) ) {
            foreach ($data as $key => $value) {
                $output->writeln('<info>Status Id - ' . $value['status_id'] . '<info>');
                $output->writeln('<info>Date - ' . $value['created_at'] . '<info>');
                $output->writeln('<info>Detail - ' . $value['detail'] . '<info>');
                $output->writeln('<info>Nickname - ' . $value['nickname'] . '<info>');
                //$output->writeln('<info>' . $value['entity_pk_value'] . '<info>');
            }
        } else {
            $output->writeln('<info>' . $data . '<info>');
        }
	   
    }

    
    protected function getReviews($id) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create("Magento\Catalog\Model\Product")->load($id);

        if (empty($product)) {
            return 'No product found!';
        }

        $rating = $objectManager->get("Magento\Review\Model\ResourceModel\Review\CollectionFactory");
        $collection = $rating->create()->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)->addEntityFilter('product', $id)->setDateOrder();
        
        return $collection->getData();

    }
}
