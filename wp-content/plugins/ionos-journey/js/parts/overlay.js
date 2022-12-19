export default class Overlay {

    constructor() {
        this.parent = document.body
        this.id = 'ionos-journey-overlay'
        this.add()

        this.registerClickEvent()
        this.onClick = []
    }

    getHtmlElement() {
        return document.body.querySelector('#' + this.id)
    }

    registerClickEvent() {
        this.getHtmlElement().addEventListener('click', (e) => {
            if (e.target === this.getHtmlElement()) {
                this.onClick.forEach((fn) => {
                    fn()
                })
            }
        })
    }

    add() {
        let el = document.createElement('DIV')
        el.id = this.id
        this.parent.prepend(el)
    }

    setBackground(color){
        this.getHtmlElement().style.backgroundColor = color;
    }

    hide() {
        this.getHtmlElement().style.display = 'none'
    }
}
