import UrlHelper from "../wp-content/plugins/ionos-journey/js/helpers/url-helper.js";
import Helper from "../wp-content/plugins/ionos-journey/js/helpers/helper.js";

const name = '%s'

class StartButton{

    constructor(name) {
        this.name = name

        this.toolbar = document.body.querySelector('#wp-admin-bar-top-secondary')
        Helper.checkMediaQuery('min-width: 992px', [
            () => {
                this.addButton()
            }
        ], [
            () => {
                this.removeButton()
            }
        ])
    }

    /*
     *
     */
    removeButton(){
        if(this.button !== undefined)
            this.button.remove()
    }

    /*
     * Add start button to the admin bar
     */
    addButton(){
        this.button = this.createButton('journey-start', name, 'ionos-journey-container')
        this.button.addEventListener('click', () => {
            UrlHelper.addParam("wp_tour", "started");
            location.reload();
        })
    }

    /*
    * Button creation function
    */
    createButton(id, name, containerClass){
        let container = document.createElement('LI');
        container.classList.add(containerClass);

        let button = document.createElement('A');
        button.id = id;
        button.classList.add('ab-item');
        button.href = '#';

        this.toolbar.appendChild(container);
        container.appendChild(button);
        button.appendChild(this.addSpan('', 'ab-icon'));
        button.appendChild(this.addSpan(name, 'ab-label'));

        return button
    }

    /*
    * Span creation function
    */
    addSpan(name, className){
        let span = document.createElement('SPAN')
        span.classList.add(className);
        span.innerText = name;
        return span;
    }

}

new StartButton(name);
