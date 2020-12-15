<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List_subscribersController
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class List_subscribersController extends Controller
{
    // access rules for this controller
    public function accessRules()
    {
        return array(
            // allow all authenticated users on all actions
            array('allow', 'users' => array('@')),
            // deny all rule.
            array('deny'),
        );
    }

    /**
     * Handles the listing of the email list subscribers.
     * The listing is based on page number and number of subscribers per page.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionIndex($list_uid)
    {
        $request = Yii::app()->request;

        $criteria = new CDbCriteria();
        $criteria->compare('list_uid', $list_uid);
        $criteria->compare('customer_id', (int)Yii::app()->user->getId());
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        $list = Lists::model()->find($criteria);

        if (empty($list)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not exist.')
            ), 404);
        }

        $criteria = new CDbCriteria();
        $criteria->compare('list_id', $list->list_id);
        $fields = ListField::model()->findAll($criteria);

        if (empty($fields)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not have any custom field defined.')
            ), 404);
        }

        $perPage    = (int)$request->getQuery('per_page', 10);
        $page       = (int)$request->getQuery('page', 1);

        $maxPerPage = 50;
        $minPerPage = 10;

        if ($perPage < $minPerPage) {
            $perPage = $minPerPage;
        }

        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        if ($page < 1) {
            $page = 1;
        }

        $data = array(
            'count'         => null,
            'total_pages'   => null,
            'current_page'  => null,
            'next_page'     => null,
            'prev_page'     => null,
            'records'       => array(),
        );

        $criteria = new CDbCriteria();
        $criteria->compare('t.list_id', (int)$list->list_id);

        $count = ListSubscriber::model()->count($criteria);

        if ($count == 0) {
            return $this->renderJson(array(
                'status'    => 'success',
                'data'      => $data
            ), 200);
        }

        $totalPages = ceil($count / $perPage);

        $data['count']          = $count;
        $data['current_page']   = $page;
        $data['next_page']      = $page < $totalPages ? $page + 1 : null;
        $data['prev_page']      = $page > 1 ? $page - 1 : null;
        $data['total_pages']    = $totalPages;

        $criteria->order    = 't.subscriber_id DESC';
        $criteria->limit    = $perPage;
        $criteria->offset   = ($page - 1) * $perPage;

        $subscribers = ListSubscriber::model()->findAll($criteria);

        foreach ($subscribers as $subscriber) {
            $record = array('subscriber_uid' => null); // keep this first!
            foreach ($fields as $field) {
                
                if ($field->tag == 'EMAIL') {
                    $record[$field->tag] = CHtml::encode($subscriber->displayEmail);
                    continue;
                }
                
                $value = null;
                $criteria = new CDbCriteria();
                $criteria->select = 'value';
                $criteria->compare('field_id', (int)$field->field_id);
                $criteria->compare('subscriber_id', (int)$subscriber->subscriber_id);
                $valueModels = ListFieldValue::model()->findAll($criteria);
                if (!empty($valueModels)) {
                    $value = array();
                    foreach($valueModels as $valueModel) {
                        $value[] = $valueModel->value;
                    }
                    $value = implode(', ', $value);
                }
                $record[$field->tag] = CHtml::encode($value);
            }

            $record['subscriber_uid']   = $subscriber->subscriber_uid;
            $record['status']           = $subscriber->status;
            $record['source']           = $subscriber->source;
            $record['ip_address']       = $subscriber->ip_address;
            $record['date_added']       = $subscriber->date_added;

            $data['records'][] = $record;
        }

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data
        ), 200);
    }

    /**
     * Handles the listing of a single subscriber from a list.
     * This action will produce a valid ETAG for caching purposes.
     *
     * @param $list_uid The list unique id
     * @param subscriber_uid The subscriber unique id
     */
    public function actionView($list_uid, $subscriber_uid)
    {
        $request = Yii::app()->request;

        if (!($list = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not exist.')
            ), 404);
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'subscriber_uid'    => $subscriber_uid,
            'list_id'           => $list->list_id,
        ));
        if (empty($subscriber)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscriber does not exist in this list.')
            ), 404);
        }

        $fields = ListField::model()->findAllByAttributes(array(
            'list_id' => $list->list_id,
        ));

        if (empty($fields)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not have any custom field defined.')
            ), 404);
        }

        $data = array(
            'record' => array(
                'subscriber_uid' => null,
                'status'         => null,
                'source'         => null,
                'ip_address'     => null,
            ),
        );

        foreach ($fields as $field) {

            if ($field->tag == 'EMAIL') {
                $data['record'][$field->tag] = CHtml::encode($subscriber->displayEmail);
                continue;
            }
            
            $value = null;
            $criteria = new CDbCriteria();
            $criteria->select = 'value';
            $criteria->compare('field_id', (int)$field->field_id);
            $criteria->compare('subscriber_id', (int)$subscriber->subscriber_id);
            $valueModels = ListFieldValue::model()->findAll($criteria);
            if (!empty($valueModels)) {
                $value = array();
                foreach($valueModels as $valueModel) {
                    $value[] = $valueModel->value;
                }
                $value = implode(', ', $value);
            }
            
            $data['record'][$field->tag] = CHtml::encode($value);
        }

        $data['record']['subscriber_uid'] = $subscriber->subscriber_uid;
        $data['record']['status']         = $subscriber->status;
        $data['record']['source']         = $subscriber->source;
        $data['record']['ip_address']     = $subscriber->ip_address;

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data,
        ), 200);
    }

    /**
     * Handles the creation of a new subscriber for a certain email list.
     *
     * @param $list_uid The list unique id where this subscriber should go
     */
    public function actionCreate($list_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPostRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only POST requests allowed for this endpoint.')
            ), 400);
        }

        $email = $request->getPost('EMAIL');
        if (empty($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide the subscriber email address.')
            ), 422);
        }

        $validator = new CEmailValidator();
        $validator->allowEmpty  = false;
        $validator->validateIDN = true;
        if (Yii::app()->options->get('system.common.dns_email_check', false)) {
            $validator->checkMX     = CommonHelper::functionExists('checkdnsrr');
            $validator->checkPort   = CommonHelper::functionExists('dns_get_record') && CommonHelper::functionExists('fsockopen');
        }

        if (!$validator->validateValue($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide a valid email address.')
            ), 422);
        }

        if (!($list = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not exist.')
            ), 404);
        }

        $customer                = $list->customer;
        $maxSubscribersPerList   = (int)$customer->getGroupOption('lists.max_subscribers_per_list', -1);
        $maxSubscribers          = (int)$customer->getGroupOption('lists.max_subscribers', -1);

        if ($maxSubscribers > -1 || $maxSubscribersPerList > -1) {
            $criteria = new CDbCriteria();
            $criteria->select = 'COUNT(DISTINCT(t.email)) as counter';

            if ($maxSubscribers > -1 && ($listsIds = $customer->getAllListsIdsNotMerged())) {
                $criteria->addInCondition('t.list_id', $listsIds);
                $totalSubscribersCount = ListSubscriber::model()->count($criteria);
                if ($totalSubscribersCount >= $maxSubscribers) {
                    return $this->renderJson(array(
                        'status'    => 'error',
                        'error'     => Yii::t('lists', 'The maximum number of allowed subscribers has been reached.')
                    ), 409);
                }
            }

            if ($maxSubscribersPerList > -1) {
                $criteria->compare('t.list_id', (int)$list->list_id);
                $listSubscribersCount = ListSubscriber::model()->count($criteria);
                if ($listSubscribersCount >= $maxSubscribersPerList) {
                    return $this->renderJson(array(
                        'status'    => 'error',
                        'error'     => Yii::t('lists', 'The maximum number of allowed subscribers for this list has been reached.')
                    ), 409);
                }
            }
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'   => (int)$list->list_id,
            'email'     => $email,
        ));

        // 1.6.6
        $stop = false;
        if (!empty($subscriber)) {
	        $stop = true;
        	if ($subscriber->getIsUnsubscribed()) {
		        $stop = false;
	        }
        } 
        
        if ($stop) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscriber already exists in this list.')
            ), 409);
        }

        // 1.6.6
	    if (empty($subscriber)) {
		    $subscriber = new ListSubscriber();
	    }
	    
        $subscriber->list_id    = $list->list_id;
        $subscriber->email      = $email;
        $subscriber->source     = ListSubscriber::SOURCE_API;
        $subscriber->ip_address = $request->getServer('HTTP_X_MW_REMOTE_ADDR', $request->getServer('REMOTE_ADDR'));

        if ($list->opt_in == Lists::OPT_IN_SINGLE) {
            $subscriber->status = ListSubscriber::STATUS_CONFIRMED;
        } else {
            $subscriber->status = ListSubscriber::STATUS_UNCONFIRMED;
        }

        $blacklisted = $subscriber->getIsBlacklisted(array('checkZone' => EmailBlacklist::CHECK_ZONE_LIST_SUBSCRIBE));
        if (!empty($blacklisted)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'This email address is blacklisted.')
            ), 409);
        }

        $fields = ListField::model()->findAllByAttributes(array(
            'list_id' => $list->list_id,
        ));

        if (empty($fields)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not have any custom field defined.')
            ), 404);
        }

        $errors = array();
        foreach ($fields as $field) {
            $value = $request->getPost($field->tag);
            if ($field->required == ListField::TEXT_YES && empty($value)) {
                $errors[$field->tag] = Yii::t('api', 'The field {field} is required by the list but it has not been provided!', array(
                    '{field}' => $field->tag
                ));
            }
        }

        if (!empty($errors)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => $errors,
            ), 422);
        }

        // since 1.3.5.7
        $details = (array)$request->getPost('details', array());
        if (!empty($details)) {
            $statuses   = array_keys($subscriber->getStatusesList());
            $statuses[] = ListSubscriber::STATUS_UNAPPROVED;
            $statuses[] = ListSubscriber::STATUS_BLACKLISTED; // 1.3.7.1
            $statuses   = array_unique($statuses);
            if (!empty($details['status']) && in_array($details['status'], $statuses)) {
                $subscriber->status = $details['status'];
            }
            if (!empty($details['ip_address']) && FilterVarHelper::ip($details['ip_address'])) {
                $subscriber->ip_address = $details['ip_address'];
            }
            if (!empty($details['source']) && in_array($details['source'], array_keys($subscriber->getSourcesList()))) {
                $subscriber->source = $details['source'];
            }
        }

        if (!$subscriber->save()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Unable to save the subscriber!'),
            ), 422);
        }
        
        // 1.3.7.1
        if ($subscriber->status == ListSubscriber::STATUS_BLACKLISTED) {
            $subscriber->addToBlacklist('Blacklisted via API');
        }

        $substr = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';
        
        foreach ($fields as $field) {

        	// 1.8.1
	        ListFieldValue::model()->deleteAllByAttributes(array(
		        'subscriber_id' => (int)$subscriber->subscriber_id,
		        'field_id'      => $field->field_id,
	        ));
	        
            $value = $request->getPost($field->tag, $field->default_value);
            if (!is_array($value)) {
                $value = array($value);
            }
            $value = array_unique($value);
            
            foreach ($value as $val) {
                $valueModel                 = new ListFieldValue();
                $valueModel->field_id       = $field->field_id;
                $valueModel->subscriber_id  = $subscriber->subscriber_id;
                $valueModel->value          = $substr($val, 0, 255);
                $valueModel->save();
            }
        }
        
        // since 1.3.6.2
        $this->handleListSubscriberMustApprove($list, $subscriber, $customer);
        
        return $this->renderJson(array(
            'status' => 'success',
            'data'   => array(
                'record' => $subscriber->getAttributes(array('subscriber_uid', 'email', 'ip_address', 'source', 'date_added'))
            ),
        ), 201);
    }

	/**
	 * Handles the creation of bulk subscribers for a certain email list.
	 *
	 * @param $list_uid The list unique id where this subscribers should go
	 */
	public function actionCreate_bulk($list_uid)
	{
		$request = Yii::app()->request;

		if (!$request->isPostRequest) {
			return $this->renderJson(array(
				'status'    => 'error',
				'error'     => Yii::t('api', 'Only POST requests allowed for this endpoint.')
			), 400);
		}

		if (!($list = $this->loadListByUid($list_uid))) {
			return $this->renderJson(array(
				'status'    => 'error',
				'error'     => Yii::t('api', 'The subscribers list does not exist.')
			), 404);
		}

		$customer                = $list->customer;
		$maxSubscribersPerList   = (int)$customer->getGroupOption('lists.max_subscribers_per_list', -1);
		$maxSubscribers          = (int)$customer->getGroupOption('lists.max_subscribers', -1);
		$totalSubscribersCount   = 0;
		$listSubscribersCount    = 0;
		
		if ($maxSubscribers > -1 || $maxSubscribersPerList > -1) {
			$criteria = new CDbCriteria();
			$criteria->select = 'COUNT(DISTINCT(t.email)) as counter';

			if ($maxSubscribers > -1 && ($listsIds = $customer->getAllListsIdsNotMerged())) {
				$criteria->addInCondition('t.list_id', $listsIds);
				$totalSubscribersCount = ListSubscriber::model()->count($criteria);
				if ($totalSubscribersCount >= $maxSubscribers) {
					return $this->renderJson(array(
						'status'    => 'error',
						'error'     => Yii::t('lists', 'The maximum number of allowed subscribers has been reached.')
					), 409);
				}
			}

			if ($maxSubscribersPerList > -1) {
				$criteria->compare('t.list_id', (int)$list->list_id);
				$listSubscribersCount = ListSubscriber::model()->count($criteria);
				if ($listSubscribersCount >= $maxSubscribersPerList) {
					return $this->renderJson(array(
						'status'    => 'error',
						'error'     => Yii::t('lists', 'The maximum number of allowed subscribers for this list has been reached.')
					), 409);
				}
			}
		}
		
		$subscribers = $request->getPost('subscribers');
		if (empty($subscribers) || !is_array($subscribers)) {
			return $this->renderJson(array(
				'status'    => 'error',
				'error'     => Yii::t('api', 'Please provide the subscribers list.')
			), 422);
		}
		
		// at most 10k
		$subscribers = array_slice($subscribers, 0, 10000);

		$fields = ListField::model()->findAllByAttributes(array(
			'list_id' => $list->list_id,
		));
		if (empty($fields)) {
			return $this->renderJson(array(
				'status'    => 'error',
				'error'     => Yii::t('api', 'The subscribers list does not have any custom field defined.')
			), 404);
		}

		$substr = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';
		
		$emailValidator = new CEmailValidator();
		$emailValidator->allowEmpty  = false;
		$emailValidator->validateIDN = true;
		
		$subscribersList = array();
		foreach ($subscribers as $subscriber) {
			
			$subscribersListItem = array(
				'data'   => array(
					'details' => isset($subscriber['details']) && is_array($subscriber['details']) ? $subscriber['details'] : array(),
				),
				'errors' => array(),
			);

			if (empty($subscriber['EMAIL']) || !$emailValidator->validateValue($subscriber['EMAIL'])) {
				$subscribersListItem['data']   = $subscriber;
				$subscribersListItem['errors'] = array(
					'EMAIL' => Yii::t('api', 'Please provide a valid email address.')
				);
				$subscribersList[] = $subscribersListItem;
				continue;
			}

			$errors = array();
			foreach ($fields as $field) {
				
				$value = isset($subscriber[$field->tag]) ? $subscriber[$field->tag] : null;
				$subscribersListItem['data'][$field->tag] = $value;
				
				if ($field->required == ListField::TEXT_YES && empty($value)) {
					$errors[$field->tag] = Yii::t('api', 'The field {field} is required by the list but it has not been provided!', array(
						'{field}' => $field->tag
					));
				}
			}
			
			if (!empty($errors)) {
				$subscribersListItem['errors']  = $errors;
				$subscribersList[]              = $subscribersListItem;
				continue;	
			}

			$subscribersList[] = $subscribersListItem;
		}
		
		foreach ($subscribersList as $index => $subscribersListItem) {
			
			if (!empty($subscribersListItem['errors'])) {
				continue;
			}

			// handle the limits 
			if ($maxSubscribers > -1 && $totalSubscribersCount >= $maxSubscribers) {
				$subscribersList[$index]['errors'] = array(
					'_common' => Yii::t('lists', 'The maximum number of allowed subscribers has been reached.')
				);
				continue;
			}
			if ($maxSubscribersPerList > -1 && $listSubscribersCount >= $maxSubscribersPerList) {
				$subscribersList[$index]['errors'] = array(
					'_common' => Yii::t('lists', 'The maximum number of allowed subscribers for this list has been reached.')
				);
				continue;
			}
			//
			
			$totalSubscribersCount++;
			$listSubscribersCount++;
			// end limits

			$subscriber = ListSubscriber::model()->findByAttributes(array(
				'list_id'   => (int)$list->list_id,
				'email'     => $subscribersListItem['data']['EMAIL'],
			));

			// 1.6.6
			$stop = false;
			if (!empty($subscriber)) {
				$stop = true;
				if ($subscriber->getIsUnsubscribed()) {
					$stop = false;
				}
			}

			if ($stop) {
				$subscribersList[$index]['errors'] = array(
					'EMAIL' => Yii::t('api', 'The subscriber already exists in this list.')
				);
				continue;
			}


			// 1.6.6
			if (empty($subscriber)) {
				$subscriber = new ListSubscriber();
			}
			
			$subscriber->list_id    = $list->list_id;
			$subscriber->email      = $subscribersListItem['data']['EMAIL'];
			$subscriber->source     = ListSubscriber::SOURCE_API;
			$subscriber->ip_address = $request->getServer('HTTP_X_MW_REMOTE_ADDR', $request->getServer('REMOTE_ADDR'));

			if ($list->opt_in == Lists::OPT_IN_SINGLE) {
				$subscriber->status = ListSubscriber::STATUS_CONFIRMED;
			} else {
				$subscriber->status = ListSubscriber::STATUS_UNCONFIRMED;
			}

			$blacklisted = $subscriber->getIsBlacklisted(array('checkZone' => EmailBlacklist::CHECK_ZONE_LIST_SUBSCRIBE));
			if (!empty($blacklisted)) {
				$subscribersList[$index]['errors'] = array(
					'EMAIL' => Yii::t('api', 'This email address is blacklisted.')
				);
				continue;
			}

			// since 1.3.5.7
			$details = $subscribersListItem['data']['details'];
			if (!empty($details)) {
				$statuses   = array_keys($subscriber->getStatusesList());
				$statuses[] = ListSubscriber::STATUS_UNAPPROVED;
				$statuses[] = ListSubscriber::STATUS_BLACKLISTED; // 1.3.7.1
				$statuses   = array_unique($statuses);
				if (!empty($details['status']) && in_array($details['status'], $statuses)) {
					$subscriber->status = $details['status'];
				}
				if (!empty($details['ip_address']) && FilterVarHelper::ip($details['ip_address'])) {
					$subscriber->ip_address = $details['ip_address'];
				}
				if (!empty($details['source']) && in_array($details['source'], array_keys($subscriber->getSourcesList()))) {
					$subscriber->source = $details['source'];
				}
			}

			if (!$subscriber->save()) {
				$subscribersList[$index]['errors'] = array(
					'_common' => Yii::t('api', 'Unable to save the subscriber!')
				);
				continue;
			}

			// 1.3.7.1
			if ($subscriber->status == ListSubscriber::STATUS_BLACKLISTED) {
				$subscriber->addToBlacklist('Blacklisted via API');
			}
			
			foreach ($fields as $field) {

				// 1.8.1
				ListFieldValue::model()->deleteAllByAttributes(array(
					'subscriber_id' => (int)$subscriber->subscriber_id,
					'field_id'      => (int)$field->field_id,
				));
				
				$value = !empty($subscribersListItem['data'][$field->tag]) ? $subscribersListItem['data'][$field->tag] : $field->default_value;
				if (!is_array($value)) {
					$value = array($value);
				}
				$value = array_unique($value);

				foreach ($value as $val) {
					$valueModel                 = new ListFieldValue();
					$valueModel->field_id       = $field->field_id;
					$valueModel->subscriber_id  = $subscriber->subscriber_id;
					$valueModel->value          = $substr($val, 0, 255);
					$valueModel->save();
				}
			}

			// since 1.3.6.2
			$this->handleListSubscriberMustApprove($list, $subscriber, $customer);
		}
		
		foreach ($subscribersList as $index => $item) {
			unset($subscribersList[$index]['data']['details']);
			if (empty($subscribersList[$index]['errors'])) {
				unset($subscribersList[$index]['errors']);
			}
		}

		return $this->renderJson(array(
			'status' => 'success',
			'data'   => array(
				'records' => $subscribersList
			),
		), 201);
	}

    /**
     * Handles the updating of an list subscriber.
     *
     * @param $list_uid The email list unique id.
     * @param $subscriber_uid The subscriber unique id
     */
    public function actionUpdate($list_uid, $subscriber_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPutRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only PUT requests allowed for this endpoint.')
            ), 400);
        }

        if (!($list = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not exist.')
            ), 404);
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'subscriber_uid'    => $subscriber_uid,
            'list_id'           => $list->list_id,
        ));

        if (empty($subscriber)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscriber does not exist in this list.')
            ), 409);
        }

        $email = $request->getPut('EMAIL');
        if (empty($email)) {
            $email = $subscriber->email;
        }

        $validator = new CEmailValidator();
        $validator->allowEmpty  = false;
        $validator->validateIDN = true;
        if (Yii::app()->options->get('system.common.dns_email_check', false)) {
            $validator->checkMX     = CommonHelper::functionExists('checkdnsrr');
            $validator->checkPort   = CommonHelper::functionExists('dns_get_record') && CommonHelper::functionExists('fsockopen');
        }

        if (!$validator->validateValue($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide a valid email address.')
            ), 422);
        }

        $fields = ListField::model()->findAllByAttributes(array(
            'list_id'   => $list->list_id,
        ));

        if (empty($fields)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not have any custom field defined.')
            ), 404);
        }

        $errors = array();
        foreach ($fields as $field) {
            
            // no need for email since we have it anyway.
            if ($field->tag == 'EMAIL') {
                continue;
            }
            
            $value = $request->getPut($field->tag);
            if ($field->required == ListField::TEXT_YES && empty($value)) {
                $errors[$field->tag] = Yii::t('api', 'The field {field} is required by the list but it has not been provided!', array(
                    '{field}' => $field->tag
                ));
            }
        }

        if (!empty($errors)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => $errors,
            ), 422);
        }

        $criteria = new CDbCriteria();
        $criteria->condition = 't.list_id = :lid AND t.email = :email AND t.subscriber_id != :sid';
        $criteria->params = array(
            ':lid'      => $list->list_id,
            ':email'    => $email,
            ':sid'      => $subscriber->subscriber_id,
        );
        $duplicate = ListSubscriber::model()->find($criteria);
        if (!empty($duplicate)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Another subscriber with this email address already exists in this list.')
            ), 409);
        }

        $subscriber->email = $email;
        $blacklisted = $subscriber->getIsBlacklisted(array('checkZone' => EmailBlacklist::CHECK_ZONE_LIST_SUBSCRIBE));
        if (!empty($blacklisted)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'This email address is blacklisted.')
            ), 409);
        }

        // since 1.3.5.7
        $details = (array)$request->getPut('details', array());
        if (!empty($details)) {
            $statuses   = array_keys($subscriber->getStatusesList());
            $statuses[] = ListSubscriber::STATUS_BLACKLISTED;
            if (!empty($details['status']) && in_array($details['status'], $statuses)) {
                $subscriber->status = $details['status'];
            }
            if (!empty($details['ip_address']) && FilterVarHelper::ip($details['ip_address'])) {
                $subscriber->ip_address = $details['ip_address'];
            }
            if (!empty($details['source']) && in_array($details['source'], array_keys($subscriber->getSourcesList()))) {
                $subscriber->source = $details['source'];
            }
        }

        if (!$subscriber->save()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Unable to save the subscriber!'),
            ), 422);
        }

        // 1.3.7.1
        if ($subscriber->status == ListSubscriber::STATUS_BLACKLISTED) {
            $subscriber->addToBlacklist('Blacklisted via API');
        }
        
        $substr = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';

        foreach ($fields as $field) {
            $fieldValue = $request->getPut($field->tag, null);

            // if the field has not been sent, skip it.
            if ($fieldValue === null) {
                continue;
            }

            // delete existing values
            ListFieldValue::model()->deleteAllByAttributes(array(
                'field_id'      => $field->field_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));
            
            if (!is_array($fieldValue)) {
                $fieldValue = array($fieldValue);
            }
            $fieldValue = array_unique($fieldValue);
            
            // insert new ones
            foreach ($fieldValue as $value) {
                $valueModel                 = new ListFieldValue();
                $valueModel->field_id       = $field->field_id;
                $valueModel->subscriber_id  = $subscriber->subscriber_id;
                $valueModel->value          = $substr($value, 0, 255);
                $valueModel->save();
            }
        }

        if ($logAction = Yii::app()->user->getModel()->asa('logAction')) {
            $logAction->subscriberUpdated($subscriber);
        }

        return $this->renderJson(array(
            'status' => 'success',
            'data'   => array(
                'record' => $subscriber->getAttributes(array('subscriber_uid', 'email', 'ip_address', 'source', 'date_added'))
            ),
        ), 200);
    }

    /**
     * Handles unsubscription of an existing email list subscriber.
     * 
     * @param $list_uid
     * @param $subscriber_uid
     * @return BaseController
     */
    public function actionUnsubscribe($list_uid, $subscriber_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPutRequest) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'Only PUT requests allowed for this endpoint.')
            ), 400);
        }

        if (!($list = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The subscribers list does not exist.')
            ), 404);
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'subscriber_uid' => $subscriber_uid,
            'list_id'        => $list->list_id,
        ));

        if (empty($subscriber)) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The subscriber does not exist in this list.')
            ), 404);
        }

        if (!$subscriber->getIsConfirmed()) {
            return $this->renderJson(array(
                'status' => 'success',
            ), 200);
        }
        
        $saved = $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

        // since 1.3.5 - this should be expanded in future
        if ($saved) {
            $subscriber->takeListSubscriberAction(ListSubscriberAction::ACTION_UNSUBSCRIBE);
        }

        if ($logAction = Yii::app()->user->getModel()->asa('logAction')) {
            $logAction->subscriberUnsubscribed($subscriber);
        }

        return $this->renderJson(array(
            'status'    => 'success',
        ), 200);
    }
    
    /**
     * Handles deleting of an existing email list subscriber.
     * 
     * @param $list_uid
     * @param $subscriber_uid
     * @return BaseController
     * @throws CDbException
     */
    public function actionDelete($list_uid, $subscriber_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isDeleteRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only DELETE requests allowed for this endpoint.')
            ), 400);
        }

        if (!($list = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not exist.')
            ), 404);
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'subscriber_uid'    => $subscriber_uid,
            'list_id'           => $list->list_id,
        ));

        if (empty($subscriber)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscriber does not exist in this list.')
            ), 404);
        }

        $subscriber->delete();

        if ($logAction = Yii::app()->user->getModel()->asa('logAction')) {
            $logAction->subscriberDeleted($subscriber);
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller'  => $this,
            'list'        => $list,
            'subscriber'  => $subscriber,
        )));

        return $this->renderJson(array(
            'status'    => 'success',
        ), 200);
    }

    /**
     * Search given list for a subscriber by the given email address
     *
     * @param $list_uid The email list unique id.
     * @return string
     */
    public function actionSearch_by_email($list_uid)
    {
        $request = Yii::app()->request;

        $email = $request->getQuery('EMAIL');
        if (empty($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide the subscriber email address.')
            ), 422);
        }
        
        $validator = new CEmailValidator();
        $validator->allowEmpty  = false;
        $validator->validateIDN = true;
        if (!$validator->validateValue($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide a valid email address.')
            ), 422);
        }

        if (!($list = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not exist.')
            ), 404);
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'   => $list->list_id,
            'email'     => $email,
        ));

        if (empty($subscriber)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscriber does not exist in this list.')
            ), 404);
        }

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $subscriber->getAttributes(array('subscriber_uid', 'status', 'date_added')),
        ), 200);
    }

    /**
     * Search by email in all lists
     * 
     * @since 1.3.6.2
     * @return string
     */
    public function actionSearch_by_email_in_all_lists()
    {
        $request = Yii::app()->request;

        $email = $request->getQuery('EMAIL');
        if (empty($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide the subscriber email address.')
            ), 422);
        }

        $validator = new CEmailValidator();
        $validator->allowEmpty  = false;
        $validator->validateIDN = true;
        if (!$validator->validateValue($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide a valid email address.')
            ), 422);
        }
        
        $customer = Yii::app()->user->getModel();
        $criteria = new CDbCriteria();
        $criteria->compare('email', $email);
        $criteria->addInCondition('list_id', $customer->getAllListsIdsNotMergedNotArchived());
        $criteria->limit = 100;
        
        $subscribers = ListSubscriber::model()->findAll($criteria);
        
        $data = array('records' => array());
        $data['count']          = count($subscribers);
        $data['current_page']   = 1;
        $data['next_page']      = null;
        $data['prev_page']      = null;
        $data['total_pages']    = 1;
        
        foreach ($subscribers as $subscriber) {
            $record = array();
            $record['subscriber_uid']   = $subscriber->subscriber_uid;
            $record['email']            = $subscriber->email;
            $record['status']           = $subscriber->status;
            $record['source']           = $subscriber->source;
            $record['ip_address']       = $subscriber->ip_address;
            $record['list']             = $subscriber->list->getAttributes(array('list_uid', 'display_name', 'name'));
            $record['date_added']       = $subscriber->date_added;

            $data['records'][] = $record;
        }

        return $this->renderJson(array(
            'status' => 'success',
            'data'   => $data
        ), 200);
    }

    /**
     * Unsubscribe by email from all lists
     *
     * @since 1.4.4
     * @return string
     */
    public function actionUnsubscribe_by_email_from_all_lists()
    {
        $request = Yii::app()->request;

        if (!$request->isPutRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only PUT requests allowed for this endpoint.')
            ), 400);
        }
        
        $email = $request->getPut('EMAIL');
        if (empty($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide the subscriber email address.')
            ), 422);
        }

        $validator = new CEmailValidator();
        $validator->allowEmpty  = false;
        $validator->validateIDN = true;
        if (!$validator->validateValue($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide a valid email address.')
            ), 422);
        }

        $customer = Yii::app()->user->getModel();
        $criteria = new CDbCriteria();
        $criteria->compare('email', $email);
        $criteria->addInCondition('list_id', $customer->getAllListsIds());
        $criteria->limit = 1000;

        $subscribers = ListSubscriber::model()->findAll($criteria);
        
        foreach ($subscribers as $subscriber) {
   
            $saved = $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
            
            if ($saved) {
                $subscriber->takeListSubscriberAction(ListSubscriberAction::ACTION_UNSUBSCRIBE);
            }

            if ($logAction = Yii::app()->user->getModel()->asa('logAction')) {
                $logAction->subscriberUnsubscribed($subscriber);
            }
        }

        return $this->renderJson(array(
            'status' => 'success',
        ), 200);
    }

	/**
	 * Handles the listing of the email list subscribers based on search params for custom fields.
	 * The listing is based on page number and number of subscribers per page.
	 */
	public function actionSearch_by_custom_fields($list_uid)
	{
		$request = Yii::app()->request;

		$criteria = new CDbCriteria();
		$criteria->compare('list_uid', $list_uid);
		$criteria->compare('customer_id', (int)Yii::app()->user->getId());
		$criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
		$list = Lists::model()->find($criteria);

		if (empty($list)) {
			return $this->renderJson(array(
				'status'    => 'error',
				'error'     => Yii::t('api', 'The subscribers list does not exist.')
			), 404);
		}

		$criteria = new CDbCriteria();
		$criteria->compare('list_id', $list->list_id);
		$fields = ListField::model()->findAll($criteria);

		if (empty($fields)) {
			return $this->renderJson(array(
				'status'    => 'error',
				'error'     => Yii::t('api', 'The subscribers list does not have any custom field defined.')
			), 404);
		}

		$perPage    = (int)$request->getQuery('per_page', 10);
		$page       = (int)$request->getQuery('page', 1);

		$maxPerPage = 50;
		$minPerPage = 10;

		if ($perPage < $minPerPage) {
			$perPage = $minPerPage;
		}

		if ($perPage > $maxPerPage) {
			$perPage = $maxPerPage;
		}

		if ($page < 1) {
			$page = 1;
		}

		$data = array(
			'count'         => null,
			'total_pages'   => null,
			'current_page'  => null,
			'next_page'     => null,
			'prev_page'     => null,
			'records'       => array(),
		);

		$criteria = new CDbCriteria();
		$criteria->compare('t.list_id', (int)$list->list_id);
		
		$listFieldValue = array();
		foreach ($fields as $field) {
			if ($val = $request->getQuery($field->tag)) {
				$listFieldValue[$field->field_id] = $val;
				continue;
			}
		}

		if (empty($listFieldValue)) {
			return $this->renderJson(array(
				'status'    => 'success',
				'data'      => $data
			), 200);
		}

		$criteria->with['fieldValues'] = array(
			'joinType'  => 'INNER JOIN',
			'together'  => true,
		);
		
		foreach ($listFieldValue as $fieldId => $value) {
			$criteria->compare('fieldValues.field_id', $fieldId);
			$criteria->compare('fieldValues.value', $value);
		}
		
		$countCriteria = clone $criteria;
		$findCriteria  = clone $criteria;

		$countCriteria->select = 'COUNT(DISTINCT(t.subscriber_id)) as counter';
		$findCriteria->group   = 't.subscriber_id';

		$count = ListSubscriber::model()->count($countCriteria);

		if ($count == 0) {
			return $this->renderJson(array(
				'status'    => 'success',
				'data'      => $data
			), 200);
		}

		$totalPages = ceil($count / $perPage);

		$data['count']          = $count;
		$data['current_page']   = $page;
		$data['next_page']      = $page < $totalPages ? $page + 1 : null;
		$data['prev_page']      = $page > 1 ? $page - 1 : null;
		$data['total_pages']    = $totalPages;

		$findCriteria->order    = 't.subscriber_id DESC';
		$findCriteria->limit    = $perPage;
		$findCriteria->offset   = ($page - 1) * $perPage;

		$subscribers = ListSubscriber::model()->findAll($findCriteria);

		foreach ($subscribers as $subscriber) {
			$record = array('subscriber_uid' => null); // keep this first!
			foreach ($fields as $field) {

				if ($field->tag == 'EMAIL') {
					$record[$field->tag] = CHtml::encode($subscriber->displayEmail);
					continue;
				}

				$value = null;
				$criteria = new CDbCriteria();
				$criteria->select = 'value';
				$criteria->compare('field_id', (int)$field->field_id);
				$criteria->compare('subscriber_id', (int)$subscriber->subscriber_id);
				$valueModels = ListFieldValue::model()->findAll($criteria);
				if (!empty($valueModels)) {
					$value = array();
					foreach($valueModels as $valueModel) {
						$value[] = $valueModel->value;
					}
					$value = implode(', ', $value);
				}
				$record[$field->tag] = CHtml::encode($value);
			}

			$record['subscriber_uid']   = $subscriber->subscriber_uid;
			$record['status']           = $subscriber->status;
			$record['source']           = $subscriber->source;
			$record['ip_address']       = $subscriber->ip_address;
			$record['date_added']       = $subscriber->date_added;

			$data['records'][] = $record;
		}

		return $this->renderJson(array(
			'status'    => 'success',
			'data'      => $data
		), 200);
	}
	
	/**
	 * @param $list_uid
	 *
	 * @return Lists|null
	 */
    public function loadListByUid($list_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('list_uid', $list_uid);
        $criteria->compare('customer_id', (int)Yii::app()->user->getId());
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        return Lists::model()->find($criteria);
    }

    /**
     * It will generate the timestamp that will be used to generate the ETAG for GET requests.
     */
    public function generateLastModified()
    {
        static $lastModified;

        if ($lastModified !== null) {
            return $lastModified;
        }

        $request = Yii::app()->request;
        $row = array();

        if ($this->action->id == 'index') {

            $listUid    = $request->getQuery('list_uid');
            $perPage    = (int)$request->getQuery('per_page', 10);
            $page       = (int)$request->getQuery('page', 1);

            $maxPerPage = 50;
            $minPerPage = 10;

            if ($perPage < $minPerPage) {
                $perPage = $minPerPage;
            }

            if ($perPage > $maxPerPage) {
                $perPage = $maxPerPage;
            }

            if ($page < 1) {
                $page = 1;
            }

            $list = Lists::model()->findByAttributes(array(
                'list_uid'      => $listUid,
                'customer_id'   => (int)Yii::app()->user->getId(),
            ));

            if (empty($list)) {
                return $lastModified = parent::generateLastModified();
            }

            $limit  = $perPage;
            $offset = ($page - 1) * $perPage;

            $sql = '
                SELECT AVG(t.last_updated) as `timestamp`
                FROM (
                     SELECT `a`.`list_id`, `a`.`status`, UNIX_TIMESTAMP(`a`.`last_updated`) as `last_updated`
                     FROM `{{list_subscriber}}` `a`
                     WHERE `a`.`list_id` = :lid
                     ORDER BY a.`subscriber_id` DESC
                     LIMIT :l OFFSET :o
                ) AS t
                WHERE `t`.`list_id` = :lid
            ';

            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':lid', (int)$list->list_id, PDO::PARAM_INT);
            $command->bindValue(':l', (int)$limit, PDO::PARAM_INT);
            $command->bindValue(':o', (int)$offset, PDO::PARAM_INT);

            $row = $command->queryRow();

        } elseif ($this->action->id == 'view') {

            $listUid        = $request->getQuery('list_uid');
            $subscriberUid  = $request->getQuery('subscriber_uid');

            $list = Lists::model()->findByAttributes(array(
                'list_uid'    => $listUid,
                'customer_id' => (int)Yii::app()->user->getId(),
            ));

            if (empty($list)) {
                return $lastModified = parent::generateLastModified();
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'subscriber_uid' => $subscriberUid,
                'list_id'        => $list->list_id,
            ));

            if (!empty($subscriber)) {
                $row['timestamp'] = strtotime($subscriber->last_updated);
            }
        }

        if (isset($row['timestamp'])) {
            $timestamp = round($row['timestamp']);
            if (preg_match('/\.(\d+)/', $row['timestamp'], $matches)) {
                $timestamp += (int)$matches[1];
            }
            return $lastModified = $timestamp;
        }

        return $lastModified = parent::generateLastModified();
    }

	/**
	 * @param $list
	 * @param $subscriber
	 *
	 * @return bool
	 * @throws Throwable
	 */
    protected function sendSubscribeConfirmationEmail($list, $subscriber)
    {
        if (!($server = DeliveryServer::pickServer(0, $list))) {
            return false;
        }

        $pageType = ListPageType::model()->findBySlug('subscribe-confirm-email');

        if (empty($pageType)) {
            return false;
        }

        $page = ListPage::model()->findByAttributes(array(
            'list_id'   => $list->list_id,
            'type_id'   => $pageType->type_id
        ));

        $content = !empty($page->content) ? $page->content : $pageType->content;
        $subject = !empty($page->email_subject) ? $page->email_subject : $pageType->email_subject;
        $options = Yii::app()->options;

        $subscribeUrl = $options->get('system.urls.frontend_absolute_url');
        $subscribeUrl .= 'lists/' . $list->list_uid . '/confirm-subscribe/' . $subscriber->subscriber_uid;

        // 1.5.3
        $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/update-profile/' . $subscriber->subscriber_uid;
        $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/unsubscribe/' . $subscriber->subscriber_uid;
        
        $searchReplace = array(
            '[LIST_NAME]'           => $list->display_name,
            '[COMPANY_NAME]'        => !empty($list->company) ? $list->company->name : null,
            '[SUBSCRIBE_URL]'       => $subscribeUrl,
            '[CURRENT_YEAR]'        => date('Y'),

            // 1.5.3
            '[UPDATE_PROFILE_URL]'  => $updateProfileUrl,
            '[UNSUBSCRIBE_URL]'     => $unsubscribeUrl,
            '[COMPANY_FULL_ADDRESS]'=> !empty($list->company) ? nl2br($list->company->getFormattedAddress()) : null,
        );
        
        //
        $subscriberCustomFields = $subscriber->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        //
        
        $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
        $subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $subject);

        // 1.5.3
        if (CampaignHelper::isTemplateEngineEnabled()) {
            $content = CampaignHelper::parseByTemplateEngine($content, $searchReplace);
            $subject = CampaignHelper::parseByTemplateEngine($subject, $searchReplace);
        }
            
        $params = array(
            'to'        => $subscriber->email,
            'fromName'  => $list->default->from_name,
            'subject'   => $subject,
            'body'      => $content,
        );

        $sent = false;
        for ($i = 0; $i < 3; ++$i) {
            if ($sent = $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($list)->sendEmail($params)) {
                break;
            }
            if (!($server = DeliveryServer::pickServer($server->server_id, $list))) {
                break;
            }
        }

        return $sent;
    }

	/**
	 * @param $list
	 * @param $subscriber
	 *
	 * @throws Throwable
	 */
    protected function sendSubscribeWelcomeEmail($list, $subscriber)
    {
        if ($list->welcome_email != Lists::TEXT_YES) {
            return;
        }

        $pageType = ListPageType::model()->findBySlug('welcome-email');
        if (!($server = DeliveryServer::pickServer(0, $list))) {
            $pageType = null;
        }

        if (empty($pageType)) {
            return;
        }

        $page = ListPage::model()->findByAttributes(array(
            'list_id' => $list->list_id,
            'type_id' => $pageType->type_id
        ));

        $options          = Yii::app()->options;
        $_content         = !empty($page->content) ? $page->content : $pageType->content;
        $_subject         = !empty($page->email_subject) ? $page->email_subject : $pageType->email_subject;
        $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/update-profile/' . $subscriber->subscriber_uid;
        $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/unsubscribe/' . $subscriber->subscriber_uid;
        $searchReplace    = array(
            '[LIST_NAME]'           => $list->display_name,
            '[COMPANY_NAME]'        => !empty($list->company) ? $list->company->name : null,
            '[UPDATE_PROFILE_URL]'  => $updateProfileUrl,
            '[UNSUBSCRIBE_URL]'     => $unsubscribeUrl,
            '[COMPANY_FULL_ADDRESS]'=> !empty($list->company) ? nl2br($list->company->getFormattedAddress()) : null,
            '[CURRENT_YEAR]'        => date('Y'),
        );
        
        //
        $subscriberCustomFields = $subscriber->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        //
        
        $_content = str_replace(array_keys($searchReplace), array_values($searchReplace), $_content);
        $_subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $_subject);

        // 1.5.3
        if (CampaignHelper::isTemplateEngineEnabled()) {
            $_content = CampaignHelper::parseByTemplateEngine($_content, $searchReplace);
            $_subject = CampaignHelper::parseByTemplateEngine($_subject, $searchReplace);
        }
        
        $params = array(
            'to'        => $subscriber->email,
            'fromName'  => $list->default->from_name,
            'subject'   => $_subject,
            'body'      => $_content,
        );

        for ($i = 0; $i < 3; ++$i) {
            if ($server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($list)->sendEmail($params)) {
                break;
            }
            if (!($server = DeliveryServer::pickServer($server->server_id, $list))) {
                break;
            }
        }
    }

	/**
	 * @param $list
	 * @param $subscriber
	 * @param $customer
	 *
	 * @throws Throwable
	 */
	protected function handleListSubscriberMustApprove($list, $subscriber, $customer)
	{
		// since 1.3.6.2
		$mustApprove = $list->subscriber_require_approval == Lists::TEXT_YES && $subscriber->getIsUnapproved();
		if ($mustApprove && !($server = DeliveryServer::pickServer(0, $list))) {
			$subscriber->status = ListSubscriber::STATUS_CONFIRMED;
			$mustApprove = false;
		}

		if ($mustApprove) {

			$fields     = array();
			$listFields = ListField::model()->findAll(array(
				'select'    => 'field_id, label',
				'condition' => 'list_id = :lid',
				'params'    => array(':lid' => (int)$list->list_id),
			));

			foreach ($listFields as $field) {
				$fieldValues = ListFieldValue::model()->findAll(array(
					'select'    => 'value',
					'condition' => 'subscriber_id = :sid AND field_id = :fid',
					'params'    => array(':sid' => (int)$subscriber->subscriber_id, ':fid' => (int)$field->field_id),
				));
				$values = array();
				foreach ($fieldValues as $value) {
					$values[] = $value->value;
				}
				$fields[$field->label] = implode(', ', $values);
			}

			$submittedData = array();
			foreach ($fields as $key => $value) {
				$submittedData[] = sprintf('%s: %s', $key, $value);
			}
			$submittedData = implode('<br />', $submittedData);

			$options = Yii::app()->options;
			$params  = CommonEmailTemplate::getAsParamsArrayBySlug('new-list-subscriber',
				array(
					'fromName'  => $list->default->from_name,
					'subject'   => Yii::t('lists', 'New list subscriber!'),
				), array(
					'[LIST_NAME]'      => $list->name,
					'[DETAILS_URL]'    => $options->get('system.urls.customer_absolute_url') . sprintf('lists/%s/subscribers/%s/update', $this->list_uid, $subscriber->subscriber_uid),
					'[SUBMITTED_DATA]' => $submittedData,
				)
			);

			$recipients = explode(',', $list->customerNotification->subscribe_to);
			$recipients = array_map('trim', $recipients);

			foreach ($recipients as $recipient) {
				if (!FilterVarHelper::email($recipient)) {
					continue;
				}
				$params['to'] = array($recipient => $customer->getFullName());
				$server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($list)->sendEmail($params);
			}

		} else {

			if ($list->opt_in == Lists::OPT_IN_DOUBLE) {
				if ($subscriber->isUnconfirmed) {
					$this->sendSubscribeConfirmationEmail($list, $subscriber);
				}
			} else {
				if ($subscriber->isConfirmed) {
					// since 1.3.5 - this should be expanded in future
					$subscriber->takeListSubscriberAction(ListSubscriberAction::ACTION_SUBSCRIBE);

					// since 1.3.5.4 - send the welcome email
					$this->sendSubscribeWelcomeEmail($list, $subscriber);
				}
			}
		}
	}
}
