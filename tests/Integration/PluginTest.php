<?php
/**
 * Integration tests for the plugin.
 *
 * @package Automattic\BuddyPressProfileTypeAssigner\Tests\Integration
 */

namespace Automattic\BuddyPressProfileTypeAssigner\Tests\Integration;

/**
 * Test case for plugin integration.
 */
class PluginTest extends TestCase {

	/**
	 * Test that the plugin is loaded.
	 */
	public function test_plugin_loaded(): void {
		$this->assertTrue(
			function_exists( 'bp_profile_type_assigner_init' )
			|| class_exists( 'BP_Profile_Type_Assigner' )
		);
	}
}
