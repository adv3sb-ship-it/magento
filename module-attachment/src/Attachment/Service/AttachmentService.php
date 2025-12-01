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

namespace Mirasvit\Attachment\Service;

use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Repository\HistoryRepository;
use Mirasvit\Attachment\Repository\LinkRepository;

class AttachmentService
{
    private $linkRepository;

    private $historyRepository;

    public function __construct(LinkRepository $linkRepository, HistoryRepository $historyRepository)
    {
        $this->linkRepository    = $linkRepository;
        $this->historyRepository = $historyRepository;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function saveForEntity(string $entityType, int $entityId, array $attachmentsData): void
    {
        $linkAttachmentsMap = [];
        $linkCollection     = $this->linkRepository->getCollection()
            ->addFieldToFilter(LinkInterface::ENTITY_TYPE, $entityType)
            ->addFieldToFilter(LinkInterface::ENTITY_PK_VALUE, $entityId);
        foreach ($linkCollection as $link) {
            $linkAttachmentsMap[$link->getId()] = $link->getAttachmentId();
            
            $linkAttachmentKey = array_search($link->getAttachmentId(), array_column($attachmentsData, LinkInterface::ATTACHMENT_ID));
            if (!isset($attachmentsData[$linkAttachmentKey])) {
                continue;
            }
            $linkPosition = $attachmentsData[$linkAttachmentKey][LinkInterface::POSITION];
            
            if ($linkPosition != $link->getPosition()) {
                $link->setPosition(intval($linkPosition));
                $this->linkRepository->save($link);
            }
        }

        foreach ($attachmentsData as $attachmentData) {
            $attachmentId = (int)$attachmentData['attachment_id'];
            if (!in_array($attachmentId, array_values($linkAttachmentsMap))) {
                $linkPosition = $attachmentData[LinkInterface::POSITION] ?? 0;
                $link = $this->linkRepository->create()
                    ->setEntityType($entityType)
                    ->setEntityPkValue($entityId)
                    ->setPosition(intval($linkPosition))
                    ->setAttachmentId($attachmentId);
                $this->linkRepository->save($link);
            }
        }

        $attachmentIds = [];
        foreach ($attachmentsData as $attachmentData) {
            $attachmentIds[] = (int)$attachmentData['attachment_id'];
        }

        foreach ($linkAttachmentsMap as $linkId => $linkedAttachmentId) {
            if (!in_array($linkedAttachmentId, $attachmentIds)) {
                $link = $this->linkRepository->get((int)$linkId);
                if ($link) {
                    $this->linkRepository->delete($link);
                }
            }
        }
    }

    public function getIdentities(AttachmentInterface $attachment): array
    {
        $cacheTags      = $attachment->getIdentities();
        $linkCollection = $this->linkRepository->getCollection()
            ->addFieldToFilter(LinkInterface::ATTACHMENT_ID, $attachment->getId());
        /** @var LinkInterface $link */
        foreach ($linkCollection as $link) {
            $cacheTags = array_merge($cacheTags, $link->getIdentities());
        }

        return $cacheTags;
    }

    public function saveHistory(AttachmentInterface $attachment): void
    {
        $attachmentId = $attachment->getId();
        if (!$attachmentId) {
            return;
        }

        $clickEvent = $this->historyRepository->create();
        $clickEvent->setAttachmentId($attachmentId);
        $clickEvent->setSource('');
        $clickEvent->setAction('');
        $clickEvent->setValue(1);
        $this->historyRepository->save($clickEvent);
    }
}
