<?php
/*
** Zabbix
** Copyright (C) 2001-2018 Zabbix SIA
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


class CRouter {
	/**
	 * Layout used for view rendering.
	 *
	 * @var string
	 */
	private $layout = null;

	/**
	 * Controller class for action handling.
	 *
	 * @var string
	 */
	private $controller = null;

	/**
	 * View used to generate HTML, CSV, JSON and other content.
	 *
	 * @var string
	 */
	private $view = null;

	/**
	 * Unique action (request) identifier.
	 *
	 * @var string
	 */
	private $action = null;

	/**
	 * Mapping between action and corresponding controller, layout and view.
	 *
	 * @var array
	 */
	private $routes = [
		// action							controller								layout					view
		'acknowledge.create'			=> ['CControllerAcknowledgeCreate',			null,					null],
		'acknowledge.edit'				=> ['CControllerAcknowledgeEdit',			'layout.htmlpage',		'monitoring.acknowledge.edit'],
		'dashboard.view'				=> ['CControllerDashboardView',				'layout.htmlpage',		'monitoring.dashboard.view'],
		'dashboard.list'				=> ['CControllerDashboardList',				'layout.htmlpage',		'monitoring.dashboard.list'],
		'dashboard.delete'				=> ['CControllerDashboardDelete',			null,					null],
		'dashboard.widget.edit'			=> ['CControllerDashboardWidgetEdit',		'layout.json',			'monitoring.dashboard.widget.edit'],
		'dashboard.widget.check'		=> ['CControllerDashboardWidgetCheck',		'layout.json',			null],
		'dashboard.widget.rfrate'		=> ['CControllerDashboardWidgetRfRate',		'layout.json',			null],
		'dashboard.get'					=> ['CControllerDashboardGet',				'layout.json',			null],
		'dashboard.properties.check'	=> ['CControllerDashboardPropertiesCheck',	'layout.json',			null],
		'dashboard.properties.edit'		=> ['CControllerDashboardPropertiesEdit',	'layout.json',			'dashboard.properties.edit'],
		'dashboard.share.edit'			=> ['CControllerDashboardShareEdit',		'layout.json',			'dashboard.sharing.edit'],
		'dashboard.share.update'		=> ['CControllerDashboardShareUpdate',		'layout.json',			null],
		'dashboard.update'				=> ['CControllerDashboardUpdate',			'layout.json',			null],
		'discovery.view'				=> ['CControllerDiscoveryView',				'layout.htmlpage',		'monitoring.discovery.view'],
		'favourite.create'				=> ['CControllerFavouriteCreate',			'layout.javascript',	null],
		'favourite.delete'				=> ['CControllerFavouriteDelete',			'layout.javascript',	null],
		'map.view'						=> ['CControllerMapView',					'layout.htmlpage',		'monitoring.map.view'],
		'mediatype.create'				=> ['CControllerMediatypeCreate',			null,					null],
		'mediatype.delete'				=> ['CControllerMediatypeDelete',			null,					null],
		'mediatype.disable'				=> ['CControllerMediatypeDisable',			null,					null],
		'mediatype.edit'				=> ['CControllerMediatypeEdit',				'layout.htmlpage',		'administration.mediatype.edit'],
		'mediatype.enable'				=> ['CControllerMediatypeEnable',			null,					null],
		'mediatype.list'				=> ['CControllerMediatypeList',				'layout.htmlpage',		'administration.mediatype.list'],
		'mediatype.update'				=> ['CControllerMediatypeUpdate',			null,					null],
		'problem.view'					=> ['CControllerProblemView',				'layout.htmlpage',		'monitoring.problem.view'],
		'problem.view.csv'				=> ['CControllerProblemView',				'layout.csv',			'monitoring.problem.view'],
		'profile.update'				=> ['CControllerProfileUpdate',				'layout.json',			null],
		'proxy.create'					=> ['CControllerProxyCreate',				null,					null],
		'proxy.delete'					=> ['CControllerProxyDelete',				null,					null],
		'proxy.edit'					=> ['CControllerProxyEdit',					'layout.htmlpage',		'administration.proxy.edit'],
		'proxy.hostdisable'				=> ['CControllerProxyHostDisable',			null,					null],
		'proxy.hostenable'				=> ['CControllerProxyHostEnable',			null,					null],
		'proxy.list'					=> ['CControllerProxyList',					'layout.htmlpage',		'administration.proxy.list'],
		'proxy.update'					=> ['CControllerProxyUpdate',				null,					null],
		'report.services'				=> ['CControllerReportServices',			'layout.htmlpage',		'report.services'],
		'report.status'					=> ['CControllerReportStatus',				'layout.htmlpage',		'report.status'],
		'script.create'					=> ['CControllerScriptCreate',				null,					null],
		'script.delete'					=> ['CControllerScriptDelete',				null,					null],
		'script.edit'					=> ['CControllerScriptEdit',				'layout.htmlpage',		'administration.script.edit'],
		'script.list'					=> ['CControllerScriptList',				'layout.htmlpage',		'administration.script.list'],
		'script.update'					=> ['CControllerScriptUpdate',				null,					null],
		'system.warning'				=> ['CControllerSystemWarning',				'layout.warning',		'system.warning'],
		'timeline.update'				=> ['CControllerTimelineUpdate',			'layout.json',			null],
		'web.view'						=> ['CControllerWebView',					'layout.htmlpage',		'monitoring.web.view'],
		'widget.favgraphs.view'			=> ['CControllerWidgetFavGraphsView',		'layout.widget',		'monitoring.widget.favgraphs.view'],
		'widget.favmaps.view'			=> ['CControllerWidgetFavMapsView',			'layout.widget',		'monitoring.widget.favmaps.view'],
		'widget.favscreens.view'		=> ['CControllerWidgetFavScreensView',		'layout.widget',		'monitoring.widget.favscreens.view'],
		'widget.discovery.view'			=> ['CControllerWidgetDiscoveryView',		'layout.widget',		'monitoring.widget.discovery.view'],
		'widget.graph.view'				=> ['CControllerWidgetGraphView',			'layout.widget',		'monitoring.widget.graph.view'],
		'widget.problemhosts.view'		=> ['CControllerWidgetProblemHostsView',	'layout.widget',		'monitoring.widget.problemhosts.view'],
		'widget.problems.view'			=> ['CControllerWidgetProblemsView',		'layout.widget',		'monitoring.widget.problems.view'],
		'widget.systeminfo.view'		=> ['CControllerWidgetSystemInfoView',		'layout.widget',		'monitoring.widget.systeminfo.view'],
		'widget.problemsbysv.view'		=> ['CControllerWidgetProblemsBySvView',	'layout.widget',		'monitoring.widget.problemsbysv.view'],
		'widget.web.view'				=> ['CControllerWidgetWebView',				'layout.widget',		'monitoring.widget.web.view'],
		'widget.clock.view'				=> ['CControllerWidgetClockView',			'layout.widget',		'monitoring.widget.clock.view'],
		'widget.map.view'				=> ['CControllerWidgetMapView',				'layout.widget',		'monitoring.widget.map.view'],
		'widget.navtree.view'			=> ['CControllerWidgetNavTreeView',			'layout.widget',		'monitoring.widget.navtree.view'],
		'widget.navtree.item.edit'		=> ['CControllerWidgetNavTreeItemEdit',		'layout.json',			null],
		'widget.navtree.item.update'	=> ['CControllerWidgetNavTreeItemUpdate',	'layout.json',			null],
		'widget.actionlog.view'			=> ['CControllerWidgetActionLogView',		'layout.widget',		'monitoring.widget.actionlog.view'],
		'widget.dataover.view'			=> ['CControllerWidgetDataOverView',		'layout.widget',		'monitoring.widget.dataover.view'],
		'widget.trigover.view'			=> ['CControllerWidgetTrigOverView',		'layout.widget',		'monitoring.widget.trigover.view'],
		'widget.url.view'				=> ['CControllerWidgetUrlView',				'layout.widget',		'monitoring.widget.url.view'],
		'widget.plaintext.view'			=> ['CControllerWidgetPlainTextView',		'layout.widget',		'monitoring.widget.plaintext.view'],
		'popup.generic'					=> ['CControllerPopupGeneric',				'layout.json',			'popup.generic'],
		'popup.httpstep'				=> ['CControllerPopupHttpStep',				'layout.json',			'popup.httpstep'],
		'popup.media'					=> ['CControllerPopupMedia',				'layout.json',			'popup.media'],
		'popup.scriptexec'				=> ['CControllerPopupScriptExec',			'layout.json',			'popup.scriptexec'],
		'popup.triggerexpr'				=> ['CControllerPopupTriggerExpr',			'layout.json',			'popup.triggerexpr'],
		'popup.services'				=> ['CControllerPopupServices',				'layout.json',			'popup.services'],
		'popup.testtriggerexpr'			=> ['CControllerPopupTestTriggerExpr',		'layout.json',			'popup.testtriggerexpr'],
		'popup.triggerwizard'			=> ['CControllerPopupTriggerWizard',		'layout.json',			'popup.triggerwizard'],
		'popup.trigdesc.view'			=> ['CControllerPopupTrigDescView',			'layout.json',			'popup.trigdesc.view'],
		'trigdesc.update'				=> ['CControllerTrigDescUpdate',			'layout.json',			null]
	];

	public function __construct($action) {
		$this->action = $action;
		$this->calculateRoute();
	}

	/**
	 * Locate and set controller, layout and view by action name.
	 *
	 * @return string
	 */
	public function calculateRoute() {
		if (array_key_exists($this->action, $this->routes)) {
			$this->controller = $this->routes[$this->action][0];
			$this->layout = $this->routes[$this->action][1];
			$this->view = $this->routes[$this->action][2];
		}
	}

	/**
	 * Returns layout name.
	 *
	 * @return string
	 */
	public function getLayout() {
		return $this->layout;
	}

	/**
	 * Returns controller name.
	 *
	 * @return string
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * Returns view name.
	 *
	 * @return string
	 */
	public function getView() {
		return $this->view;
	}

	/**
	 * Returns action name.
	 *
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}
}
