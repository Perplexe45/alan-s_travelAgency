import History from "../helpers/history.js";

export default class Bubble {

    constructor(parent, focus, color) {
        this.parent = parent
        this.distance = 15
        this.color = color
        this.focus = focus
        this.id = 'ionos-journey-bubble'
        this.htmlElement = null
        this.text = null

        this.onStop = []
        this.onNext = []
        this.onPrev = []
    }

    /*
     * Initialize the bubble without text
     */
    init() {
        if(this.htmlElement !== null){
            this.htmlElement.remove()
        }

        let bubbleContainer = document.createElement('DIV')
        bubbleContainer.id = this.id

        this.stopButton = document.createElement('A')
        this.stopButton.className = 'ionos-journey-button-stop'

        this.textContainer = document.createElement('DIV')

        bubbleContainer.style.visibility = 'hidden'
        bubbleContainer.style.opacity = '0'

        bubbleContainer.append(this.stopButton, this.textContainer)

        this.htmlElement = bubbleContainer
        this.parent.prepend(this.htmlElement)

        this.createNavigationBar()

        this.stopButton.addEventListener('click', (e) => {
            this.onStop.forEach((fn) => {
                fn()
            })
        })
    }

    /*
     * Bubble navigation creation
     */
    createNavigationBar() {
        let navBar = document.createElement('DIV')
        navBar.id = 'ionos-journey-bubble-navigation'

        let next = document.createElement('a')
        next.id = 'ionos-journey-bubble-next'
        if(this.isLast){
            next.className = "disabled"
        } else {
            next.className = "clickable-highlighted"
        }

        let back = document.createElement('a')
        back.id = 'ionos-journey-bubble-back'
        if(History.get().length === 0){
            back.className = "disabled"
        } else {
            back.className = "clickable-highlighted"
        }

        this.autoProgress = document.createElement('div')
        this.autoProgress.id = 'ionos-journey-bubble-auto'
        this.autoProgress.style.visibility = 'hidden'
        this.autoProgress.style.opacity = '0'
        this.autoProgress.style.border = '3px solid ' + this.color

        navBar.append(next, back, this.autoProgress)
        this.htmlElement.append(navBar)

        next.addEventListener('click', (e) => {
            this.onNext.forEach((fn) => {
                fn()
            })
        })

        back.addEventListener('click', (e) => {
            this.onPrev.forEach((fn) => {
                fn()
            })
        })
    }

    /*
     * Show the bubble to the user (make it visible)
     */
    show(configItem, isLast) {
        this.isLast = isLast
        this.init()

        this.text = configItem.htmlContent
        this.textContainer.innerHTML = this.text

        this.update()

        this.htmlElement.style.visibility = 'visible'
        this.htmlElement.style.opacity = '1'
    }

    /*
     * Hide the bubble from the user (make it invisible)
     */
    hide() {
        if(this.htmlElement !== null){
            this.htmlElement.style.opacity = '0'
            this.htmlElement.style.visibility = 'hidden'
        }
    }

    /*
     * Update the bubble and change the position and the shown text
     */
    update() {
        this.hideAutoProgress()

        let wpAdminBarRect = document.querySelector('#wpadminbar').getBoundingClientRect()
        let sourceRect = this.focus.getHtmlElement().getBoundingClientRect()
        let parentRect = this.parent.getBoundingClientRect()

        const viewHeight = (window.innerHeight || document.documentElement.clientHeight)

        let bubbleRect = this.htmlElement.getBoundingClientRect();

        if(parentRect.width - sourceRect.right > 350 &&
            (sourceRect.top  - wpAdminBarRect.height) + bubbleRect.height < viewHeight){
            this.htmlElement.className = "bubble-left"

            this.htmlElement.style.top =  (sourceRect.top  - wpAdminBarRect.height) + 'px'
            this.htmlElement.style.left = (sourceRect.right + this.distance) + 'px'
        } else if (sourceRect.left > 350
            && (sourceRect.top  - wpAdminBarRect.height) + bubbleRect.height < viewHeight) {
            this.htmlElement.className = 'bubble-right'

            this.htmlElement.style.top =  (sourceRect.top - wpAdminBarRect.height) + 'px'
            this.htmlElement.style.left = (sourceRect.left - (this.htmlElement.scrollWidth)) + 'px';
        }else if (sourceRect.top > 350
            && (sourceRect.x - (bubbleRect.width/2) + (sourceRect.width/2) > 0)) {
            this.htmlElement.className = 'bubble-bot'

            this.htmlElement.style.left = sourceRect.left + (sourceRect.width / 2) - (this.htmlElement.scrollWidth / 2) + 'px';
            this.htmlElement.style.bottom = (parentRect.height - sourceRect.top + this.distance) + 'px'

            this.htmlElement.style.position = 'fixed';
        }else if ((parentRect.height - sourceRect.bottom) > 350
            && (sourceRect.x - (bubbleRect.width/2) + (sourceRect.width/2) > 0)) {
            this.htmlElement.className = 'bubble-top'

            this.htmlElement.style.left = (sourceRect.width / 2) + 'px';
            this.htmlElement.style.top = (sourceRect.top + sourceRect.height) - this.distance + 'px'
        } else {
            this.htmlElement.className = ''

            this.htmlElement.style.margin = 'auto';
            this.htmlElement.style.left = (parentRect.width / 2 - (bubbleRect.width / 2)) + 'px';
            this.htmlElement.style.top = ((parentRect.height / 2.5))+ 'px'
            this.htmlElement.style.position = 'fixed'
        }
    }

    /*
     * Show an progress circle (when autoplay enabled)
     */
    showAutoProgress(timeout = 5000){
        this.autoProgress.style.opacity = '1'
        this.autoProgress.style.visibility = 'visible'

        this.autoProgress.style.animation = 'circle-animation ' + (timeout/1000) + 's linear'
        this.autoProgress.style.clipPath = 'polygon(50% 50%, 50% 0%, 100% 0%, 100% 100%, 0% 100%, 0% 0%, 50% 0%)'
    }

    /*
     * Hide the progress circle
     */
    hideAutoProgress(){
        this.autoProgress.style.opacity = '0'
        this.autoProgress.style.visibility = 'hidden'

        this.autoProgress.style.clipPath = 'polygon(50% 50%, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%)'
    }
}
