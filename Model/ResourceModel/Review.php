<?php

namespace DavidRobert\ResponseToReview\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;


class Review extends \Magento\Review\Model\ResourceModel\Review {

	protected function _afterSave( AbstractModel $object ) {
		$connection = $this->getConnection();
		/**
		 * save detail
		 */
		$detail   = [
			'title'    => $object->getTitle(),
			'detail'   => $object->getDetail(),
			'nickname' => $object->getNickname(),
			'response_review' => $object->getResponseReview()
		];
		$select   = $connection->select()->from( $this->_reviewDetailTable, 'detail_id' )->where( 'review_id = :review_id' );
		$detailId = $connection->fetchOne( $select, [ ':review_id' => $object->getId() ] );

		if ( $detailId ) {
			$condition = [ "detail_id = ?" => $detailId ];
			$connection->update( $this->_reviewDetailTable, $detail, $condition );
		} else {
			$detail['store_id']    = $object->getStoreId();
			$detail['customer_id'] = $object->getCustomerId();
			$detail['review_id']   = $object->getId();
			$connection->insert( $this->_reviewDetailTable, $detail );
		}

		/**
		 * save stores
		 */
		$stores = $object->getStores();
		if ( ! empty( $stores ) ) {
			$condition = [ 'review_id = ?' => $object->getId() ];
			$connection->delete( $this->_reviewStoreTable, $condition );

			$insertedStoreIds = [];
			foreach ( $stores as $storeId ) {
				if ( in_array( $storeId, $insertedStoreIds ) ) {
					continue;
				}

				$insertedStoreIds[] = $storeId;
				$storeInsert        = [ 'store_id' => $storeId, 'review_id' => $object->getId() ];
				$connection->insert( $this->_reviewStoreTable, $storeInsert );
			}
		}

		// reaggregate ratings, that depend on this review
		$this->_aggregateRatings( $this->_loadVotedRatingIds( $object->getId() ), $object->getEntityPkValue() );

		return $this;
	}
}