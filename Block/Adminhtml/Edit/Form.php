<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml Review Edit Form
 */
namespace DavidRobert\ResponseToReview\Block\Adminhtml\Edit;

use Magento\Review\Block\Adminhtml\Edit\Form as FormCore;
use \Magento\Backend\Block\Widget\Form\Generic;

class Form extends FormCore
{

	protected function _prepareForm()
	{
		$review = $this->_coreRegistry->registry('review_data');
		$product = $this->_productFactory->create()->load($review->getEntityPkValue());

		/** @var \Magento\Framework\Data\Form $form */
		$form = $this->_formFactory->create(
			[
				'data' => [
					'id' => 'edit_form',
					'action' => $this->getUrl(
						'review/*/save',
						[
							'id' => $this->getRequest()->getParam('id'),
							'ret' => $this->_coreRegistry->registry('ret')
						]
					),
					'method' => 'post',
				],
			]
		);

		$fieldset = $form->addFieldset(
			'review_details',
			['legend' => __('Review Details'), 'class' => 'fieldset-wide']
		);

		$fieldset->addField(
			'product_name',
			'note',
			[
				'label' => __('Product'),
				'text' => '<a href="' . $this->getUrl(
						'catalog/product/edit',
						['id' => $product->getId()]
					) . '" onclick="this.target=\'blank\'">' . $this->escapeHtml(
						$product->getName()
					) . '</a>'
			]
		);

		try {
			$customer = $this->customerRepository->getById($review->getCustomerId());
			$customerText = __(
				'<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
				$this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'review']),
				$this->escapeHtml($customer->getFirstname()),
				$this->escapeHtml($customer->getLastname()),
				$this->escapeHtml($customer->getEmail())
			);
		} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
			$customerText = ($review->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID)
				? __('Administrator') : __('Guest');
		}

		$fieldset->addField('customer', 'note', ['label' => __('Author'), 'text' => $customerText]);

		$fieldset->addField(
			'summary-rating',
			'note',
			[
				'label' => __('Summary Rating'),
				'text' => $this->getLayout()->createBlock(
					\Magento\Review\Block\Adminhtml\Rating\Summary::class
				)->toHtml()
			]
		);

		$fieldset->addField(
			'detailed-rating',
			'note',
			[
				'label' => __('Detailed Rating'),
				'required' => true,
				'text' => '<div id="rating_detail">' . $this->getLayout()->createBlock(
						\Magento\Review\Block\Adminhtml\Rating\Detailed::class
					)->toHtml() . '</div>'
			]
		);

		$fieldset->addField(
			'status_id',
			'select',
			[
				'label' => __('Status'),
				'required' => true,
				'name' => 'status_id',
				'values' => $this->_reviewData->getReviewStatusesOptionArray()
			]
		);

		/**
		 * Check is single store mode
		 */
		if (!$this->_storeManager->hasSingleStore()) {
			$field = $fieldset->addField(
				'select_stores',
				'multiselect',
				[
					'label' => __('Visibility'),
					'required' => true,
					'name' => 'stores[]',
					'values' => $this->_systemStore->getStoreValuesForForm()
				]
			);
			$renderer = $this->getLayout()->createBlock(
				\Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
			);
			$field->setRenderer($renderer);
			$review->setSelectStores($review->getStores());
		} else {
			$fieldset->addField(
				'select_stores',
				'hidden',
				['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
			);
			$review->setSelectStores($this->_storeManager->getStore(true)->getId());
		}

		$fieldset->addField(
			'nickname',
			'text',
			['label' => __('Nickname'), 'required' => true, 'name' => 'nickname']
		);

		$fieldset->addField(
			'title',
			'text',
			['label' => __('Summary of Review'), 'required' => true, 'name' => 'title']
		);

		$fieldset->addField(
			'detail',
			'textarea',
			['label' => __('Review'), 'required' => true, 'name' => 'detail', 'style' => 'height:24em;']
		);

		$fieldset->addField(
			'response_review',
			'textarea',
			['label' => __('Réponse'), 'required' => false, 'name' => 'response_review', 'style' => 'height:24em;']
		);

		$form->setUseContainer(true);
		$form->setValues($review->getData());
		$this->setForm($form);
		return Generic::_prepareForm();
	}
}
