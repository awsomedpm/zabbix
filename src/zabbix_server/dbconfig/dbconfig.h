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

#ifndef ZABBIX_DBCONFIG_H
#define ZABBIX_DBCONFIG_H

#include "zbxthreads.h"
#include "zbxvault.h"

typedef struct
{
	zbx_config_vault_t	*config_vault;
	int			config_timeout;
	int			proxyconfig_frequency;
	int			proxydata_frequency;
	int			config_confsyncer_frequency;
	const char		*config_source_ip;
	const char		*config_ssl_ca_location;
	const char		*config_ssl_cert_location;
	const char		*config_ssl_key_location;
}
zbx_thread_dbconfig_args;

ZBX_THREAD_ENTRY(dbconfig_thread, args);

#endif
