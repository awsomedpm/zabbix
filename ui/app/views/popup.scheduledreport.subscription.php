<?php
/*
** Zabbix
** Copyright (C) 2001-2021 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * @var CView $this
 */

$form = (new CForm())
	->cleanItems()
	->setId('subscription-form')
	->setName('subscription_form')
	->addVar('action', $data['action'])
	->addVar('subscriptionid', $data['subscriptionid'])
	->addVar('recipient_type', $data['recipient_type'])
	->addVar('recipient_name', $data['recipient_name'])
	->addVar('update', 1)
	->addItem((new CInput('submit', 'submit'))
		->addStyle('display: none;')
		->removeId()
	);

if ($data['edit']) {
	$form->addVar('edit', $data['edit']);
}

$form_grid = (new CFormGrid())->addClass(CFormGrid::ZBX_STYLE_FORM_GRID_1_1);

$recipient_multiselect = (new CMultiSelect(
	($data['recipient_type'] == ZBX_REPORT_RECIPIENT_TYPE_USER)
		? [
			'name' => 'recipientid',
			'object_name' => 'users',
			'multiple' => false,
			'data' => $data['recipient_ms'],
			'popup' => [
				'parameters' => [
					'srctbl' => 'users',
					'srcfld1' => 'userid',
					'srcfld2' => 'fullname',
					'dstfrm' => $form->getName(),
					'dstfld1' => 'recipientid'
				]
			],
			'add_post_js' => false
		]
		: [
			'name' => 'recipientid',
			'object_name' => 'usersGroups',
			'multiple' => false,
			'data' => $data['recipient_ms'],
			'popup' => [
				'parameters' => [
					'srctbl' => 'usrgrp',
					'srcfld1' => 'usrgrpid',
					'srcfld2' => 'name',
					'dstfrm' => $form->getName(),
					'dstfld1' => 'recipientid'
				]
			],
			'add_post_js' => false
		]
))->setWidth(ZBX_TEXTAREA_MEDIUM_WIDTH);

$form_grid
	->addItem([
		(new CLabel(_('Recipient'), 'recipientid'))->setAsteriskMark(),
		(new CFormField($recipient_multiselect))->addClass(CFormField::ZBX_STYLE_FORM_FIELD_FLUID)
	])
	->addItem([
		new CLabel(_('Generate report by'), 'creator_type'),
		(new CFormField(
			(new CRadioButtonList('creator_type', $data['creator_type']))
				->addValue(_('Current user'), ZBX_REPORT_CREATOR_TYPE_USER)
				->addValue(_('Recipient'), ZBX_REPORT_CREATOR_TYPE_RECIPIENT)
				->setModern(true)
		))->addClass(CFormField::ZBX_STYLE_FORM_FIELD_FLUID)
	]);

if ($data['recipient_type'] == ZBX_REPORT_RECIPIENT_TYPE_USER) {
	$form_grid->addItem([
		new CLabel(_('Status'), 'exclude'),
		(new CFormField(
			(new CRadioButtonList('exclude', $data['exclude']))
				->addValue(_('Include'), ZBX_REPORT_EXCLUDE_USER_FALSE)
				->addValue(_('Exclude'), ZBX_REPORT_EXCLUDE_USER_TRUE)
				->setModern(true)
		))->addClass(CFormField::ZBX_STYLE_FORM_FIELD_FLUID)
	]);
}

$form->addItem($form_grid);

$output = [
	'header' => $data['title'],
	'body' => $form->toString(),
	'script_inline' => $this->readJsFile('popup.scheduledreport.subscription.js.php'),
	'buttons' => [
		[
			'title' => $data['edit'] ? _('Update') : _('Add'),
			'keepOpen' => true,
			'isSubmit' => true,
			'action' => 'return submitScheduledReportSubscription(overlay);'
		]
	]
];

$output['script_inline'] .= $recipient_multiselect->getPostJS();

if (($messages = getMessages()) !== null) {
	$output['errors'] = $messages->toString();
}

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
	CProfiler::getInstance()->stop();
	$output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);
