<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UE_Core {

	const BATCH_SIZE = 1000;

	public static function get_public_post_types() {
		$types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $types['attachment'] );

		$result = array();
		foreach ( $types as $type ) {
			$count     = wp_count_posts( $type->name );
			$published = isset( $count->publish ) ? (int) $count->publish : 0;
			$result[]  = array(
				'name'      => $type->name,
				'label'     => $type->labels->name,
				'published' => $published,
			);
		}
		return $result;
	}

	public static function count_total( $post_types ) {
		global $wpdb;
		if ( empty( $post_types ) ) {
			return 0;
		}
		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		$sql = "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ({$placeholders})";
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $post_types ) );
	}

	public static function get_batch_ids( $post_types, $offset, $limit ) {
		global $wpdb;
		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		$sql  = "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ({$placeholders}) ORDER BY ID ASC LIMIT %d OFFSET %d";
		$args = array_merge( $post_types, array( $limit, $offset ) );
		return $wpdb->get_col( $wpdb->prepare( $sql, $args ) );
	}

	public static function job_dir() {
		$upload_dir = wp_upload_dir();
		$dir = trailingslashit( $upload_dir['basedir'] ) . 'url-exporter';
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
			file_put_contents( $dir . '/index.html', '' );
		}
		return $dir;
	}

	public static function job_file( $job_id ) {
		return self::job_dir() . '/export-' . sanitize_file_name( $job_id ) . '.csv';
	}

	public static function job_url( $job_id ) {
		$upload_dir = wp_upload_dir();
		return trailingslashit( $upload_dir['baseurl'] ) . 'url-exporter/export-' . sanitize_file_name( $job_id ) . '.csv';
	}

	public static function start_job( $langs ) {
		$job_id = wp_generate_password( 16, false, false );
		$file   = self::job_file( $job_id );

		$handle = fopen( $file, 'w' );
		$header = array( 'ID', 'post_type', 'title', 'url', 'lang_code' );
		fputcsv( $handle, $header );
		fclose( $handle );

		return $job_id;
	}

	/**
	 * Procesa un lote de posts: obtiene sus permalinks y, si hay códigos
	 * de idioma configurados, genera además una fila por cada variante
	 * (ej. /es/mi-video, /en/mi-video...), además de la URL original.
	 */
	public static function process_batch( $job_id, $post_types, $offset, $langs ) {
		$ids = self::get_batch_ids( $post_types, $offset, self::BATCH_SIZE );

		if ( empty( $ids ) ) {
			return array( 'processed' => 0, 'next_offset' => $offset );
		}

		$file   = self::job_file( $job_id );
		$handle = fopen( $file, 'a' );
		$home   = trailingslashit( get_option( 'home' ) );

		foreach ( $ids as $id ) {
			$post = get_post( $id );
			if ( ! $post ) {
				continue;
			}

			$permalink = get_permalink( $post );

			// Fila del idioma/URL original.
			fputcsv( $handle, array( $id, $post->post_type, $post->post_title, $permalink, '(original)' ) );

			if ( ! empty( $langs ) && strpos( $permalink, $home ) === 0 ) {
				$rest = substr( $permalink, strlen( $home ) );
				foreach ( $langs as $code ) {
					$variant_url = $home . $code . '/' . $rest;
					fputcsv( $handle, array( $id, $post->post_type, $post->post_title, $variant_url, $code ) );
				}
			}
		}

		fclose( $handle );

		return array( 'processed' => count( $ids ), 'next_offset' => $offset + count( $ids ) );
	}
}
