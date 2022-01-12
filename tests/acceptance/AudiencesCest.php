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
		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->activatePlugin( 'altis-analytics-demo-tools' );
		$I->amOnAdminPage( 'tools.php?page=analytics-demo' );
		$I->waitForElementClickable( 'input[name="altis-analytics-demo-fortnight"]' );
		$I->click( 'input[name="altis-analytics-demo-fortnight"]' );
		$I->waitForElement( 'p.success', 300 ); // Import takes a long time.
		$I->seePostInDatabase( [
			'post_type' => 'audience',
			'post_name' => 'France',
		] );
		$I->seePostInDatabase( [
			'post_type' => 'audience',
			'post_name' => 'Japan',
		] );
	}

}
