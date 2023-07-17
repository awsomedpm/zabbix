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
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
**/


/**
 * Pie chart widget form view.
 *
 * @var CView $this
 * @var array $data
 */

use Widgets\PieChart\Includes\{
	CWidgetFieldDataSet,
	CWidgetFieldDataSetView
};

$form = (new CWidgetFormView($data));

$form_tabs = (new CTabView())
	->addTab('data_set', _('Data set'), getDatasetTab($form, $data['fields']),
		TAB_INDICATOR_PIE_CHART_DATASET
	)
	->addTab('displaying_options', _('Displaying options'), getDisplayOptionsTab($form, $data['fields']),
		TAB_INDICATOR_PIE_CHART_DISPLAY_OPTIONS
	)
	->addTab('time_period', _('Time period'), getTimePeriodTab($form, $data['fields']),
		TAB_INDICATOR_PIE_CHART_TIME
	)
	->addTab('legend_tab', _('Legend'), getLegendTab($form, $data['fields']),
		TAB_INDICATOR_PIE_CHART_LEGEND
	)
	->setSelected(0)
	->addClass('pie-chart-widget-config-tabs');

$form
	->addItem($form_tabs)
	->addJavaScript($form_tabs->makeJavascript())
	->includeJsFile('widget.edit.js.php')
	->addJavaScript('widget_pie_chart_form.init('.json_encode([
			'form_tabs_id' => $form_tabs->getId(),
			'color_palette' => CWidgetFieldDataSet::DEFAULT_COLOR_PALETTE,
			'templateid' => $data['templateid']
		], JSON_THROW_ON_ERROR).');')
	->show();

function getDatasetTab(CWidgetFormView $form, array $fields): array {
	$dataset_field = $form->registerField(new CWidgetFieldDataSetView($fields['ds']));

	return [
		(new CDiv($dataset_field->getView()))->addClass(ZBX_STYLE_LIST_VERTICAL_ACCORDION),
		(new CDiv($dataset_field->getFooterView()))->addClass(ZBX_STYLE_LIST_ACCORDION_FOOT)
	];
}

function getDisplayOptionsTab(CWidgetFormView $form, array $fields): CDiv {
	$source_field = $form->registerField(new CWidgetFieldRadioButtonListView($fields['source']));
	$draw_type_field = $form->registerField(new CWidgetFieldRadioButtonListView($fields['draw_type']));
	$width_field = $form->registerField(new CWidgetFieldRangeControlView($fields['width']));
	$space_field = $form->registerField(new CWidgetFieldRangeControlView($fields['space']));
	$merge_field = $form->registerField(new CWidgetFieldCheckBoxView($fields['merge']));
	$merge_percent_field = $form->registerField(new CWidgetFieldIntegerBoxView($fields['merge_percent']));
	$merge_color_field = $form->registerField(new CWidgetFieldColorView($fields['merge_color']));
	$total_show_field = $form->registerField(new CWidgetFieldCheckBoxView($fields['total_show']));
	$value_size_field = $form->registerField(new CWidgetFieldIntegerBoxView($fields['value_size']));
	$decimal_places_field = $form->registerField(new CWidgetFieldIntegerBoxView($fields['decimal_places']));
	$units_show_field = $form->registerField(new CWidgetFieldCheckBoxView($fields['units_show']));
	$units_field = $form->registerField(new CWidgetFieldTextBoxView($fields['units']));
	$value_bold_field = $form->registerField(new CWidgetFieldCheckBoxView($fields['value_bold']));
	$value_color_field = $form->registerField(new CWidgetFieldColorView($fields['value_color']));

	return (new CDiv())
		->addClass(ZBX_STYLE_GRID_COLUMNS)
		->addClass(ZBX_STYLE_GRID_COLUMNS_2)
		->addItem(
			(new CFormGrid())
				->addItem([
					$source_field->getLabel(),
					new CFormField($source_field->getView())
				])
				->addItem([
					$draw_type_field->getLabel(),
					new CFormField($draw_type_field->getView())
				])
				->addItem([
					$width_field->getLabel()->setId('width_label'),
					(new CFormField([$width_field->getView(), ' %']))->setId('width_range')
				])
				->addItem([
					$space_field->getLabel(),
					new CFormField($space_field->getView())
				])
				->addItem([
					$merge_field->getLabel(),
					(new CFormField([
						$merge_field->getView(),
						($merge_percent_field->getView())->setWidth(ZBX_TEXTAREA_NUMERIC_SMALL_WIDTH),
						' % ',
						$merge_color_field->getView()
					]))
				])

		)
		->addItem(
			(new CFormGrid())
				->addItem([
					$total_show_field->getLabel(),
					new CFormField($total_show_field->getView())
				])
				->addItem([
					$value_size_field->getLabel(),
					(new CFormField([$value_size_field->getView(), ' %']))
				])
				->addItem([
					$decimal_places_field->getLabel(),
					new CFormField($decimal_places_field->getView())
				])
				->addItem([
					$units_show_field->getView(),
					(new CFormField(($units_field->getView())->setWidth(ZBX_TEXTAREA_MEDIUM_WIDTH)))
				])
				->addItem([
					$value_bold_field->getLabel(),
					new CFormField($value_bold_field->getView())
				])
				->addItem([
					$value_color_field->getLabel(),
					new CFormField($value_color_field->getView())
				])->setId('show_total_fields')
		);
}

function getTimePeriodTab(CWidgetFormView $form, array $fields): CFormGrid {
	$set_time_field = $form->registerField(new CWidgetFieldCheckBoxView($fields['graph_time']));
	$time_from_field = $form->registerField(
		(new CWidgetFieldDatePickerView($fields['time_from']))
			->setDateFormat(ZBX_FULL_DATE_TIME)
			->setPlaceholder(_('YYYY-MM-DD hh:mm:ss'))
	);
	$time_to_field = $form->registerField(
		(new CWidgetFieldDatePickerView($fields['time_to']))
			->setDateFormat(ZBX_FULL_DATE_TIME)
			->setPlaceholder(_('YYYY-MM-DD hh:mm:ss'))
	);

	return (new CFormGrid())
		->addItem([
			$set_time_field->getLabel(),
			new CFormField($set_time_field->getView())
		])
		->addItem([
			$time_from_field->getLabel(),
			new CFormField($time_from_field->getView())
		])
		->addItem([
			$time_to_field->getLabel(),
			new CFormField($time_to_field->getView())
		]);
}

function getLegendTab(CWidgetFormView $form, array $fields): CFormGrid {
	$show_legend_field = $form->registerField(new CWidgetFieldCheckBoxView($fields['legend']));
	$legend_lines_field = $form->registerField(new CWidgetFieldRangeControlView($fields['legend_lines']));
	$legend_columns_field = $form->registerField(new CWidgetFieldRangeControlView($fields['legend_columns']));

	return (new CFormGrid())
		->addItem([
			$show_legend_field->getLabel(),
			new CFormField($show_legend_field->getView())
		])
		->addItem([
			$legend_lines_field->getLabel(),
			new CFormField($legend_lines_field->getView())
		])
		->addItem([
			$legend_columns_field->getLabel(),
			new CFormField($legend_columns_field->getView())
		]);
}
