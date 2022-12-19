import History from "./history.js";
import UrlHelper from "./url-helper.js";

export default class Helper {

    /*
     * Functionality for checking changes on the media query
     */
    static checkMediaQuery(mediaQuery, onMatch = [], onNotMatch = []) {
        let mql = window.matchMedia('(' + mediaQuery + ')')
        let matching = mql.matches
        // Execute onMatch when screen is matching at the beginning
        if (matching) {
            onMatch.forEach((fn) => {
                fn()
            })
        }
        mql.addEventListener('change', (event) => {
            if (event.matches && !matching) {
                matching = event.matches
                onMatch.forEach((fn) => {
                    fn()
                })
            } else if (matching) {
                matching = event.matches
                onNotMatch.forEach((fn) => {
                    fn()
                })
            }
        })
    }

    /*
     * Function to check if an element is above, below or in the Viewport
     */
    static isInViewport(element) {
        const wpAdminBarRect = document.querySelector('#wpadminbar').getBoundingClientRect()
        const rect = element.getBoundingClientRect();

        const viewHeight = (window.innerHeight || document.documentElement.clientHeight)

        if(rect.height >= viewHeight){
            return 'VISIBLE';
        }

        if (rect.top <= wpAdminBarRect.bottom) {
            return 'ABOVE';
        }
        if (rect.top >= viewHeight) {
            return 'BELOW';
        }

        return 'VISIBLE';
    }

    /*
    * Disable scrolling completely
    */
    static disableScrolling(currentRect = undefined) {
        let x = window.scrollX;
        let y = window.scrollY;
        if (currentRect !== undefined) {
            x = currentRect.x;
            y = currentRect.y;
        }
        window.onscroll = function () {
            window.scrollTo(x, y);
        };
    }

    /*
     * Re enable scrolling
     */
    static enableScrolling() {
        window.onscroll = function () {
        };
    }

    /*
     * Function to scroll to an element
     * + Disable Scrolling afterwards
     */
    static scrollToElement(element, callback = null){
        this.enableScrolling()

        let elementRect = element.getBoundingClientRect()
        window.scrollTo({
            top: elementRect.y,
            left: elementRect.x,
            behavior: 'smooth'
        });

        if(callback !== null){
            let x = window.scrollX;
            let y = window.scrollY;
            let loop = setInterval(() => {
                if(x === window.scrollX && y === window.scrollY){
                    callback()
                    this.disableScrolling(elementRect)
                    clearInterval(loop)
                }else{
                    x = window.scrollX
                    y = window.scrollY
                }
            }, 175);
        }else{
            this.disableScrolling(elementRect)
        }
    }

    static isLastStep(index, max){
        return index === (max - 1) && History.getFirst() != null && History.getFirst().pageName === UrlHelper.getPageName();
    }
}