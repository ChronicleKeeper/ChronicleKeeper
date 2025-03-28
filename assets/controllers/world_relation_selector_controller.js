import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['relationSelect']
    static values = {
        sourceType: String
    }

    initialize() {
        this._onPreConnect = this._onPreConnect.bind(this);
    }

    connect() {
        this.sourceType = this.element.dataset.sourceType;
        this.targetType = this.element.dataset.relationSelectorTarget;
        this.availableRelations = JSON.parse(this.element.dataset.relations);

        this.element.addEventListener('autocomplete:pre-connect', this._onPreConnect);

        // Because of some change ... there need to be the form-select manually added again
        setTimeout(() => {
            const wrappers = document.querySelectorAll('.ts-wrapper');
            wrappers.forEach(element => {
                element.classList.add('form-select');
            });
        }, 100);
    }

    disconnect() {
        this.element.removeEventListener('autocomplete:pre-connect', this._onPreConnect);
    }

    _onPreConnect(event) {
        event.detail.options.render.option = function (data) {
            return `<div data-target-type="${data.type}">${data.text}</div>`;
        }

        event.detail.options.render.item = function (data) {
            return `<div id="autocomplete_selected_item" data-target-type="${data.type}">${data.text}</div>`;
        }

        event.detail.options.onChange = (value) => {
            const selectedOption = document.getElementById('autocomplete_selected_item');
            if (selectedOption) {
                this.updateRelations(selectedOption);
            }
        }
    }

    updateRelations(selectedItem) {
        const targetType = selectedItem.dataset.targetType;
        const possibleRelations = this.availableRelations[this.sourceType][targetType] || {};

        document.getElementById(this.targetType).innerHTML = Object.entries(possibleRelations)
            .map(([value, label]) => `<option value="${value}">${label}</option>`)
            .join('');
    }
}
