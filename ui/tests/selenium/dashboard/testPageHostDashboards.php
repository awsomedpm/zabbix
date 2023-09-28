<?php
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

require_once dirname(__FILE__) . '/../../include/CWebTest.php';

/**
 * @onBefore prepareHostDashboardsData
 *
 * @backup hosts
 */
class testPageHostDashboards extends CWebTest {

	protected const HOST_NAME = 'Host for Host Dashboards';
	protected const TEMPLATE_NAME = 'Template for '.self::HOST_NAME;

	public function prepareHostDashboardsData() {
		$data = [
			'host_name' => self::HOST_NAME,
			'dashboards' => [
				[
					'name' => 'Dashboard 1',
					'pages' => [
						[
							'name' => 'Page 1',
							'widgets' => [
								[
									'type' => 'svggraph',
									'name' => 'Graph widget',
									'width' => 6,
									'height' => 4,
									'fields' => [
										[
											'type' => 0,
											'name' => '*',
											'value' => 0
										]
									]
								]
							]
						]
					]
				]
			]
		];

		$this->createHostWithDashboards($data);
	}

	/**
	 * Check layout.
	 */
	public function testPageHostDashboards_Layout() {
		$this->openDashboardsForHost(self::HOST_NAME);

		$this->page->assertTitle('Dashboards');
		$this->page->assertHeader('Host dashboards');

		$breadcrumbs = $this->query('class:breadcrumbs')->one();
		$this->assertEquals('zabbix.php?action=host.view', $breadcrumbs->query('link:All hosts')->one()->getAttribute('href'));
		$this->assertEquals(self::HOST_NAME, $breadcrumbs->query('xpath:./li[2]/span')->one()->getText());

		$dashboard_nav = $this->query('class:host-dashboard-navigation')->one();

		$prev_button = $dashboard_nav->query('xpath:.//button[@title="Previous dashboard"]')->one();
		$this->assertTrue($prev_button->isDisplayed());
		$this->assertFalse($prev_button->isEnabled());

		$dasboard_tab = $dashboard_nav->query('xpath:.//span[text()="Dashboard 1"]')->one();
		$this->assertEquals('Dashboard 1', $dasboard_tab->getAttribute('title'));

		// Assert the listed dashboard dropdown.
		$list_button = $dashboard_nav->query('xpath:.//button[@title="Dashboard list"]')->one();
		$this->assertTrue($list_button->isClickable());
		$list_button->click();
		$popup_menu = $list_button->asPopupButton()->getMenu();
		$this->assertEquals(['Dashboard 1'], $popup_menu->getItems()->asText());
		$popup_menu->close();

		$next_button = $dashboard_nav->query('xpath:.//button[@title="Next dashboard"]')->one();
		$this->assertTrue($next_button->isDisplayed());
		$this->assertFalse($next_button->isEnabled());
	}

	/**
	 * Open and close the Kiosk mode.
	 */
	public function testPageHostDashboards_CheckKioskMode() {
		$this->openDashboardsForHost(self::HOST_NAME);

		// Test Kiosk mode.
		$this->query('xpath://button[@title="Kiosk mode"]')->one()->click();
		$this->page->waitUntilReady();

		// Check that Header and Filter disappeared.
		$this->query('xpath://h1[@id="page-title-general"]')->waitUntilNotVisible();
		$this->assertFalse($this->query('xpath://div[@aria-label="Filter"]')->exists());
		$this->assertFalse($this->query('class:host-dashboard-navigation')->exists());
		$this->assertTrue($this->query('class:dashboard')->exists());

		$this->query('xpath://button[@title="Normal view"]')->waitUntilPresent()->one()->click(true);
		$this->page->waitUntilReady();

		// Check that Header and Filter are visible again.
		$this->query('xpath://h1[@id="page-title-general"]')->waitUntilVisible();
		$this->assertTrue($this->query('xpath://div[@aria-label="Filter"]')->exists());
		$this->assertTrue($this->query('class:host-dashboard-navigation')->exists());
		$this->assertTrue($this->query('class:dashboard')->exists());
	}

	public function getCheckFiltersData() {
		return [
			[
				[
					'fields' => ['id:from' => 'now-2h', 'id:to' => 'now-1h'],
					'expected' => 'now-2h – now-1h',
					'zoom_buttons' => [
						'js-btn-time-left' => true,
						'btn-time-zoomout' => true,
						'js-btn-time-right' => true
					]
				]
			],
			[
				[
					'fields' => ['id:from' => 'now-2y', 'id:to' => 'now-1y'],
					'expected' => 'now-2y – now-1y',
					'zoom_buttons' => [
						'js-btn-time-left' => true,
						'btn-time-zoomout' => true,
						'js-btn-time-right' => true
					]
				]
			],
			[
				[
					'link' => 'Last 30 days',
					'zoom_buttons' => [
						'js-btn-time-left' => true,
						'btn-time-zoomout' => true,
						'js-btn-time-right' => false
					]
				]
			],
			[
				[
					'link' => 'Last 2 years',
					'zoom_buttons' => [
						'js-btn-time-left' => true,
						'btn-time-zoomout' => false,
						'js-btn-time-right' => false
					]
				]
			]
		];
	}

	/**
	 * Change values in the filter section and check the resulting changes.
	 *
	 * @dataProvider getCheckFiltersData
	 */
	public function testPageHostDashboards_CheckFilters($data) {
		$this->openDashboardsForHost(self::HOST_NAME);
		$form = $this->query('class:filter-container')->asForm(['normalized' => true])->one();

		// Set custom time filter.
		if (CTestArrayHelper::get($data, 'fields')) {
			$form->fill($data['fields']);
			$form->query('id:apply')->one()->click();
		}
		else {
			$form->query('link', $data['link'])->waitUntilClickable()->one()->click();
		}

		$this->page->waitUntilReady();

		// Check Zoom buttons.
		foreach ($data['zoom_buttons'] as $button => $state) {
			$this->assertTrue($this->query('xpath://button[contains(@class, '.CXPathHelper::escapeQuotes($button).
				')]')->one()->isEnabled($state)
			);
		}

		// Check tab title.
		$this->assertTrue($this->query('link',
				CTestArrayHelper::get($data, 'expected', CTestArrayHelper::get($data, 'link')))->exists()
		);
	}

	public function getCheckNavigationData() {
		return [
			[
				[
					'host_name' => 'One Dashboard - one Page',
					'dashboards' => [['name' => 'Dashboard 1']]
				]
			],
			[
				[
					'host_name' => 'One Dashboard - three Pages',
					'dashboards' => [
						[
							'name' => 'Dashboard 1',
							'pages' => [['name' => 'Page 1'], ['name' => 'Page 2'], ['name' => 'Page 3']]
						]
					]
				]
			],
			[
				[
					'host_name' => 'Three Dashboards - three Pages each',
					'dashboards' => [
						[
							'name' => 'Dashboard 1',
							'pages' => [['name' => 'Page 11'], ['name' => 'Page 12'], ['name' => 'Page 13']]
						],
						[
							'name' => 'Dashboard 2',
							'pages' => [['name' => 'Page 21'], ['name' => 'Page 22'], ['name' => 'Page 23']]
						],
						[
							'name' => 'Dashboard 3',
							'pages' => [['name' => 'Page 31'], ['name' => 'Page 32'], ['name' => 'Page 33']]
						]
					]
				]
			],
			[
				[
					'host_name' => 'Unicode Dashboards',
					'dashboards' => [
						['name' => '🙂🙃'],
						['name' => 'test тест 测试 テスト ทดสอบ'],
						['name' => '<script>alert("hi!");</script>'],
						['name' => '&nbsp; &amp;'],
						['name' => '☺♥²©™"\'']
					]
				]
			],
			[
				[
					'host_name' => 'Unicode Pages',
					'dashboards' => [
						[
							'name' => 'Dashboard 1',
							'pages' => [
								['name' => '🙂🙃'],
								['name' => 'test тест 测试 テスト ทดสอบ'],
								['name' => '<script>alert("hi!");</script>'],
								['name' => '&nbsp; &amp;'],
								['name' => '☺♥²©™"\'']
							]
						]
					]
				]
			],
			[
				[
					'host_name' => 'Long names',
					'dashboards' => [
						[
							'name' => STRING_255,
							'pages' => [['name' => STRING_255], ['name' => STRING_128]]
						]
					]
				]
			],
			[
				[
					'host_name' => 'Many Dashboards',
					'dashboards' => [
						['name' => 'Dashboard 1'],
						['name' => 'Dashboard 2'],
						['name' => 'Dashboard 3'],
						['name' => 'Dashboard 4'],
						['name' => 'Dashboard 5'],
						['name' => 'Dashboard 6'],
						['name' => 'Dashboard 7'],
						['name' => 'Dashboard 8'],
						['name' => 'Dashboard 9'],
						['name' => 'Dashboard 10'],
						['name' => 'Dashboard 11'],
						['name' => 'Dashboard 12'],
						['name' => 'Dashboard 13'],
						['name' => 'Dashboard 14'],
						['name' => 'Dashboard 15']
					]
				]
			]
		];
	}

	/**
	 * Check Dashboard Tab navigation.
	 *
	 * @dataProvider getCheckNavigationData
	 */
	public function testPageHostDashboards_CheckNavigation($data) {
		// Create the required entities in database.
		$api_dashboards = $this->createHostWithDashboards($data);

		$this->openDashboardsForHost($data['host_name']);

		// Parent to all Dashboard navigation elements.
		$nav = $this->query('class:host-dashboard-navigation')->one();

		// Assert buttons.
		$prev_button = $nav->query('xpath:.//button[@title="Previous dashboard"]')->one();
		$this->assertFalse($prev_button->isEnabled());
		$next_button = $nav->query('xpath:.//button[@title="Next dashboard"]')->one();
		$this->assertEquals(count($api_dashboards) > 1, $next_button->isEnabled());

		// Assert dashboard Tabs and Pages.
		foreach ($api_dashboards as $i => $dashboard) {
			$dasboard_tab = $nav->query('xpath:.//span[text()='.CXPathHelper::escapeQuotes($dashboard['name']).']')->one();
			$this->assertEquals($dashboard['name'], $dasboard_tab->getAttribute('title'));

			// Only switch the Dashboard if it is not the first one.
			if ($i > 0) {
				$dasboard_tab->click();
				$this->page->waitUntilReady();
			}

			// Check Page switching.
			// It is expected that in every page there will be a Widget named like so: 'Dashboard 1 - Page 2 widget'.
			if (count($dashboard['pages']) === 1) {
				// Case when there is only one Page. The Page button is not even visible.
				$this->assertTrue($this->query('xpath://h4[text()='.
						CXPathHelper::escapeQuotes(
						$this->widgetName($dashboard['name'], $dashboard['pages'][0]['name'])).']')->exists());
			}
			else {
				// When a Dashboard contains several Pages.
				$page_tabs = $this->query('class:dashboard-navigation-tabs')->one();

				foreach ($dashboard['pages'] as $j => $page) {
					$page_tab = $page_tabs->query('xpath:.//span[text()='.CXPathHelper::escapeQuotes($page['name']).']')->one();
					$this->assertEquals($page['name'], $page_tab->getAttribute('title'));

					// Only switch the Page if it is not the first one.
					if ($j > 0) {
						$page_tab->click();
						$this->page->waitUntilReady();
					}

					// Assert the widget name.
					$this->assertTrue($this->query('xpath://h4[text()='.
							CXPathHelper::escapeQuotes($this->widgetName($dashboard['name'], $page['name'])).']')->exists());
				}
			}


		}

		// Assert the Dashboard dropdown.
		$list_button = $nav->query('xpath:.//button[@title="Dashboard list"]')->one();
		$list_button->click();
		$popup_menu = $list_button->asPopupButton()->getMenu();
		$this->assertEquals(array_column($api_dashboards, 'name'), $popup_menu->getItems()->asText());
		$popup_menu->close();
	}

	/**
	 * Opens the 'Host dashboards' page for a specific host.
	 *
	 * @param $host_name    name of the Host to open Dashboards for
	 */
	protected function openDashboardsForHost($host_name) {
		// Instead of searching the Host in the UI it is faster to just get the ID from the database.
		$id = CDBHelper::getValue('SELECT hostid FROM hosts WHERE host='.zbx_dbstr($host_name));
		$this->page->login()->open('zabbix.php?action=host.dashboard.view&hostid='.$id);
		$this->page->waitUntilReady();
	}

	/**
	 * Creates a Template with required Dashboards using API and assigns it to a new Host.
	 *
	 * @param $data    data from data provider
	 *
	 * @returns array    dashboard data, that was actually sent to the API (with the defaults set)
	 */
	protected function createHostWithDashboards($data) {
		$response = CDataHelper::createTemplates([
			[
				'host' => 'Template for '.$data['host_name'],
				'groups' => [
					['groupid' => '1']
				]
			]
		]);
		$template_id = $response['templateids']['Template for '.$data['host_name']];

		CDataHelper::createHosts([
			[
				'host' => $data['host_name'],
				'groups' => [
					['groupid' => '6']
				],
				'templates' => [
					'templateid' => $template_id
				]
			]
		]);

		// Add all resulting dashboard data and then return.
		$api_dashboards = [];

		foreach ($data['dashboards'] as $dashboard) {
			// Set Template ID.
			$dashboard['templateid'] = $template_id;

			// Add the default Dashboard Page if none set.
			if (!array_key_exists('pages', $dashboard)) {
				$dashboard['pages'] = [
					[
						'name' => 'Page 1',
						'widgets' => [
							[
								'type' => 'clock',
								'name' => $this->widgetName($dashboard['name'], 'Page 1'),
								'width' => 6,
								'height' => 4
							]
						]
					]
				];
			}

			// Add default widgets if missing, the name is important.
			foreach ($dashboard['pages'] as $i => $page) {
				if (!array_key_exists('widgets', $dashboard['pages'][$i])) {
					$dashboard['pages'][$i]['widgets'] = [
						[
							'type' => 'clock',
							'name' => $this->widgetName($dashboard['name'], $page['name']),
							'width' => 6,
							'height' => 4
						]
					];
				}
			}

			// Create the Dashboard with API.
			CDataHelper::call('templatedashboard.create', [
				$dashboard
			]);

			$api_dashboards[] = $dashboard;
		}

		// The dashboard tabs are sorted alphabetically.
		CTestArrayHelper::usort($api_dashboards, ['name']);
		return $api_dashboards;
	}

	/**
	 * Create a widget name from the dashboard and page name.
	 * The name is used for making sure the correct dashboard has successfully opened.
	 *
	 * @param $dashboard_name    name of the dashboard this widget is on
	 * @param $page_name         name of the page this widget is on
	 *
	 * @return string            calculated widget name
	 */
	protected function widgetName($dashboard_name, $page_name) {
		// Widget name max length 255.
		return substr($dashboard_name.' - '.$page_name.' widget', 0, 255);
	}

}
