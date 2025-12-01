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

namespace Mirasvit\Attachment\Ui\Icon\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Attachment\Model\ConfigProvider;

class Thumbnail extends Column
{
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->configProvider = $configProvider;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['icon_path']) {
                    $item[$fieldName . '_src']      = $this->configProvider->getAbsUrl($item['icon_path']);
                    $item[$fieldName . '_alt']      = $item['label'];
                    $item[$fieldName . '_orig_src'] = $this->configProvider->getAbsUrl($item['icon_path']);
                }
            }
        }

        return $dataSource;
    }
}
