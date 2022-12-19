export default class Focus {
    constructor(parent) {
        this.parent = parent
        this.target = document.body
        this.id = 'ionos-journey-focus'
        this.jumps = 1
        this.add()
        this.registerClickEvent()

        this.onClick = []
    }

    getHtmlElement() {
        return document.body.querySelector('#' + this.id)
    }

    add() {
        let targetRect = this.target.getBoundingClientRect()
        let el = document.createElement('DIV')

        el.id = this.id
        el.style.top = targetRect.top + 'px'
        el.style.left = targetRect.left + 'px'
        el.style.width = targetRect.width - 8 + 'px'
        el.style.height = targetRect.height - 8 + 'px'
        el.style.borderColor = 'transparent'

        this.parent.prepend(el)
    }

    changeCursor(cursor) {
        this.getHtmlElement().style.cursor = cursor;
    }

    addAnimation() {
        this.getHtmlElement().className = 'pulse-with-background'
        this.changeCursor('pointer')
    }

    rmvAnimation() {
        this.getHtmlElement().className = ''
        this.changeCursor('default')
    }

    registerClickEvent() {
        this.getHtmlElement().addEventListener('click', (e) => {
            if (e.target === this.getHtmlElement() && e.target.closest('#ionos-journey-guide') === null) {
                this.onClick.forEach((fn) => {
                    fn()
                })
            }
        })
    }

    removeChildren() {
        const focus = this.getHtmlElement()
        while (focus.firstChild) {
            focus.removeChild(focus.lastChild)
        }
    }

    resize(width, height){
        this.getHtmlElement().style.width = width + 'px'
        this.getHtmlElement().style.height = height + 'px'
    }

    hide(){
        this.getHtmlElement().style.visibility = 'hidden'
    }

    show(){
        this.getHtmlElement().style.visibility = 'visible'
    }

    setColor(color) {
        this.getHtmlElement().style.borderColor = color
    }

    moveToTop(){
        let cachedTransition = this.getHtmlElement().style.transition;

        this.getHtmlElement().style.transition = '0s all'
        this.getHtmlElement().style.top = '0px'
        setTimeout(() => {
            this.getHtmlElement().style.transition = cachedTransition
        }, 500)
    }

    moveToBottom(){
        let cachedTransition = this.getHtmlElement().style.transition;

        this.getHtmlElement().style.transition = '0s all'
        this.getHtmlElement().style.top = ((window.innerHeight || document.documentElement.clientHeight) + 10) + 'px'
        setTimeout(() => {
            this.getHtmlElement().style.transition = cachedTransition
        }, 500)
    }

    move(x, y, width, height, callback) {
        requestAnimationFrame(() => {
            this.getHtmlElement().style.left = x + 'px'
            this.getHtmlElement().style.top = y + 'px'
            this.getHtmlElement().style.width = (width - 8) + 'px'
            this.getHtmlElement().style.height = (height - 8) + 'px'
        })

        setTimeout(() => {
            callback()
        }, 1000)
    }
}
