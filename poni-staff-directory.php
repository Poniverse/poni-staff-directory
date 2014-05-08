<?php

/*
Plugin Name: Staff Directory Page
Plugin URI: https://developer.poniverse.net
Description: Facilitates the easy management of a staff directory page.
Version: 1.0
Author: Poniverse
Author URI: https://poniverse.net
License: MIT
*/


register_activation_hook( __FILE__, 'poni_staff_rewrite_flush' );
add_action( 'init', 'poni_staff_create_post_type' );
add_action( 'add_meta_boxes', 'poni_staff_create_metaboxes' );
add_action( 'save_post', 'poni_staff_save' );


function poni_staff_create_post_type() {
	add_image_size( 'poni_staff_thumbnail', 200, 200, true );

	register_taxonomy( 'department', array( 'poni_staff_member' ), array(
		'labels'            => array(
			'name'              => _x( 'Departments', 'taxonomy general name' ),
			'singular_name'     => _x( 'Department', 'taxonomy singular name' ),
			'all_items'         => __( 'All Departments' ),
			'edit_item'         => __( 'Edit Department' ),
			'view_item'         => __( 'View Department' ),
			'update_item'       => __( 'Update Department' ),
			'add_new_item'      => __( 'Add New Department' ),
			'new_item_name'     => __( 'New Department Name' ),
			'parent_item'       => __( 'Parent Department' ),
			'parent_item_colon' => __( 'Parent Department:' ),
			'search_items'      => __( 'Search Departments' ),
		),
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'hierarchical'      => true,
	) );

	register_post_type( 'poni_staff_member',
		array(
			'labels'              => array(
				'name'               => _x( 'Staff Members', 'Post Type General Name' ),
				'singular_name'      => __( 'Staff Member', 'Post Type Singular Name' ),
				'add_new_item'       => __( 'Add New Staff Member' ),
				'edit_item'          => __( 'Edit Staff Member' ),
				'new_item'           => __( 'New Staff Member' ),
				'view_item'          => __( 'View Staff Member' ),
				'search_items'       => __( 'Search Staff' ),
				'not_found'          => __( 'No staff found' ),
				'not_found_in_trash' => __( 'No staff found in Trash' ),
			),
			'public'              => true,
			'exclude_from_search' => true,
			'show_in_nav_menus'   => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-groups',
			'capability_type'     => 'page',
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
				'revisions',
				'page-attributes',
			),
			'taxonomies'          => array( 'department' ),
			'has_archive'         => true,
			'rewrite'             => array(
				'slug'       => 'staff',
				'with_front' => false,
				'pages'      => false,
			),
			'query_var'           => false,
		)
	);
}


function poni_staff_rewrite_flush() {
	poni_staff_create_post_type();
	flush_rewrite_rules();
}

function poni_staff_create_metaboxes() {
	add_meta_box(
		'poni_staff_contact_metabox',
		__( 'Contact Info' ),
		'poni_staff_contact_metabox_content',
		'poni_staff_member',
		'side',
		'high'
	);
}

function poni_staff_contact_metabox_content( $post ) {
	wp_nonce_field( plugin_basename( __FILE__ ), 'poni_staff_contact_metabox_content_nonce' );

	$title      = get_post_meta( $post->ID, 'title', true );
	$email      = get_post_meta( $post->ID, 'email', true );
	$twitter    = get_post_meta( $post->ID, 'twitter', true );

	echo <<<EOF
	<p>If filled out, this will be shown on this staff member&#39;s profile in the listing.</p>
	<p>
		<label for="title">Title</label>
		<input type="text" id="title" name="title" placeholder="Head of Magical Excellence" value="{$title}" />
	</p>
	<p>
		<label for="email">Email</label>
		<input type="email" id="email" name="email" placeholder="twilight@sparkle.poni" value="{$email}" />
	</p>
	<p>
		<label for="twitter">Twitter</label>
		<input type="text" id="twitter" name="twitter" placeholder="username (no @)" value="{$twitter}" />
	</p>
EOF;
}


function poni_staff_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( ! wp_verify_nonce( $_POST['poni_staff_contact_metabox_content_nonce'], plugin_basename( __FILE__ ) ) )
		return;

	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return;
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;
	}

	update_post_meta( $post_id, 'title', $_POST['title'] );
	update_post_meta( $post_id, 'email', $_POST['email'] );
	update_post_meta( $post_id, 'twitter', $_POST['twitter'] );
}