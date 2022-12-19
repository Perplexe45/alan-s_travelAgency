import UrlHelper from "./url-helper.js";
import Progress from "./progress.js";

export default class History {

    static historyKey = 'ionos-journey-history'

    static store(history) {
        sessionStorage.setItem(History.historyKey, JSON.stringify(history))
    }

    static push(index) {
        let history = History.get()
        let params = UrlHelper.convertToJson(UrlHelper.getAllParams())
        if(params.hasOwnProperty('autoplay')){
            delete params.autoplay
        }
        history.push({
            'pageName': UrlHelper.getPageName(),
            'index': index,
            'urlParams': params
        })
        History.store(history)
    }

    static getLast(){
        let history = History.get()
        return history.pop()
    }

    static getFirst(){
        let history = History.get()
        return history[0] || null
    }

    static pop() {
        let history = History.get()
        let item = history.pop()
        History.store(history)
        return item
    }

    static get() {
        return JSON.parse(sessionStorage.getItem(History.historyKey)) || []
    }

    static clear(saveProgress = true) {
        if(History.get().length > 0 && saveProgress) {
            Progress.store(History.get())
        }
        sessionStorage.removeItem(History.historyKey)
    }
}
