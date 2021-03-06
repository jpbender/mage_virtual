/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

var giftMessagesController = {
    toogleRequired: function(source, objects)
    {
        if(!$(source).value.blank()) {
            objects.each(function(item) {
               $(item).addClassName('required-entry');
               var label = findFieldLabel($(item));
               if (label) {
                   var span = label.down('span');
                   if (!span) {
                       Element.insert(label, {bottom: '&nbsp;<span class="required">*</span>'});
                   }
               }
            });
        } else {
            objects.each(function(item) {
                if($(source).formObj && $(source).formObj.validator) {
                    $(source).formObj.validator.reset(item);
                }
                $(item).removeClassName('required-entry');
                var label = findFieldLabel($(item));
                if (label) {
                    var span = label.down('span');
                    if (span) {
                        Element.remove(span);
                    }
                }
            });
        }
    },
    toogleGiftMessage: function(container) {
        if(!$(container).toogleGiftMessage) {
            $(container).toogleGiftMessage = true;
            $(this.getFieldId(container, 'edit')).show();
            $(container).down('.action-link').addClassName('open');
            $(container).down('.default-text').hide();
            $(container).down('.close-text').show();
            this.toogleRequired(this.getFieldId(container, 'message'), [
                this.getFieldId(container, 'sender'),
                this.getFieldId(container, 'recipient')
            ]);
        } else {
            $(container).toogleGiftMessage = false;
            $(this.getFieldId(container, 'message')).formObj = $(this.getFieldId(container, 'form'));

            if(!$(this.getFieldId(container, 'form')).validator) {
                $(this.getFieldId(container, 'form')).validator = new Validation(this.getFieldId(container, 'form'));
            }

            if(!$(this.getFieldId(container, 'form')).validator.validate()) {
                return false;
            }

            new Ajax.Request($(this.getFieldId(container, 'form')).action, {
                parameters: Form.serialize($(this.getFieldId(container, 'form')), true),
                loaderArea: container,
                onComplete: function(transport) {

                    $(container).down('.action-link').removeClassName('open');
                    $(container).down('.default-text').show();
                    $(container).down('.close-text').hide();
                    $(this.getFieldId(container, 'edit')).hide();
                    if (transport.responseText.match(/YES/g)) {
                        $(container).down('.default-text').down('.edit').show();
                        $(container).down('.default-text').down('.add').hide();
                    } else {
                        $(container).down('.default-text').down('.add').show();
                        $(container).down('.default-text').down('.edit').hide();
                    }

                }.bind(this)
            });
        }

        return false;
    },
    saveGiftMessage: function(container) {
        this.toogleRequired(this.getFieldId(container, 'message'), [
            this.getFieldId(container, 'sender'),
            this.getFieldId(container, 'recipient')
        ]);

        $(this.getFieldId(container, 'message')).formObj = $(this.getFieldId(container, 'form'));

        if(!$(this.getFieldId(container, 'form')).validator) {
            $(this.getFieldId(container, 'form')).validator = new Validation(this.getFieldId(container, 'form'));
        }

        if(!$(this.getFieldId(container, 'form')).validator.validate()) {
            return;
        }

        new Ajax.Request($(this.getFieldId(container, 'form')).action, {
            parameters: Form.serialize($(this.getFieldId(container, 'form')), true),
            loaderArea: container
        });
    },
    getFieldId: function(container, name) {
        return container + '_' + name;
    }
};

function findFieldLabel(field) {
    var tdField = $(field).up('td');
    if (tdField) {
       var tdLabel = tdField.previous('td');
       if (tdLabel) {
           var label = tdLabel.down('label');
           if (label) {
               return label;
           }
       }
    }

    return false;
}

