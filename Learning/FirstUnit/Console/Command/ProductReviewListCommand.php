<?php
/**
 *
 */

namespace Learning\FirstUnit\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Magento\Framework\App\State;

class ProductReviewListCommand extends Command
{

    private $moduleList;
    private $productRepository;
    private $reviewFactory;
    protected $_appState;

    /**
     * @param State $appState
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewFactory
     */
    public function __construct(
        State $appState, 
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, 
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewFactory)
    {
        $this->_appState = $appState;
        $this->productRepository = $productRepository;
        $this->reviewFactory = $reviewFactory;
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
            }
        } else {
            $output->writeln('<info>' . $data . '<info>');
        }
       
    }

    
    protected function getReviews($id) 
    {
        $product = $this->productRepository->getById($id);

        if (empty($product)) {
            return 'No product found!';
        }

        $rating = $this->reviewFactory->create();
        $collection = $rating->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)->addEntityFilter('product', $id)->setDateOrder();
        
        return $collection->getData();

    }
}
