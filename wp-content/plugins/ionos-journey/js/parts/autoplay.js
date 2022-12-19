import UrlHelper from "../helpers/url-helper.js";

export default class Autoplay {

    constructor(player) {
        this.player = player
        this.init()
    }

    init(){
        this.getPlayer().guide.onUpdate.push(() => {
            if(this.isEnabled()){
                this.start()
            }
        })
    }

    isEnabled(){
        return UrlHelper.getParam('autoplay') !== null;
    }

    getPlayer(){
        return this.player
    }

    /*
     * Enables/Disables the autoplay
     */
    switch(){
        if(this.isEnabled()){
            this.getPlayer().navigation.autoButton.children[0].id= "journey-auto"
            UrlHelper.deleteParam('autoplay')
            if(this.getPlayer().current.type === 'guide'){
                this.getPlayer().guide.update()
            }else{
                this.getPlayer().bubble.hideAutoProgress()
            }
        }else{
            UrlHelper.addParam('autoplay', 'enabled')
            this.getPlayer().navigation.autoButton.children[0].id= "journey-auto-active"
            this.start()
        }
    }

    /*
     * Function for autoplaying the tour
     */
    start(){
        if(this.isEnabled()){
            let timeout = this.getPlayer().current.timeout | 5000

            if(this.getPlayer().current.type === 'guide'){
                this.getPlayer().guide.showAutoplayBar(timeout)
            }else{
                this.getPlayer().bubble.showAutoProgress(timeout)
            }

            let currentOnStart = this.getPlayer().current
            setTimeout(() => {
                if(this.getPlayer().current !== currentOnStart) return

                if(this.isEnabled()){
                    if(this.getPlayer().current.isClickable){
                        this.getPlayer().click()
                    }else if(this.getPlayer().current.type === 'guide'){
                        this.guide(timeout)
                    }else{
                        this.getPlayer().next()
                    }
                }else{
                    if(this.getPlayer().current.type === 'guide'){
                        this.getPlayer().guide.update()
                    }else{
                        this.getPlayer().bubble.hideAutoProgress()
                    }
                }
            }, timeout)
        }
    }

    /*
     * Autoplaying guide with recursion
     */
    guide(timeout){
        if(this.getPlayer().current.type !== 'guide' || !this.isEnabled()) return;

        if(this.getPlayer().guide.next()){
            let indexOnStart = this.getPlayer().guide.currentIndex
            this.getPlayer().guide.showAutoplayBar(timeout)
            setTimeout(() => {
                if(indexOnStart !== this.getPlayer().guide.currentIndex) return
                this.guide(timeout)
            }, timeout)
        }
    }

}