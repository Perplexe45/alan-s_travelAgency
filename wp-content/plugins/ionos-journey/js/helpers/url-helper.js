export default class UrlHelper {

    static getUrl() {
        return new URL(window.location)
    }

    static getAllParams() {
        return Array.from(this.getUrl().searchParams.entries())
    }

    static getParam(name) {
        return this.getUrl().searchParams.get(name)
    }

    static addParam(key, value) {
        const url = this.getUrl()
        url.searchParams.append(key, value)
        window.history.pushState({}, '', url);
    }

    static deleteParam(key) {
        const url = this.getUrl()
        url.searchParams.delete(key)
        window.history.pushState({}, '', url);
    }

    static getPageName() {
        return window.location.pathname.split("/").pop() || 'index.php'
    }

    static convertToJson(urlParams){
        let params = {}
        for(const value in urlParams){
            params[urlParams[value][0]] = urlParams[value][1]
        }
        return params
    }

    static redirectToPage(page, urlParams) {
        let params = urlParams instanceof Array ? this.convertToJson(urlParams) : urlParams
        if(!params.hasOwnProperty('wp_tour')){
            params['wp_tour'] = 'started'
        }

        let pageName = page.includes(".php") ? page : page + ".php"
        let url = this.getUrl().toString().includes('?') ? new URL(this.getUrl().toString().split('?')[0]) : this.getUrl()

        if(window.location.pathname.split("/").pop().length > 1){
            url = new URL(url.toString().replace(this.getPageName(), pageName))
        }else{
            url = new URL(url.toString() + pageName)
        }

        for(const key in params){
            url.searchParams.append(key, params[key])
        }
        if(UrlHelper.getParam('autoplay') !== null){
            url.searchParams.append('autoplay', 'enabled')
        }

        window.location = url.toString()
    }
}


/*

        getUrlParam: function (url) {
            var vars = {};
            url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
                vars[key] = value;
            });
            return vars;
        },
        changeQueryParameter: function (query, index, param) {
            let queryParams = new URLSearchParams(query);
            queryParams.delete(index);
            queryParams.set(index, param);
            return queryParams.toString();
        }
 */
