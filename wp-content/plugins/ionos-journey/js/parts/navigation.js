import UrlHelper from "../helpers/url-helper.js";

export default class Navigation {

    constructor() {
        this.parentSelector = '#wp-admin-bar-top-secondary'
        this.parent = document.querySelector(this.parentSelector)
        if (this.parent === null) {
            console.log('Error: ' + this.parentSelector + ' not found')
        }else{
            this.createNavigation()
            this.registerClickEvent()
            this.registerKeyEvent()
        }

        /* Array of functions to call on event */
        this.onNext = []
        this.onPrev = []
        this.onStop = []
        this.onAuto = []
        this.onEnter = []
    }

    getHtmlElement() {
        return document.querySelector()
    }

    createNavigation() {
        this.prevButton = this.createButton('journey-prev')
        this.stopButton = this.createButton('journey-stop')
        this.autoButton = this.createButton('journey-auto')
        if(UrlHelper.getParam('autoplay') !== null){
            this.autoButton.children[0].id= "journey-auto-active"
        }

        this.nextButton = this.createButton('journey-next')
        this.parent.append(this.nextButton, this.autoButton, this.stopButton, this.prevButton)
    }

    createStartButton() {
        this.startButton = this.createButton('journey-start', 'IONOS Journey')
        this.parent.append(this.startButton)
    }

    createButton(id, text = '') {
        let anchor = document.createElement('A')
        anchor.id = id
        anchor.classList.add('ab-item')

        // anchor.href = '#';
        let iconSpan = document.createElement('SPAN')
        iconSpan.classList.add('ab-icon')

        let labelSpan = document.createElement('SPAN')
        labelSpan.classList.add('ab-label')
        labelSpan.innerText = text
        anchor.append(iconSpan, labelSpan)

        let button = document.createElement('LI')
        button.classList.add('ionos-journey-container')
        button.append(anchor)

        return button;
    }

    registerClickEvent() {
        this.prevButton.addEventListener('click', () => {
            this.prev()
        })
        this.stopButton.addEventListener('click', () => {
            this.stop()
        })
        this.nextButton.addEventListener('click', () => {
            this.next()
        })
        this.autoButton.addEventListener('click', () => {
            this.onAuto.forEach((fn) => {
                fn()
            })
        })
    }

    registerKeyEvent() {
        window.onkeyup = (e) => {
            if (e.defaultPrevented) {
                return; // We could also stop the propagation of this event
            }

            e.preventDefault()
            e.stopPropagation()
            switch (e.key) {
                case 'ArrowRight':
                    if (!this.nextButton.classList.contains('wait')) {
                        this.next()
                    }
                    break
                case 'ArrowLeft':
                    if (!this.nextButton.classList.contains('wait')) {
                        this.prev()
                    }
                    break
                case 'Escape':
                    this.stop()
                    break
                case 'Enter': {
                    this.enter()
                    break
                }
            }
        }
    }

    next() {
        this.onNext.forEach((fn) => {
            fn()
        })
    }

    stop() {
        this.onStop.forEach((fn) => {
            fn()
        })
    }

    prev() {
        this.onPrev.forEach((fn) => {
            fn()
        })
    }

    enter() {
        this.onEnter.forEach((fn) => {
            fn()
        })
    }
}


/*
    navigation: function () {
        this.click = function () {
            document.body.querySelector('#journeyNext')
                .addEventListener('click', function (e) {
                    e.target.classList.add('wait');
                    if( IONOS.plugin.journey.focus instanceof Object
                        && IONOS.plugin.journey.configItem instanceof Object
                        && IONOS.plugin.journey.configItem['type'] == "guide"
                        && IONOS.plugin.journey.guide.guideIndex != (Object.keys(IONOS.plugin.journey.configItem['elements']).length - 1)
                    ) {
                        IONOS.plugin.journey.guide.next(IONOS.plugin.journey.configItem, IONOS.plugin.journey.focus.get());
                    } else {
                        IONOS.plugin.journey.navigation.forward();
                    }
                    setTimeout(function () {
                        e.target.classList.remove('wait');
                    }, 400);
                });

            document.body.querySelector('#journeyStop')
                .addEventListener('click', function () {
                    IONOS.plugin.journey.navigation.stop();
                });

            document.body.querySelector('#journeyBack')
                .addEventListener('click', function (e) {
                    e.target.classList.add('wait');
                    IONOS.plugin.journey.navigation.backwards();
                    setTimeout(function () {
                        e.target.classList.remove('wait');
                    }, 400);
                });
        };
        this.start = function () {
            if( IONOS.plugin.journey.configCollection instanceof Object
                && IONOS.plugin.journey.urlParam instanceof Object
                && Object.keys(IONOS.plugin.journey.configCollection).length >= 1
            ) {

                IONOS.plugin.journey.index = (IONOS.plugin.journey.urlParam['wp_tour'] && IONOS.plugin.journey.configCollection[IONOS.plugin.journey.urlParam['wp_tour']])
                    ? IONOS.plugin.journey.urlParam['wp_tour']
                    : Object.keys(IONOS.plugin.journey.configCollection)[0];
                IONOS.plugin.journey.nextConfigItem = IONOS.plugin.journey.index ? IONOS.plugin.journey.configCollection[IONOS.plugin.journey.index] : undefined;
                IONOS.plugin.journey.target = IONOS.plugin.journey.nextConfigItem ? document.body.querySelector(IONOS.plugin.journey.nextConfigItem['selector']) : undefined;

                if( IONOS.plugin.journey.focus instanceof Object
                    && IONOS.plugin.journey.target instanceof HTMLElement
                    && IONOS.plugin.journey.nextConfigItem instanceof Object
                    && IONOS.plugin.journey.index
                ) {
                    let focus = IONOS.plugin.journey.focus.get();
                    IONOS.plugin.journey.movement.step(
                        focus,
                        IONOS.plugin.journey.target,
                        IONOS.plugin.journey.nextConfigItem,
                        IONOS.plugin.journey.index
                    );

                    IONOS.plugin.journey.configItem = IONOS.plugin.journey.configCollection[IONOS.plugin.journey.index];
                    IONOS.plugin.journey.nextConfigItem = IONOS.plugin.journey.configItem
                        ? IONOS.plugin.journey.configCollection[IONOS.plugin.journey.configItem['next']]
                        : undefined;
                    IONOS.plugin.journey.target = IONOS.plugin.journey.nextConfigItem
                        ? document.body.querySelector(IONOS.plugin.journey.nextConfigItem['selector'])
                        : undefined;

                    IONOS.plugin.journey.configItem['behavior'] == 'click' ?
                        focus.classList.add('clickable') :
                        focus.classList.remove('clickable');
                } else if( IONOS.plugin.journey.urlParam instanceof Object && IONOS.plugin.journey.urlParam['wp_tour_return_index'] !== undefined ) {
                    window.location.href = decodeURIComponent(IONOS.plugin.journey.urlParam['wp_tour_last_page']) +
                        '?wp_tour=' + IONOS.plugin.journey.urlParam['wp_tour_return_index'] +
                        '&wp_tour_last_page=' + IONOS.plugin.journey.pageName +
                        '&wp_tour_last_index=' + IONOS.plugin.journey.index +
                        '&wp_tour_return_index=' + IONOS.plugin.journey.configItem['next'] || 'none';
                } else {
                    console.log('finished');
                    document.body.querySelector('#journeyStop').click();
                }
            };
        };
        this.enter = function() {
            if( IONOS.plugin.journey.configItem instanceof Object
                && IONOS.plugin.journey.configItem instanceof Object
                && IONOS.plugin.journey.configItem['behavior'] == 'click'
            ) {
                let source = document.body.querySelector(IONOS.plugin.journey.configItem['selector']);
                if(source) {
                    let url = source.querySelector('a').href;
                    let char = '&';
                    if (url.search(/[?]/) == -1) {
                        char = '?';
                    }
                    IONOS.plugin.journey.history.set();
                    window.location.href = source.querySelector('a').href + char + 'wp_tour=0&wp_tour_last_page=' + IONOS.plugin.journey.pageName + '&wp_tour_last_index=' + IONOS.plugin.journey.index + '&wp_tour_return_index=' + IONOS.plugin.journey.configItem['next'] || 'none';
                }
            }
        };
        this.stop = function () {
            console.log('Navigation: stop');
            window.location = IONOS.plugin.journey.pageName;
        };
        this.forward = function () {
            console.log('Navigation: next');

            if( IONOS.plugin.journey.focus instanceof Object
                && IONOS.plugin.journey.target instanceof HTMLElement
                && IONOS.plugin.journey.nextConfigItem instanceof Object
                && IONOS.plugin.journey.index
            ) {
                IONOS.plugin.journey.history.set();
                let focus = IONOS.plugin.journey.focus.get();
                IONOS.plugin.journey.movement.step(
                    focus,
                    IONOS.plugin.journey.target,
                    IONOS.plugin.journey.nextConfigItem,
                    IONOS.plugin.journey.index
                );

                IONOS.plugin.journey.index = IONOS.plugin.journey.configItem['next'];
                IONOS.plugin.journey.configItem = IONOS.plugin.journey.configCollection[IONOS.plugin.journey.index];
                IONOS.plugin.journey.nextConfigItem = IONOS.plugin.journey.configItem
                    ? IONOS.plugin.journey.configCollection[IONOS.plugin.journey.configItem['next']]
                    : undefined;
                IONOS.plugin.journey.target = IONOS.plugin.journey.nextConfigItem
                    ? document.body.querySelector(IONOS.plugin.journey.nextConfigItem['selector'])
                    : undefined;

                IONOS.plugin.journey.configItem['behavior'] == 'click' ?
                    focus.classList.add('clickable') :
                    focus.classList.remove('clickable');
            } else if( IONOS.plugin.journey.urlParam instanceof Object
                && IONOS.plugin.journey.urlParam['wp_tour_return_index'] != "null"
                && IONOS.plugin.journey.urlParam['wp_tour_return_index'] != undefined
            ) {

                IONOS.plugin.journey.history.set();
                window.location.href = decodeURIComponent(IONOS.plugin.journey.urlParam['wp_tour_last_page']) +
                    '?wp_tour=' + IONOS.plugin.journey.urlParam['wp_tour_return_index'] +
                    '&wp_tour_last_page=' + IONOS.plugin.journey.pageName +
                    '&wp_tour_last_index=' + IONOS.plugin.journey.index +
                    '&wp_tour_return_index=' + IONOS.plugin.journey.configItem['next'] || 'none';
            } else {
                console.log('finished');
                document.body.querySelector('#journeyStop').click();
            }
        };
        this.backwards = function () {
            let story = IONOS.plugin.journey.history.get();
            if( IONOS.plugin.journey.guide.guideIndex > 0
                && IONOS.plugin.journey.configItem instanceof Object
                && IONOS.plugin.journey.configItem['type'] == 'guide'
                && IONOS.plugin.journey.focus instanceof Object
            ) {
                IONOS.plugin.journey.guide.back(IONOS.plugin.journey.configItem, IONOS.plugin.journey.focus.get());
            }
            else if (Array.isArray(story) && story.length > 0) {
                let last_config = story.pop();
                if (last_config['page'] == window.location.pathname.split("/").pop()) {


                    if( IONOS.plugin.journey.focus instanceof Object
                        && IONOS.plugin.journey.configCollection instanceof Object
                        && IONOS.plugin.journey.index
                    ) {
                        let focus = IONOS.plugin.journey.focus.get();
                        let next = IONOS.plugin.journey.configCollection[last_config['index']]

                        IONOS.plugin.journey.focus.removeChildren(focus);
                        IONOS.plugin.journey.movement.step(
                            focus,
                            document.body.querySelector(next['selector']),
                            next,
                            IONOS.plugin.journey.index
                        );

                        IONOS.plugin.journey.index = last_config['index'];
                        IONOS.plugin.journey.configItem = IONOS.plugin.journey.configCollection[last_config['index']];
                        IONOS.plugin.journey.nextConfigItem = IONOS.plugin.journey.configItem
                            ? IONOS.plugin.journey.configCollection[IONOS.plugin.journey.configItem['next']]
                            : undefined;
                        IONOS.plugin.journey.target = IONOS.plugin.journey.nextConfigItem
                            ? document.body.querySelector(IONOS.plugin.journey.nextConfigItem['selector'])
                            : undefined;


                        IONOS.plugin.journey.configItem['behavior'] == 'click' ?
                            focus.classList.add('clickable') :
                            focus.classList.remove('clickable');

                        window.sessionStorage.setItem('ionos_journey_history', JSON.stringify(story));
                    }
                } else {
                    window.sessionStorage.setItem('ionos_journey_history',JSON.stringify(story));
                    window.location.href = last_config['page'] + '?' +
                        IONOS.plugin.journey.core.helper.changeQueryParameter(
                            '?wp_tour=0&wp_tour_last_page=index.php&wp_tour_last_index=3rd&wp_tour_return_index=4th',
                            'wp_tour',
                            last_config['index']
                        );

                }
            }
        };
    },

 */
