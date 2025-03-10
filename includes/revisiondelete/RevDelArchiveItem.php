<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup RevisionDelete
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionFactory;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * Item class for a archive table row
 */
class RevDelArchiveItem extends RevDelRevisionItem {
	protected static function initRevisionRecord( $list, $row ) {
		$revRecord = MediaWikiServices::getInstance()
			->getRevisionFactory()
			->newRevisionFromArchiveRow(
				$row,
				RevisionFactory::READ_NORMAL,
				null,
				[ 'page_id' => $list->getPage()->getId() ]
			);

		return $revRecord;
	}

	public function getIdField() {
		return 'ar_timestamp';
	}

	public function getTimestampField() {
		return 'ar_timestamp';
	}

	public function getAuthorIdField() {
		return 'ar_user';
	}

	public function getAuthorNameField() {
		return 'ar_user_text';
	}

	public function getAuthorActorField() {
		return 'ar_actor';
	}

	public function getId() {
		# Convert DB timestamp to MW timestamp
		return $this->revisionRecord->getTimestamp();
	}

	public function setBits( $bits ) {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		$dbw->newUpdateQueryBuilder()
			->update( 'archive' )
			->set( [ 'ar_deleted' => $bits ] )
			->where( [
				'ar_namespace' => $this->list->getPage()->getNamespace(),
				'ar_title' => $this->list->getPage()->getDBkey(),
				// use timestamp for index
				'ar_timestamp' => $this->row->ar_timestamp,
				'ar_rev_id' => $this->row->ar_rev_id,
				'ar_deleted' => $this->getBits()
			] )
			->caller( __METHOD__ )->execute();

		return (bool)$dbw->affectedRows();
	}

	protected function getRevisionLink() {
		$date = $this->list->getLanguage()->userTimeAndDate(
			$this->revisionRecord->getTimestamp(), $this->list->getUser() );

		if ( $this->isDeleted() && !$this->canViewContent() ) {
			return htmlspecialchars( $date );
		}

		return $this->getLinkRenderer()->makeLink(
			SpecialPage::getTitleFor( 'Undelete' ),
			$date,
			[],
			[
				'target' => $this->list->getPageName(),
				'timestamp' => $this->revisionRecord->getTimestamp()
			]
		);
	}

	protected function getDiffLink() {
		if ( $this->isDeleted() && !$this->canViewContent() ) {
			return $this->list->msg( 'diff' )->escaped();
		}

		return $this->getLinkRenderer()->makeLink(
			SpecialPage::getTitleFor( 'Undelete' ),
			$this->list->msg( 'diff' )->text(),
			[],
			[
				'target' => $this->list->getPageName(),
				'diff' => 'prev',
				'timestamp' => $this->revisionRecord->getTimestamp()
			]
		);
	}
}
