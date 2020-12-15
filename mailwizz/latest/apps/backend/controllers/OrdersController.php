<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OrdersController
 *
 * Handles the actions for price plans orders related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */

class OrdersController extends Controller
{
	public function init()
	{
		$this->onBeforeAction = array($this, '_registerJuiBs');
		parent::init();
	}

	/**
	 * Define the filters for various controller actions
	 * Merge the filters with the ones from parent implementation
	 */
	public function filters()
	{
		$filters = array(
			'postOnly + delete, delete_note',
		);

		return CMap::mergeArray($filters, parent::filters());
	}

	/**
	 * List all available orders
	 */
	public function actionIndex()
	{
		$request = Yii::app()->request;
		$ioFilter= Yii::app()->ioFilter;
		$order   = new PricePlanOrder('search');
		$order->unsetAttributes();

		$order->attributes = $ioFilter->xssClean((array)$request->getOriginalQuery($order->modelName, array()));

		$this->setData(array(
			'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('orders', 'View orders'),
			'pageHeading'       => Yii::t('orders', 'View orders'),
			'pageBreadcrumbs'   => array(
				Yii::t('orders', 'Orders') => $this->createUrl('orders/index'),
				Yii::t('app', 'View all')
			)
		));

		$this->render('list', compact('order'));
	}

	/**
	 * Create order
	 */
	public function actionCreate()
	{
		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;
		$order   = new PricePlanOrder();

		if ($request->isPostRequest && ($attributes = (array)$request->getPost($order->modelName, array()))) {
			$order->attributes = $attributes;
			if (!$order->save()) {
				$notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
			} else {
				$note = new PricePlanOrderNote();
				$note->attributes = (array)$request->getPost($note->modelName, array());
				$note->order_id   = $order->order_id;
				$note->user_id    = Yii::app()->user->getId();
				$note->save();

				$notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
			}

			Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
				'controller'=> $this,
				'success'   => $notify->hasSuccess,
				'order'     => $order,
			)));

			if ($collection->success) {
				$this->redirect(array('orders/index'));
			}
		}

		$note = new PricePlanOrderNote('search');
		$note->attributes = (array)$request->getQuery($note->modelName, array());
		$note->order_id   = (int)$order->order_id;

		$this->setData(array(
			'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('orders', 'Create order'),
			'pageHeading'       => Yii::t('orders', 'Create order'),
			'pageBreadcrumbs'   => array(
				Yii::t('orders', 'Orders') => $this->createUrl('orders/index'),
				Yii::t('app', 'Create'),
			)
		));

		$this->render('form', compact('order', 'note'));
	}

	/**
	 * Update existing order
	 */
	public function actionUpdate($id)
	{
		$order = PricePlanOrder::model()->findByPk((int)$id);

		if (empty($order)) {
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}

		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;

		if ($request->isPostRequest && ($attributes = (array)$request->getPost($order->modelName, array()))) {
			$order->attributes = $attributes;
			if (!$order->save()) {
				$notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
			} else {
				$note = new PricePlanOrderNote();
				$note->attributes = (array)$request->getPost($note->modelName, array());
				$note->order_id   = $order->order_id;
				$note->user_id    = Yii::app()->user->getId();
				$note->save();

				$notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
			}

			Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
				'controller'=> $this,
				'success'   => $notify->hasSuccess,
				'order'     => $order,
			)));

			if ($collection->success) {
				$this->redirect(array('orders/index'));
			}
		}

		$note = new PricePlanOrderNote('search');
		$note->attributes = (array)$request->getQuery($note->modelName, array());
		$note->order_id   = (int)$order->order_id;

		$this->setData(array(
			'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('orders', 'Update order'),
			'pageHeading'       => Yii::t('orders', 'Update order'),
			'pageBreadcrumbs'   => array(
				Yii::t('orders', 'Orders') => $this->createUrl('orders/index'),
				Yii::t('app', 'Update'),
			)
		));

		$this->render('form', compact('order', 'note'));
	}

	/**
	 * View order
	 */
	public function actionView($id)
	{
		$request = Yii::app()->request;
		$order   = PricePlanOrder::model()->findByPk((int)$id);

		if (empty($order)) {
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}

		$pricePlan = $order->plan;
		$customer  = $order->customer;

		$note = new PricePlanOrderNote('search');
		$note->unsetAttributes();
		$note->attributes = (array)$request->getQuery($note->modelName, array());
		$note->order_id   = (int)$order->order_id;

		$transaction = new PricePlanOrderTransaction('search');
		$transaction->unsetAttributes();
		$transaction->attributes = (array)$request->getQuery($transaction->modelName, array());
		$transaction->order_id   = $order->order_id;

		$this->setData(array(
			'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('orders', 'View your order'),
			'pageHeading'       => Yii::t('orders', 'View your order'),
			'pageBreadcrumbs'   => array(
				Yii::t('price_plans', 'Price plans') => $this->createUrl('price_plans/index'),
				Yii::t('orders', 'Orders') => $this->createUrl('price_plans/orders'),
				Yii::t('app', 'View')
			)
		));

		$this->render('order_detail', compact('order', 'pricePlan', 'customer', 'note', 'transaction'));
	}

	/**
	 * View order in PDF format
	 */
	public function actionPdf($id)
	{
		$request = Yii::app()->request;
		$order   = PricePlanOrder::model()->findByPk((int)$id);

		if (empty($order)) {
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}

		$pricePlan = $order->plan;
		$customer  = $order->customer;
		$invoiceOptions = new OptionMonetizationInvoices();

		Yii::import('common.vendors.Invoicr.*');

		$invoice = new Invoicr("A4", $order->currency->code, null);

		if (!empty($invoiceOptions->logo)) {
			$logoImage = $_SERVER['DOCUMENT_ROOT'] . $invoiceOptions->getLogoUrl();
			if (is_file($logoImage)) {
				$invoice->setLogo($logoImage);
			}
		} elseif (is_file($logoImage = Yii::getPathOfAlias('common.vendors.Invoicr.images.logo') . '.png')) {
			$invoice->setLogo($logoImage);
		}

		$invoice
			->setColor("#" . $invoiceOptions->color_code)
			->setType(Yii::t('orders', "Invoice"))
			->setReference($invoiceOptions->prefix . ($order->order_id < 10 ? '0' . $order->order_id : $order->order_id))
			->setDate(preg_replace('/\s.*/', '', $order->dateAdded))
			->setDue(preg_replace('/\s.*/', '', $order->dateAdded))
			->setFrom(array_map('trim', explode("\n", $order->getHtmlPaymentFrom(null, "\n"))))
			->setTo(array_map('trim', explode("\n", $order->getHtmlPaymentTo(null, "\n"))))
			->addItem($pricePlan->name, StringHelper::truncateLength($pricePlan->description, 50), 1, false, $pricePlan->formattedPrice, false, $order->formattedTotal)
			->addTotal(Yii::t('orders', "Subtotal"), $order->formattedSubtotal)
			->addTotal(Yii::t('orders', "Tax"). ' '. $order->formattedTaxPercent, $order->formattedTaxValue)
			->addTotal(Yii::t('orders', "Discount"), $order->formattedDiscount)
			->addTotal(Yii::t('orders', "Total"), $order->formattedTotal);

		if ($order->getIsComplete()) {
			$order->total = 0.00;
		}

		$invoice->addTotal(Yii::t('orders', "Total due"), $order->formattedTotal, true);

		if ($order->getIsComplete()) {
			$invoice->addBadge(Yii::t('orders', "Paid"));
		}

		if (!empty($invoiceOptions->notes)) {
			$invoice->addTitle(Yii::t('orders', 'Extra notes'))->addParagraph($invoiceOptions->notes);
		}

		$invoice->setFooternote(Yii::app()->options->get('system.urls.frontend_absolute_url'));

		// 1.8.4
		$invoice = Yii::app()->hooks->applyFilters('price_plan_order_generate_pdf_invoice', $invoice, $order);
		
		//Render
		$invoice->render($order->order_uid . '.pdf','I');
	}

	/**
	 * Email the invoice
	 */
	public function actionEmail_invoice($id)
	{
		$options = Yii::app()->options;
		$notify  = Yii::app()->notify;
		$order   = PricePlanOrder::model()->findByPk((int)$id);

		if (empty($order)) {
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}

		$customer = $order->customer;
		$dsParams = array('useFor' => array(DeliveryServer::USE_FOR_INVOICES));

		if (!($deliveryServer = DeliveryServer::pickServer(0, null, $dsParams))) {
			$notify->addWarning(Yii::t('orders', 'Please try again later!'));
			$this->redirect(array('orders/view', 'id' => $id));
		}

		$invoiceOptions = new OptionMonetizationInvoices();
		$ref            = $invoiceOptions->prefix . ($order->order_id < 10 ? '0' . $order->order_id : $order->order_id);
		
		$storagePath = Yii::getPathOfAlias('root.frontend.assets.files.invoices');
		if ((!file_exists($storagePath) || !is_dir($storagePath)) && !mkdir($storagePath, 0777, true)) {
			$notify->addWarning(Yii::t('orders', 'Unable to create the invoices storage directory!'));
			$this->redirect(array('orders/view', 'id' => $id));
		}
		$invoicePath = $storagePath . '/' . preg_replace('/(\-){2,}/', '-', preg_replace('/[^a-z0-9\-]+/i', '-', $ref)) . '.pdf';

		ob_start();
		ob_implicit_flush(false);
		$this->actionPdf($id);
		$pdf = ob_get_clean();

		if (!file_put_contents($invoicePath, $pdf)) {
			$notify->addWarning(Yii::t('orders', 'Unable to create the invoice!'));
			$this->redirect(array('orders/view', 'id' => $id));
		}
		
		if (!($emailSubject = $invoiceOptions->email_subject)) {
			$emailSubject = Yii::t('orders', 'Your requested invoice - {ref}', array(
				'{ref}' => $ref,
			));
		}

		$params = CommonEmailTemplate::getAsParamsArrayBySlug('order-invoice',
			array(
				'to'          => array($customer->email => $customer->fullName),
				'subject'     => $emailSubject,
				'from_name'   => $options->get('system.common.site_name', 'Marketing website'),
				'attachments' => array($invoicePath),
			), array(
				'[CUSTOMER_NAME]' => $customer->fullName,
				'[REF]'           => $ref
			)
		);

		if ($emailBody = $invoiceOptions->email_content) {
			$params['body'] = nl2br($emailBody);
		}

		if ($deliveryServer->sendEmail($params)) {
			$notify->addSuccess(Yii::t('orders', 'The invoice has been successfully emailed!'));
		} else {
			$notify->addError(Yii::t('orders', 'Unable to email the invoice!'));
		}

		unlink($invoicePath);

		$this->redirect(array('orders/view', 'id' => $id));
	}

	/**
	 * Delete existing order
	 */
	public function actionDelete($id)
	{
		$order = PricePlanOrder::model()->findByPk((int)$id);

		if (empty($order)) {
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}

		$order->delete();

		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;

		$redirect = null;
		if (!$request->getQuery('ajax')) {
			$notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
			$redirect = $request->getPost('returnUrl', array('orders/index'));
		}

		// since 1.3.5.9
		Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
			'controller' => $this,
			'model'      => $order,
			'redirect'   => $redirect,
		)));

		if ($collection->redirect) {
			$this->redirect($collection->redirect);
		}
	}

	/**
	 * Delete existing order note
	 */
	public function actionDelete_note($id)
	{
		$note = PricePlanOrderNote::model()->findByPk((int)$id);

		if (empty($note)) {
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}

		$note->delete();

		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;

		if (!$request->getQuery('ajax')) {
			$notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
			$this->redirect($request->getPost('returnUrl', array('orders/index')));
		}
	}

	/**
	 * Callback to register Jquery ui bootstrap only for certain actions
	 */
	public function _registerJuiBs($event)
	{
		if (in_array($event->params['action']->id, array('create', 'update'))) {
			$this->getData('pageStyles')->mergeWith(array(
				array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
			));
		}
	}
}
