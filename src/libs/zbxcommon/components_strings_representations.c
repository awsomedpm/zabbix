/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
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

#include "common.h"

/******************************************************************************
 *                                                                            *
 * Purpose: Returns process name                                              *
 *                                                                            *
 * Parameters: proc_type - [IN] process type; ZBX_PROCESS_TYPE_*              *
 *                                                                            *
 * Comments: used in internals checks zabbix["process",...], process titles   *
 *           and log files                                                    *
 *                                                                            *
 ******************************************************************************/
const char	*get_process_type_string(unsigned char proc_type)
{
	switch (proc_type)
	{
		case ZBX_PROCESS_TYPE_POLLER:
			return "poller";
		case ZBX_PROCESS_TYPE_UNREACHABLE:
			return "unreachable poller";
		case ZBX_PROCESS_TYPE_IPMIPOLLER:
			return "ipmi poller";
		case ZBX_PROCESS_TYPE_PINGER:
			return "icmp pinger";
		case ZBX_PROCESS_TYPE_JAVAPOLLER:
			return "java poller";
		case ZBX_PROCESS_TYPE_HTTPPOLLER:
			return "http poller";
		case ZBX_PROCESS_TYPE_TRAPPER:
			return "trapper";
		case ZBX_PROCESS_TYPE_SNMPTRAPPER:
			return "snmp trapper";
		case ZBX_PROCESS_TYPE_PROXYPOLLER:
			return "proxy poller";
		case ZBX_PROCESS_TYPE_ESCALATOR:
			return "escalator";
		case ZBX_PROCESS_TYPE_HISTSYNCER:
			return "history syncer";
		case ZBX_PROCESS_TYPE_DISCOVERER:
			return "discoverer";
		case ZBX_PROCESS_TYPE_ALERTER:
			return "alerter";
		case ZBX_PROCESS_TYPE_TIMER:
			return "timer";
		case ZBX_PROCESS_TYPE_HOUSEKEEPER:
			return "housekeeper";
		case ZBX_PROCESS_TYPE_DATASENDER:
			return "data sender";
		case ZBX_PROCESS_TYPE_CONFSYNCER:
			return "configuration syncer";
		case ZBX_PROCESS_TYPE_HEARTBEAT:
			return "heartbeat sender";
		case ZBX_PROCESS_TYPE_SELFMON:
			return "self-monitoring";
		case ZBX_PROCESS_TYPE_VMWARE:
			return "vmware collector";
		case ZBX_PROCESS_TYPE_COLLECTOR:
			return "collector";
		case ZBX_PROCESS_TYPE_LISTENER:
			return "listener";
		case ZBX_PROCESS_TYPE_ACTIVE_CHECKS:
			return "active checks";
		case ZBX_PROCESS_TYPE_TASKMANAGER:
			return "task manager";
		case ZBX_PROCESS_TYPE_IPMIMANAGER:
			return "ipmi manager";
		case ZBX_PROCESS_TYPE_ALERTMANAGER:
			return "alert manager";
		case ZBX_PROCESS_TYPE_PREPROCMAN:
			return "preprocessing manager";
		case ZBX_PROCESS_TYPE_PREPROCESSOR:
			return "preprocessing worker";
		case ZBX_PROCESS_TYPE_LLDMANAGER:
			return "lld manager";
		case ZBX_PROCESS_TYPE_LLDWORKER:
			return "lld worker";
		case ZBX_PROCESS_TYPE_ALERTSYNCER:
			return "alert syncer";
		case ZBX_PROCESS_TYPE_HISTORYPOLLER:
			return "history poller";
		case ZBX_PROCESS_TYPE_AVAILMAN:
			return "availability manager";
		case ZBX_PROCESS_TYPE_REPORTMANAGER:
			return "report manager";
		case ZBX_PROCESS_TYPE_REPORTWRITER:
			return "report writer";
		case ZBX_PROCESS_TYPE_SERVICEMAN:
			return "service manager";
		case ZBX_PROCESS_TYPE_TRIGGERHOUSEKEEPER:
			return "trigger housekeeper";
		case ZBX_PROCESS_TYPE_HA_MANAGER:
			return "ha manager";
		case ZBX_PROCESS_TYPE_ODBCPOLLER:
			return "odbc poller";
		case ZBX_PROCESS_TYPE_MAIN:
			return "main";
	}

	THIS_SHOULD_NEVER_HAPPEN;
	exit(EXIT_FAILURE);
}

int	get_process_type_by_name(const char *proc_type_str)
{
	int	i;

	for (i = 0; i < ZBX_PROCESS_TYPE_COUNT; i++)
	{
		if (0 == strcmp(proc_type_str, get_process_type_string((unsigned char)i)))
			return i;
	}

	for (i = ZBX_PROCESS_TYPE_EXT_FIRST; i <= ZBX_PROCESS_TYPE_EXT_LAST; i++)
	{
		if (0 == strcmp(proc_type_str, get_process_type_string((unsigned char)i)))
			return i;
	}

	return ZBX_PROCESS_TYPE_UNKNOWN;
}

const char	*get_program_type_string(unsigned char program_type)
{
	switch (program_type)
	{
		case ZBX_PROGRAM_TYPE_SERVER:
			return "server";
		case ZBX_PROGRAM_TYPE_PROXY_ACTIVE:
		case ZBX_PROGRAM_TYPE_PROXY_PASSIVE:
			return "proxy";
		case ZBX_PROGRAM_TYPE_AGENTD:
			return "agent";
		case ZBX_PROGRAM_TYPE_SENDER:
			return "sender";
		case ZBX_PROGRAM_TYPE_GET:
			return "get";
		default:
			return "unknown";
	}
}

const char	*zbx_permission_string(int perm)
{
	switch (perm)
	{
		case PERM_DENY:
			return "dn";
		case PERM_READ:
			return "r";
		case PERM_READ_WRITE:
			return "rw";
		default:
			return "unknown";
	}
}

const char	*zbx_agent_type_string(zbx_item_type_t item_type)
{
	switch (item_type)
	{
		case ITEM_TYPE_ZABBIX:
			return "Zabbix agent";
		case ITEM_TYPE_SNMP:
			return "SNMP agent";
		case ITEM_TYPE_IPMI:
			return "IPMI agent";
		case ITEM_TYPE_JMX:
			return "JMX agent";
		default:
			return "generic";
	}
}

const char	*zbx_item_value_type_string(zbx_item_value_type_t value_type)
{
	switch (value_type)
	{
		case ITEM_VALUE_TYPE_FLOAT:
			return "Numeric (float)";
		case ITEM_VALUE_TYPE_STR:
			return "Character";
		case ITEM_VALUE_TYPE_LOG:
			return "Log";
		case ITEM_VALUE_TYPE_UINT64:
			return "Numeric (unsigned)";
		case ITEM_VALUE_TYPE_TEXT:
			return "Text";
		default:
			return "unknown";
	}
}

const char	*zbx_interface_type_string(zbx_interface_type_t type)
{
	switch (type)
	{
		case INTERFACE_TYPE_AGENT:
			return "Zabbix agent";
		case INTERFACE_TYPE_SNMP:
			return "SNMP";
		case INTERFACE_TYPE_IPMI:
			return "IPMI";
		case INTERFACE_TYPE_JMX:
			return "JMX";
		case INTERFACE_TYPE_OPT:
			return "optional";
		case INTERFACE_TYPE_ANY:
			return "any";
		case INTERFACE_TYPE_UNKNOWN:
		default:
			return "unknown";
	}
}

const char	*zbx_sysinfo_ret_string(int ret)
{
	switch (ret)
	{
		case SYSINFO_RET_OK:
			return "SYSINFO_SUCCEED";
		case SYSINFO_RET_FAIL:
			return "SYSINFO_FAIL";
		default:
			return "SYSINFO_UNKNOWN";
	}
}

const char	*zbx_result_string(int result)
{
	switch (result)
	{
		case SUCCEED:
			return "SUCCEED";
		case FAIL:
			return "FAIL";
		case CONFIG_ERROR:
			return "CONFIG_ERROR";
		case NOTSUPPORTED:
			return "NOTSUPPORTED";
		case NETWORK_ERROR:
			return "NETWORK_ERROR";
		case TIMEOUT_ERROR:
			return "TIMEOUT_ERROR";
		case AGENT_ERROR:
			return "AGENT_ERROR";
		case GATEWAY_ERROR:
			return "GATEWAY_ERROR";
		case SIG_ERROR:
			return "SIG_ERROR";
		case SYSINFO_RET_FAIL:
			return "SYSINFO_RET_FAIL";
		default:
			return "unknown";
	}
}

const char	*zbx_item_logtype_string(unsigned char logtype)
{
	switch (logtype)
	{
		case ITEM_LOGTYPE_INFORMATION:
			return "Information";
		case ITEM_LOGTYPE_WARNING:
			return "Warning";
		case ITEM_LOGTYPE_ERROR:
			return "Error";
		case ITEM_LOGTYPE_FAILURE_AUDIT:
			return "Failure Audit";
		case ITEM_LOGTYPE_SUCCESS_AUDIT:
			return "Success Audit";
		case ITEM_LOGTYPE_CRITICAL:
			return "Critical";
		case ITEM_LOGTYPE_VERBOSE:
			return "Verbose";
		default:
			return "unknown";
	}
}

const char	*zbx_dservice_type_string(zbx_dservice_type_t service)
{
	switch (service)
	{
		case SVC_SSH:
			return "SSH";
		case SVC_LDAP:
			return "LDAP";
		case SVC_SMTP:
			return "SMTP";
		case SVC_FTP:
			return "FTP";
		case SVC_HTTP:
			return "HTTP";
		case SVC_POP:
			return "POP";
		case SVC_NNTP:
			return "NNTP";
		case SVC_IMAP:
			return "IMAP";
		case SVC_TCP:
			return "TCP";
		case SVC_AGENT:
			return "Zabbix agent";
		case SVC_SNMPv1:
			return "SNMPv1 agent";
		case SVC_SNMPv2c:
			return "SNMPv2c agent";
		case SVC_SNMPv3:
			return "SNMPv3 agent";
		case SVC_ICMPPING:
			return "ICMP ping";
		case SVC_HTTPS:
			return "HTTPS";
		case SVC_TELNET:
			return "Telnet";
		default:
			return "unknown";
	}
}

const char	*zbx_alert_type_string(unsigned char type)
{
	switch (type)
	{
		case ALERT_TYPE_MESSAGE:
			return "message";
		default:
			return "script";
	}
}

const char	*zbx_alert_status_string(unsigned char type, unsigned char status)
{
	switch (status)
	{
		case ALERT_STATUS_SENT:
			return (ALERT_TYPE_MESSAGE == type ? "sent" : "executed");
		case ALERT_STATUS_NOT_SENT:
			return "in progress";
		default:
			return "failed";
	}
}

const char	*zbx_escalation_status_string(unsigned char status)
{
	switch (status)
	{
		case ESCALATION_STATUS_ACTIVE:
			return "active";
		case ESCALATION_STATUS_SLEEP:
			return "sleep";
		case ESCALATION_STATUS_COMPLETED:
			return "completed";
		default:
			return "unknown";
	}
}

const char	*zbx_trigger_value_string(unsigned char value)
{
	switch (value)
	{
		case TRIGGER_VALUE_PROBLEM:
			return "PROBLEM";
		case TRIGGER_VALUE_OK:
			return "OK";
		default:
			return "unknown";
	}
}

const char	*zbx_trigger_state_string(unsigned char state)
{
	switch (state)
	{
		case TRIGGER_STATE_NORMAL:
			return "Normal";
		case TRIGGER_STATE_UNKNOWN:
			return "Unknown";
		default:
			return "unknown";
	}
}

const char	*zbx_item_state_string(unsigned char state)
{
	switch (state)
	{
		case ITEM_STATE_NORMAL:
			return "Normal";
		case ITEM_STATE_NOTSUPPORTED:
			return "Not supported";
		default:
			return "unknown";
	}
}

const char	*zbx_event_value_string(unsigned char source, unsigned char object, unsigned char value)
{
	if (EVENT_SOURCE_TRIGGERS == source || EVENT_SOURCE_SERVICE == source)
	{
		switch (value)
		{
			case EVENT_STATUS_PROBLEM:
				return "PROBLEM";
			case EVENT_STATUS_RESOLVED:
				return "RESOLVED";
			default:
				return "unknown";
		}
	}

	if (EVENT_SOURCE_INTERNAL == source)
	{
		switch (object)
		{
			case EVENT_OBJECT_TRIGGER:
				return zbx_trigger_state_string(value);
			case EVENT_OBJECT_ITEM:
			case EVENT_OBJECT_LLDRULE:
				return zbx_item_state_string(value);
		}
	}

	return "unknown";
}
