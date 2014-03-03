/* global define */
define(['underscore', 'backbone', 'oro/dialog-widget', 'oro/workflow-management/helper', 'oro/layout'],
function(_, Backbone, DialogWidget, Helper, layout) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/workflow-management/transition/view/edit
     * @class   oro.WorkflowManagement.TransitionEditView
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        attributes: {
            'class': 'widget-content'
        },

        events: {
            'change [name=label]': 'updateExampleView',
            'change [name$="[transition_prototype_icon]"]': 'updateExampleView',
            'change [name=button_color]': 'updateExampleView'
        },

        options: {
            template: null,
            workflow: null,
            step_from: null,
            button_example_template: '<button type="button" class="btn <%= button_color %>">' +
                '<% if (transition_prototype_icon) { %><i class="<%= transition_prototype_icon %>"/> <% } %>' +
                '<%= label %></button>',
            allowed_button_styles: [
                {
                    'label': 'Gray button',
                    'name': ''
                },
                {
                    'label': 'Navy blue button',
                    'name': 'btn-primary'
                },
                {
                    'label': 'Blue button',
                    'name': 'btn-info'
                },
                {
                    'label': 'Green button',
                    'name': 'btn-success'
                },
                {
                    'label': 'Yellow button',
                    'name': 'btn-warning'
                },
                {
                    'label': 'Red button',
                    'name': 'btn-danger'
                },
                {
                    'label': 'Black button',
                    'name': 'btn-inverse'
                },
                {
                    'label': 'Link',
                    'name': 'btn-link'
                }
            ]
        },

        initialize: function() {
            var template = this.options.template || $('#transition-form-template').html();
            this.template = _.template(template);
            this.button_example_template = _.template(this.options.button_example_template);
            this.widget = null;
        },

        updateExampleView: function() {
            var formData = Helper.getFormData(this.widget.form);
            formData.transition_prototype_icon = formData.transition_prototype_icon ||
                this._getFrontendOption('icon');
            if (formData.transition_prototype_icon || formData.label) {
                this.$exampleBtnContainer.html(
                    this.button_example_template(formData)
                );
                this.$exampleContainer.show();
            } else {
                this.$exampleContainer.hide();
            }
        },

        onStepAdd: function() {
            var formData = Helper.getFormData(this.widget.form);
            if (!this.model.get('name')) {
                this.model.set('name', Helper.getNameByString(formData.label, 'transition_'));
            }
            this.model.set('label', formData.label);
            this.model.set('step_to', formData.step_to);
            this.model.set('display_type', formData.display_type);
            this.model.set('message', formData.message);

            var frontendOptions = this.model.get('frontend_options')
            frontendOptions = _.extend({}, frontendOptions, {
                'icon': formData.transition_prototype_icon,
                'class': formData.button_color
            });
            this.model.set('frontend_options', frontendOptions);

            this.trigger('transitionAdd', this.model, formData.step_from);
            this.widget.remove();
        },

        _getFrontendOption: function(key) {
            var result = '';
            var formOptions = this.model.get('frontend_options');
            if (formOptions && formOptions.hasOwnProperty(key)) {
                result  = formOptions[key]
            }
            return result;
        },

        render: function() {
            var data = this.model.toJSON();
            var steps = this.options.workflow.get('steps').models;
            data.stepFrom = this.options.step_from ? this.options.step_from.get('name') : '';
            data.allowedButtonStyles = this.options.allowed_button_styles;
            data.buttonIcon = this._getFrontendOption('icon');
            data.buttonStyle = this._getFrontendOption('class');
            data.allowedStepsFrom = steps;
            data.allowedStepsTo = steps.slice(1);

            var form = $(this.template(data));
            layout.init(form);
            this.$el.append(form);

            this.widget = new DialogWidget({
                'title': this.model.get('name').length ? 'Edit transition' : 'Add new transition',
                'el': this.$el,
                'stateEnabled': false,
                'incrementalPosition': false,
                'dialogOptions': {
                    'close': _.bind(this.remove, this),
                    'width': 600,
                    'modal': true
                }
            });
            this.widget.render();

            // Disable widget submit handler and set our own instead
            this.widget.form.off('submit');
            this.widget.form.validate({
                'submitHandler': _.bind(this.onStepAdd, this)
            });

            this.$exampleContainer = this.$('.transition-example-container');
            this.$exampleBtnContainer = this.$exampleContainer.find('.transition-btn-example');
            this.updateExampleView();

            return this;
        }
    });
});
