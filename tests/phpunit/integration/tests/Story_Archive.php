<?php

declare(strict_types = 1);

/**
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Web_Stories\Tests\Integration;

use Google\Web_Stories\Settings;
use Google\Web_Stories\Story_Archive as Testee;
use Google\Web_Stories\Story_Post_Type;
use WP_UnitTest_Factory;

/**
 * @coversDefaultClass \Google\Web_Stories\Story_Archive
 */
class Story_Archive extends DependencyInjectedTestCase {
	/**
	 * Admin user for test.
	 */
	protected static int $admin_id;

	/**
	 * Story id.
	 */
	protected static int $story_id;

	/**
	 * Archive page ID.
	 */
	protected static int $archive_page_id;

	/**
	 * Test instance.
	 */
	protected Testee $instance;

	private Settings $settings;

	private Story_Post_Type $story_post_type;

	protected string $redirect_location = '';

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ): void {
		self::$admin_id = $factory->user->create(
			[ 'role' => 'administrator' ]
		);

		self::$story_id = $factory->post->create(
			[
				'post_type'    => Story_Post_Type::POST_TYPE_SLUG,
				'post_title'   => 'Story_Post_Type Test Story',
				'post_status'  => 'publish',
				'post_content' => 'Example content',
				'post_author'  => self::$admin_id,
			]
		);

		/**
		 * @var int $poster_attachment_id
		 */
		$poster_attachment_id = $factory->attachment->create_object(
			[
				'file'           => DIR_TESTDATA . '/images/canola.jpg',
				'post_parent'    => 0,
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'Test Image',
			]
		);

		set_post_thumbnail( self::$story_id, $poster_attachment_id );

		self::$archive_page_id = self::factory()->post->create( [ 'post_type' => 'page' ] );
	}

	public function set_up(): void {
		parent::set_up();

		$this->settings        = $this->injector->make( Settings::class );
		$this->story_post_type = new Story_Post_Type( $this->settings );
		$this->instance        = new Testee( $this->settings, $this->story_post_type );

		add_filter( 'wp_redirect', [ $this, 'filter_wp_redirect' ] );
	}

	public function tear_down(): void {
		$this->redirect_location = '';
		remove_filter( 'wp_redirect', [ $this, 'filter_wp_redirect' ] );

		delete_option( $this->settings::SETTING_NAME_ARCHIVE );
		delete_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID );

		parent::tear_down();
	}

	public function filter_wp_redirect( string $location ): bool {
		$this->redirect_location = $location;

		return false;
	}

	/**
	 * @covers ::register
	 */
	public function test_register(): void {
		$this->instance->register();


		$this->assertSame( 10, has_filter( 'pre_handle_404', [ $this->instance, 'redirect_post_type_archive_urls' ] ) );
		$this->assertSame( 10, has_action( 'wp_trash_post', [ $this->instance, 'on_remove_archive_page' ] ) );
		$this->assertSame( 10, has_action( 'delete_post', [ $this->instance, 'on_remove_archive_page' ] ) );

		$this->assertSame( 10, has_action( 'add_option_' . $this->settings::SETTING_NAME_ARCHIVE, [ $this->instance, 'update_archive_setting' ] ) );
		$this->assertSame( 10, has_action( 'update_option_' . $this->settings::SETTING_NAME_ARCHIVE, [ $this->instance, 'update_archive_setting' ] ) );
		$this->assertSame( 10, has_action( 'add_option_' . $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, [ $this->instance, 'update_archive_setting' ] ) );
		$this->assertSame( 10, has_action( 'update_option_' . $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, [ $this->instance, 'update_archive_setting' ] ) );

		$this->assertSame( 10, has_filter( 'display_post_states', [ $this->instance, 'filter_display_post_states' ] ) );
		$this->assertSame( 10, has_action( 'pre_get_posts', [ $this->instance, 'pre_get_posts' ] ) );
	}


	/**
	 * @covers ::pre_get_posts
	 */
	public function test_pre_get_posts_default_archive(): void {
		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'default' );

		$archive_link = (string) get_post_type_archive_link( Story_Post_Type::POST_TYPE_SLUG );

		$this->go_to( $archive_link );

		delete_option( $this->settings::SETTING_NAME_ARCHIVE );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive' );
	}

	/**
	 * @covers ::pre_get_posts
	 */
	public function test_pre_get_posts_custom_archive(): void {
		$this->set_permalink_structure( '/%postname%/' );

		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, self::$archive_page_id );

		$this->story_post_type->register_post_type();

		$archive_link = (string) get_post_type_archive_link( Story_Post_Type::POST_TYPE_SLUG );

		$this->go_to( $archive_link );

		delete_option( $this->settings::SETTING_NAME_ARCHIVE );
		delete_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID );

		$this->assertQueryTrue( 'is_page', 'is_singular' );
	}

	/**
	 * @covers ::on_remove_archive_page
	 */
	public function test_on_remove_archive_page_trash(): void {
		$archive_page_id = self::factory()->post->create( [ 'post_type' => 'page' ] );

		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, $archive_page_id );

		wp_delete_post( $archive_page_id );

		$archive = $this->settings->get_setting( $this->settings::SETTING_NAME_ARCHIVE );

		$archive_page_id = $this->settings->get_setting( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID );

		$this->assertIsString( $archive );
		$this->assertSame( 'default', $archive );
		$this->assertSame( 0, $archive_page_id );
	}

	/**
	 * @covers ::on_remove_archive_page
	 */
	public function test_on_remove_archive_page_delete(): void {
		$archive_page_id = self::factory()->post->create( [ 'post_type' => 'page' ] );

		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, $archive_page_id );

		wp_delete_post( $archive_page_id, true );

		$archive = $this->settings->get_setting( $this->settings::SETTING_NAME_ARCHIVE );
		/**
		 * @var int $archive_page_id
		 */
		$archive_page_id = $this->settings->get_setting( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID );

		$this->assertIsString( $archive );
		$this->assertSame( 'default', $archive );
		$this->assertSame( 0, $archive_page_id );
	}

	/**
	 * @covers ::pre_get_posts
	 */
	public function test_pre_get_posts_custom_archive_not_published(): void {
		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, self::$archive_page_id );

		wp_update_post(
			[
				'ID'          => self::$archive_page_id,
				'post_status' => 'draft',
			]
		);

		$archive_link = (string) get_post_type_archive_link( Story_Post_Type::POST_TYPE_SLUG );

		$this->go_to( $archive_link );

		delete_option( $this->settings::SETTING_NAME_ARCHIVE );
		delete_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID );

		wp_update_post(
			[
				'ID'          => self::$archive_page_id,
				'post_status' => 'publish',
			]
		);

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive' );
	}

	/**
	 * @covers ::filter_display_post_states
	 */
	public function test_filter_display_post_states(): void {
		$archive_page = get_post( self::$archive_page_id );
		$this->assertNotNull( $archive_page );

		$actual = $this->instance->filter_display_post_states( [], $archive_page );

		$this->assertSame( [], $actual );
	}

	/**
	 * @covers ::filter_display_post_states
	 */
	public function test_filter_display_post_states_custom_archive(): void {
		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, self::$archive_page_id );

		$archive_page = get_post( self::$archive_page_id );
		$this->assertNotNull( $archive_page );

		$actual = $this->instance->filter_display_post_states( [], $archive_page );

		delete_option( $this->settings::SETTING_NAME_ARCHIVE );
		delete_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID );

		$this->assertEqualSetsWithIndex(
			[
				'web_stories_archive_page' => __( 'Web Stories Archive Page', 'web-stories' ),
			],
			$actual
		);
	}

	/**
	 * @covers ::filter_display_post_states
	 */
	public function test_filter_display_post_states_custom_archive_not_published(): void {
		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, self::$archive_page_id );

		wp_update_post(
			[
				'ID'          => self::$archive_page_id,
				'post_status' => 'draft',
			]
		);

		$archive_page = get_post( self::$archive_page_id );
		$this->assertNotNull( $archive_page );

		$actual = $this->instance->filter_display_post_states( [], $archive_page );

		delete_option( $this->settings::SETTING_NAME_ARCHIVE );
		delete_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID );

		wp_update_post(
			[
				'ID'          => self::$archive_page_id,
				'post_status' => 'publish',
			]
		);

		$this->assertSame( [], $actual );
	}

	/**
	 * @covers ::redirect_post_type_archive_urls
	 */
	public function test_redirect_post_type_archive_urls_experiment_disabled(): void {
		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, PHP_INT_MAX );

		$query  = new \WP_Query();
		$result = $this->instance->redirect_post_type_archive_urls( true, $query );

		$this->assertTrue( $result );
		$this->assertEmpty( $this->redirect_location );
	}

	/**
	 * @covers ::redirect_post_type_archive_urls
	 */
	public function test_redirect_post_type_archive_urls_bypass(): void {
		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, PHP_INT_MAX );

		$query  = new \WP_Query();
		$result = $this->instance->redirect_post_type_archive_urls( true, $query );

		$this->assertTrue( $result );
		$this->assertEmpty( $this->redirect_location );
	}

	/**
	 * @covers ::redirect_post_type_archive_urls
	 */
	public function test_redirect_post_type_archive_urls_ugly_permalinks(): void {
		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, PHP_INT_MAX );

		// Needed so that the archive page change takes effect.
		$this->story_post_type->register_post_type();

		$query  = new \WP_Query();
		$result = $this->instance->redirect_post_type_archive_urls( false, $query );

		$this->assertFalse( $result );
		$this->assertEmpty( $this->redirect_location );
	}

	/**
	 * @covers ::redirect_post_type_archive_urls
	 */
	public function test_redirect_post_type_archive_urls_pretty_permalinks(): void {
		$this->set_permalink_structure( '/%postname%/' );

		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, PHP_INT_MAX );

		// Needed so that the archive page change takes effect.
		$this->story_post_type->register_post_type();

		$query  = new \WP_Query();
		$result = $this->instance->redirect_post_type_archive_urls( false, $query );

		$this->assertFalse( $result );
		$this->assertEmpty( $this->redirect_location );
	}

	/**
	 * @covers ::redirect_post_type_archive_urls
	 */
	public function test_redirect_post_type_archive_urls_page(): void {
		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, PHP_INT_MAX );

		// Needed so that the archive page change takes effect.
		$this->story_post_type->register_post_type();

		$query                    = new \WP_Query();
		$query->query['pagename'] = $this->story_post_type::REWRITE_SLUG;
		$query->set( 'name', $this->story_post_type::REWRITE_SLUG );
		$query->set( 'page', self::$story_id );

		add_filter( 'post_type_link', '__return_false' );
		add_filter( 'post_type_archive_link', '__return_false' );

		$result = $this->instance->redirect_post_type_archive_urls( false, $query );

		remove_filter( 'post_type_link', '__return_false' );
		remove_filter( 'post_type_archive_link', '__return_false' );

		$this->assertFalse( $result );
		$this->assertEmpty( $this->redirect_location );
	}

	/**
	 * @covers ::redirect_post_type_archive_urls
	 */
	public function test_redirect_post_type_archive_urls_pagename_set(): void {
		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, PHP_INT_MAX );

		// Needed so that the archive page change takes effect.
		$this->story_post_type->register_post_type();

		$query                    = new \WP_Query();
		$query->query['pagename'] = $this->story_post_type::REWRITE_SLUG;
		$query->set( 'pagename', $this->story_post_type::REWRITE_SLUG );

		add_filter( 'post_type_archive_link', '__return_false' );

		$result = $this->instance->redirect_post_type_archive_urls( false, $query );

		remove_filter( 'post_type_archive_link', '__return_false' );

		$this->assertFalse( $result );
		$this->assertEmpty( $this->redirect_location );
	}

	/**
	 * @covers ::redirect_post_type_archive_urls
	 */
	public function test_redirect_post_type_archive_urls_existing_custom_page(): void {
		$this->set_permalink_structure( '/%postname%/' );

		update_option( $this->settings::SETTING_NAME_ARCHIVE, 'custom' );
		update_option( $this->settings::SETTING_NAME_ARCHIVE_PAGE_ID, self::$archive_page_id );

		// Needed so that the archive page change takes effect.
		$this->story_post_type->register_post_type();

		$query                    = new \WP_Query();
		$query->query['pagename'] = $this->story_post_type::REWRITE_SLUG;
		$query->set( 'pagename', $this->story_post_type::REWRITE_SLUG );

		$result = $this->instance->redirect_post_type_archive_urls( false, $query );

		$this->assertFalse( $result );
		$this->assertSame( get_permalink( self::$archive_page_id ), $this->redirect_location );
	}
}
