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
class Mdl_Clients extends Response_Model
{
    public $table = 'ip_clients';

    public $primary_key = 'ip_clients.client_id';

    public $date_created_field = 'client_date_created';

    public $date_modified_field = 'client_date_modified';

    public function default_select(): void
    {
        $this->db->select('SQL_CALC_FOUND_ROWS ' . $this->table . '.*, CONCAT(' . $this->table . '.client_name, " ", ' . $this->table . '.client_surname) as client_fullname', false);
    }

    public function default_order_by(): void
    {
        $this->db->order_by('ip_clients.client_name');
    }

    public function validation_rules()
    {
        return [
            'client_title' => [
                'field' => 'client_title',
                'label' => trans('client_title'),
            ],
            'client_name' => [
                'field' => 'client_name',
                'label' => trans('client_name'),
                'rules' => 'required',
            ],
            'client_surname' => [
                'field' => 'client_surname',
                'label' => trans('client_surname'),
            ],
            'client_active' => [
                'field' => 'client_active',
            ],
            'client_language' => [
                'field' => 'client_language',
                'label' => trans('language'),
                'rules' => 'trim',
            ],
            'client_address_1' => [
                'field' => 'client_address_1',
            ],
            'client_address_2' => [
                'field' => 'client_address_2',
            ],
            'client_city' => [
                'field' => 'client_city',
            ],
            'client_state' => [
                'field' => 'client_state',
            ],
            'client_zip' => [
                'field' => 'client_zip',
            ],
            'client_country' => [
                'field' => 'client_country',
                'rules' => 'trim',
            ],
            'client_phone' => [
                'field' => 'client_phone',
            ],
            'client_fax' => [
                'field' => 'client_fax',
            ],
            'client_mobile' => [
                'field' => 'client_mobile',
            ],
            'client_email' => [
                'field' => 'client_email',
            ],
            'client_web' => [
                'field' => 'client_web',
            ],
            'client_company' => [
                'field' => 'client_company',
            ],
            'client_vat_id' => [
                'field' => 'client_vat_id',
            ],
            'client_tax_code' => [
                'field' => 'client_tax_code',
            ],
            'client_invoicing_contact' => [
                'field' => 'client_invoicing_contact',
                'rules' => 'trim',
            ],
            'client_einvoicing_version' => [
                'field' => 'client_einvoicing_version',
            ],
            'client_einvoicing_active' => [
                'field' => 'client_einvoicing_active',
            ],
            // SUMEX
            'client_birthdate' => [
                'field' => 'client_birthdate',
                'rules' => 'callback_convert_date',
            ],
            'client_gender' => [
                'field' => 'client_gender',
            ],
            'client_avs' => [
                'field' => 'client_avs',
                'label' => trans('sumex_ssn'),
                'rules' => 'callback_fix_avs',
            ],
            'client_insurednumber' => [
                'field' => 'client_insurednumber',
                'label' => trans('sumex_insurednumber'),
            ],
            'client_veka' => [
                'field' => 'client_veka',
                'label' => trans('sumex_veka'),
            ],
        ];
    }

    /**
     * @param int $amount
     *
     * @return mixed
     */
    public function get_latest($amount = 10)
    {
        return $this->mdl_clients
            ->where('client_active', 1)
            ->order_by('client_id', 'DESC')
            ->limit($amount)
            ->get()
            ->result();
    }

    /**
     * @return string
     */
    public function fix_avs($input)
    {
        if ($input != '') {
            if (preg_match('/(\d{3})\.(\d{4})\.(\d{4})\.(\d{2})/', $input, $matches)) {
                return $matches[1] . $matches[2] . $matches[3] . $matches[4];
            }

            if (preg_match('/^\d{13}$/', $input)) {
                return $input;
            }
        }

        return '';
    }

    public function convert_date($input)
    {
        $this->load->helper('date_helper');

        if ($input == '') {
            return '';
        }

        return date_to_mysql($input);
    }

    public function db_array()
    {
        $db_array = parent::db_array();

        if ( ! isset($db_array['client_active'])) {
            $db_array['client_active'] = 0;
        }

        return $db_array;
    }

    /**
     * @param int $id
     */
    public function delete($id): void
    {
        parent::delete($id);

        $this->load->helper('orphan');
        delete_orphans();
    }

    /**
     * Returns client_id of existing client.
     *
     * @param $client_name
     *
     * @return int|null
     */
    public function client_lookup($client_name)
    {
        $client = $this->mdl_clients->where('client_name', $client_name)->get();

        if ($client->num_rows()) {
            $client_id = $client->row()->client_id;
        } else {
            $db_array = [
                'client_name' => $client_name,
            ];

            $client_id = parent::save(null, $db_array);
        }

        return $client_id;
    }

    public function with_total()
    {
        $this->filter_select('IFnull((SELECT SUM(invoice_total) FROM ip_invoice_amounts WHERE invoice_id IN (SELECT invoice_id FROM ip_invoices WHERE ip_invoices.client_id = ip_clients.client_id)), 0) AS client_invoice_total', false);

        return $this;
    }

    public function with_total_paid()
    {
        $this->filter_select('IFnull((SELECT SUM(invoice_paid) FROM ip_invoice_amounts WHERE invoice_id IN (SELECT invoice_id FROM ip_invoices WHERE ip_invoices.client_id = ip_clients.client_id)), 0) AS client_invoice_paid', false);

        return $this;
    }

    public function with_total_balance()
    {
        $this->filter_select('IFnull((SELECT SUM(invoice_balance) FROM ip_invoice_amounts WHERE invoice_id IN (SELECT invoice_id FROM ip_invoices WHERE ip_invoices.client_id = ip_clients.client_id)), 0) AS client_invoice_balance', false);

        return $this;
    }

    public function is_inactive()
    {
        $this->filter_where('client_active', 0);

        return $this;
    }

    /**
     * @param $user_id
     *
     * @return $this
     */
    public function get_not_assigned_to_user($user_id)
    {
        $this->load->model('user_clients/mdl_user_clients');
        $clients = $this->mdl_user_clients->select('ip_user_clients.client_id')
            ->assigned_to($user_id)->get()->result();

        $assigned_clients = [];
        foreach ($clients as $client) {
            $assigned_clients[] = $client->client_id;
        }

        if ($assigned_clients !== []) {
            $this->where_not_in('ip_clients.client_id', $assigned_clients);
        }

        $this->is_active();

        return $this->get()->result();
    }

    public function is_active()
    {
        $this->filter_where('client_active', 1);

        return $this;
    }
}
