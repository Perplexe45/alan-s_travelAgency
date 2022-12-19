<?php

namespace Assistant\Wizard;

class View_Helper {

	public static function print_hidden_fields( $fields ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $_GET[ Manager::STATE_INPUT_NAMES[ $field ] ] ) ) {
				continue;
			}

			$value = $_GET[ Manager::STATE_INPUT_NAMES[ $field ] ];

			if ( is_array( $value ) ) {
				self::print_hidden_field_group( $field );
				continue;
			}

			self::print_hidden_input_field( $field, $value );
		}
	}

	private static function print_hidden_input_field( $name, $value ) {
		printf( '<input type="hidden" name="%s" value="%s">', $name, $value );
	}

	private static function print_hidden_field_group( $field_name ) {
		foreach ( $_GET[ Manager::STATE_INPUT_NAMES[ $field_name ] ] as $value ) {
			self::print_hidden_input_field( $field_name . '[]', $value );
		}
	}
}
