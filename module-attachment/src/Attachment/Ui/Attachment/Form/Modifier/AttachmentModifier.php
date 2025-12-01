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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Model\Config\Source\DefaultSortDirection;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Model\ResourceModel\Attachment\CollectionFactory as AttachmentCollectionFactory;
use Mirasvit\Attachment\Repository\AttachmentRepository;
use Mirasvit\Attachment\Repository\LinkRepository;
use Mirasvit\Attachment\Service\IconService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttachmentModifier
{
    const DATA_SCOPE_ENTITY      = 'attachment';
    const SCOPE_PREFIX           = 'attachment_';
    const PREVIOUS_GROUP         = 'search-engine-optimization';
    const GROUP                  = 'link_attachment';
    const GROUP_LABEL            = 'Attachments';
    const ASSIGN_BUTTON_TITLE    = 'Assign Attacment';
    const ADD_NEW_BUTTON_TITLE   = 'Add New Attachment';
    const MODAL_TITLE            = 'Assign Attacment ';
    const MODAL_ADD_BUTTON_TITLE = 'Add Selected Attachments';
    const GRID_DATA_PROVIDER     = 'attachment_attachment_grid';

    protected $urlBuilder;

    private   $linkRepository;

    private   $iconService;

    private   $request;

    private   $attachmentCollectionFactory;

    private   $attachmentRepository;

    private   $config;

    public function __construct(
        ConfigProvider $config,
        AttachmentRepository $attachmentRepository,
        AttachmentCollectionFactory $attachmentCollectionFactory,
        RequestInterface $request,
        LinkRepository $linkRepository,
        IconService $iconService,
        UrlInterface $urlBuilder
    ) {
        $this->config                      = $config;
        $this->attachmentRepository        = $attachmentRepository;
        $this->attachmentCollectionFactory = $attachmentCollectionFactory;
        $this->request                     = $request;
        $this->linkRepository              = $linkRepository;
        $this->iconService                 = $iconService;
        $this->urlBuilder                  = $urlBuilder;
    }

    public function modifyData(array $data, string $entityType, int $entityId): array
    {
        $linkCollection = $this->linkRepository->getCollection()
            ->addFieldToFilter(LinkInterface::ENTITY_TYPE, $entityType)
            ->addFieldToFilter(LinkInterface::ENTITY_PK_VALUE, $entityId);
        
        $defaultSortDirection = 
            $this->config->getDefaultSortOrder() === DefaultSortDirection::OPTION_NEWEST
            ? 'DESC' 
            : 'ASC';

        $attachmentCollection = $this->attachmentRepository->getCollection()
            ->join(['link' => $linkCollection->getTable(LinkInterface::TABLE_NAME)],
                'main_table.' . AttachmentInterface::ID . ' = link.' . LinkInterface::ATTACHMENT_ID
            )->addFieldToFilter('link.' . LinkInterface::ENTITY_TYPE, $entityType)
            ->addFieldToFilter('link.' . LinkInterface::ENTITY_PK_VALUE, $entityId)
            ->addOrder('link.' . LinkInterface::POSITION, 'ASC')
            ->addOrder('main_table.' . AttachmentInterface::POSITION, 'ASC')
            ->addOrder('main_table.' . AttachmentInterface::ID, $defaultSortDirection);

        $data[$entityId]['links'][static::DATA_SCOPE_ENTITY] = [];

        $linkPositions = [];

        foreach ($linkCollection as $link) {
            $linkPositions[$link->getAttachmentId()] = $link->getPosition();
        }

        foreach ($attachmentCollection as $attachment) {
            $icon = $this->iconService->getIcon($attachment);

            $data[$entityId]['links'][static::DATA_SCOPE_ENTITY][] = [
                AttachmentInterface::ID          => $attachment->getId(),
                ConfigProvider::ICON_FIELD_NAME  => $icon ? $this->config->getAbsUrl($icon->getIconPath()) : '',
                AttachmentInterface::LABEL       => $attachment->getLabel(),
                AttachmentInterface::IS_ACTIVE   => $attachment->isActive() ? "1" : "0",
                AttachmentInterface::TYPE        => $attachment->getType(),
                LinkInterface::POSITION          => $linkPositions[$attachment->getId()],
                AttachmentInterface::SOURCE_NAME => $attachment->getSourceName(),
            ];
        }

        return $data;
    }

    public function modifyMeta(
        array $meta,
        string $scopeName,
        string $gridDataScope,
        string $gridDataProvider,
        string $buttonSetContent,
        int $groupSortOrder = null
    ): array {
        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label'             => (string)__(static::GROUP_LABEL),
                                'additionalClasses' => 'mst_attachment__linked-attachment',
                                'collapsible'       => true,
                                'componentType'     => Fieldset::NAME,
                                'dataScope'         => '',
                                'sortOrder'         => $groupSortOrder ? $groupSortOrder : $this->getNextGroupSortOrder($meta, static::PREVIOUS_GROUP, 2),
                            ],
                        ],
                    ],
                    'children'  => [
                        static::SCOPE_PREFIX . static::DATA_SCOPE_ENTITY => [
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
                                'button_set'              => $this->getButtonSet($scopeName, $buttonSetContent),
                                'modal'                   => $this->getGenericModal(),
                                'new_attachment_modal'    => $this->getNewAttachmentModal(),
                                static::DATA_SCOPE_ENTITY => $this->getGrid($gridDataScope, $gridDataProvider),
                            ],
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }

    protected function getGenericModal(): array
    {
        $listingTarget = static::GRID_DATA_PROVIDER;

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope'     => '',
                        'options'       => [
                            'title'   => __(static::MODAL_TITLE),
                            'buttons' => [
                                [
                                    'text'    => (string)__('Cancel'),
                                    'actions' => [
                                        'closeModal',
                                    ],
                                ],
                                [
                                    'text'    => (string)__(static::MODAL_ADD_BUTTON_TITLE),
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

    protected function getNewAttachmentModal(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'isTemplate'    => false,
                        'options'       => [
                            'title' => __('Create Attachment'),
                        ],
                        'imports'       => [
                            'state' => '!index=create_attachment:responseStatus',
                        ],
                    ],
                ],
            ],
            'children'  => [
                'create_attachment' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label'            => '',
                                'componentType'    => 'container',
                                'component'        => 'Magento_Ui/js/form/components/insert-form',
                                'dataScope'        => '',
                                'update_url'       => $this->urlBuilder->getUrl('mui/index/render'),
                                'render_url'       => $this->urlBuilder->getUrl(
                                    'mui/index/render_handle',
                                    [
                                        'handle'  => 'mst_attachment_attachment_create',
                                        'buttons' => 1,
                                    ]
                                ),
                                'autoRender'       => false,
                                'ns'               => 'attachment_attachment_form',
                                'externalProvider' => 'attachment_attachment_form.attachment_attachment_form_data_source',
                                'toolbarContainer' => '${ $.parentName }',
                                '__disableTmpl'    => ['toolbarContainer' => false],
                                'formSubmitType'   => 'ajax',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getButtonSet(string $scopeName, string $content): array
    {
        $scope       = static::SCOPE_PREFIX . static::DATA_SCOPE_ENTITY;
        $modalTarget = $scopeName . '.' . static::GROUP . '.' . $scope . '.modal';

        $newAttachmentModalTarget = $scopeName . '.' . static::GROUP . '.' . $scope . '.new_attachment_modal';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement'   => 'container',
                        'componentType' => 'container',
                        'label'         => false,
                        'content'       => (string)__($content),
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
                                        'targetName' => $modalTarget . '.' . static::GRID_DATA_PROVIDER,
                                        'actionName' => 'render',
                                    ],
                                ],
                                'title'         => (string)__(static::ASSIGN_BUTTON_TITLE),
                                'provider'      => null,
                            ],
                        ],
                    ],
                ],

                'button_new_attachment' . $scope => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement'   => 'container',
                                'componentType' => 'container',
                                'component'     => 'Magento_Ui/js/form/components/button',
                                'actions'       => [
                                    [
                                        'targetName' => $newAttachmentModalTarget,
                                        'actionName' => 'toggleModal',
                                    ],
                                    [
                                        'targetName' => $newAttachmentModalTarget . '.' . 'create_attachment',
                                        'actionName' => 'render',
                                    ],
                                    [
                                        'targetName' => $newAttachmentModalTarget . '.' . 'create_attachment',
                                        'actionName' => 'resetForm',
                                    ],
                                ],
                                'title'         => (string)__(static::ADD_NEW_BUTTON_TITLE),
                                'provider'      => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getGrid(string $dataScope, string $dataProvider): array
    {
        $grid = [
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
                        'dataScope'                => $dataScope,
                        'deleteButtonLabel'        => (string)__('Remove'),
                        'dataProvider'             => $dataProvider,
                        'map'                      => $this->getGridMap(),
                        'identificationProperty'   => AttachmentInterface::ID,
                        'identificationDRProperty' => AttachmentInterface::ID,
                        'dndConfig'                => [
                            'enabled' => true,
                        ],
                        'links'                    => [
                            'insertData'    => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' => ['insertData' => false],
                        ],
                        'sortOrder'                => 10,
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

        return $grid;
    }

    protected function getTextColumn(string $dataScope, string $label, int $sortOrder, bool $fit, bool $visible = true): array
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
                        'label'         => (string)__($label),
                        'sortOrder'     => $sortOrder,
                        'visible'       => $visible
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
                        'label'         => (string)__($label),
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
                        'label'             => (string)__($label),
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

    protected function fillMeta(): array
    {
        return [
            AttachmentInterface::ID          => $this->getTextColumn(AttachmentInterface::ID, 'ID', 10, false),
            AttachmentInterface::SOURCE_NAME => $this->getTextColumn(AttachmentInterface::SOURCE_NAME, 'Name', 20, false),
            ConfigProvider::ICON_FIELD_NAME  => $this->getThumbnailColumn(ConfigProvider::ICON_FIELD_NAME, 'Icon', 30, false),
            AttachmentInterface::LABEL       => $this->getTextColumn(AttachmentInterface::LABEL, 'Label', 40, false),
            AttachmentInterface::TYPE        => $this->getTextColumn(AttachmentInterface::TYPE, 'Type', 50, false),
            LinkInterface::POSITION          => $this->getTextColumn(LinkInterface::POSITION, 'Position', 60, false, false),
            'actionDelete'                   => $this->getMetaActionDelete('Actions', 200, true),
        ];
    }

    protected function getGridMap(): array
    {
        return [
            AttachmentInterface::ID          => AttachmentInterface::ID,
            ConfigProvider::ICON_FIELD_NAME  => 'icon_src',
            AttachmentInterface::LABEL       => 'label',
            AttachmentInterface::TYPE        => 'type',
            AttachmentInterface::SOURCE_NAME => 'source_name',
        ];
    }
}
