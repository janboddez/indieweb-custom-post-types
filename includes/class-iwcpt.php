<?php
/**
 * Main plugin class.
 *
 * @package IWCPT
 */

namespace IWCPT;

/**
 * Main plugin class.
 */
class IWCPT {
	/**
	 * This class's single instance.
	 *
	 * @var IWCPT $instance Plugin instance.
	 */
	private static $instance;

	/**
	 * Returns the single instance of this class.
	 *
	 * @return IWCPT This class's single instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Hooks and such.
	 */
	public function register() {
		register_activation_hook( dirname( dirname( __FILE__ ) ) . '/indieweb-custom-post-types.php', array( $this, 'activate' ) );
		register_deactivation_hook( dirname( dirname( __FILE__ ) ) . '/indieweb-custom-post-types.php', array( $this, 'deactivate' ) );

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ), 9 );

		add_filter( 'micropub_post_type', array( $this, 'set_post_type' ), 10, 2 );
		add_filter( 'wp_insert_post_data', array( $this, 'set_title' ), 10, 2 );
		add_filter( 'wp_insert_post_data', array( $this, 'set_slug' ), 11, 2 );

		remove_all_actions( 'do_feed_rss2' );
		add_action( 'do_feed_rss2', array( $this, 'rss' ), 20 );
	}

	/**
	 * Registers custom post types.
	 */
	public function register_post_types() {
		// Notes.
		register_post_type(
			'iwcpt_note',
			array(
				'labels'            => array(
					'name'               => __( 'Notes', 'iwcpt' ),
					'singular_name'      => __( 'Note', 'iwcpt' ),
					'add_new'            => __( 'New Note', 'iwcpt' ),
					'add_new_item'       => __( 'Add New Note', 'iwcpt' ),
					'edit_item'          => __( 'Edit Note', 'iwcpt' ),
					'view_item'          => __( 'View Note', 'iwcpt' ),
					'view_items'         => __( 'View Notes', 'iwcpt' ),
					'search_items'       => __( 'Search Notes', 'iwcpt' ),
					'not_found'          => __( 'No notes found.', 'iwcpt' ),
					'not_found_in_trash' => __( 'No notes found in trash.', 'iwcpt' ),
				),
				'public'            => true,
				'has_archive'       => true,
				'show_in_nav_menus' => true,
				'rewrite'           => array(
					'slug'       => __( 'notes', 'iwcpt' ),
					'with_front' => false,
				),
				'supports'          => array( 'author', 'title', 'editor', 'thumbnail', 'custom-fields', 'comments' ),
				'menu_icon'         => 'dashicons-format-status',
			)
		);

		// Likes.
		register_post_type(
			'iwcpt_like',
			array(
				'labels'            => array(
					'name'               => __( 'Likes', 'iwcpt' ),
					'singular_name'      => __( 'Like', 'iwcpt' ),
					'add_new'            => __( 'New Like', 'iwcpt' ),
					'add_new_item'       => __( 'Add New Like', 'iwcpt' ),
					'edit_item'          => __( 'Edit Like', 'iwcpt' ),
					'view_item'          => __( 'View Like', 'iwcpt' ),
					'view_items'         => __( 'View Likes', 'iwcpt' ),
					'search_items'       => __( 'Search Likes', 'iwcpt' ),
					'not_found'          => __( 'No likes found.', 'iwcpt' ),
					'not_found_in_trash' => __( 'No likes found in trash.', 'iwcpt' ),
				),
				'public'            => true,
				'has_archive'       => true,
				'show_in_nav_menus' => true,
				'rewrite'           => array(
					'slug'       => __( 'likes', 'iwcpt' ),
					'with_front' => false,
				),
				'supports'          => array( 'author', 'title', 'editor', 'custom-fields' ),
				'menu_icon'         => 'dashicons-heart',
			)
		);
	}

	/**
	 * Registers custom taxonomies.
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_taxonomy/#comment-2274
	 */
	public function register_taxonomies() {
		$args = array(
			'labels'                => array(
				'name'                       => __( 'Tags', 'iwcpt' ),
				'singular_name'              => __( 'Tag', 'iwcpt' ),
				'search_items'               => __( 'Search Tags', 'iwcpt' ),
				'popular_items'              => __( 'Popular Tags', 'iwcpt' ),
				'all_items'                  => __( 'All Tags', 'iwcpt' ),
				'edit_item'                  => __( 'Edit Tag', 'iwcpt' ),
				'update_item'                => __( 'Update Tag', 'iwcpt' ),
				'add_new_item'               => __( 'Add New Tag', 'iwcpt' ),
				'new_item_name'              => __( 'New Tag Name', 'iwcpt' ),
				'separate_items_with_commas' => __( 'Separate tags with commas', 'iwcpt' ),
				'add_or_remove_items'        => __( 'Add or remove tags', 'iwcpt' ),
				'choose_from_most_used'      => __( 'Choose from the most used tags', 'iwcpt' ),
				'not_found'                  => __( 'No tags found.', 'iwcpt' ),
			),
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array(
				'slug'       => __( 'notes/tag', 'iwcpt' ),
				'with_front' => false,
			),
		);

		register_taxonomy( 'iwcpt_tag', array( 'iwcpt_note' ), $args );
	}

	/**
	 * Registers permalinks on activation.
	 */
	public function activate() {
		$this->register_taxonomies();
		$this->register_post_types();
		flush_rewrite_rules();
	}

	/**
	 * Flushes permalinks on deactivation.
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Maps Micropub entries to a Custom Post Type.
	 *
	 * @param  string $post_type Post type.
	 * @param  array  $input     Input data.
	 * @return string            Post type slug.
	 */
	public function set_post_type( $post_type, $input ) {
		if ( ! empty( $input['properties']['like-of'][0] ) ) {
			$post_type = 'iwcpt_like';
		} elseif ( ! empty( $input['properties']['bookmark-of'][0] ) ) {
			$post_type = 'iwcpt_note';
		} elseif ( ! empty( $input['properties']['repost-of'][0] ) ) {
			$post_type = 'iwcpt_note';
		} elseif ( ! empty( $input['properties']['in-reply-to'][0] ) && false === stripos( (string) $input['properties']['in-reply-to'][0], $post_content ) ) {
			$post_type = 'iwcpt_note';
		} elseif ( ! empty( $input['properties']['content'][0] ) && empty( $input['post_title'] ) ) {
			$post_type = 'iwcpt_note';
		}

		return $post_type;
	}

	/**
	 * Sets a random slug for short-form content.
	 *
	 * @param  array $data    Filtered data.
	 * @param  array $postarr Original data.
	 * @return array          Updated (slashed) data.
	 */
	public function set_slug( $data, $postarr ) {
		if ( ! empty( $postarr['ID'] ) ) {
			// Not a new post. Bail.
			return $data;
		}

		if ( ! in_array( $data['post_type'], array( 'iwcpt_like', 'iwcpt_note' ), true ) ) {
			return $data;
		}

		global $wpdb;

		// Generate a random slug for short-form content, i.e., notes and likes.
		do {
			// Generate random slug.
			$slug = bin2hex( openssl_random_pseudo_bytes( 5 ) );

			// Check uniqueness.
			$result = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM $wpdb->posts WHERE post_name = %s LIMIT 1", $slug ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		} while ( $result );

		$data['post_name'] = $slug;

		return $data;
	}

	/**
	 * Automatically sets a post title for short-form content, so that it's easier
	 * to browse within WP Admin.
	 *
	 * The one exception is bookmarks, which often _do_ have an actual title.
	 *
	 * @param  array $data    Filtered data.
	 * @param  array $postarr Original data, mostly.
	 * @return array          Updated (slashed) data.
	 */
	public function set_title( $data, $postarr ) {
		if ( ! in_array( $data['post_type'], array( 'iwcpt_like', 'iwcpt_note' ), true ) ) {
			return $data;
		}

		if ( ! empty( $postarr['meta_input']['mf2_bookmark-of'][0] ) && ! empty( $data['post_title'] ) && apply_filters( 'iwcpt_ignore_bookmark_titles', true ) ) {
			// Leave _non-empty_ bookmark titles alone. Use `add_filter( 'iwcpt_ignore_bookmark_titles', '__return_false' );` to reverse.
			return $data;
		}

		/*
		 * In all other cases, let's generate a post title off of the post's
		 * content.
		 */

		$title = wp_unslash( $data['post_content'] );

		/*
		 * Some default "filters." Use the `iwcpt_title` filter to undo or
		 * extend.
		 */

		// Remove HTML.
		$title = trim( wp_strip_all_tags( $title ) );
		// Avoid double-encoded characters.
		$title = html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// Wrap lines that start with `> ` in (double) quotes.
		$title = preg_replace( '/^> (.+)$/m', "\"$1\"", $title ); // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired
		// Prevent duplicate quotes.
		$title = str_replace( '""', '"', $title );
		$title = str_replace( '"“', '"', $title );
		$title = str_replace( '”"', '"', $title );

		// Collapse lines and remove excess whitespace.
		$title = preg_replace( '/\s+/', ' ', $title );

		// Shorten.
		$title = wp_trim_words( $title, 8, ' ...' );
		// Prevent duplicate ellipses.
		$title = str_replace( '... ...', '...', $title );
		$title = str_replace( '… ...', '...', $title );

		$title = wp_slash( $title );

		// Define a filter that allows others to do something else entirely.
		$data['post_title'] = apply_filters( 'iwcpt_title', $title, $data['post_title'], $data['post_content'] );

		return $data;
	}

	/**
	 * Hides note titles from RSS feeds.
	 */
	public function rss() {
		require_once dirname( dirname( __FILE__ ) ) . '/templates/feed-rss2.php';
	}
}
