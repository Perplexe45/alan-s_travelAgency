if( typeof ionos_plugin_deactivation_warning === 'undefined' && typeof plugin_deactivation_warning_call === 'object' ) {
    let ionos_plugin_deactivation_warning = new IONOS_Plugin_Deactivation_Warning()

    ionos_plugin_deactivation_warning.init(
        plugin_deactivation_warning_call.headline,
        plugin_deactivation_warning_call.body,
        plugin_deactivation_warning_call.primary,
        plugin_deactivation_warning_call.slug
    )
}