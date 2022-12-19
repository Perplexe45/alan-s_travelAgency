export default class ProgressPopup {

    constructor(parent, translations) {
        this.parent = parent
        this.translations = translations

        this.id = 'ionos-journey-popup'

        this.onAccept = []
        this.onDeny = []
        this.onCancel = []

        this.init()
    }

    init(){
        this.popupContainer = document.createElement('div')
        this.popupContainer.style.opacity = "0"
        this.popupContainer.style.visibility = "hidden"

        this.popupContainer.id = this.id

        let header = document.createElement('div')
        header.className = 'header'

        this.cancelButton = document.createElement('A')
        this.cancelButton.className = 'stop'

        header.append(this.cancelButton)

        let content = document.createElement('div')
        content.className = 'content'

        let title = document.createElement('h2')
        title.innerHTML = this.translations['popup_title']
        content.append(title)

        let information = document.createElement('p')
        information.innerHTML = this.translations['popup_content'] + '<br><small>' + this.translations['popup_warning'] + "</small>"
        content.append(information)

        let actions = document.createElement('div')
        actions.className = 'actions'

        this.acceptButton = document.createElement('A')
        this.acceptButton.className = 'button primary-button accept-button'
        this.acceptButton.innerHTML = this.translations['continue']

        this.denyButton = document.createElement('A')
        this.denyButton.className = 'button primary-button deny-button'
        this.denyButton.innerHTML = this.translations['restart']

        actions.append(this.acceptButton)
        actions.append(this.denyButton)

        this.popupContainer.append(header, content, actions)

        this.parent.getHtmlElement().prepend(this.popupContainer)

        this.acceptButton.addEventListener('click', () => {
            this.onAccept.forEach((fn) => {
                fn()
            })
        })

        this.denyButton.addEventListener('click', () => {
            this.onDeny.forEach((fn) => {
                fn()
            })
        })

        this.cancelButton.addEventListener('click', () => {
            this.onCancel.forEach((fn) => {
                fn()
            })
        })

    }

    show(){
        this.popupContainer.style.opacity = "1"
        this.popupContainer.style.visibility = "visible"
    }

    hide(){
        this.popupContainer.remove()
    }

}
