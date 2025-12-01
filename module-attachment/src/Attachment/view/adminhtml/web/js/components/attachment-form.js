define([
    'Magento_Ui/js/form/form'
], function (Form) {
    'use strict';

    return Form.extend({
        defaults: {
            imports: {
                attachment: '${ $.provider }:data.attachment',
                label:      '${ $.provider }:data.label'
            },
            listens: {
                attachment: 'onAttachmentUpdate'
            },
            exports: {
                label: '${ $.provider }:data.label'
            }
        },

        initialize: function () {
            this._super();
        },

        initObservable:     function () {
            return this._super()
                .observe([
                    'label'
                ]);
        },

        onAttachmentUpdate: function () {
            if (this.attachment[0] === undefined || this.attachment[0]['name'] === undefined) {
                return;
            }

            let fileName = this.attachment[0]['name'];
            let label = fileName.split('.').slice(0, -1).join('.');
            label = label.charAt(0).toUpperCase() + label.slice(1);
            if (this.label() === "") {
                this.label(label);
            }
        }
    });
});
