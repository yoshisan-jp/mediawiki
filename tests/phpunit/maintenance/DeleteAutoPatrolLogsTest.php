<?php

namespace MediaWiki\Tests\Maintenance;

use DeleteAutoPatrolLogs;

/**
 * @group Database
 * @covers DeleteAutoPatrolLogs
 */
class DeleteAutoPatrolLogsTest extends MaintenanceBaseTestCase {

	public function getMaintenanceClass() {
		return DeleteAutoPatrolLogs::class;
	}

	protected function setUp(): void {
		parent::setUp();

		$this->insertLoggingData();
	}

	private function insertLoggingData() {
		$dbw = $this->getDb();
		$logs = [];

		$comment = \MediaWiki\MediaWikiServices::getInstance()->getCommentStore()
			->createComment( $dbw, '' );

		// Manual patrolling
		$logs[] = [
			'log_type' => 'patrol',
			'log_action' => 'patrol',
			'log_actor' => 7251,
			'log_params' => '',
			'log_timestamp' => $dbw->timestamp( '20041223210426' ),
			'log_namespace' => NS_MAIN,
			'log_title' => 'DeleteAutoPatrolLogs',
			'log_comment_id' => $comment->id,
		];

		// Autopatrol #1
		$logs[] = [
			'log_type' => 'patrol',
			'log_action' => 'autopatrol',
			'log_actor' => 7252,
			'log_params' => '',
			'log_timestamp' => $dbw->timestamp( '20051223210426' ),
			'log_namespace' => NS_MAIN,
			'log_title' => 'DeleteAutoPatrolLogs',
			'log_comment_id' => $comment->id,
		];

		// Block
		$logs[] = [
			'log_type' => 'block',
			'log_action' => 'block',
			'log_actor' => 7253,
			'log_params' => '',
			'log_timestamp' => $dbw->timestamp( '20061223210426' ),
			'log_namespace' => NS_MAIN,
			'log_title' => 'DeleteAutoPatrolLogs',
			'log_comment_id' => $comment->id,
		];

		// Very old/ invalid patrol
		$logs[] = [
			'log_type' => 'patrol',
			'log_action' => 'patrol',
			'log_actor' => 7253,
			'log_params' => 'nanana',
			'log_timestamp' => $dbw->timestamp( '20061223210426' ),
			'log_namespace' => NS_MAIN,
			'log_title' => 'DeleteAutoPatrolLogs',
			'log_comment_id' => $comment->id,
		];

		// Autopatrol #2
		$logs[] = [
			'log_type' => 'patrol',
			'log_action' => 'autopatrol',
			'log_actor' => 7254,
			'log_params' => '',
			'log_timestamp' => $dbw->timestamp( '20071223210426' ),
			'log_namespace' => NS_MAIN,
			'log_title' => 'DeleteAutoPatrolLogs',
			'log_comment_id' => $comment->id,
		];

		// Autopatrol #3 old way
		$logs[] = [
			'log_type' => 'patrol',
			'log_action' => 'patrol',
			'log_actor' => 7255,
			'log_params' => serialize( [ '6::auto' => true ] ),
			'log_timestamp' => $dbw->timestamp( '20081223210426' ),
			'log_namespace' => NS_MAIN,
			'log_title' => 'DeleteAutoPatrolLogs',
			'log_comment_id' => $comment->id,
		];

		// Manual patrol #2 old way
		$logs[] = [
			'log_type' => 'patrol',
			'log_action' => 'patrol',
			'log_actor' => 7256,
			'log_params' => serialize( [ '6::auto' => false ] ),
			'log_timestamp' => $dbw->timestamp( '20091223210426' ),
			'log_namespace' => NS_MAIN,
			'log_title' => 'DeleteAutoPatrolLogs',
			'log_comment_id' => $comment->id,
		];

		// Autopatrol #4 very old way
		$logs[] = [
			'log_type' => 'patrol',
			'log_action' => 'patrol',
			'log_actor' => 7257,
			'log_params' => "9227851\n0\n1",
			'log_timestamp' => $dbw->timestamp( '20081223210426' ),
			'log_namespace' => NS_MAIN,
			'log_title' => 'DeleteAutoPatrolLogs',
			'log_comment_id' => $comment->id,
		];

		// Manual patrol #3 very old way
		$logs[] = [
			'log_type' => 'patrol',
			'log_action' => 'patrol',
			'log_actor' => 7258,
			'log_params' => "9227851\n0\n0",
			'log_timestamp' => $dbw->timestamp( '20091223210426' ),
			'log_namespace' => NS_MAIN,
			'log_title' => 'DeleteAutoPatrolLogs',
			'log_comment_id' => $comment->id,
		];

		$dbw->insert( 'logging', $logs );
	}

	public static function runProvider() {
		$allRows = [
			(object)[
				'log_type' => 'patrol',
				'log_action' => 'patrol',
				'log_actor' => '7251',
			],
			(object)[
				'log_type' => 'patrol',
				'log_action' => 'autopatrol',
				'log_actor' => '7252',
			],
			(object)[
				'log_type' => 'block',
				'log_action' => 'block',
				'log_actor' => '7253',
			],
			(object)[
				'log_type' => 'patrol',
				'log_action' => 'patrol',
				'log_actor' => '7253',
			],
			(object)[
				'log_type' => 'patrol',
				'log_action' => 'autopatrol',
				'log_actor' => '7254',
			],
			(object)[
				'log_type' => 'patrol',
				'log_action' => 'patrol',
				'log_actor' => '7255',
			],
			(object)[
				'log_type' => 'patrol',
				'log_action' => 'patrol',
				'log_actor' => '7256',
			],
			(object)[
				'log_type' => 'patrol',
				'log_action' => 'patrol',
				'log_actor' => '7257',
			],
			(object)[
				'log_type' => 'patrol',
				'log_action' => 'patrol',
				'log_actor' => '7258',
			],
		];

		$cases = [
			'dry run' => [
				$allRows,
				[ '--sleep', '0', '--dry-run', '-q' ]
			],
			'basic run' => [
				[
					$allRows[0],
					$allRows[2],
					$allRows[3],
					$allRows[5],
					$allRows[6],
					$allRows[7],
					$allRows[8],
				],
				[ '--sleep', '0', '-q' ]
			],
			'run with before' => [
				[
					$allRows[0],
					$allRows[2],
					$allRows[3],
					$allRows[4],
					$allRows[5],
					$allRows[6],
					$allRows[7],
					$allRows[8],
				],
				[ '--sleep', '0', '--before', '20060123210426', '-q' ]
			],
			'run with check-old' => [
				[
					$allRows[0],
					$allRows[1],
					$allRows[2],
					$allRows[3],
					$allRows[4],
					$allRows[6],
					$allRows[8],
				],
				[ '--sleep', '0', '--check-old', '-q' ]
			],
		];

		foreach ( $cases as $key => $case ) {
			yield $key . '-batch-size-1' => [
				$case[0],
				array_merge( $case[1], [ '--batch-size', '1' ] )
			];
			yield $key . '-batch-size-5' => [
				$case[0],
				array_merge( $case[1], [ '--batch-size', '5' ] )
			];
			yield $key . '-batch-size-1000' => [
				$case[0],
				array_merge( $case[1], [ '--batch-size', '1000' ] )
			];
		}
	}

	/**
	 * @dataProvider runProvider
	 */
	public function testRun( $expected, $args ) {
		$this->maintenance->loadWithArgv( $args );

		$this->maintenance->execute();

		$remainingLogs = $this->getDb()->newSelectQueryBuilder()
			->select( [ 'log_type', 'log_action', 'log_actor' ] )
			->from( 'logging' )
			->orderBy( 'log_id' )
			->caller( __METHOD__ )->fetchResultSet();

		$this->assertEquals( $expected, iterator_to_array( $remainingLogs, false ) );
	}

	public function testFromId() {
		$fromId = $this->getDb()->newSelectQueryBuilder()
			->select( 'log_id' )
			->from( 'logging' )
			->where( [ 'log_params' => 'nanana' ] )
			->fetchField();

		$this->maintenance->loadWithArgv( [ '--sleep', '0', '--from-id', strval( $fromId ), '-q' ] );

		$this->maintenance->execute();

		$remainingLogs = $this->getDb()->newSelectQueryBuilder()
			->select( [ 'log_type', 'log_action', 'log_actor' ] )
			->from( 'logging' )
			->orderBy( 'log_id' )
			->caller( __METHOD__ )->fetchResultSet();

		$deleted = [
			'log_type' => 'patrol',
			'log_action' => 'autopatrol',
			'log_actor' => '7254',
		];
		$notDeleted = [
			'log_type' => 'patrol',
			'log_action' => 'autopatrol',
			'log_actor' => '7252',
		];

		$remainingLogs = array_map(
			static function ( $val ) {
				return (array)$val;
			},
			iterator_to_array( $remainingLogs, false )
		);

		$this->assertNotContains( $deleted, $remainingLogs );
		$this->assertContains( $notDeleted, $remainingLogs );
	}

}
