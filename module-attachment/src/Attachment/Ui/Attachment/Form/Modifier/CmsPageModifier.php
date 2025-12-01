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

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Repository\LinkRepository;

class CmsPageModifier extends AbstractModifier
{
    const DATA_SCOPE_ENTITY = 'attachment_cms_page';

    protected $group                  = 'link_cms_page';

    protected $groupLabel             = 'Cms Pages';

    protected $groupSortOrder         = 120;

    protected $fieldSetContent        = '';

    protected $fieldSetAddButtonTitle = 'Add Pages';

    protected $modalTitle             = 'Add Pages';

    protected $modalAddButtonTitle    = 'Add Selected Pages';

    private   $linkRepository;

    private   $request;

    private   $cmsPageCollectionFactory;

    public function __construct(
        CmsPageCollectionFactory $cmsPageCollectionFactory,
        RequestInterface $request,
        LinkRepository $linkRepository,
        UrlInterface $urlBuilder
    ) {
        $this->cmsPageCollectionFactory = $cmsPageCollectionFactory;
        $this->request                  = $request;
        $this->linkRepository           = $linkRepository;
        parent::__construct($urlBuilder);
    }

    public function modifyData(array $data): array
    {
        $attachmentId   = (int)$this->request->getParam('attachment_id');
        $linkCollection = $this->linkRepository->getCollection()
            ->addFieldToFilter(LinkInterface::ATTACHMENT_ID, $attachmentId)
            ->addFieldToFilter(LinkInterface::ENTITY_TYPE, LinkInterface::ENTITY_TYPE_CMS_PAGE);

        $entityPkValues = [];
        foreach ($linkCollection as $link) {
            $entityPkValues[] = $link->getEntityPkValue();
        }

        $cmsPageCollection = $this->cmsPageCollectionFactory->create()->addFieldToFilter(PageInterface::PAGE_ID, ['in' => $entityPkValues]);

        $data[$attachmentId]['links'][static::DATA_SCOPE_ENTITY] = [];
        foreach ($cmsPageCollection as $cmsPage) {
            $data[$attachmentId]['links'][static::DATA_SCOPE_ENTITY][] = [
                'id'    => $cmsPage->getId(),
                'title' => $cmsPage->getTitle(),
            ];
        }

        return $data;
    }

    protected function fillMeta(): array
    {
        return [
            'id'           => $this->getTextColumn('id', 'ID', 10, false),
            'title'        => $this->getTextColumn('title', 'Title', 20, false),
            'actionDelete' => $this->getMetaActionDelete('Actions', 200, true),
        ];
    }

    protected function getGridMap(): array
    {
        return [
            'id'    => 'page_id',
            'title' => 'title',
        ];
    }
}
