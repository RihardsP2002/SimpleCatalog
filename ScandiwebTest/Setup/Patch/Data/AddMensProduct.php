<?php

declare(strict_types=1);

namespace Scandiweb\ScandiwebTest\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;


class AddMensProduct implements DataPatchInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var SourceItemInterfaceFactory
     */
    protected $sourceItemFactory;

    /**
     * @var SourceItemsSaveInterface
     */
    protected $sourceItemsSaveInterface;

    /**
     * AddMensProduct constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param ProductInterfaceFactory $productFactory
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param State $state
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductInterfaceFactory $productFactory,
        CategoryLinkManagementInterface $categoryLinkManagement,
        State $state,
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSaveInterface $sourceItemsSaveInterface
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->state = $state;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            function () {
                $productData = [
                    'sku' => 'MTS001',
                    'name' => 'Men\'s T-Shirt',
                    'price' => 20.00,
                    'attribute_set_id' => 4,
                    'status' => ProductInterface::STATUS_ENABLED,
                    'visibility' => ProductInterface::VISIBILITY_BOTH,
                    'type_id' => ProductType::TYPE_SIMPLE,
                ];

                $product = $this->productFactory->create(['data' => $productData]);
                $product = $this->productRepository->save($product);

                $categoryId = 4;
                $this->categoryLinkManagement->assignProductToCategories($product->getSku(), [$categoryId]);

                // Set inventory for the product
                $this->setProductInventory($product->getSku(), 100); // Setting quantity as 100
            }
        );
    }

    /**
     * Set inventory for the product
     *
     * @param string $sku
     * @param int $quantity
     * @throws LocalizedException
     */
    protected function setProductInventory(string $sku, int $quantity)
    {
        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSourceCode('default');
        $sourceItem->setQuantity($quantity);
        $sourceItem->setSku($sku);
        $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);

        $this->sourceItemsSaveInterface->execute([$sourceItem]);
    }
}
