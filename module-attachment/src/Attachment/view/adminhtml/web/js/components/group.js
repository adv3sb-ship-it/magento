define([
    'Magento_Ui/js/form/components/group'
], function (Group) {
    'use strict';

    return Group.extend({
        default: {
            toggle: {
                selector: '',
                value:    ''
            }
        },

        initialize: function () {
            this._super();

            this.setLinks({
                toggleVisibility: this.toggle.selector
            }, 'imports');
        },

        toggleVisibility: function (selected) {
            if (selected === this.toggle.value) {
                this.visible(true);
            } else {
                this.visible(false);
            }
        }
    });
});

