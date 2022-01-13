<?php
/**
 * Tests for analytics module's audiences feature.
 *
 * phpcs:disable WordPress.Files, WordPress.NamingConventions, PSR1.Classes.ClassDeclaration.MissingNamespace, HM.Functions.NamespacedFunctions
 */

/**
 * Audience tests.
 */
class AudiencesCest {

	/**
	 * Create demo data.
	 *
	 * @param AcceptanceTester $I Tester object.
	 * @return void
	 */
	public function importData( AcceptanceTester $I ) {
		$I->wantToTest( 'Analytics demo data import is successful.' );
		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->activatePlugin( 'altis-analytics-demo-tools' );
		$I->amOnAdminPage( 'tools.php?page=analytics-demo' );
		$I->waitForElementClickable( 'input[name="altis-analytics-demo-fortnight"]' );
		$I->click( 'input[name="altis-analytics-demo-fortnight"]' );
		$I->wait( 3 );
		$I->seePostInDatabase( [
			'post_type' => 'audience',
			'post_name' => 'France',
		] );
		$I->seePostInDatabase( [
			'post_type' => 'audience',
			'post_name' => 'Japan',
		] );

		// Run the background task manually.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		exec( 'WPBROWSER_HOST_REQUEST=1 wp cron event run altis_analytics_import_demo_data' );

		// Check contents of ES has at least 9000 rows from data import.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		exec( 'curl -XPOST http://elasticsearch:9200/analytics-*/_search?size=0', $output, $return );
		$I->assertEquals( 0, $return );
		$data = json_decode( $output[0] ?? '', true );
		$I->assertGreaterThan( 9000, $data['hits']['total']['value'] ?? $data['hits']['total'] ?? 0 );
	}

	/**
	 * Test the Audience Listing React UI.
	 *
	 * @param AcceptanceTester $I Tester object.
	 * @return void
	 */
	public function audienceListUI( AcceptanceTester $I ) {
		$I->wantToTest( 'Audience list UI works as expected.' );
		$I->loginAsAdmin();
		$I->amOnAdminPage( 'admin.php?page=audience' );
		$I->waitForElementClickable( '.row-title', 5 );
		$I->wait( 5 ); // React app needs to make API queries and re-render.
		$I->see( 'France', '.row-title' );
		$I->see( 'Japan', '.row-title' );
		$views = $I->grabTextFrom( 'tr:first-child .audience-estimate__count strong' );
		$I->assertTrue( is_numeric( $views ) );
		$I->assertGreaterThan( 1, (int) $views );
	}

}
