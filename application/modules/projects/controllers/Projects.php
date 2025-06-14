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
class Projects extends Admin_Controller
{
    /**
     * Projects constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('mdl_projects');
    }

    /**
     * @param int $page
     */
    public function index($page = 0)
    {
        $this->mdl_projects->paginate(site_url('projects/index'), $page);
        $projects = $this->mdl_projects->result();

        $this->layout->set(
            [
                'filter_display'     => true,
                'filter_placeholder' => trans('filter_projects'),
                'filter_method'      => 'filter_projects',
                'projects'           => $projects,
            ]
        );
        $this->layout->buffer('content', 'projects/index');
        $this->layout->render();
    }

    public function form($id = null)
    {
        if ($this->input->post('btn_cancel')) {
            redirect('projects');
        }

        $this->filter_input();  // <<<--- filters _POST array for nastiness

        if ($this->mdl_projects->run_validation()) {
            $this->mdl_projects->save($id);
            redirect('projects');
        }

        if ($id && ! $this->input->post('btn_submit') && ! $this->mdl_projects->prep_form($id)) {
            show_404();
        }

        $this->load->model('clients/mdl_clients');

        $this->layout->set(
            [
                'project' => $this->mdl_projects->get_by_id($id),
                'clients' => $this->mdl_clients->where('client_active', 1)->get()->result(),
            ]
        );

        $this->layout->buffer('content', 'projects/form');
        $this->layout->render();
    }

    public function view($project_id)
    {
        if ($this->input->post('btn_cancel')) {
            redirect('projects');
        }

        $this->load->model('projects/mdl_projects');
        $project = $this->mdl_projects->get_by_id($project_id);

        if ( ! $project) {
            show_404();
        }

        $this->load->model('tasks/mdl_tasks');

        $this->layout->set([
            'project'       => $project,
            'tasks'         => $this->mdl_projects->get_tasks($project->project_id),
            'task_statuses' => $this->mdl_tasks->statuses(),
        ]);
        $this->layout->buffer('content', 'projects/view');
        $this->layout->render();
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
        $this->load->model('tasks/mdl_tasks');
        $this->mdl_tasks->update_on_project_delete($id);

        $this->mdl_projects->delete($id);
        redirect('projects');
    }
}
