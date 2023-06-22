<?php declare(strict_types = 0);
/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
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


class CControllerTriggerUpdate extends CController {

	protected function init(): void {
		$this->setPostContentType(self::POST_CONTENT_TYPE_JSON);
	}

	protected function checkInput(): bool {
		$fields = [
			'comments' =>							'db triggers.comments',
			'context' =>							'in '.implode(',', ['host', 'template']),
			'correlation_mode' =>					'db triggers.correlation_mode',
			'correlation_tag' =>					'db triggers.correlation_tag',
			'dependencies' =>						'array',
			'description' =>						'required|db triggers.description|not_empty',
			'event_name' =>							'db triggers.event_name',
			'expression' =>							'required|db triggers.expression|not_empty',
			'hostid' =>								'db hosts.hostid',
			'manual_close' =>						'db triggers.manual_close',
			'opdata' =>								'db triggers.opdata',
			'priority' =>							'db triggers.priority',
			'recovery_expression' =>				'db triggers.recovery_expression',
			'recovery_mode' =>						'db triggers.recovery_mode',
			'status' =>								'db triggers.status',
			'tags' =>								'array',
			'triggerid' =>							'fatal|required|db triggers.triggerid',
			'type' =>								'db triggers.type',
			'url' =>								'db triggers.url',
			'url_name' =>							'db triggers.url_name'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(
				(new CControllerResponseData(['main_block' => json_encode([
					'error' => [
						'messages' => array_column(get_and_clear_messages(), 'message')
					]
				])]))->disableView()
			);
		}

		return $ret;
	}

	protected function checkPermissions(): bool {
		if ($this->getInput('hostid') && !isWritableHostTemplates([$this->getInput('hostid')])) {
			return false;
		}

		return $this->checkAccess(CRoleHelper::UI_CONFIGURATION_HOSTS);
	}

	protected function doAction(): void {
		$tags = $this->getInput('tags', []);

		// Unset empty and inherited tags.
		foreach ($tags as $key => $tag) {
			if ($tag['tag'] === '' && $tag['value'] === '') {
				unset($tags[$key]);
			}
			elseif (array_key_exists('type', $tag) && !($tag['type'] & ZBX_PROPERTY_OWN)) {
				unset($tags[$key]);
			}
			else {
				unset($tags[$key]['type']);
			}
		}

		$trigger = [
			'triggerid' => $this->getInput('triggerid'),
			'description' => $this->getInput('description'),
			'event_name' => $this->getInput('event_name', ''),
			'opdata' => $this->getInput('opdata', ''),
			'expression' => $this->getInput('expression'),
			'recovery_mode' => $this->getInput('recovery_mode', ZBX_RECOVERY_MODE_EXPRESSION),
			'type' => $this->getInput('type', 0),
			'url_name' => $this->getInput('url_name', ''),
			'url' => $this->getInput('url', ''),
			'priority' => $this->getInput('priority', TRIGGER_SEVERITY_NOT_CLASSIFIED),
			'comments' => $this->getInput('comments', ''),
			'tags' => $tags,
			'manual_close' => $this->getInput('manual_close', ZBX_TRIGGER_MANUAL_CLOSE_NOT_ALLOWED),
			'dependencies' => zbx_toObject($this->getInput('dependencies', []), 'triggerid'),
			'status' => $this->hasInput('status') ? TRIGGER_STATUS_ENABLED : TRIGGER_STATUS_DISABLED
		];


		switch ($trigger['recovery_mode']) {
			case ZBX_RECOVERY_MODE_RECOVERY_EXPRESSION:
				$trigger['recovery_expression'] = $this->getInput('recovery_expression', '');
			// break; is not missing here.

			case ZBX_RECOVERY_MODE_EXPRESSION:
				$trigger['correlation_mode'] = $this->getInput('correlation_mode', ZBX_TRIGGER_CORRELATION_NONE);

				if ($trigger['correlation_mode'] == ZBX_TRIGGER_CORRELATION_TAG) {
					$trigger['correlation_tag'] = $this->getInput('correlation_tag', '');
				}
				break;
		}

		$result = (bool) API::Trigger()->update($trigger);

		if ($result) {
			$output['success']['title'] = _('Trigger updated');

			if ($messages = get_and_clear_messages()) {
				$output['success']['messages'] = array_column($messages, 'message');
			}
		}
		else {
			$output['error'] = [
				'title' => _('Cannot update trigger'),
				'messages' => array_column(get_and_clear_messages(), 'message')
			];
		}

		$this->setResponse(new CControllerResponseData(['main_block' => json_encode($output)]));
	}
}
