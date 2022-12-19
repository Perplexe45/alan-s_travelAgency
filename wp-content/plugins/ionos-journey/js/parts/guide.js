import History from "../helpers/history.js";

export default class Guide {

    constructor(parent, color) {
        this.parent = parent
        this.color = color
        this.id = 'ionos-journey-guide'
        this.htmlElement = null
        this.elements = null
        this.currentIndex = 0

        this.init()
    }

    init() {
        let guideContainer = document.createElement('DIV')
        guideContainer.id = this.id
        this.htmlElement = guideContainer

        this.header = document.createElement('DIV')
        this.header.className = 'ionos-journey-guide-header'

        this.stopButton = document.createElement("A")
        this.stopButton.className = 'ionos-journey-button-stop'

        this.progressBar = document.createElement('DIV')
        this.progressBar.className = 'ionos-journey-guide-progress'

        this.image = document.createElement('IMG')
        this.image.className = 'ionos-journey-guide-image'

        this.autoplayBar = document.createElement('DIV')
        this.autoplayBar.className = 'ionos-journey-guide-autoplay'

        this.header.append(this.stopButton, this.image, this.autoplayBar, this.progressBar)

        this.content = document.createElement('DIV')
        this.content.className = 'ionos-journey-guide-content'

        this.headline = document.createElement('H1')
        this.headline.className = 'ionos-journey-guide-headline'

        this.text = document.createElement('P')
        this.text.className = 'ionos-journey-guide-text'

        this.content.append(
            this.headline,
            this.text
        )

        this.footer = document.createElement('DIV')
        this.footer.className = 'ionos-journey-guide-footer'

        guideContainer.append(this.header, this.content, this.footer)
        this.parent.prepend(this.htmlElement)

        this.createNavigationBar()

        /* Array of functions to call on event */
        this.onUpperBoundaryReached = []
        this.onLowerBoundaryReached = []
        this.onStop = []

        this.onUpdate = []

        this.stopButton.addEventListener('click', (e) => {
            this.onStop.forEach((fn) => {
                fn()
            })
        })
    }

    createAutoplayBar() {
        if(this.autoplayBar !== null && this.autoplayBar !== undefined){
            this.header.removeChild(this.autoplayBar)
        }

        this.autoplayBar = document.createElement('div')
        this.autoplayBar.className = 'ionos-journey-guide-autoplay'

        this.autoplayBarProgress = document.createElement('DIV')
        this.autoplayBarProgress.className = 'progress-bar'
        this.autoplayBarProgress.style.background = this.color

        this.autoplayBar.append(this.autoplayBarProgress)

        this.header.append(this.autoplayBar)
    }

    createNavigationBar() {
        let navBar = document.createElement('DIV')
        navBar.id = 'ionos-journey-guide-navigation'

        this.nextButton = document.createElement('a')
        this.nextButton.id = 'ionos-journey-bubble-next'
        if(this.isLast && this.currentIndex === this.elements.length){
            this.nextButton.className = "disabled"
        }else{
            this.nextButton.className = "clickable-highlighted"
        }

        this.backButton = document.createElement('a')
        this.backButton.id = 'ionos-journey-bubble-back'
        if(History.get().length === 0 && this.currentIndex === 0){
            this.backButton.className = "disabled"
        }else{
            this.backButton.className = "clickable-highlighted"
        }

        navBar.append(this.nextButton, this.backButton)
        this.footer.append(navBar)

        this.nextButton.addEventListener('click', (e) => {
            this.next()
            this.onUpdate.forEach((fn) => {
                fn()
            })
        })

        this.backButton.addEventListener('click', (e) => {
            this.back()
            this.onUpdate.forEach((fn) => {
                fn()
            })
        })
    }

    show(configItem, isLast) {
        this.isLast = isLast
        this.currentIndex = 0

        this.htmlElement.style.visibility = 'visible'
        this.elements = configItem.children

        if (Object.keys(this.elements).length > 0) {
            this.update()
        }

        this.htmlElement.style.opacity = '1'
    }

    hide() {
        this.htmlElement.style.opacity = '0'
        this.htmlElement.style.visibility = 'hidden'
    }

    createProgressBar() {
        this.header.removeChild(this.progressBar)

        let bar = document.createElement('UL')
        bar.className = 'ionos-journey-guide-progress'

        Object.keys(this.elements).forEach(index => {
            let barItem = document.createElement('LI')
            let barItemButton = document.createElement('BUTTON')
            let buttonImage = '<svg width="8" height="8" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false">' +
                '<circle cx="4" cy="4" r="4" fill=' + (parseInt(index) === this.currentIndex ? this.color : 'lightgray') + '></circle>' +
                '</svg>'

            barItemButton.className = "ionos-journey-guide-targeted components-button has-icon"
            barItemButton.setAttribute("data-index", index)

            barItemButton.innerHTML = buttonImage
            barItem.append(barItemButton)

            barItemButton.addEventListener('click', (e) => {
                this.showIndex(e.currentTarget.getAttribute('data-index'))
                this.onUpdate.forEach((fn) => {
                    fn()
                })
            })
            bar.append(barItem)
        })

        this.header.append(bar)
        this.progressBar = bar
    }

    update() {
        let element = this.elements[this.currentIndex]
        this.image.src = element.imageUrl

        this.headline.innerHTML = element.headline
        this.text.innerHTML = element.htmlContent

        if(History.get().length === 0 && this.currentIndex === 0){
            this.backButton.className = "disabled"
        }else{
            this.backButton.className = "clickable-highlighted"
        }

        if(this.isLast && this.currentIndex === this.elements.length){
            this.nextButton.className = "disabled"
        }else{
            this.nextButton.className = "clickable-highlighted"
        }

        this.createAutoplayBar()
        this.createProgressBar()
    }

    showIndex(index){
        let i = Number.parseInt(index)
        if (i < Object.keys(this.elements).length && i !== this.currentIndex) {
            this.currentIndex = i;
            this.update()
        }
    }

    next() {
        if (this.currentIndex + 1 < Object.keys(this.elements).length) {
            this.currentIndex++
            this.update()
            return true
        } else {
            this.onUpperBoundaryReached.forEach((fn) => {
                fn()
            })
            return false
        }
    }

    back() {
        if (this.currentIndex > 0) {
            this.currentIndex--
            this.update();
        } else {
            this.onLowerBoundaryReached.forEach((fn) => {
                fn()
            })
        }
    }

    showAutoplayBar(timeout){
        this.autoplayBar.style.visibility = 'visible'
        this.autoplayBar.style.opacity = '1'

        this.autoplayBarProgress.style.animation = 'progress-animation ' + (timeout / 1000) + 's ease-in-out'
        this.autoplayBarProgress.style.width = '100%'
    }

    targeted() {

    }

}

/*
        guide: function () {
            this.guideFadeCount = 0;
            this.guideIndex = 0;

            this.update = function (configItem, focus, hideFirst) {
                console.log(focus);
                if(configItem['type'] == 'guide' && focus instanceof HTMLElement) {
                    let guide = {}
                    focus.innerHTML =
                        '<img class="ionos-journey-guide-image" />' +
                        '<ul class="ionos-journey-guide-progress"></ul>' +
                        '<h1 class="ionos-journey-guide-headline"></h1>' +
                        '<p class="ionos-journey-guide-text"></p>' +
                        '<div class="ionos-journey-guide-footer"></div>';

                    guide.image = focus.querySelector('.ionos-journey-guide-image');
                    guide.image.src = configItem['elements'][IONOS.plugin.journey.guide.guideIndex]["image-url"];
                    guide.image.style.backgroundColor = configItem['elements'][IONOS.plugin.journey.guide.guideIndex]["image-background"];

                    guide.headline = focus.querySelector('.ionos-journey-guide-headline');
                    guide.headline.innerText = configItem['elements'][IONOS.plugin.journey.guide.guideIndex]["headline"];

                    if ( Object.keys(configItem['elements']).length > 1) {
                        guide.position = focus.querySelector('.ionos-journey-guide-progress');
                        let length = Object.keys(configItem['elements']).length;
                        let li = '';
                        configItem['elements'].forEach(function (item, key) {
                            li += '<li><button type="button" index = ' + key + ' aria-label="Seite ' + (parseInt(key) + 1) + ' von ' + length + '" class="ionos-journey-guide-targeted components-button has-icon">' +
                                '<svg width="8" height="8" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false">' +
                                '<circle cx="4" cy="4" r="4" fill=' + (key == IONOS.plugin.journey.guide.guideIndex ? '#003d8f' : 'lightgray') + '></circle>' +
                                '</svg>' +
                                '</button></li>';
                        });
                        guide.position.innerHTML = "<ul>" + li + "</ul>";
                    }

                    guide.text = focus.querySelector('.ionos-journey-guide-text');
                    guide.text.innerHTML = configItem['elements'][IONOS.plugin.journey.guide.guideIndex]["text"];

                    guide.footer = focus.querySelector('.ionos-journey-guide-footer');


                    guide.stop = document.createElement('a');
                    guide.stop.id = 'ionos-journey-bubble-stop';
                    focus.prepend(guide.stop);

                    if(configItem['navigation'] == "true") {
                        let navigation = document.createElement('DIV');
                        navigation.id = 'ionos-journey-bubble-navigation';
                        let next = document.createElement('a');
                        next.id = 'ionos-journey-bubble-next';
                        let back = document.createElement('a');
                        back.id = 'ionos-journey-bubble-back';

                        navigation.prepend(next);
                        navigation.prepend(back);
                        guide.footer.prepend(navigation);

                        focus.append(guide.footer);

                        back.addEventListener('click', function (e) {
                            if(e.target === back) {
                                if(!document.body.querySelector('#journeyBack').classList.contains('wait')) {
                                    document.body.querySelector('#journeyBack').click();
                                }
                            }
                        });
                        next.addEventListener('click', function (e) {
                            if(e.target === next) {
                                if(!document.body.querySelector('#journeyNext').classList.contains('wait')) {
                                    document.body.querySelector('#journeyNext').click();
                                }
                            }
                        });
                    }


                    guide.stop.addEventListener('click', function (e) {
                        if(e.target === guide.stop) {
                            if(!document.body.querySelector('#journeyStop').classList.contains('wait')) {
                                document.body.querySelector('#journeyStop').click();
                            }
                        }
                    });

                    focus.querySelectorAll('.ionos-journey-guide-targeted').forEach(function (item, index) {
                        item.addEventListener('click', function () {
                            IONOS.plugin.journey.guide.targeted(configItem, focus, item.getAttribute('index'));
                        });
                    });



                    if(IONOS.plugin.journey.guide.guideIndex == 0 && hideFirst !== false) {
                        guide.image.style.opacity = 0;
                        guide.position.style.opacity = 0;
                        guide.headline.style.opacity = 0;
                        guide.text.style.opacity = 0;
                        guide.footer.style.opacity = 0;
                        guide.stop.style.opacity = 0;
                    };


                }
            };
            this.next = function (configItem, focus) {
                if(configItem['type'] == 'guide' && configItem['elements'][IONOS.plugin.journey.guide.guideIndex + 1]) {
                    IONOS.plugin.journey.guide.guideIndex += 1;
                    IONOS.plugin.journey.guide.update(configItem, focus, false);
                }
            };
            this.back = function (configItem, focus) {
                if(configItem['type'] == 'guide' && configItem['elements'][IONOS.plugin.journey.guide.guideIndex - 1]) {
                    IONOS.plugin.journey.guide.guideIndex -= 1;
                    IONOS.plugin.journey.guide.update(configItem, focus, false);
                }
            };
            this.targeted = function (configItem, focus, index) {
                if(configItem['type'] == 'guide' && configItem['elements'][index]) {
                    IONOS.plugin.journey.guide.guideIndex = parseInt(index);
                    IONOS.plugin.journey.guide.update(configItem, focus, false);
                }

            };
            this.fadeIn = function (configItem, focus) {
                if(configItem['type'] == 'guide') {
                    let fadeIn = setInterval(function () {
                        if (focus.childNodes && focus.childNodes[0] && focus.childNodes[0].style.opacity >= 1) {
                            focus.childNodes.forEach(function (item, key) {
                                item.style.opacity = 1;
                            });
                            clearTimeout(fadeIn);
                        } else {
                            focus.childNodes.forEach(function (item, key) {
                                item.style.opacity = parseFloat(item.style.opacity) + 0.1;
                            });
                        }
                    }, 40);
                }
            };
        }

 */
