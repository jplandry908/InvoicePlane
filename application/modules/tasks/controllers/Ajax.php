<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * InvoicePlane
 *
 * @author      InvoicePlane Developers & Contributors
 * @copyright   Copyright (c) 2012 - 2018 InvoicePlane.com
 * @license     https://invoiceplane.com/license.txt
 * @link        https://invoiceplane.com
 */

#[AllowDynamicProperties]
class Ajax extends Admin_Controller
{
    /**
     * @param null|int $invoice_id
     */
    public function modal_task_lookups($invoice_id = null)
    {
        $default_item_tax_rate = get_setting('default_item_tax_rate');
        $data                  = [
            'default_item_tax_rate' => $default_item_tax_rate !== '' ?: 0,
            'tasks'                 => [],
        ];

        if ( ! empty($invoice_id)) {
            $this->load->model('mdl_tasks');
            $data['tasks'] = $this->mdl_tasks->get_tasks_to_invoice($invoice_id);
        }

        $this->layout->load_view('tasks/modal_task_lookups', $data);
    }

    public function process_task_selections()
    {
        $this->load->model('mdl_tasks');

        $tasks = $this->mdl_tasks->where_in('task_id', $this->input->post('task_ids'))->get()->result();
        foreach ($tasks as $task) {
            $task->task_price = format_amount($task->task_price);
        }

        echo json_encode($tasks);
    }
}
