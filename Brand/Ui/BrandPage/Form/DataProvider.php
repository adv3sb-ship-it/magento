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
 * @package   mirasvit/module-navigation
 * @version   2.9.26
 * @copyright Copyright (C) 2025 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Ui\BrandPage\Form;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\ImageUploader;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;
use Mirasvit\Brand\Model\Config\GeneralConfig;
use Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory;
use Mirasvit\Brand\Service\ImageUrlService;
use Mirasvit\Brand\Ui\BrandPage\Form\Modifier\NewBrandModifier;
use Magento\Store\Model\Store;
use Mirasvit\Brand\Model\ResourceModel\BrandPage as BrandPageResource;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class DataProvider extends AbstractDataProvider
{
    private $imageUploader;

    private $mime;

    /**
     * @var ReadInterface
     */
    private $mediaDirectory;

    private $imageUrlService;

    private $status;

    private $imageHelper;

    private $modifier;

    private $context;

    private $productCollectionFactory;

    private $generalConfig;

    private BrandPageResource $brandPageResource;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ImageUploader $imageUploader,
        Filesystem $filesystem,
        Mime $mime,
        CollectionFactory $collectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        Status $status,
        ContextInterface $context,
        ImageHelper $imageHelper,
        ImageUrlService $imageUrlService,
        GeneralConfig $generalConfig,
        NewBrandModifier $modifier,
        BrandPageResource $brandPageResource,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection               = $collectionFactory->create();
        $this->productCollectionFactory = $productCollectionFactory;
        $this->status                   = $status;
        $this->imageHelper              = $imageHelper;
        $this->imageUrlService          = $imageUrlService;
        $this->imageUploader            = $imageUploader;
        $this->mediaDirectory           = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->mime                     = $mime;
        $this->context                  = $context;
        $this->generalConfig            = $generalConfig;
        $this->modifier                 = $modifier;
        $this->brandPageResource        = $brandPageResource;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        $meta = $this->modifier->modifyMeta($meta);

        return $meta;
    }

    protected function prepareComponent(UiComponentInterface $component): array
    {
        $data = [];
        foreach ($component->getChildComponents() as $name => $child) {
            $data['children'][$name] = $this->prepareComponent($child);
        }

        $data['arguments']['data']  = $component->getData();
        $data['arguments']['block'] = $component->getBlock();

        return $data;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getData()
    {
        $result  = [];
        $storeId = (int)$this->context->getRequestParam('store');
        /** @var BrandPageInterface $item */
        foreach ($this->collection->getItems() as $item) {
            $this->brandPageResource->loadByStore($item, $item->getId(), $storeId);

            $data = $item->getData();
            $data = $this->prepareImageData($data, 'logo');
            $data = $this->prepareImageData($data, 'banner');

            if (isset($data[BrandPageInterface::STORE_IDS])) {
                $data[BrandPageStoreInterface::STORE_ID] = $data[BrandPageInterface::STORE_IDS];
            } else {
                $data[BrandPageStoreInterface::STORE_ID] = '0';
            }

            if ($this->generalConfig->isShowProductsInForm()) {
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addAttributeToSelect('*')
                    ->addAttributeToFilter($this->generalConfig->getBrandAttribute(), $item->getAttributeOptionId())
                    ->setOrder('entity_id', 'ASC');

                $data['links']['products'] = [];
                $data['configured'] = true;

                foreach ($productCollection as $product) {
                    $data['links']['products'][] = [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'status' => $this->status->getOptionText($product->getStatus()),
                        'thumbnail' => $this->imageHelper->init($product, 'product_listing_thumbnail')->getUrl(),
                    ];
                }
            }

            $data[BrandPageInterface::DISABLE_DEFAULT] = $storeId !== Store::DEFAULT_STORE_ID;
            $data[BrandPageInterface::DISABLE_DEFAULT_INVERSE] = $storeId === Store::DEFAULT_STORE_ID;
            $data[BrandPageInterface::STORE_ID]        = $storeId;

            unset($data[BrandPageInterface::DEFAULT]);
            $useDefault = $item->getUseDefault();

            if (Store::DEFAULT_STORE_ID !== $storeId) {
                $data[BrandPageInterface::DEFAULT] = [];

                foreach ($item->getStoreFields() as $field) {
                    $data[BrandPageInterface::DEFAULT][$field] = isset($useDefault[$field]) && (int)$useDefault[$field];
                }
            }

            $result[$item->getId()] = $data;
        }
        return $result;
    }

    /**
     * @param array  $data
     * @param string $imageKey
     *
     * @return array
     */
    private function prepareImageData($data, $imageKey)
    {
        if (isset($data[$imageKey])) {
            $imageName = $data[$imageKey];
            unset($data[$imageKey]);
            
            if ($this->mediaDirectory->isExist($this->getFilePath($imageName))) {
                $data[$imageKey] = [
                    [
                        'name' => $imageName,
                        'url'  => $this->imageUrlService->getImageUrl($imageName),
                        'size' => $this->mediaDirectory->stat($this->getFilePath($imageName))['size'],
                        'type' => $this->getMimeType($imageName),
                    ],
                ];
            }
        }

        return $data;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getMimeType($fileName)
    {
        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($this->getFilePath($fileName));

        return $this->mime->getMimeType($absoluteFilePath);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getFilePath($fileName)
    {
        return $this->imageUploader->getFilePath($this->imageUploader->getBasePath(), $fileName);
    }
}
