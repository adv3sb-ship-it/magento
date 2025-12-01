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

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

abstract class AbstractModifier implements ModifierInterface
{
    const DATA_SCOPE_ENTITY = '';

    protected $scopePrefix            = 'attachment_';

    protected $scopeName              = 'attachment_attachment_form.attachment_attachment_form';

    protected $dataScope              = '';

    protected $previousGroup          = 'search-engine-optimization';

    protected $group                  = '';

    protected $groupLabel             = '';

    protected $groupSortOrder         = 10;

    protected $fieldSetContent        = '';

    protected $fieldSetAddButtonTitle = '';

    protected $fieldSetSortOrder      = 10;

    protected $modalTitle             = '';

    protected $modalAddButtonTitle    = '';

    protected $urlBuilder;


    abstract protected function getGridMap(): array;

    abstract protected function fillMeta(): array;


    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    public function modifyData(array $data): array
    {
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        $meta = array_replace_recursive(
            $meta,
            [
                $this->group => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label'         => __($this->groupLabel),
                                'collapsible'   => true,
                                'componentType' => Fieldset::NAME,
                                'dataScope'     => $this->dataScope,
                                'sortOrder'     => $this->getNextGroupSortOrder(
                                    $meta,
                                    $this->previousGroup,
                                    $this->groupSortOrder
                                ),
                            ],
                        ],
                    ],
                    'children'  => [
                        $this->scopePrefix . static::DATA_SCOPE_ENTITY => $this->getFieldset(),
                    ],
                ],
            ]
        );

        return $meta;
    }

    protected function getFieldset(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label'             => __(''),
                        'collapsible'       => false,
                        'componentType'     => Fieldset::NAME,
                        'dataScope'         => '',
                        'sortOrder'         => 10,
                    ],
                ],
            ],
            'children'  => [
                'button_set'              => $this->getButtonSet(),
                'modal'                   => $this->getGenericModal(),
                static::DATA_SCOPE_ENTITY => $this->getGrid(),
            ],
        ];
    }

    protected function getGenericModal(): array
    {
        $listingTarget = $this->scopePrefix . static::DATA_SCOPE_ENTITY . '_listing';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope'     => '',
                        'options'       => [
                            'title'   => __($this->modalTitle),
                            'buttons' => [
                                [
                                    'text'    => __('Cancel'),
                                    'actions' => [
                                        'closeModal',
                                    ],
                                ],
                                [
                                    'text'    => __($this->modalAddButtonTitle),
                                    'class'   => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => 'index = ' . $listingTarget,
                                            'actionName' => 'save',
                                        ],
                                        'closeModal',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children'  => [
                $listingTarget => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender'         => false,
                                'componentType'      => 'insertListing',
                                'dataScope'          => $listingTarget,
                                'externalProvider'   => $listingTarget . '.' . $listingTarget . '_data_source',
                                'selectionsProvider' => $listingTarget . '.' . $listingTarget . '.columns.ids',
                                'ns'                 => $listingTarget,
                                'render_url'         => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink'       => true,
                                'dataLinks'          => [
                                    'imports' => false,
                                    'exports' => true,
                                ],
                                'behaviourType'      => 'simple',
                                'externalFilterMode' => true,
                                'imports'            => [
                                    'id'            => '${ $.provider }:data.current_id',
                                    'storeId'       => '${ $.provider }:data.current_store_id',
                                    '__disableTmpl' => ['id' => false, 'storeId' => false],
                                ],
                                'exports'            => [
                                    'id'            => '${ $.externalProvider }:params.current_id',
                                    'storeId'       => '${ $.externalProvider }:params.current_store_id',
                                    '__disableTmpl' => ['id' => false, 'storeId' => false],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getButtonSet(): array
    {
        $scope       = $this->scopePrefix . static::DATA_SCOPE_ENTITY;
        $modalTarget = $this->scopeName . '.' . $this->group . '.' . $scope . '.modal';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement'   => 'container',
                        'componentType' => 'container',
                        'label'         => false,
                        'content'       => __($this->fieldSetContent),
                        'template'      => 'ui/form/components/complex',
                    ],
                ],
            ],
            'children'  => [
                'button_' . $scope => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement'   => 'container',
                                'componentType' => 'container',
                                'component'     => 'Magento_Ui/js/form/components/button',
                                'actions'       => [
                                    [
                                        'targetName' => $modalTarget,
                                        'actionName' => 'toggleModal',
                                    ],
                                    [
                                        'targetName' => $modalTarget . '.' . $scope . '_listing',
                                        'actionName' => 'render',
                                    ],
                                ],
                                'title'         => __($this->fieldSetAddButtonTitle),
                                'provider'      => null,
                            ],
                        ],
                    ],

                ],
            ],
        ];
    }

    protected function getGrid(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses'        => 'admin__field-wide',
                        'componentType'            => DynamicRows::NAME,
                        'label'                    => null,
                        'columnsHeader'            => false,
                        'columnsHeaderAfterRender' => true,
                        'renderDefaultRecord'      => false,
                        'template'                 => 'ui/dynamic-rows/templates/grid',
                        'component'                => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                        'addButton'                => false,
                        'recordTemplate'           => 'record',
                        'dataScope'                => 'links',
                        'deleteButtonLabel'        => __('Remove'),
                        'dataProvider'             => 'data.' . $this->scopePrefix . static::DATA_SCOPE_ENTITY . '_listing',
                        'map'                      => $this->getGridMap(),
                        'dndConfig'                => [
                            'enabled' => false
                        ],
                        'links'                    => [
                            'insertData'    => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' => ['insertData' => false],
                        ],
                        'sortOrder'                => $this->fieldSetSortOrder,
                    ],
                ],
            ],
            'children'  => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'isTemplate'    => true,
                                'is_collection' => true,
                                'component'     => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope'     => '',
                            ],
                        ],
                    ],
                    'children'  => $this->fillMeta(),
                ],
            ],
        ];
    }

    protected function getTextColumn(string $dataScope, string $label, int $sortOrder, bool $fit): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'elementTmpl'   => 'ui/dynamic-rows/cells/text',
                        'component'     => 'Magento_Ui/js/form/element/text',
                        'dataType'      => Text::NAME,
                        'dataScope'     => $dataScope,
                        'fit'           => $fit,
                        'label'         => __($label),
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    protected function getThumbnailColumn(string $dataScope, string $label, int $sortOrder, bool $fit): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'elementTmpl'   => 'ui/dynamic-rows/cells/thumbnail',
                        'dataType'      => Text::NAME,
                        'dataScope'     => $dataScope,
                        'fit'           => $fit,
                        'label'         => __($label),
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    protected function getMetaActionDelete(string $label, int $sortOrder, bool $fit): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'data-grid-actions-cell',
                        'componentType'     => 'actionDelete',
                        'dataType'          => Text::NAME,
                        'label'             => __($label),
                        'sortOrder'         => $sortOrder,
                        'fit'               => $fit,
                    ],
                ],
            ],
        ];
    }

    protected function getNextGroupSortOrder(array $meta, string $groupCode, int $defaultSortOrder, int $iteration = 1): int
    {
        $groupCodes = (array)$groupCode;

        foreach ($groupCodes as $groupCode) {
            if (isset($meta[$groupCode]['arguments']['data']['config']['sortOrder'])) {
                return $meta[$groupCode]['arguments']['data']['config']['sortOrder'] + $iteration;
            }
        }

        return $defaultSortOrder;
    }
}
