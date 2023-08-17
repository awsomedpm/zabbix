
#include "async_manager.h"
#include "async_worker.h"
#include "zbxtimekeeper.h"
#include "zbxstr.h"
#include "zbxalgo.h"
#include "poller.h"

ZBX_PTR_VECTOR_IMPL(interface_status, zbx_interface_status_t *)

struct zbx_async_manager
{
	zbx_async_worker_t		*workers;
	int				workers_num;
	int				program_type;

	zbx_uint64_t			revision;

	zbx_async_queue_t		queue;

	zbx_timekeeper_t		*timekeeper;

	/*zbx_dc_um_shared_handle_t	*um_handle;*/
};

zbx_async_manager_t	*zbx_async_manager_create(int workers_num, zbx_async_notify_cb_t finished_cb,
		void *finished_data, zbx_thread_poller_args *poller_args_in, char **error)
{
	int			i, ret = FAIL, started_num = 0;
	time_t			time_start;
	struct timespec		poll_delay = {0, 1e8};
	zbx_async_manager_t	*manager;

	zabbix_log(LOG_LEVEL_DEBUG, "In %s() workers:%d", __func__, workers_num);

	manager = (zbx_async_manager_t *)zbx_malloc(NULL, sizeof(zbx_async_manager_t));
	memset(manager, 0, sizeof(zbx_async_manager_t));

	if (SUCCEED != async_task_queue_init(&manager->queue, poller_args_in, error))
		goto out;

	manager->workers_num = workers_num;
	manager->workers = (zbx_async_worker_t *)zbx_calloc(NULL, (size_t)workers_num, sizeof(zbx_async_worker_t));

	for (i = 0; i < workers_num; i++)
	{
		if (SUCCEED != async_worker_init(&manager->workers[i], i + 1, &manager->queue, error))
			goto out;

		async_worker_set_finished_cb(&manager->workers[i], finished_cb, finished_data);
	}

	/* wait for threads to start */
	time_start = time(NULL);

#define PP_STARTUP_TIMEOUT	10

	while (started_num != workers_num)
	{
		if (time_start + PP_STARTUP_TIMEOUT < time(NULL))
		{
			*error = zbx_strdup(NULL, "timeout occurred while waiting for workers to start");
			goto out;
		}

		pthread_mutex_lock(&manager->queue.lock);
		started_num = manager->queue.workers_num;
		pthread_mutex_unlock(&manager->queue.lock);

		nanosleep(&poll_delay, NULL);
	}

#undef PP_STARTUP_TIMEOUT

	ret = SUCCEED;
out:
	if (FAIL == ret)
	{
		for (i = 0; i < manager->workers_num; i++)
			async_worker_stop(&manager->workers[i]);

		async_task_queue_destroy(&manager->queue);
		zbx_free(manager);

		manager = NULL;
	}

	zabbix_log(LOG_LEVEL_DEBUG, "End of %s() ret:%s error:%s", __func__, zbx_result_string(ret),
			ZBX_NULL2EMPTY_STR(*error));

	return manager;
}

void	zbx_async_manager_free(zbx_async_manager_t *manager)
{
	int	i;

	async_task_queue_lock(&manager->queue);
	for (i = 0; i < manager->workers_num; i++)
		async_worker_stop(&manager->workers[i]);

	async_task_queue_notify_all(&manager->queue);
	async_task_queue_unlock(&manager->queue);

	for (i = 0; i < manager->workers_num; i++)
		async_worker_destroy(&manager->workers[i]);

	zbx_free(manager->workers);

	async_task_queue_destroy(&manager->queue);

	zbx_free(manager);
}

void	zbx_async_manager_queue_get(zbx_async_manager_t *manager, zbx_vector_poller_item_t *poller_items)
{
	async_task_queue_lock(&manager->queue);

	if (0 != manager->queue.poller_items.values_num)
	{
		zbx_vector_poller_item_append_array(poller_items, manager->queue.poller_items.values,
				manager->queue.poller_items.values_num);
		zbx_vector_poller_item_clear(&manager->queue.poller_items);
	}

	async_task_queue_unlock(&manager->queue);
}

void	zbx_async_manager_requeue(zbx_async_manager_t *manager, zbx_uint64_t itemid, int errcode, int lastclock)
{
	async_task_queue_lock(&manager->queue);

	zbx_vector_uint64_append(&manager->queue.itemids, itemid);
	zbx_vector_int32_append(&manager->queue.errcodes, errcode);
	zbx_vector_int32_append(&manager->queue.lastclocks, lastclock);

	async_task_queue_unlock(&manager->queue);
}

void	zbx_async_manager_requeue_flush(zbx_async_manager_t *manager)
{
	async_task_queue_lock(&manager->queue);

	if (0 != manager->queue.itemids.values_num)
		async_task_queue_notify(&manager->queue);

	async_task_queue_unlock(&manager->queue);
}

void	zbx_async_manager_queue_sync(zbx_async_manager_t *manager)
{
	async_task_queue_lock(&manager->queue);

	manager->queue.check_queue = 1;
	async_task_queue_notify(&manager->queue);

	async_task_queue_unlock(&manager->queue);
}

void	zbx_async_manager_interfaces_flush(zbx_async_manager_t *manager, zbx_hashset_t *interfaces)
{
	zbx_hashset_iter_t	iter;
	zbx_interface_status_t	*interface_status;

	async_task_queue_lock(&manager->queue);

	zbx_hashset_iter_reset(interfaces, &iter);

	while (NULL != (interface_status = (zbx_interface_status_t *)zbx_hashset_iter_next(&iter)))
	{
		zbx_interface_status_t	*interface_status_ptr = zbx_malloc(NULL, sizeof(zbx_interface_status_t));

		*interface_status_ptr = *interface_status;

		zbx_vector_interface_status_append(&manager->queue.interfaces, interface_status_ptr);
		interface_status->key_orig = NULL;
		interface_status->error = NULL;
	}

	async_task_queue_unlock(&manager->queue);

	zbx_hashset_clear(interfaces);
}

void	zbx_interface_status_clean(zbx_interface_status_t *interface_status)
{
	zbx_free(interface_status->key_orig);
	zbx_free(interface_status->error);
}

void	zbx_interface_status_free(zbx_interface_status_t *interface_status)
{
	zbx_interface_status_clean(interface_status);
	zbx_free(interface_status);
}
