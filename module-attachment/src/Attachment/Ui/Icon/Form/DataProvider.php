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

namespace Mirasvit\Attachment\Ui\Icon\Form;

use Magento\Framework\DataObject;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Attachment\Api\Data\IconInterface;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Repository\IconRepository;

class DataProvider extends AbstractDataProvider
{
    private $iconRepository;

    private $config;

    public function __construct(
        ConfigProvider $config,
        IconRepository $iconRepository,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->config         = $config;
        $this->iconRepository = $iconRepository;
        $this->collection     = $this->iconRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $result = [];

        /** @var IconInterface|DataObject $item */
        foreach ($this->getCollection() as $item) {
            $types = [];
            foreach ($item->getTypes() as $type) {
                $types[] = ['type' => $type];
            }

            $result[$item->getId()] = [
                IconInterface::ID               => $item->getId(),
                IconInterface::IS_ACTIVE        => $item->isActive() ? "1" : "0",
                IconInterface::LABEL            => $item->getLabel(),
                IconInterface::TYPES            => $types,
                IconInterface::ICON_PATH        => $item->getIconPath(),
                ConfigProvider::ICON_FIELD_NAME => $this->getIconData($item),
            ];
        }

        return $result;
    }

    private function getIconData(IconInterface $icon): array
    {
        $absPath = $this->config->getAbsPath($icon->getIconPath());

        if (!is_readable($absPath)) {
            return [];
        }

        $fileName = pathinfo($absPath, PATHINFO_BASENAME);

        $mimeType = null;
        if (in_array(strtolower(pathinfo($absPath, PATHINFO_EXTENSION)), ['gif', 'png', 'jpg', 'jpeg'])) {
            $mimeType = 'image/*';
        }

        $iconData = [
            'name' => $fileName,
            'url'  => $this->config->getAbsUrl($icon->getIconPath()),
            'size' => filesize($absPath),
            'type' => $mimeType,
            'file' => $fileName,
        ];

        return [$iconData];
    }
}
