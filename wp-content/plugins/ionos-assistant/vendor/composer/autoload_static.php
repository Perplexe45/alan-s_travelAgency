<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdd9598ad936719ce0fed852824e82334
{
    public static $files = array (
        'd4de440df51fd829b41ef03ee02e0626' => __DIR__ . '/..' . '/ionos/assistant-feature-auto-update/load.php',
        '413411128f3e8f80edbd547d13ce4a03' => __DIR__ . '/..' . '/ionos/assistant-feature-auto-update/inc/class-manager.php',
        '629b2c1dd62e70f7d35fddbbd26b2319' => __DIR__ . '/..' . '/ionos/assistant-feature-banner/load.php',
        'e4bbe4c48b9177fad6858fce0e11d0d0' => __DIR__ . '/..' . '/ionos/assistant-feature-descriptify/load.php',
        'e13a226b3c3fcb0e9dded3fedeeb5961' => __DIR__ . '/..' . '/ionos/assistant-feature-descriptify/inc/class-manager.php',
        '129395000df21ae6f26dca2d338a45cc' => __DIR__ . '/..' . '/ionos/assistant-feature-jetpack-backup-flow/load.php',
        '64ff5886888f9e1bc9df8cb8887af00c' => __DIR__ . '/..' . '/ionos/assistant-feature-jetpack-backup-flow/inc/class-manager.php',
        '51d4dba1bb966fae9420101fa132230a' => __DIR__ . '/..' . '/ionos/assistant-feature-login-redesign/load.php',
        '8ed7a65e5d7299aea8b47a9cd0b5ee79' => __DIR__ . '/..' . '/ionos/assistant-feature-login-redesign/inc/class-manager.php',
        '3b8941ed77bf23f7a4423255f5dea763' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/load.php',
        '6245daceb82bcc5b37ade43c530561bc' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-manager.php',
    );

    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Ionos\\LoginRedirect\\' => 20,
            'Ionos\\HiddenAdminPage\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ionos\\LoginRedirect\\' => 
        array (
            0 => __DIR__ . '/..' . '/ionos/login-redirect/src',
        ),
        'Ionos\\HiddenAdminPage\\' => 
        array (
            0 => __DIR__ . '/..' . '/ionos/hidden-admin-page/src',
        ),
    );

    public static $classMap = array (
        'Assistant\\AutoUpdate\\Manager' => __DIR__ . '/..' . '/ionos/assistant-feature-auto-update/inc/class-manager.php',
        'Assistant\\Banner\\Branding' => __DIR__ . '/..' . '/ionos/assistant-feature-banner/inc/class-branding.php',
        'Assistant\\Banner\\Manager' => __DIR__ . '/..' . '/ionos/assistant-feature-banner/inc/class-manager.php',
        'Assistant\\Descriptify\\Manager' => __DIR__ . '/..' . '/ionos/assistant-feature-descriptify/inc/class-manager.php',
        'Assistant\\JetpackBackupFlow\\Controllers\\Confirm' => __DIR__ . '/..' . '/ionos/assistant-feature-jetpack-backup-flow/inc/controllers/class-confirm.php',
        'Assistant\\JetpackBackupFlow\\Controllers\\Install' => __DIR__ . '/..' . '/ionos/assistant-feature-jetpack-backup-flow/inc/controllers/class-install.php',
        'Assistant\\JetpackBackupFlow\\Controllers\\ViewController' => __DIR__ . '/..' . '/ionos/assistant-feature-jetpack-backup-flow/inc/controllers/interface-view-controller.php',
        'Assistant\\JetpackBackupFlow\\Manager' => __DIR__ . '/..' . '/ionos/assistant-feature-jetpack-backup-flow/inc/class-manager.php',
        'Assistant\\LoginRedesign\\Branding' => __DIR__ . '/..' . '/ionos/assistant-feature-login-redesign/inc/class-branding.php',
        'Assistant\\LoginRedesign\\Custom_CSS' => __DIR__ . '/..' . '/ionos/assistant-feature-login-redesign/inc/class-custom-css.php',
        'Assistant\\LoginRedesign\\Manager' => __DIR__ . '/..' . '/ionos/assistant-feature-login-redesign/inc/class-manager.php',
        'Assistant\\Wizard\\Controllers\\Abort_Plugin_Selection' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-abort-plugin-selection.php',
        'Assistant\\Wizard\\Controllers\\Completed' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-completed.php',
        'Assistant\\Wizard\\Controllers\\Install' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-install.php',
        'Assistant\\Wizard\\Controllers\\Plugin_Advertising' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-plugin-advertising.php',
        'Assistant\\Wizard\\Controllers\\Plugin_Selection' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-plugin-selection.php',
        'Assistant\\Wizard\\Controllers\\Summary' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-summary.php',
        'Assistant\\Wizard\\Controllers\\Theme_Preview' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-theme-preview.php',
        'Assistant\\Wizard\\Controllers\\Theme_Selection' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-theme-selection.php',
        'Assistant\\Wizard\\Controllers\\Use_Case_Selection' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-use-case-selection.php',
        'Assistant\\Wizard\\Controllers\\View_Controller' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/interface-view-controller.php',
        'Assistant\\Wizard\\Controllers\\Welcome' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/controllers/class-welcome.php',
        'Assistant\\Wizard\\Custom_CSS' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-custom-css.php',
        'Assistant\\Wizard\\Installer' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-installer.php',
        'Assistant\\Wizard\\Manager' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-manager.php',
        'Assistant\\Wizard\\Market_Helper' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-market-helper.php',
        'Assistant\\Wizard\\Request_Validator' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-request-validator.php',
        'Assistant\\Wizard\\Rest_Api' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-rest-api.php',
        'Assistant\\Wizard\\Theme' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-theme.php',
        'Assistant\\Wizard\\Use_Case' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-use-case.php',
        'Assistant\\Wizard\\View_Helper' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-view-helper.php',
        'Assistant\\Wizard\\Wp_Org_Api' => __DIR__ . '/..' . '/ionos/assistant-feature-wizard/inc/class-wp-org-api.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Ionos\\HiddenAdminPage\\HiddenAdminPage' => __DIR__ . '/..' . '/ionos/hidden-admin-page/src/HiddenAdminPage.php',
        'Ionos\\LoginRedirect\\LoginRedirect' => __DIR__ . '/..' . '/ionos/login-redirect/src/LoginRedirect.php',
        'Ionos\Assistant\\Config' => __DIR__ . '/..' . '/ionos/ionos-library/src/config.php',
        'Ionos\Assistant\\Data_Provider\\Cloud' => __DIR__ . '/..' . '/ionos/ionos-library/src/data-providers/cloud.php',
        'Ionos\Assistant\\Menu' => __DIR__ . '/..' . '/ionos/ionos-library/src/features/menu/class-menu.php',
        'Ionos\Assistant\\Meta' => __DIR__ . '/..' . '/ionos/ionos-library/src/meta.php',
        'Ionos\Assistant\\Options' => __DIR__ . '/..' . '/ionos/ionos-library/src/options.php',
        'Ionos\Assistant\\Updater' => __DIR__ . '/..' . '/ionos/ionos-library/src/updater.php',
        'Ionos\Assistant\\Warning' => __DIR__ . '/..' . '/ionos/ionos-library/src/features/disable-plugins/class-manager.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdd9598ad936719ce0fed852824e82334::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdd9598ad936719ce0fed852824e82334::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdd9598ad936719ce0fed852824e82334::$classMap;

        }, null, ClassLoader::class);
    }
}
