import UrlHelper from "./url-helper.js";

export default class Progress {

    static versionConstraint = '1'

    static timestamp = -1
    static progressKey = 'ionos-journey-progress-'
    static currentProgress = []

    static init(progressString, user_id){
        this.user_id = user_id
        if(localStorage.getItem(Progress.progressKey+this.user_id) == null){
            this.version = this.versionConstraint
            return
        }
        if(localStorage.getItem(Progress.progressKey+user_id).length > 0){
            let progressData = JSON.parse(localStorage.getItem(Progress.progressKey+this.user_id))

            this.version = progressData['version']
            this.timestamp = progressData['timestamp']
            this.currentProgress = progressData['data']
        }
        if(progressString.length > 0){
            let progressData = JSON.parse(progressString);

            if(progressData['timestamp'] > this.timestamp){
                this.version = progressData['version']
                this.timestamp = progressData['timestamp']
                this.currentProgress = progressData['data']
            }else if(progressData['timestamp'] < this.timestamp){
                this.persistProgress(localStorage.getItem(Progress.progressKey+this.user_id))
            }
        }else if(localStorage.getItem(Progress.progressKey+user_id).length > 0){
            this.persistProgress(localStorage.getItem(Progress.progressKey+this.user_id))
        }
    }

    static store(progress){
        if(Date.now() > this.timestamp){
            let progressData = {}

            progressData['version'] = this.versionConstraint
            progressData['timestamp'] = Date.now()
            progressData['data'] = progress

            localStorage.setItem(Progress.progressKey+this.user_id, JSON.stringify(progressData))
            this.persistProgress(JSON.stringify(progressData))
        }
    }

    static get() {
        return this.currentProgress
    }

    static clear(){
        localStorage.removeItem(Progress.progressKey+this.user_id)
    }

    static clearMeta(retry = false) {
        let httpRequest = new XMLHttpRequest();
        httpRequest.onreadystatechange = function (){
            if(this.readyState === 4 && this.status !== 200 && retry){
                Progress.clear(!retry)
            }
        }

        let url = UrlHelper.getUrl().toString();
        if(url.includes('?')){
            url = url.split('?')[0]
        }

        httpRequest.open('POST', url+'?journey_persistance=clear')
        httpRequest.send()
    }

    static persistProgress(progress, retry = true){
        let httpRequest = new XMLHttpRequest();
        httpRequest.onreadystatechange = function (){
            if(this.readyState === 4 && this.status !== 200 && retry){
                Progress.persistProgress(progress, !retry)
            }
        }

        let url = UrlHelper.getUrl().toString();
        if(url.includes('?')){
            url = url.split('?')[0]
        }

        httpRequest.open('POST', url+'?journey_persistance=save')
        httpRequest.setRequestHeader('Content-type', 'application/json')
        httpRequest.send(progress)
    }

}
