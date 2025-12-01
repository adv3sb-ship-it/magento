<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-attachment
 * @version   1.1.12
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Attachment\Ui\Attachment\Form\Modifier;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Ui\Component\Listing\Columns\Price;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Repository\LinkRepository;

class ProductModifier extends AbstractModifier
{
    const DATA_SCOPE_ENTITY = 'attachment_product';

    protected $group                  = 'link_product';

    protected $groupLabel             = 'Products';

    protected $groupSortOrder         = 100;

    protected $fieldSetContent        = '';

    protected $fieldSetAddButtonTitle = 'Add Products';

    protected $modalTitle             = 'Add Products';

    protected $modalAddButtonTitle    = 'Add Selected Products';

    private   $status;

    private   $attributeSetRepository;

    private   $priceModifier;

    private   $linkRepository;

    private   $request;

    private   $productHelper;

    private   $productCollectionFactory;

    public function __construct(
        Price $priceModifier,
        AttributeSetRepositoryInterface $attributeSetRepository,
        Status $status,
        ProductCollectionFactory $productCollectionFactory,
        ProductHelper $productHelper,
        RequestInterface $request,
        LinkRepository $linkRepository,
        UrlInterface $urlBuilder
    ) {
        $this->priceModifier            = $priceModifier;
        $this->attributeSetRepository   = $attributeSetRepository;
        $this->status                   = $status;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productHelper            = $productHelper;
        $this->request                  = $request;
        $this->linkRepository           = $linkRepository;
        parent::__construct($urlBuilder);
    }

    public function modifyData(array $data): array
    {
        $attachmentId   = (int)$this->request->getParam('attachment_id');
        $linkCollection = $this->linkRepository->getCollection()
            ->addFieldToFilter(LinkInterface::ATTACHMENT_ID, $attachmentId)
            ->addFieldToFilter(LinkInterface::ENTITY_TYPE, LinkInterface::ENTITY_TYPE_PRODUCT);

        $entityPkValues = [];
        foreach ($linkCollection as $link) {
            $entityPkValues[] = $link->getEntityPkValue();
        }

        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $entityPkValues]);

        $this->priceModifier->setData('name', 'price');
        $data[$attachmentId]['links'][static::DATA_SCOPE_ENTITY] = [];
        /** @var Product $product */
        foreach ($productCollection as $product) {
            $data[$attachmentId]['links'][static::DATA_SCOPE_ENTITY][] = [
                'id'        => $product->getId(),
                'name'      => $product->getName(),
                'thumbnail' => $this->productHelper->getThumbnailUrl($product),

                'status'        => $this->status->getOptionText($product->getStatus()),
                'attribute_set' => $this->attributeSetRepository
                    ->get($product->getAttributeSetId())
                    ->getAttributeSetName(),
                'sku'           => $product->getSku(),
                'price'         => $product->getPrice(),
            ];
        }

        if (!empty($data[$attachmentId]['links'][static::DATA_SCOPE_ENTITY])) {
            $dataMap = $this->priceModifier->prepareDataSource([
                'data' => [
                    'items' => $data[$attachmentId]['links'][static::DATA_SCOPE_ENTITY],
                ],
            ]);

            $data[$attachmentId]['links'][static::DATA_SCOPE_ENTITY] = $dataMap['data']['items'];
        }

        return $data;
    }

    protected function fillMeta(): array
    {
        return [
            'id'            => $this->getTextColumn('id', 'ID', 10, false),
            'thumbnail'     => $this->getThumbnailColumn('thumbnail', 'Thumbnail', 20, true),
            'name'          => $this->getTextColumn('name', 'Name', 30, false),
            'status'        => $this->getTextColumn('status', 'Status', 40, true),
            'attribute_set' => $this->getTextColumn('attribute_set', 'Attribute Set', 50, false),
            'sku'           => $this->getTextColumn('sku', 'SKU', 60, true),
            'price'         => $this->getTextColumn('price', 'Price', 70, true),
            'actionDelete'  => $this->getMetaActionDelete('Actions', 200, true),
        ];
    }

    protected function getGridMap(): array
    {
        return [
            'id'            => 'entity_id',
            'thumbnail'     => 'thumbnail_src',
            'name'          => 'name',
            'status'        => 'status_text',
            'attribute_set' => 'attribute_set_text',
            'sku'           => 'sku',
            'price'         => 'price',
        ];
    }
}
