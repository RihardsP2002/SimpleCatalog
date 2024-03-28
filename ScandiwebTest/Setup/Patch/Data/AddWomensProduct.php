<?php
declare(strict_types=1);

namespace Scandiweb\ScandiwebTest\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\State;

class AddWomensProduct implements DataPatchInterface
{
    private $moduleDataSetup;
    private $productRepository;
    private $productFactory;
    private $categoryLinkManagement;
    private $categoryRepository;
    private $state;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ProductRepositoryInterface $productRepository,
        ProductInterfaceFactory $productFactory,
        CategoryLinkManagementInterface $categoryLinkManagement,
        CategoryRepositoryInterface $categoryRepository,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->categoryRepository = $categoryRepository;
        $this->state = $state;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $this->moduleDataSetup->startSetup();

        $productData = [
            'sku' => 'WD001',
            'name' => 'Women\'s Dress',
            'price' => 30.00,
            'attribute_set_id' => 4,
            'status' => ProductInterface::STATUS_ENABLED,
            'visibility' => ProductInterface::VISIBILITY_BOTH,
            'type_id' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
        ];

        $product = $this->productFactory->create(['data' => $productData]);
        $product = $this->productRepository->save($product);

        $categoryId = 5;
        $this->categoryLinkManagement->assignProductToCategories($product->getSku(), [$categoryId]);

        $this->moduleDataSetup->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->startSetup();

        $this->moduleDataSetup->endSetup();
    }
}

