/*
** Copyright (C) 2001-2024 Zabbix SIA
**
** This program is free software: you can redistribute it and/or modify it under the terms of
** the GNU Affero General Public License as published by the Free Software Foundation, version 3.
**
** This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
** without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
** See the GNU Affero General Public License for more details.
**
** You should have received a copy of the GNU Affero General Public License along with this program.
** If not, see <https://www.gnu.org/licenses/>.
**/
#include "zbxmocktest.h"
#include "zbxmockdata.h"
#include "zbxmockutil.h"
#include "zbxmockassert.h"

#include "zbxstr.h"

void	zbx_mock_test_entry(void **state)
{
	ZBX_UNUSED(state);

	const char	*list = zbx_mock_get_parameter_string("in.list");
	const char	*value = zbx_mock_get_parameter_string("in.value");
	char		delimiter = *zbx_mock_get_parameter_string("in.delimiter");
	int		exp_result = zbx_mock_str_to_return_code(zbx_mock_get_parameter_string("out.return"));
	int		act_result = zbx_str_in_list(list,  value, delimiter);

	zbx_mock_assert_int_eq("return value", exp_result, act_result);
}
