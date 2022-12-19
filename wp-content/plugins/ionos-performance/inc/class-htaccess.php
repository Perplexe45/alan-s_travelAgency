<?php
//phpcs:disable Generic.Commenting.DocComment

namespace Ionos\Performance;

use Ionos\Performance\Config;

class Htaccess {
	const SNIPPET_VERSIONS = array(
		'caching' => 'v2',
	);

	/**
	 * @var string $content Content of the .htaccess-file
	 */
	private $content;

	/**
	 * @var array $current_versions_from_htaccess
	 */
	private $current_versions_from_htaccess = array();

	/**
	 * @var bool $error;
	 */
	private $error = false;

	/**
	 * @var null|string $path Path to the .htaccess-file
	 */
	private $path;

	/**
	 * @var array $templates array of .htaccess snippet templates.
	 */
	private $templates = array();

	const HTACCESS_MARKERS = array(
		'caching' => array(
			'wrapper' => 'IONOS Performance Caching',
			'version' => 'IONOS Caching Snippet',
		),
	);

	const HTACCESS_MARKER_MIGRATIONS = array(
		'wrappers' => array(
			'IONOS_Performance' => self::HTACCESS_MARKERS['caching']['wrapper'],
		),
		'versions' => array(
			'IONOS_Performance Version:' => self::HTACCESS_MARKERS['caching']['version'],
		),
	);

	public function __construct() {
		$this->path  = trailingslashit( ABSPATH ) . '.htaccess';
		$is_readable = @is_readable( $this->path );
		if ( $is_readable ) {
			$this->content = file_get_contents( $this->path ); //phpcs:ignore WordPress.WP.AlternativeFunctions
			$this->read_versions();
			$this->read_templates();
			return;
		}

		$this->error = true;
	}

	/**
	 * @return void
	 */
	private function read_versions() {
		foreach ( self::HTACCESS_MARKERS as $key => $markers ) {
			if ( empty( $markers['version'] ) ) {
				continue;
			}

			$pattern_match = preg_match( '/# ' . preg_quote( $markers['version'] ) . ' ([^\r\n]*)/', $this->content, $matches );
			if ( $pattern_match ) {
				$this->current_versions_from_htaccess[ $key ] = next( $matches );
			}
		}

		if ( empty( self::HTACCESS_MARKER_MIGRATIONS['versions'] ) ) {
			return;
		}

		foreach ( self::HTACCESS_MARKER_MIGRATIONS['versions'] as $old_marker => $new_marker ) {
			$pattern_match = preg_match( '/# ' . preg_quote( $old_marker ) . ' ([^\r\n]*)/', $this->content, $matches );
			if ( ! $pattern_match ) {
				continue;
			}

			foreach ( self::HTACCESS_MARKERS as $key => $markers ) {
				if ( empty( $markers['version'] ) ) {
					continue;
				}

				if ( $new_marker === $markers['version'] ) {
					$this->current_versions_from_htaccess[ $key ] = next( $matches );
					break;
				}
			}
		}
	}

	/**
	 * @return void
	 */
	private function read_templates() {
		foreach ( self::HTACCESS_MARKERS as $key => $markers ) {
			$path = IONOS_PERFORMANCE_DIR . "/templates/template-htaccess-{$key}-snippet.tpl";

			$is_readable = @is_readable( $path );
			if ( $is_readable ) {
				$this->templates[ $key ] = trim( file_get_contents( $path ) );
			}
		}
	}

	/**
	 * @return bool
	 */
	private function snippets_exist() {
		foreach ( self::HTACCESS_MARKERS as $markers ) {
			if ( empty( $markers['wrapper'] ) ) {
				continue;
			}

			$pattern_match = preg_match_all( '/# START ' . preg_quote( $markers['wrapper'] ) . '(.|\n)*?# END ' . preg_quote( $markers['wrapper'] ) . '/', $this->content );
			if ( ! $pattern_match ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @return void
	 */
	private function remove_snippets() {
		if ( true === $this->error ) {
			return;
		}

		$is_writable = @is_writable( $this->path );
		if ( ! $is_writable ) {
			return;
		}

		$htaccess = $this->content;
		foreach ( self::HTACCESS_MARKERS as $markers ) {
			if ( empty( $markers['wrapper'] ) ) {
				continue;
			}

			$htaccess = preg_replace( '/# START ' . preg_quote( $markers['wrapper'] ) . '(.|\n)*?# END ' . preg_quote( $markers['wrapper'] ) . '(\n*)/', '', $htaccess );
		}

		$written_bytes = file_put_contents( $this->path, $htaccess );
		if ( $written_bytes ) {
			$this->content = $htaccess;
		}
	}

	/**
	 * @return void
	 */
	private function insert_snippets() {
		if ( true === $this->error ) {
			return;
		}

		$is_writable = @is_writable( $this->path );
		if ( ! $is_writable ) {
			return;
		}

		foreach ( self::HTACCESS_MARKERS as $key => $markers ) {
			if (
				empty( $markers['wrapper'] )
				|| empty( $markers['version'] )
				|| empty( $this->templates[ $key ] )
				|| empty( self::SNIPPET_VERSIONS[ $key ] )
			) {
				continue;
			}

			// Check if that snippet already exists in the .htaccess (currently, we check for all snippets
			// and have no granular option, so it could be the case that single snippets are in the .htaccess).
			$pattern_match = preg_match_all( '/# START ' . preg_quote( $markers['wrapper'] ) . '(.|\n)*?# END ' . preg_quote( $markers['wrapper'] ) . '/', $this->content );
			if ( $pattern_match ) {
				continue;
			}

			$version = self::SNIPPET_VERSIONS[ $key ];

			$snippet = $this->templates[ $key ];
			$snippet = \str_replace( '{{IONOS_PERFORMANCE_CACHE_DIR}}', IONOS_PERFORMANCE_CACHE_DIR, $snippet );
			$snippet = "# START {$markers['wrapper']}\n# {$markers['version']} {$version}\n{$snippet}\n# END {$markers['wrapper']}";
			$htaccess = "{$snippet}\n\n{$this->content}";
			$bytes_written = file_put_contents( $this->path, $htaccess );
			if ( false !== $bytes_written ) {
				$this->content = $htaccess;
			}
		}
	}

	/**
	 * If necessary adds or removes snippet from .htaccess
	 *
	 * @return void
	 */
	public function maybe_update() {
		$this->maybe_migrate();

		$remove_snippet = $this->should_remove_snippets();
		if ( $remove_snippet ) {
			$this->remove_snippets();
			Caching::clear_cache();
			return;
		}

		if ( did_action( 'deactivate_' . IONOS_PERFORMANCE_BASE ) ) {
			return;
		}

		$insert_snippet = $this->should_insert_snippets();
		if ( $insert_snippet ) {
			$this->insert_snippets();
			return;
		}

		$update_snippet = $this->should_update();
		if ( $update_snippet ) {
			$this->remove_snippets();
			$this->insert_snippets();
		}
	}

	/**
	 * Check if htaccess version is not up-to-date.
	 *
	 * @return bool
	 */
	private function should_update() {
		if ( true === $this->error ) {
			return false;
		}

		$feature_enabled = Config::get( 'features.enabled' );
		if ( ! $feature_enabled ) {
			return false;
		}

		$caching_enabled = Manager::get_option( 'caching_enabled' );
		if ( '0' === (string) $caching_enabled ) {
			return false;
		}

		$permalink_structure = get_option( 'permalink_structure' );
		if ( ! $permalink_structure ) {
			return false;
		}

		$has_conflicting_caching_plugins = Helper::has_conflicting_caching_plugins();
		if ( $has_conflicting_caching_plugins ) {
			return false;
		}

		foreach ( self::HTACCESS_MARKERS as $key => $markers ) {
			if ( empty( $markers['version'] ) || empty( self::SNIPPET_VERSIONS[ $key ] ) || empty( $this->current_versions_from_htaccess[ $key ] ) ) {
				continue;
			}

			$version_compare = version_compare( trim( self::SNIPPET_VERSIONS[ $key ] ), trim( $this->current_versions_from_htaccess[ $key ] ), '!=' );
			if ( $version_compare ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	private function should_remove_snippets() {
		if ( true === $this->error ) {
			return false;
		}

		$snippet_exists = $this->snippets_exist();
		if ( ! $snippet_exists ) {
			return false;
		}

		$feature_enabled = Config::get( 'features.enabled' );
		if ( ! $feature_enabled ) {
			return true;
		}

		$caching_enabled = Manager::get_option( 'caching_enabled' );
		if ( '0' === (string) $caching_enabled ) {
			return true;
		}

		$permalink_structure = get_option( 'permalink_structure' );
		if ( ! $permalink_structure ) {
			return true;
		}

		$has_conflicting_caching_plugins = Helper::has_conflicting_caching_plugins();
		if ( $has_conflicting_caching_plugins ) {
			return true;
		}

		$did_deactivate = did_action( 'deactivate_' . IONOS_PERFORMANCE_BASE );
		if ( $did_deactivate ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	private function should_insert_snippets() {
		if ( true === $this->error ) {
			return false;
		}

		$snippet_exists = $this->snippets_exist();
		if ( $snippet_exists ) {
			return false;
		}

		$feature_enabled = Config::get( 'features.enabled' );
		if ( ! $feature_enabled ) {
			return false;
		}

		$permalink_structure = get_option( 'permalink_structure' );
		if ( ! $permalink_structure ) {
			return false;
		}

		$has_conflicting_caching_plugins = Helper::has_conflicting_caching_plugins();
		if ( $has_conflicting_caching_plugins ) {
			return false;
		}

		$caching_enabled = Manager::get_option( 'caching_enabled' );
		if ( '0' === (string) $caching_enabled  ) {
			return false;
		}

		return true;
	}

	/**
	 * Update .htaccess when a conflicting plugin is activated or deactivated.
	 *
	 * @return void
	 */
	public function handle_plugin_changes() {
		// If the caching feature is disabled because of another active caching plugin, the .htaccess snippet
		// is removed, so we need to check if we have to add it again after a plugin has been deactivated.
		add_action(
			'update_option_active_plugins',
			array( $this, 'maybe_update' )
		);
	}

	/**
	 * @return void
	 */
	public function handle_activation() {
		$this->maybe_update();
	}

	/**
	 * @return void
	 */
	public function handle_deactivation() {
		$should_remove_snippet = $this->should_remove_snippets();
		if ( $should_remove_snippet ) {
			$this->remove_snippets();
		}
	}

	/**
	 * Migrate markers from old to new if needed.
	 *
	 * @return void
	 */
	private function maybe_migrate() {
		if ( true === $this->error ) {
			return;
		}

		$migrated = false;
		$htaccess = $this->content;
		foreach ( self::HTACCESS_MARKER_MIGRATIONS as $type => $migrations ) {
			if ( ! in_array( $type, array( 'wrappers', 'versions' ) ) ) {
				continue;
			}

			foreach ( $migrations as $old_version => $new_version ) {
				if ( false === strpos( $htaccess, $old_version ) ) {
					continue;
				}

				$htaccess = str_replace( $old_version, $new_version, $htaccess );
				$migrated = true;
			}
		}

		if ( ! $migrated ) {
			return;
		}

		$bytes_written = file_put_contents( $this->path, $htaccess );
		if ( $bytes_written ) {
			$this->content = $htaccess;
		}
	}

	/**
	 * @return string
	 */
	public static function get_caching_version() {
		return self::SNIPPET_VERSIONS['caching'];
	}
}
