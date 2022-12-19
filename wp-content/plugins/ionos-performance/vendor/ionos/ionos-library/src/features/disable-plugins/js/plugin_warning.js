class IONOS_Plugin_Deactivation_Warning {
    constructor() {
        this.html = '';
        if ( typeof plugin_deactivation_warning === 'object' && 'html' in plugin_deactivation_warning ) {
            this.html = plugin_deactivation_warning.html
        }
    }

    init( headline, body, primary, slug ) {
        if (!!document.getElementsByClassName('wp-toolbar')) {
            let deactivation_button = document.getElementById('deactivate-' + slug)
            if (!!deactivation_button) {
                let el = document.createElement('div')
                el.innerHTML = this.html

                el.getElementsByClassName('ionos_deactivation_warning')[0].classList.add(slug + '_ipdwf')
                el.getElementsByClassName('ionos_deactivation_warning_modal_header')[0].innerText = headline
                el.getElementsByClassName('ionos_deactivation_warning_modal_body')[0].innerHTML = body
                el.getElementsByClassName('ionos_deactivation_warning_modal_primary')[0].innerHTML = '<span>' + primary + '</span>'

                el.getElementsByClassName('ionos_deactivation_warning_modal_close_button')[0]
                    .addEventListener("click", (function (e) {
                        e.preventDefault()
                        e.stopImmediatePropagation()

                        document.getElementsByClassName('ionos_deactivation_warning ' + slug + '_ipdwf')[0].classList.add('hidden')
                    }))

                el.getElementsByClassName('ionos_deactivation_warning_modal_primary')[0]
                    .addEventListener("click", (function (e) {
                        e.preventDefault()
                        e.stopImmediatePropagation()

                        document.getElementsByClassName('ionos_deactivation_warning ' + slug + '_ipdwf')[0].classList.add('hidden')
                        window.location.href = deactivation_button.href
                    }))

                deactivation_button
                    .addEventListener("click", (function (e) {
                        e.preventDefault()
                        e.stopImmediatePropagation()

                        document.getElementsByClassName('ionos_deactivation_warning ' + slug + '_ipdwf')[0].classList.remove('hidden')
                    }))

                document.getElementById('wpwrap').append(el)

            }
        }
    }
}
