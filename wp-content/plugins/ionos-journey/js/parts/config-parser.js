import History from "../helpers/history.js";
import UrlHelper from "../helpers/url-helper.js";

export default class ConfigParser {

    constructor(config) {
        this.config = JSON.parse(config)
        this.maxIndex = Object.keys(this.config).length
        this.currentIndex = 0
    }

    loadHistory(){
        let lastItem = History.getLast()
        if (lastItem !== undefined &&
            lastItem.pageName === UrlHelper.getPageName()) {
            this.currentIndex = History.pop().index
        }
    }

    current() {
        return this.config[this.currentIndex]
    }

    next() {
        if (Object.keys(this.config).length <= this.currentIndex + 1) {
            return null
        }

        if(this.config[this.currentIndex].type !== 'redirect'){
            History.push(this.currentIndex)
        }

        this.currentIndex++
        return this.current()
    }

    prev() {
        let historyItem = History.getLast()
        if (historyItem !== undefined) {
            if(historyItem.pageName !== UrlHelper.getPageName()){
                UrlHelper.redirectToPage(historyItem.pageName, historyItem.urlParams)
                return null;
            }
            this.currentIndex = History.pop().index
            return this.current()
        }
        return null
    }
}
