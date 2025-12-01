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

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Repository\LinkRepository;

class CategoryModifier extends AbstractModifier
{
    const DATA_SCOPE_ENTITY = 'attachment_category';

    protected $group                  = 'link_category';

    protected $groupLabel             = 'Categories';

    protected $groupSortOrder         = 110;

    protected $fieldSetContent        = '';

    protected $fieldSetAddButtonTitle = 'Add Categories';

    protected $modalTitle             = 'Add Categories';

    protected $modalAddButtonTitle    = 'Add Selected Categories';

    private   $linkRepository;

    private   $request;

    private   $categoryCollectionFactory;

    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        RequestInterface $request,
        LinkRepository $linkRepository,
        UrlInterface $urlBuilder
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->request                   = $request;
        $this->linkRepository            = $linkRepository;
        parent::__construct($urlBuilder);
    }

    public function modifyData(array $data): array
    {
        $attachmentId   = (int)$this->request->getParam('attachment_id');
        $linkCollection = $this->linkRepository->getCollection()
            ->addFieldToFilter(LinkInterface::ATTACHMENT_ID, $attachmentId)
            ->addFieldToFilter(LinkInterface::ENTITY_TYPE, LinkInterface::ENTITY_TYPE_CATEGORY);

        $entityPkValues = [];
        foreach ($linkCollection as $link) {
            $entityPkValues[] = $link->getEntityPkValue();
        }

        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $entityPkValues])
            ->addFieldToSelect('name');

        $data[$attachmentId]['links'][static::DATA_SCOPE_ENTITY] = [];
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $data[$attachmentId]['links'][static::DATA_SCOPE_ENTITY][] = [
                'id'   => $category->getId(),
                'name' => $category->getName(),
            ];
        }

        return $data;
    }

    protected function fillMeta(): array
    {
        return [
            'id'           => $this->getTextColumn('id', 'ID', 10, false),
            'name'         => $this->getTextColumn('name', 'Name', 20, false),
            'actionDelete' => $this->getMetaActionDelete('Actions', 200, true),
        ];
    }

    protected function getGridMap(): array
    {
        return [
            'id'   => 'entity_id',
            'name' => 'name',
        ];
    }
}
