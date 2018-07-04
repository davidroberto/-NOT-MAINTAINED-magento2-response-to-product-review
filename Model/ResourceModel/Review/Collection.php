<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DavidRobert\ResponseToReview\Model\ResourceModel\Review;

use \Magento\Review\Model\ResourceModel\Review\Collection as CollectionCore;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends CollectionCore
{

	protected function _initSelect()
	{
		AbstractCollection::_initSelect();
		$this->getSelect()->join(
			['detail' => $this->getReviewDetailTable()],
			'main_table.review_id = detail.review_id',
			['detail_id', 'title', 'detail', 'nickname', 'customer_id', 'response_review']
		);
		return $this;
	}

}
