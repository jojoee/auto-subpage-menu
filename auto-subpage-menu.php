<?php
/*
Plugin Name: Auto Subpage Menu
Plugin URI: https://github.com/jojoee/auto-subpage-menu
Description: Automatically add child page into menus, update the menu hierarchy when page's updated and also remove page from menu when page's moved to trash
Version: 1.0
Author: Nathachai Thongniran
Author URI: http://jojoee.com/
Text Domain: asm
License: GPL2
*/

class Auto_Subpage_Menu {

	function __construct() {

		// when publish page
		add_action( 'publish_page', array( &$this, 'when_publish_page' ) );

		// when update page
		add_action( 'post_updated', array( &$this, 'when_update_page' ), 10, 3 );
		
		// when move page to trash
		add_action( 'wp_trash_post', '_wp_delete_post_menu_item' );

		// when restore page
		add_action( 'untrashed_post ', array( &$this, 'when_restore_page' ) );
	}

	/**
	 * Check the theme, support menu or not
	 * 
	 * @see https://codex.wordpress.org/Function_Reference/current_theme_supports
	 * 
	 * @return boolean
	 */
	function is_support_menus() {
		if ( ! current_theme_supports( 'menus' ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check the page, has page parent or not
	 * 
	 * @param	integer $page_id
	 * @return boolean
	 */
	function has_parent( $page_id ) {
		$post = get_post( $page_id );

		if ( ! $post->post_parent ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check the menu, set auto_add or not (Automatically add new top-level pages to this menu)
	 * 
	 * @see https://codex.wordpress.org/Appearance_Menus_Screen
	 * 
	 * @return boolean
	 */
	function has_auto_add() {
		$auto_add = get_option( 'nav_menu_options' );

		if ( ! is_array( $auto_add ) || empty( $auto_add ) || ! isset( $auto_add['auto_add'] ) || ! is_array( $auto_add['auto_add'] ) || empty( $auto_add['auto_add'] ) ) {
			return false;
		} else {
			return true;
		}
	}

	function get_auto_add() {
		$auto_add = get_option( 'nav_menu_options' );

		return $auto_add['auto_add'];
	}

	function remove_page_from_menu( $object_id ) {
		global $wpdb;
		$query = sprintf( 'DELETE FROM wp_term_relationships WHERE object_id = %d', $object_id );

		$wpdb->get_results( $query, OBJECT );
	}

	function remove_page_from_menus( $menu_ids, $page ) {
		foreach ( $menu_ids as $menu_id ) {
			$menu_items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'publish' ) );

			if ( ! is_array( $menu_items ) ) {
				continue;

			} else {
				foreach ( $menu_items as $menu_item ) {

					// if page's existed in menu, then remove it from menu
					if ( $menu_item->object_id == $page->ID ) { $this->remove_page_from_menu( $menu_item->ID ); }
				} 
			}
		}
	}

	function add_page_into_menu( $menu_id, $page_id, $post_type, $page_parent_id = 0 ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-object-id'   => $page_id,
			'menu-item-object'      => $post_type,
			'menu-item-parent-id'   => $page_parent_id,
			'menu-item-type'        => 'post_type',
			'menu-item-status'      => 'publish'
		) );
	}

	function add_page_into_submenu( $menu_id, $page, $page_parent ) {
		$this->add_page_into_menu( $menu_id, $page->ID, $page->post_type, $page_parent->ID );
	}

	function add_page_into_topmenu( $menu_id, $page ) {
		$this->add_page_into_menu( $menu_id, $page->ID, $page->post_type, 0 );
	}

	function update_submenu( $menu_ids, $page ) {

		// loop through each menu
		foreach ( $menu_ids as $menu_id ) {
			$page_parent = null;
			$menu_items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'publish' ) );

			if ( ! is_array( $menu_items ) ) {
				continue;

			} else {

				// loop through each menu item
				foreach ( $menu_items as $menu_item ) {

					// if page's existed in this menu, then ignore
					if ( $menu_item->object_id == $page->ID ) { continue; }

					// if page parent's existed in this menu, then get this menu item
					if ( $menu_item->object_id == $page->post_parent ) { $page_parent = $menu_item; }
				}
			}

			// if page has parent page, then add page into sub-menu under page parent
			if ( ! is_null( $page_parent ) ) { $this->add_page_into_submenu( $menu_id, $page, $page_parent ); }
		}
	}

	function update_topmenu( $menu_ids, $page ) {
		foreach ( $menu_ids as $menu_id ) { $this->add_page_into_topmenu( $menu_id, $page ); }
	}

	/**
	 * When publish a page then update the menu (add/relocate)
	 * 
	 * @param integer $post_id
	 */
	function when_publish_page( $page_id ) {
		if ( $this->is_support_menus() && $this->has_parent( $page_id ) && $this->has_auto_add() ) {
			$menu_ids = $this->get_auto_add();
			$page = get_post( $page_id );
			$this->update_submenu( $menu_ids, $page );
		}
	}

	function when_update_page( $page_id, $page_after, $page_before ) {

		// if parent page's changed and
		// theme support menus and
		// has auto_add
		if ( ( $page_after->post_parent != $page_before->post_parent ) && $this->is_support_menus() && $this->has_auto_add() ) {
			$page = $page_after;
			$menu_ids = $this->get_auto_add();
			$this->remove_page_from_menus( $menu_ids, $page );

			if ( $this->has_parent( $page_id ) ) {
				$this->update_submenu( $menu_ids, $page );
			} else {
				$this->update_topmenu( $menu_ids, $page );
			}
		}
	}

	function when_restore_page( $page_id ) {
		$this->when_publish_page( $page_id );
	}
}

$auto_subpage_menu = new Auto_Subpage_Menu();
